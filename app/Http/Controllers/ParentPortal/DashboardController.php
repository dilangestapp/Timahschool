<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $parentUser = auth()->user();

        $children = $parentUser->children()
            ->with(['studentProfile.schoolClass', 'learningProfile'])
            ->orderBy('full_name')
            ->orderBy('name')
            ->get();

        $childIds = $children->pluck('id')->filter()->values();
        $courseProgress = collect();

        if (Schema::hasTable('course_progress') && $childIds->isNotEmpty()) {
            $courseProgress = DB::table('course_progress')
                ->join('courses', 'courses.id', '=', 'course_progress.course_id')
                ->leftJoin('subjects', 'subjects.id', '=', 'courses.subject_id')
                ->whereIn('course_progress.student_id', $childIds)
                ->select(['course_progress.*', 'courses.title as course_title', 'courses.published_at', 'subjects.name as subject_name'])
                ->orderByDesc('courses.published_at')
                ->limit(80)
                ->get()
                ->groupBy('student_id');
        }

        $notifications = collect();
        if (Schema::hasTable('mobile_notifications')) {
            $notifications = DB::table('mobile_notifications')
                ->where('user_id', $parentUser->id)
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(30)
                ->get();
        }

        $allProgress = $courseProgress->flatten(1);
        $stats = [
            'children' => $children->count(),
            'courses_total' => $allProgress->count(),
            'courses_completed' => $allProgress->where('status', 'completed')->count(),
            'courses_not_started' => $allProgress->where('status', 'not_started')->count(),
            'notifications' => $notifications->count(),
        ];

        return view('parent.dashboard', compact('parentUser', 'children', 'courseProgress', 'notifications', 'stats'));
    }
}
