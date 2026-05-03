<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MobileAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:6'],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:40'],
            'device_id' => ['required', 'string', 'max:191'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_model' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:80'],
            'app_version' => ['nullable', 'string', 'max:80'],
        ]);

        $phone = $this->normalizePhone($data['phone']);

        $existing = User::query()->where('phone', $phone)->first();
        if ($existing) {
            return response()->json([
                'status' => 'phone_already_registered',
                'message' => 'Ce numéro WhatsApp possède déjà un compte TIMAH ACADEMY. Connectez-vous avec ce compte.',
            ], 409);
        }

        $user = User::query()->create([
            'name' => $data['name'],
            'full_name' => $data['name'],
            'username' => $this->makeUsername($phone),
            'phone' => $phone,
            'email' => null,
            'status' => 'active',
            'password' => Hash::make($data['password']),
        ]);

        $this->attachStudentRole($user);

        $profile = StudentProfile::query()->create([
            'user_id' => $user->id,
            'school_class_id' => $data['school_class_id'] ?? null,
            'parent_name' => $data['parent_name'] ?? null,
            'parent_phone' => isset($data['parent_phone']) ? $this->normalizePhone($data['parent_phone']) : null,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDay(),
            'trial_used' => true,
        ]);

        $device = $this->activateFirstDevice($user, $phone, $data);
        $subscription = $this->createTrialSubscription($user);

        return $this->mobileAccessResponse($user, $device, $subscription, 'Compte créé. Votre essai gratuit de 24h est activé.');
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
            'password' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:191'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_model' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:80'],
            'app_version' => ['nullable', 'string', 'max:80'],
        ]);

        $phone = $this->normalizePhone($data['phone']);
        $user = User::query()->where('phone', $phone)->first();

        if (!$user || !Hash::check($data['password'], (string) $user->password)) {
            return response()->json([
                'status' => 'invalid_credentials',
                'message' => 'Numéro WhatsApp ou mot de passe incorrect.',
            ], 422);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'status' => 'account_blocked',
                'message' => 'Ce compte TIMAH ACADEMY est bloqué. Contactez l’administration.',
            ], 403);
        }

        $deviceResult = $this->resolveDeviceAccess($user, $phone, $data);
        if ($deviceResult instanceof JsonResponse) {
            return $deviceResult;
        }

        $subscription = $user->activeSubscription;
        if (!$subscription && !$this->trialWasUsed($user)) {
            $subscription = $this->createTrialSubscription($user);
            $this->markTrialUsed($user);
        }

        return $this->mobileAccessResponse($user, $deviceResult, $subscription, 'Connexion mobile autorisée.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['studentProfile.schoolClass', 'activeMobileDevice']);

        return response()->json([
            'status' => 'ok',
            'user' => $this->serializeUser($user),
            'subscription' => $this->serializeSubscription($user->activeSubscription),
            'device' => $this->serializeDevice($user->activeMobileDevice),
        ]);
    }

    public function subscription(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        return response()->json([
            'status' => $subscription ? 'active' : 'expired',
            'subscription' => $this->serializeSubscription($subscription),
            'message' => $subscription
                ? 'Votre accès TIMAH ACADEMY est actif.'
                : 'Votre accès complet est expiré. Contact WhatsApp abonnement : 670 00 00 00.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'logged_out',
            'message' => 'Déconnexion effectuée.',
        ]);
    }

    private function resolveDeviceAccess(User $user, string $phone, array $data): MobileDevice|JsonResponse
    {
        $deviceId = $data['device_id'];
        $activeDevice = $user->mobileDevices()->active()->first();

        if ($activeDevice && $activeDevice->device_id !== $deviceId) {
            return response()->json([
                'status' => 'device_already_linked',
                'message' => 'Ce compte TIMAH ACADEMY est déjà utilisé sur un autre appareil. Pour transférer votre compte vers ce téléphone, contactez l’administration WhatsApp : 670 00 00 00.',
                'active_device' => $this->serializeDevice($activeDevice),
            ], 423);
        }

        if ($activeDevice && $activeDevice->device_id === $deviceId) {
            $activeDevice->update([
                'device_name' => $data['device_name'] ?? $activeDevice->device_name,
                'device_model' => $data['device_model'] ?? $activeDevice->device_model,
                'platform' => $data['platform'] ?? $activeDevice->platform,
                'app_version' => $data['app_version'] ?? $activeDevice->app_version,
                'last_seen_at' => now(),
            ]);

            return $activeDevice;
        }

        return $this->activateFirstDevice($user, $phone, $data);
    }

    private function activateFirstDevice(User $user, string $phone, array $data): MobileDevice
    {
        return MobileDevice::query()->create([
            'user_id' => $user->id,
            'phone' => $phone,
            'device_id' => $data['device_id'],
            'device_name' => $data['device_name'] ?? null,
            'device_model' => $data['device_model'] ?? null,
            'platform' => $data['platform'] ?? 'android',
            'app_version' => $data['app_version'] ?? null,
            'status' => MobileDevice::STATUS_ACTIVE,
            'first_login_at' => now(),
            'last_seen_at' => now(),
        ]);
    }

    private function createTrialSubscription(User $user): Subscription
    {
        return Subscription::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => null,
            'plan_name' => 'Essai gratuit 24h',
            'status' => Subscription::STATUS_TRIAL,
            'is_trial' => true,
            'starts_at' => now(),
            'ends_at' => now()->addDay(),
        ]);
    }

    private function trialWasUsed(User $user): bool
    {
        if ($user->studentProfile?->trial_used) {
            return true;
        }

        return $user->subscriptions()->where('is_trial', true)->exists();
    }

    private function markTrialUsed(User $user): void
    {
        $profile = $user->studentProfile ?: StudentProfile::query()->create(['user_id' => $user->id]);
        $profile->update([
            'trial_started_at' => $profile->trial_started_at ?: now(),
            'trial_ends_at' => $profile->trial_ends_at ?: now()->addDay(),
            'trial_used' => true,
        ]);
    }

    private function mobileAccessResponse(User $user, MobileDevice $device, ?Subscription $subscription, string $message): JsonResponse
    {
        $token = $user->createToken('timah-academy-mobile')->plainTextToken;

        return response()->json([
            'status' => 'ok',
            'message' => $message,
            'token' => $token,
            'user' => $this->serializeUser($user->fresh(['studentProfile.schoolClass'])),
            'device' => $this->serializeDevice($device),
            'subscription' => $this->serializeSubscription($subscription),
        ]);
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name ?: $user->name,
            'phone' => $user->phone,
            'role' => $user->isStudent() ? 'student' : ($user->isTeacher() ? 'teacher' : ($user->isAdmin() ? 'admin' : 'user')),
            'class' => $user->studentProfile?->schoolClass?->name,
        ];
    }

    private function serializeSubscription(?Subscription $subscription): ?array
    {
        if (!$subscription) {
            return null;
        }

        return [
            'id' => $subscription->id,
            'plan_name' => $subscription->plan_name,
            'status' => $subscription->status,
            'is_trial' => (bool) $subscription->is_trial,
            'starts_at' => $subscription->starts_at?->toIso8601String(),
            'ends_at' => $subscription->ends_at?->toIso8601String(),
            'is_active' => $subscription->isActive(),
        ];
    }

    private function serializeDevice(?MobileDevice $device): ?array
    {
        if (!$device) {
            return null;
        }

        return [
            'id' => $device->id,
            'device_name' => $device->device_name,
            'device_model' => $device->device_model,
            'platform' => $device->platform,
            'app_version' => $device->app_version,
            'status' => $device->status,
            'first_login_at' => $device->first_login_at?->toIso8601String(),
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
        ];
    }

    private function attachStudentRole(User $user): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_user')) {
            return;
        }

        $role = Role::query()
            ->whereRaw('LOWER(name) IN (?, ?, ?)', ['student', 'eleve', 'élève'])
            ->first();

        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
            if (!$user->role_id) {
                $user->update(['role_id' => $role->id]);
            }
        }
    }

    private function makeUsername(string $phone): string
    {
        $base = 'tm' . preg_replace('/\D+/', '', $phone);
        $username = $base;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $counter++;
            $username = $base . '_' . $counter;
        }

        return $username;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', trim($phone));
    }
}
