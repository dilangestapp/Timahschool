<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HomepageMessage;
use App\Models\HomepageSetting;
use App\Models\SchoolClass;
use Throwable;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::active()->orderBy('order')->get();

        $classGroups = $classes->groupBy('level');

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
                    ->take(18)
                    ->get();
            }
        } catch (Throwable $e) {
            // Fallback silencieux pour éviter tout 500 si DB/migrations indisponibles au boot.
            $homepage = HomepageSetting::defaults();
            $messages = collect();
        }

        $featuredClassIds = collect($homepage['featured_class_ids'] ?? [])->filter()->all();
        $featuredClasses = ! empty($featuredClassIds)
            ? $classes->whereIn('id', $featuredClassIds)->values()
            : $classes->take(9)->values();

        return view('public.home', compact('classes', 'classGroups', 'classGroupLabels', 'homepage', 'messages', 'featuredClasses'));
    }
}
