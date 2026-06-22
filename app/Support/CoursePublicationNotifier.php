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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CoursePublicationNotifier
{
    public function coursePublished(Course $course, ?User $publisher = null): void
    {
        try {
            $this->runCoursePublished($course, $publisher);
        } catch (\Throwable $e) {
            Log::warning('Course publication notification skipped', [
                'course_id' => $course->id ?? null,
                'publisher_id' => $publisher->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function runCoursePublished(Course $course, ?User $publisher = null): void
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
            try {
                $student = $profile->user;
                $this->ensureCourseProgress($course, $student);
                $this->notifyStudent($course, $student);

                $parent = $this->ensureParentForStudent($profile, $student);
                if ($parent) {
                    $this->notifyParent($course, $parent, $student);
                }
            } catch (\Throwable $e) {
                Log::warning('Course publication notification skipped for student', [
                    'course_id' => $course->id ?? null,
                    'student_profile_id' => $profile->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($publisher) {
            $this->notifyTeacher($course, $publisher, $students->count());
        }
    }

    public function courseOpened(Course $course, User $student): void
    {
        try {
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
        } catch (\Throwable $e) {
            Log::warning('Course opened tracking skipped', ['course_id' => $course->id ?? null, 'student_id' => $student->id ?? null, 'error' => $e->getMessage()]);
        }
    }

    public function courseCompleted(Course $course, User $student): void
    {
        try {
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
        } catch (\Throwable $e) {
            Log::warning('Course completed tracking skipped', ['course_id' => $course->id ?? null, 'student_id' => $student->id ?? null, 'error' => $e->getMessage()]);
        }
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

        $name = trim((string) ($profile->parent_name ?? ''));
        $phone = $this->normalizePhone((string) ($profile->parent_phone ?? ''));

        if ($phone === '') {
            return null;
        }

        $parent = User::query()->where('phone', $phone)->first();
        if (!$parent) {
            $parent = User::query()->create($this->parentUserPayload($name !== '' ? $name : 'Parent', $phone));
        }

        $this->attachParentRole($parent);

        $profilePayload = $this->filterForTable('parent_profiles', [
            'user_id' => $parent->id,
            'full_name' => $name !== '' ? $name : ($parent->full_name ?: $parent->name),
            'phone' => $phone,
            'status' => defined(ParentProfile::class.'::STATUS_ACTIVE') ? ParentProfile::STATUS_ACTIVE : 'active',
            'activated_at' => DB::raw('COALESCE(activated_at, NOW())'),
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        if (!empty($profilePayload)) {
            ParentProfile::query()->updateOrCreate(['user_id' => $parent->id], $profilePayload);
        }

        $pivotPayload = $this->filterForTable('student_parent', [
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'relationship' => 'parent',
            'is_primary' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('student_parent')->updateOrInsert(
            ['student_id' => $student->id, 'parent_id' => $parent->id],
            collect($pivotPayload)->except(['student_id', 'parent_id'])->all()
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

        $payload = $this->filterForTable('mobile_notifications', [
            'user_id' => $userId,
            'school_class_id' => $course->school_class_id,
            'audience' => $audience,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'target_type' => 'course',
            'target_id' => $course->id,
            'data' => json_encode(array_merge([
                'course_id' => $course->id,
                'subject' => $course->subject?->name,
                'class' => $course->schoolClass?->name,
            ], $extra), JSON_UNESCAPED_UNICODE),
            'published_at' => now(),
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        if (empty($payload)) {
            return;
        }

        $keys = $this->filterForTable('mobile_notifications', [
            'user_id' => $userId,
            'type' => $type,
            'target_type' => 'course',
            'target_id' => $course->id,
        ]);

        if (count($keys) === 4) {
            DB::table('mobile_notifications')->updateOrInsert($keys, collect($payload)->except(array_keys($keys))->all());
            return;
        }

        DB::table('mobile_notifications')->insert($payload);
    }

    private function courseTitleLine(Course $course): string
    {
        $subject = $course->subject?->name ?: 'Cours';
        return $subject . ' : ' . $course->title;
    }

    private function parentUserPayload(string $name, string $phone): array
    {
        return $this->filterForTable('users', [
            'name' => $name,
            'full_name' => $name,
            'username' => $this->uniqueUsername('parent' . preg_replace('/\D+/', '', $phone)),
            'phone' => $phone,
            'email' => $this->uniqueEmail('parent_' . (preg_replace('/\D+/', '', $phone) ?: time()) . '@timahacademy.local'),
            'status' => 'active',
            'password' => Hash::make($phone),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachParentRole(User $user): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user')) {
            return;
        }

        $role = Role::query()->where('name', 'parent')->first();
        if (!$role) {
            $role = Role::query()->create($this->filterForTable('roles', [
                'name' => 'parent',
                'guard_name' => 'web',
                'display_name' => 'Parent',
                'description' => 'Compte parent TIMAH ACADEMY',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        if (method_exists($user, 'roles')) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
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

    private function filterForTable(string $table, array $payload): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $columns = Schema::getColumnListing($table);
        return collect($payload)
            ->filter(fn ($value, $column) => in_array($column, $columns, true))
            ->all();
    }
}
