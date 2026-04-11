<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'full_name' => 'Administrateur TIMAHSCHOOL',
            'email' => 'admin@timahschool.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $admin->roles()->attach($adminRole);

        $this->command->info('=========================================');
        $this->command->info('COMPTE ADMIN CRÉÉ');
        $this->command->info('Email: admin@timahschool.com');
        $this->command->info('Mot de passe: password123');
        $this->command->info('=========================================');
    }
}