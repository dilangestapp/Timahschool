<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MakeAdminUser extends Command
{
    protected $signature = 'timahschool:make-admin
                            {username? : Nom d\'utilisateur admin}
                            {--password= : Mot de passe admin}
                            {--name= : Nom complet}
                            {--email= : Adresse email}';

    protected $description = 'Créer ou promouvoir un utilisateur administrateur pour TIMAH SCHOOL';

    public function handle(): int
    {
        $username = (string) ($this->argument('username') ?: $this->ask('Nom d\'utilisateur admin'));
        $password = (string) ($this->option('password') ?: $this->secret('Mot de passe admin'));
        $name = (string) ($this->option('name') ?: $this->ask('Nom complet', $username));
        $email = (string) ($this->option('email') ?: $this->ask('Email', $username . '@timahschool.local'));

        if ($username === '' || $password === '') {
            $this->error('Le nom d\'utilisateur et le mot de passe sont obligatoires.');
            return self::FAILURE;
        }

        $user = User::query()->where('username', $username)->first();

        if (!$user) {
            $payload = [
                'username' => $username,
                'name' => $name,
                'full_name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ];

            if (Schema::hasColumn('users', 'status')) {
                $payload['status'] = 'active';
            }

            $user = User::create($payload);
            $this->info('Utilisateur créé.');
        } else {
            $updates = [
                'name' => $name,
                'full_name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ];

            if (Schema::hasColumn('users', 'status') && empty($user->status)) {
                $updates['status'] = 'active';
            }

            $user->forceFill($updates)->save();
            $this->info('Utilisateur existant mis à jour.');
        }

        $adminRoleId = $this->resolveAdminRoleId();

        if ($adminRoleId === null) {
            $this->error('Impossible de créer ou trouver le rôle admin.');
            return self::FAILURE;
        }

        if (Schema::hasColumn('users', 'role_id')) {
            $user->forceFill(['role_id' => $adminRoleId])->save();
        }

        if (Schema::hasTable('role_user')) {
            $pivot = DB::table('role_user')
                ->where('user_id', $user->id)
                ->where('role_id', $adminRoleId);

            if (!$pivot->exists()) {
                $pivotPayload = [
                    'user_id' => $user->id,
                    'role_id' => $adminRoleId,
                ];

                if (Schema::hasColumn('role_user', 'created_at')) {
                    $pivotPayload['created_at'] = now();
                }

                if (Schema::hasColumn('role_user', 'updated_at')) {
                    $pivotPayload['updated_at'] = now();
                }

                DB::table('role_user')->insert($pivotPayload);
            }
        }

        $user->refresh();

        $this->newLine();
        $this->info('Compte admin prêt.');
        $this->line('Portail admin : /' . trim((string) config('timahschool.admin_path', 'backoffice-access'), '/') . '/login');
        $this->line('Utilisateur : ' . $username);
        $this->line('Rôle admin ID : ' . $adminRoleId);
        $this->line('N\'utilise pas /login pour ce compte.');

        return self::SUCCESS;
    }

    protected function resolveAdminRoleId(): ?int
    {
        if (!Schema::hasTable('roles')) {
            return null;
        }

        $role = DB::table('roles')->where('name', 'admin')->first();

        if ($role && isset($role->id)) {
            return (int) $role->id;
        }

        $payload = [];

        if (Schema::hasColumn('roles', 'name')) {
            $payload['name'] = 'admin';
        }

        if (Schema::hasColumn('roles', 'display_name')) {
            $payload['display_name'] = 'Administrateur';
        }

        if (Schema::hasColumn('roles', 'description')) {
            $payload['description'] = 'Accès complet au backoffice TIMAH SCHOOL';
        }

        if (Schema::hasColumn('roles', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('roles', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        try {
            return (int) DB::table('roles')->insertGetId($payload);
        } catch (\Throwable $e) {
            $role = DB::table('roles')->where('name', 'admin')->first();
            return ($role && isset($role->id)) ? (int) $role->id : null;
        }
    }
}
