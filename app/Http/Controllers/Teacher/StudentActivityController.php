<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\TeacherAssignment;
use App\Models\UserLoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StudentActivityController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();
        $search = trim((string) $request->get('q', ''));
        $eventFilter = trim((string) $request->get('event', ''));

        $classIds = TeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->pluck('school_class_id')
            ->filter()
            ->unique()
            ->values();

        $profilesQuery = StudentProfile::query()
            ->with(['user.roles', 'user.role', 'schoolClass'])
            ->whereIn('school_class_id', $classIds);

        if ($search !== '') {
            $profilesQuery->whereHas('user', function ($query) use ($search) {
                foreach (['name', 'full_name', 'username', 'email', 'phone', 'status'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $query->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        $students = $profilesQuery
            ->get()
            ->filter(fn ($profile) => $profile->user)
            ->sortBy(function ($profile) {
                return mb_strtolower((string) ($profile->schoolClass->name ?? '') . ' ' . ($profile->user->full_name ?? $profile->user->name ?? $profile->user->username ?? ''));
            })
            ->values();

        $studentUserIds = $students->pluck('user_id')->filter()->unique()->values();

        $connectedStudents = $students->filter(function ($profile) {
            $lastLogin = $profile->user?->last_login_at;

            if (!$lastLogin) {
                return false;
            }

            return $lastLogin->greaterThanOrEqualTo(now()->subMinutes(30));
        })->values();

        $activities = Schema::hasTable('user_login_activities')
            ? UserLoginActivity::query()
                ->with(['user.studentProfile.schoolClass'])
                ->whereIn('user_id', $studentUserIds)
                ->when($eventFilter !== '', fn ($query) => $query->where('event', $eventFilter))
                ->latest('occurred_at')
                ->limit(150)
                ->get()
            : collect();

        return view('teacher.students.activity', compact(
            'students',
            'connectedStudents',
            'activities',
            'classIds',
            'search',
            'eventFilter'
        ));
    }
}
