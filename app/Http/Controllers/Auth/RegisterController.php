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
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['full_name'],
                'username' => $validated['username'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => 'active',
                'password' => Hash::make($validated['password']),
            ]);

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
            ->with('success', 'Bienvenue sur TIMAH SCHOOL ! Votre essai gratuit de 24h commence maintenant.');
    }
}
