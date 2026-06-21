<?php

namespace App\Support;

use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CoursePublicationNotifier
{
    public function coursePublished(Course $course, ?User $publisher = null): void
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $students = StudentProfile::query()
            ->with(['user', 'schoolClass'])
            ->where('school_class_id', $course->school_class_id)
            ->get()
            ->filter(fn ($profile) => $profile->user instanceof User);

        foreach ($students as $profile) {
            $student = $profile->user;
            $this->ensureCourseProgress($course, $student);
            $this->notifyStudent($course, $student);

            $parent = $this->ensureParentForStudent($profile, $student);
            if ($parent) {
                $this->notifyParent($course, $parent, $student);
            }
        }

        if ($publisher) {
            $this->notifyTeacher($course, $publisher, $students->count());
        }
    }

    public function courseOpened(Course $course, User $student): void
    {
        if (!Schema::hasTable('course_progress')) {
            return;
        }

        CourseProgress::query()->updateOrCreate(
            ['course_id' => $course->id, 'student_id' => $student->id],
            [
                'status' => CourseProgress::STATUS_OPENED,
                'progress_percent' => DB::raw('GREATEST(progress_percent, 25)'),
                'opened_at' => DB::raw('COALESCE(opened_at, NOW())'),
                'last_seen_at' => now(),
            ]
        );
    }

    public function courseCompleted(Course $course, User $student): void
    {
        if (!Schema::hasTable('course_progress')) {
            return;
        }

        CourseProgress::query()->updateOrCreate(
            ['course_id' => $course->id, 'student_id' => $student->id],
            [
                'status' => CourseProgress::STATUS_COMPLETED,
                'progress_percent' => 100,
                'opened_at' => DB::raw('COALESCE(opened_at, NOW())'),
                'last_seen_at' => now(),
                'completed_at' => now(),
            ]
        );
    }

    private function ensureCourseProgress(Course $course, User $student): void
    {
        if (!Schema::hasTable('course_progress')) {
            return;
        }

        CourseProgress::query()->firstOrCreate(
            ['course_id' => $course->id, 'student_id' => $student->id],
            ['status' => CourseProgress::STATUS_NOT_STARTED, 'progress_percent' => 0]
        );
    }

    private function ensureParentForStudent(StudentProfile $profile, User $student): ?User
    {
        if (!Schema::hasTable('parent_profiles') || !Schema::hasTable('student_parent')) {
            return null;
        }

        $name = trim((string) ($profile->parent_name ?: $student->learningProfile?->parent_name));
        $phone = $this->normalizePhone((string) ($profile->parent_phone ?: $student->learningProfile?->parent_phone));

        if ($phone === '') {
            return null;
        }

        $parent = User::query()->where('phone', $phone)->first();
        if (!$parent) {
            $parent = User::query()->create($this->parentUserPayload($name !== '' ? $name : 'Parent', $phone));
        }

        $this->attachParentRole($parent);

        ParentProfile::query()->updateOrCreate(
            ['user_id' => $parent->id],
            [
                'full_name' => $name !== '' ? $name : ($parent->full_name ?: $parent->name),
                'phone' => $phone,
                'status' => ParentProfile::STATUS_ACTIVE,
                'activated_at' => DB::raw('COALESCE(activated_at, NOW())'),
            ]
        );

        DB::table('student_parent')->updateOrInsert(
            ['student_id' => $student->id, 'parent_id' => $parent->id],
            ['relationship' => 'parent', 'is_primary' => true, 'updated_at' => now(), 'created_at' => now()]
        );

        return $parent;
    }

    private function notifyStudent(Course $course, User $student): void
    {
        $this->notify($student->id, 'student', 'course_published', 'Nouveau cours disponible', $this->courseTitleLine($course), $course);
    }

    private function notifyParent(Course $course, User $parent, User $student): void
    {
        $studentName = $student->full_name ?: $student->name ?: 'votre enfant';
        $message = 'Un nouveau cours a été publié pour ' . $studentName . ' : ' . $this->courseTitleLine($course);
        $this->notify($parent->id, 'parent', 'course_published_parent', 'Nouveau cours pour votre enfant', $message, $course, ['student_id' => $student->id, 'student_name' => $studentName]);
    }

    private function notifyTeacher(Course $course, User $teacher, int $studentCount): void
    {
        $message = 'Votre cours « ' . $course->title . ' » a été publié pour ' . $studentCount . ' élève(s).';
        $this->notify($teacher->id, 'teacher', 'course_published_teacher', 'Cours publié avec succès', $message, $course);
    }

    private function notify(int $userId, string $audience, string $type, string $title, string $message, Course $course, array $extra = []): void
    {
        if (!Schema::hasTable('mobile_notifications')) {
            return;
        }

        $payload = [
            'user_id' => $userId,
            'school_class_id' => $course->school_class_id,
            'audience' => $audience,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'target_type' => 'course',
            'target_id' => $course->id,
            'published_at' => now(),
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if (Schema::hasColumn('mobile_notifications', 'data')) {
            $payload['data'] = json_encode(array_merge([
                'course_id' => $course->id,
                'subject' => $course->subject?->name,
                'class' => $course->schoolClass?->name,
            ], $extra), JSON_UNESCAPED_UNICODE);
        }

        $updates = collect($payload)->except(['created_at'])->all();

        DB::table('mobile_notifications')->updateOrInsert(
            ['user_id' => $userId, 'type' => $type, 'target_type' => 'course', 'target_id' => $course->id],
            $updates
        );
    }

    private function courseTitleLine(Course $course): string
    {
        $subject = $course->subject?->name ?: 'Cours';
        return $subject . ' : ' . $course->title;
    }

    private function parentUserPayload(string $name, string $phone): array
    {
        $columns = Schema::getColumnListing('users');
        $payload = [];
        $put = function (string $column, mixed $value) use (&$payload, $columns) {
            if (in_array($column, $columns, true)) {
                $payload[$column] = $value;
            }
        };

        $put('name', $name);
        $put('full_name', $name);
        $put('username', $this->uniqueUsername('parent' . preg_replace('/\D+/', '', $phone)));
        $put('phone', $phone);
        $put('email', $this->uniqueEmail('parent_' . (preg_replace('/\D+/', '', $phone) ?: time()) . '@timahacademy.local'));
        $put('status', 'active');
        $put('password', Hash::make($phone));

        return $payload;
    }

    private function attachParentRole(User $user): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user')) {
            return;
        }

        $role = Role::query()->where('name', 'parent')->first();
        if (!$role) {
            $payload = ['name' => 'parent'];
            foreach (['guard_name' => 'web', 'display_name' => 'Parent', 'description' => 'Compte parent TIMAH ACADEMY'] as $column => $value) {
                if (Schema::hasColumn('roles', $column)) {
                    $payload[$column] = $value;
                }
            }
            $role = Role::query()->create($payload);
        }

        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    private function uniqueUsername(string $base): string
    {
        $base = trim($base) !== '' ? $base : 'parent';
        $username = $base;
        $i = 1;
        while (User::query()->where('username', $username)->exists()) {
            $i++;
            $username = $base . '_' . $i;
        }
        return $username;
    }

    private function uniqueEmail(string $base): string
    {
        $email = $base;
        $i = 1;
        while (User::query()->where('email', $email)->exists()) {
            $email = preg_replace('/@/', '_' . (++$i) . '@', $base, 1) ?: ('parent_' . time() . '_' . $i . '@timahacademy.local');
        }
        return $email;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', trim($phone));
    }
}
