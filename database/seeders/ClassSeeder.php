<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            [
                'name' => '6ème',
                'level' => 'enseignement_general',
                'description' => 'Classe de 6ème - enseignement général',
                'order' => 1,
            ],
            [
                'name' => '5ème',
                'level' => 'enseignement_general',
                'description' => 'Classe de 5ème - enseignement général',
                'order' => 2,
            ],
            [
                'name' => '4ème',
                'level' => 'enseignement_general',
                'description' => 'Classe de 4ème - enseignement général',
                'order' => 3,
            ],
            [
                'name' => '3ème',
                'level' => 'enseignement_general',
                'description' => 'Classe de 3ème - enseignement général',
                'order' => 4,
            ],
            [
                'name' => 'Seconde A',
                'level' => 'enseignement_general',
                'description' => 'Classe de Seconde A - enseignement général',
                'order' => 5,
            ],
            [
                'name' => 'Seconde C',
                'level' => 'enseignement_general',
                'description' => 'Classe de Seconde C - enseignement général',
                'order' => 6,
            ],
            [
                'name' => 'Seconde D',
                'level' => 'enseignement_general',
                'description' => 'Classe de Seconde D - enseignement général',
                'order' => 7,
            ],
            [
                'name' => 'Première A',
                'level' => 'enseignement_general',
                'description' => 'Classe de Première A - enseignement général',
                'order' => 8,
            ],
            [
                'name' => 'Première C',
                'level' => 'enseignement_general',
                'description' => 'Classe de Première C - enseignement général',
                'order' => 9,
            ],
            [
                'name' => 'Première D',
                'level' => 'enseignement_general',
                'description' => 'Classe de Première D - enseignement général',
                'order' => 10,
            ],
            [
                'name' => 'Terminale A',
                'level' => 'enseignement_general',
                'description' => 'Classe de Terminale A - enseignement général',
                'order' => 11,
            ],
            [
                'name' => 'Terminale C',
                'level' => 'enseignement_general',
                'description' => 'Classe de Terminale C - enseignement général',
                'order' => 12,
            ],
            [
                'name' => 'Terminale D',
                'level' => 'enseignement_general',
                'description' => 'Classe de Terminale D - enseignement général',
                'order' => 13,
            ],
            [
                'name' => '1ère Année',
                'level' => 'enseignement_technique',
                'description' => 'Classe de 1ère Année - enseignement technique',
                'order' => 101,
            ],
            [
                'name' => '2ème Année',
                'level' => 'enseignement_technique',
                'description' => 'Classe de 2ème Année - enseignement technique',
                'order' => 102,
            ],
            [
                'name' => '3ème Année',
                'level' => 'enseignement_technique',
                'description' => 'Classe de 3ème Année - enseignement technique',
                'order' => 103,
            ],
            [
                'name' => '4ème Année',
                'level' => 'enseignement_technique',
                'description' => 'Classe de 4ème Année - enseignement technique',
                'order' => 104,
            ],
            [
                'name' => '5ème Année',
                'level' => 'enseignement_technique',
                'description' => 'Classe de 5ème Année - enseignement technique',
                'order' => 105,
            ],
            [
                'name' => '6ème Année',
                'level' => 'enseignement_technique',
                'description' => 'Classe de 6ème Année - enseignement technique',
                'order' => 106,
            ],
            [
                'name' => '7ème Année',
                'level' => 'enseignement_technique',
                'description' => 'Classe de 7ème Année - enseignement technique',
                'order' => 107,
            ],
        ];

        $activeSlugs = [];

        foreach ($classes as $class) {
            $slug = Str::slug($class['name']);
            $activeSlugs[] = $slug;

            SchoolClass::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $class['name'],
                    'description' => $class['description'],
                    'level' => $class['level'],
                    'order' => $class['order'],
                    'is_active' => true,
                ]
            );
        }

        SchoolClass::whereNotIn('slug', $activeSlugs)->update(['is_active' => false]);

        $this->command->info('Classes générales et techniques créées/mises à jour avec succès.');
    }
}
