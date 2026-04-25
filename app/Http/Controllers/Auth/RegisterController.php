<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $classes = SchoolClass::active()->orderBy('order')->orderBy('name')->get();

        return view('auth.register', compact('classes'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'alpha_dash'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'city' => ['required', 'string', 'max:120'],
            'password' => ['required', Rules\Password::defaults()],
        ], [
            'username.required' => 'Le nom d’utilisateur est obligatoire.',
            'username.unique' => 'Ce nom d’utilisateur est déjà utilisé.',
            'username.alpha_dash' => 'Le nom d’utilisateur doit contenir uniquement des lettres, chiffres, tirets ou underscores.',
            'school_class_id.required' => 'La classe est obligatoire.',
            'school_class_id.exists' => 'La classe sélectionnée est invalide.',
            'city.required' => 'La ville est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $username = trim($validated['username']);
            $generatedEmail = mb_strtolower($username) . '@timahschool.local';

            $userData = [
                'name' => $username,
                'username' => $username,
                'full_name' => $username,
                'email' => $generatedEmail,
                'phone' => '',
                'status' => 'active',
                'password' => Hash::make($validated['password']),
            ];

            if (Schema::hasColumn('users', 'city')) {
                $userData['city'] = $validated['city'];
            }

            $user = User::create($userData);

            $studentRole = Role::where('name', 'student')->first();
            if ($studentRole) {
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
            }

            StudentProfile::create([
                'user_id' => $user->id,
                'school_class_id' => $validated['school_class_id'],
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addHours(24),
                'trial_used' => true,
            ]);

            $user->subscriptions()->create([
                'plan_name' => 'Essai Gratuit',
                'status' => Subscription::STATUS_TRIAL,
                'is_trial' => true,
                'starts_at' => now(),
                'ends_at' => now()->addHours(24),
            ]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('student.dashboard')
            ->with('success', 'Bienvenue sur TIMAH ACADEMY ! Votre essai gratuit de 24h commence maintenant.');
    }
}
