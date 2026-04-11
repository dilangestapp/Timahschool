<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\SchoolClass;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Mathématiques', 'color' => '#3B82F6', 'icon' => 'calculator'],
            ['name' => 'Français', 'color' => '#EF4444', 'icon' => 'book'],
            ['name' => 'Anglais', 'color' => '#10B981', 'icon' => 'globe'],
            ['name' => 'Physique-Chimie', 'color' => '#8B5CF6', 'icon' => 'beaker'],
            ['name' => 'SVT', 'color' => '#059669', 'icon' => 'leaf'],
            ['name' => 'Histoire-Géographie', 'color' => '#F59E0B', 'icon' => 'map'],
            ['name' => 'Informatique', 'color' => '#6366F1', 'icon' => 'computer'],
        ];

        foreach ($subjects as $subject) {
            Subject::create(array_merge($subject, [
                'slug' => \Illuminate\Support\Str::slug($subject['name']),
                'is_active' => true,
            ]));
        }

        // Associer toutes les matières à toutes les classes
        $classes = SchoolClass::all();
        $subjects = Subject::all();

        foreach ($classes as $class) {
            $class->subjects()->attach($subjects->pluck('id'), ['is_active' => true]);
        }

        $this->command->info('Matières créées et associées aux classes !');
    }
}