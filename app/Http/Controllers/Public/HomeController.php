<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HomepageMessage;
use App\Models\HomepageSetting;
use App\Models\SchoolClass;
use App\Support\ExamCountdown;
use Throwable;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        try {
            $classes = SchoolClass::active()->orderBy('order')->get();
        } catch (Throwable $e) {
            $classes = collect();
        }

        try {
            if ($classes->isEmpty() && Schema::hasTable('school_classes')) {
                $classes = SchoolClass::query()
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->orderBy('name')
                    ->get();
            }
        } catch (Throwable $e) {
            $classes = collect();
        }

        $classGroups = $classes->groupBy(function ($class) {
            $system = $class->system ?? null;

            return $system === 'enseignement_technique'
                ? 'enseignement_technique'
                : 'enseignement_general';
        });

        $classGroupLabels = [
            'enseignement_general' => 'Enseignement général',
            'enseignement_technique' => 'Enseignement technique',
        ];

        $homepage = HomepageSetting::defaults();
        $messages = collect();

        try {
            if (Schema::hasTable('homepage_settings')) {
                $homepage = HomepageSetting::homepagePayload();
            }

            if (Schema::hasTable('homepage_messages')) {
                $messages = HomepageMessage::query()
                    ->where('is_published', true)
                    ->orderByDesc('is_featured')
                    ->orderBy('sort_order')
                    ->latest()
                    ->take(6)
                    ->get();
            }
        } catch (Throwable $e) {
            $homepage = HomepageSetting::defaults();
            $messages = collect();
        }

        $featuredClassIds = collect($homepage['featured_class_ids'] ?? [])->filter()->all();
        $featuredClasses = ! empty($featuredClassIds)
            ? $classes->whereIn('id', $featuredClassIds)->values()
            : $classes->take(6)->values();

        $homeExamCountdowns = collect(ExamCountdown::all())
            ->only([0, 1, 2, 3])
            ->values();

        return view('public.home_slim', compact(
            'classes',
            'classGroups',
            'classGroupLabels',
            'homepage',
            'messages',
            'featuredClasses',
            'homeExamCountdowns'
        ));
    }
}
