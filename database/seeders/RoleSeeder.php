<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => [
                'display_name' => 'Administrateur',
                'description' => 'Administration de la plateforme',
            ],
            'teacher' => [
                'display_name' => 'Enseignant',
                'description' => 'Gestion des cours et TD',
            ],
            'technical_supervisor' => [
                'display_name' => 'Responsable Enseignement Technique',
                'description' => 'Suivi pedagogique de la section technique',
            ],
            'department_responsible' => [
                'display_name' => 'Responsable de departement',
                'description' => 'Suivi pedagogique d un departement ou d une filiere',
            ],
            'student' => [
                'display_name' => 'Eleve',
                'description' => 'Acces aux cours et TD',
            ],
        ];

        foreach ($roles as $name => $role) {
            Role::query()->updateOrCreate(
                ['name' => $name],
                [
                    'guard_name' => 'web',
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                ]
            );
        }

        $this->command->info('Roles mis a jour.');
    }
}
