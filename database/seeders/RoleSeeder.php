<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les rôles
        Role::create([
            'name' => 'admin',
            'display_name' => 'Administrateur',
            'description' => 'Contrôle total de la plateforme',
        ]);

        Role::create([
            'name' => 'teacher',
            'display_name' => 'Enseignant',
            'description' => 'Gestion des contenus et réponses aux questions',
        ]);

        Role::create([
            'name' => 'student',
            'display_name' => 'Élève',
            'description' => 'Accès aux cours et apprentissage',
        ]);

        $this->command->info('Rôles créés avec succès !');
    }
}