<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\StudentLearningProfile;
use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class MobileAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:40'],
                'password' => ['required', 'string', 'min:6'],
                'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')],
                'school_level' => ['nullable', 'string', 'max:120'],
                'main_goal' => ['nullable', 'string', 'max:191'],
                'target_exam' => ['nullable', 'string', 'max:191'],
                'weak_subjects' => ['nullable', 'array'],
                'weak_subjects.*' => ['nullable', 'string', 'max:120'],
                'study_times' => ['nullable', 'array'],
                'study_times.*' => ['nullable', 'string', 'max:120'],
                'preferred_study_time' => ['nullable', 'string', 'max:120'],
                'parent_name' => ['nullable', 'string', 'max:255'],
                'parent_phone' => ['nullable', 'string', 'max:40'],
                'device_id' => ['required', 'string', 'max:191'],
                'device_name' => ['nullable', 'string', 'max:255'],
                'device_model' => ['nullable', 'string', 'max:255'],
                'platform' => ['nullable', 'string', 'max:80'],
                'app_version' => ['nullable', 'string', 'max:80'],
            ]);

            $phone = $this->normalizePhone($data['phone']);

            if (User::query()->where('phone', $phone)->exists()) {
                return response()->json([
                    'status' => 'phone_already_registered',
                    'message' => 'Ce numéro WhatsApp possède déjà un compte TIMAH ACADEMY. Connectez-vous avec ce compte.',
                ], 409);
            }

            $plainToken = $this->newMobileToken();

            $user = DB::transaction(function () use ($data, $phone, $plainToken) {
                $user = User::query()->create($this->userPayload($data['name'], $phone, $data['password'], $plainToken));
                $this->attachStudentRole($user);
                $this->safeCreateStudentProfile($user, $data);
                $this->safeCreateLearningProfile($user, $data);
                $this->safeActivateFirstDevice($user, $phone, $data);
                $this->safeCreateTrialSubscription($user);

                return $user;
            });

            $user = $user->fresh(['studentProfile.schoolClass', 'learningProfile']);

            return $this->mobileAccessResponse(
                $user,
                $plainToken,
                $this->safeActiveDevice($user),
                $this->safeActiveSubscription($user),
                'Compte créé. Votre essai gratuit de 24h est activé.'
            );
        } catch (Throwable $e) {
            Log::error('Mobile registration failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'server_error',
                'message' => 'Inscription impossible : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
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

            $deviceResult = $this->safeResolveDeviceAccess($user, $phone, $data);
            if ($deviceResult instanceof JsonResponse) {
                return $deviceResult;
            }

            $subscription = $this->safeActiveSubscription($user);
            if (!$subscription && !$this->trialWasUsed($user)) {
                $subscription = $this->safeCreateTrialSubscription($user);
                $this->markTrialUsed($user);
            }

            $plainToken = $this->newMobileToken();
            $user->forceFill(['remember_token' => hash('sha256', $plainToken)])->save();

            return $this->mobileAccessResponse($user->fresh(['studentProfile.schoolClass', 'learningProfile']), $plainToken, $deviceResult, $subscription, 'Connexion mobile autorisée.');
        } catch (Throwable $e) {
            Log::error('Mobile login failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'server_error',
                'message' => 'Connexion mobile impossible : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return response()->json(['status' => 'unauthenticated', 'message' => 'Session mobile expirée.'], 401);
        }

        $user->load(['studentProfile.schoolClass', 'learningProfile']);

        return response()->json([
            'status' => 'ok',
            'user' => $this->serializeUser($user),
            'subscription' => $this->serializeSubscription($this->safeActiveSubscription($user)),
            'device' => $this->serializeDevice($this->safeActiveDevice($user)),
            'learning_profile' => $this->serializeLearningProfile($user),
        ]);
    }

    public function subscription(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return response()->json(['status' => 'unauthenticated', 'message' => 'Session mobile expirée.'], 401);
        }

        $subscription = $this->safeActiveSubscription($user);

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
        $user = $this->userFromBearer($request);
        if ($user) {
            $user->forceFill(['remember_token' => null])->save();
        }

        return response()->json([
            'status' => 'logged_out',
            'message' => 'Déconnexion effectuée.',
        ]);
    }

    private function userPayload(string $name, string $phone, string $password, string $plainToken): array
    {
        $payload = [];
        $columns = Schema::getColumnListing('users');
        $put = function (string $column, mixed $value) use (&$payload, $columns) {
            if (in_array($column, $columns, true)) {
                $payload[$column] = $value;
            }
        };

        $put('name', $name);
        $put('full_name', $name);
        $put('username', $this->makeUsername($phone));
        $put('phone', $phone);
        $put('email', $this->makeMobileEmail($phone));
        $put('status', 'active');
        $put('password', Hash::make($password));
        $put('remember_token', hash('sha256', $plainToken));

        return $payload;
    }

    private function safeCreateStudentProfile(User $user, array $data): void
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $columns = Schema::getColumnListing('student_profiles');
        $payload = ['user_id' => $user->id];

        if (in_array('school_class_id', $columns, true)) {
            $payload['school_class_id'] = $data['school_class_id'] ?? $this->fallbackClassId();
        }
        if (in_array('parent_name', $columns, true)) {
            $payload['parent_name'] = $data['parent_name'] ?? null;
        }
        if (in_array('parent_phone', $columns, true)) {
            $payload['parent_phone'] = isset($data['parent_phone']) ? $this->normalizePhone($data['parent_phone']) : null;
        }
        if (in_array('trial_started_at', $columns, true)) {
            $payload['trial_started_at'] = now();
        }
        if (in_array('trial_ends_at', $columns, true)) {
            $payload['trial_ends_at'] = now()->addDay();
        }
        if (in_array('trial_used', $columns, true)) {
            $payload['trial_used'] = true;
        }

        StudentProfile::query()->create($payload);
    }

    private function safeCreateLearningProfile(User $user, array $data): void
    {
        if (!Schema::hasTable('student_learning_profiles')) {
            return;
        }

        $weakSubjects = collect($data['weak_subjects'] ?? [])->filter()->values()->all();
        $studyTimes = collect($data['study_times'] ?? [])->filter()->values()->all();
        $targetExam = $data['target_exam'] ?? null;
        $mainGoal = $data['main_goal'] ?? null;
        $schoolLevel = $data['school_level'] ?? null;
        $preferred = $data['preferred_study_time'] ?? ($studyTimes[0] ?? null);
        $subjectsLabel = $weakSubjects ? implode(', ', $weakSubjects) : 'les matières prioritaires';
        $examLabel = $targetExam ?: 'ton objectif scolaire';

        $title = 'Parcours personnalisé prêt';
        $message = 'Nous allons t’aider en priorité sur ' . $subjectsLabel . ' pour mieux préparer ' . $examLabel . '.';
        $actions = [
            'Consulter le programme de révision du jour',
            'Traiter les TD PDF programmés',
            'Faire les quiz de consolidation',
            'Lire les corrigés après validation des TD',
        ];

        StudentLearningProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'school_class_id' => $data['school_class_id'] ?? $this->fallbackClassId(),
                'school_level' => $schoolLevel,
                'main_goal' => $mainGoal,
                'target_exam' => $targetExam,
                'weak_subjects' => $weakSubjects,
                'study_times' => $studyTimes,
                'preferred_study_time' => $preferred,
                'parent_name' => $data['parent_name'] ?? null,
                'parent_phone' => isset($data['parent_phone']) ? $this->normalizePhone($data['parent_phone']) : null,
                'recommendation_title' => $title,
                'recommendation_message' => $message,
                'recommended_actions' => $actions,
                'generated_summary' => trim(($schoolLevel ? 'Niveau : ' . $schoolLevel . '. ' : '') . ($mainGoal ? 'Objectif : ' . $mainGoal . '. ' : '') . ($targetExam ? 'Examen : ' . $targetExam . '. ' : '') . ($weakSubjects ? 'Priorités : ' . $subjectsLabel . '.' : '')),
                'diagnostic_completed_at' => now(),
                'completed_at' => now(),
            ]
        );
    }

    private function safeActivateFirstDevice(User $user, string $phone, array $data): ?MobileDevice
    {
        if (!Schema::hasTable('mobile_devices')) {
            return null;
        }

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

    private function safeResolveDeviceAccess(User $user, string $phone, array $data): MobileDevice|JsonResponse|null
    {
        if (!Schema::hasTable('mobile_devices')) {
            return null;
        }

        $deviceId = $data['device_id'];
        $activeDevice = $user->mobileDevices()->active()->first();

        if ($activeDevice && $activeDevice->device_id !== $deviceId) {
            return response()->json([
                'status' => 'device_already_linked',
                'message' => 'Ce compte TIMAH ACADEMY est déjà utilisé sur un autre appareil. Pour transférer votre compte vers ce téléphone, contactez l’administration WhatsApp : 670 00 00 00.',
                'active_device' => $this->serializeDevice($activeDevice),
            ], 423);
        }

        if ($activeDevice) {
            $activeDevice->update([
                'device_name' => $data['device_name'] ?? $activeDevice->device_name,
                'device_model' => $data['device_model'] ?? $activeDevice->device_model,
                'platform' => $data['platform'] ?? $activeDevice->platform,
                'app_version' => $data['app_version'] ?? $activeDevice->app_version,
                'last_seen_at' => now(),
            ]);
            return $activeDevice;
        }

        return $this->safeActivateFirstDevice($user, $phone, $data);
    }

    private function safeCreateTrialSubscription(User $user): ?Subscription
    {
        if (!Schema::hasTable('subscriptions')) {
            return null;
        }

        $columns = Schema::getColumnListing('subscriptions');
        $payload = ['user_id' => $user->id];
        $put = function (string $column, mixed $value) use (&$payload, $columns) {
            if (in_array($column, $columns, true)) {
                $payload[$column] = $value;
            }
        };

        $put('subscription_plan_id', null);
        $put('plan_name', 'Essai gratuit 24h');
        $put('status', defined(Subscription::class . '::STATUS_TRIAL') ? Subscription::STATUS_TRIAL : 'trial');
        $put('is_trial', true);
        $put('starts_at', now());
        $put('ends_at', now()->addDay());

        return Subscription::query()->create($payload);
    }

    private function safeActiveSubscription(User $user): ?Subscription
    {
        if (!Schema::hasTable('subscriptions')) {
            return null;
        }

        return $user->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->first();
    }

    private function safeActiveDevice(User $user): ?MobileDevice
    {
        if (!Schema::hasTable('mobile_devices')) {
            return null;
        }

        return $user->mobileDevices()->active()->first();
    }

    private function trialWasUsed(User $user): bool
    {
        if ($user->studentProfile?->trial_used) {
            return true;
        }

        if (!Schema::hasTable('subscriptions')) {
            return false;
        }

        return $user->subscriptions()->where('is_trial', true)->exists();
    }

    private function markTrialUsed(User $user): void
    {
        if (!Schema::hasTable('student_profiles')) {
            return;
        }

        $profile = $user->studentProfile ?: StudentProfile::query()->create([
            'user_id' => $user->id,
            'school_class_id' => $this->fallbackClassId(),
        ]);

        $updates = [];
        $columns = Schema::getColumnListing('student_profiles');
        if (in_array('trial_started_at', $columns, true)) {
            $updates['trial_started_at'] = $profile->trial_started_at ?: now();
        }
        if (in_array('trial_ends_at', $columns, true)) {
            $updates['trial_ends_at'] = $profile->trial_ends_at ?: now()->addDay();
        }
        if (in_array('trial_used', $columns, true)) {
            $updates['trial_used'] = true;
        }

        if ($updates) {
            $profile->update($updates);
        }
    }

    private function mobileAccessResponse(User $user, string $plainToken, ?MobileDevice $device, ?Subscription $subscription, string $message): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => $message,
            'token' => $plainToken,
            'user' => $this->serializeUser($user),
            'device' => $this->serializeDevice($device),
            'subscription' => $this->serializeSubscription($subscription),
            'learning_profile' => $this->serializeLearningProfile($user),
        ]);
    }

    private function userFromBearer(Request $request): ?User
    {
        $header = $request->header('Authorization', '');
        $token = Str::startsWith($header, 'Bearer ') ? trim(Str::after($header, 'Bearer ')) : '';
        if ($token === '') {
            return null;
        }

        return User::query()->where('remember_token', hash('sha256', $token))->first();
    }

    private function newMobileToken(): string
    {
        return Str::random(80);
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

    private function serializeLearningProfile(User $user): ?array
    {
        $profile = $user->learningProfile ?? null;
        if (!$profile) {
            return null;
        }

        return [
            'school_level' => $profile->school_level,
            'main_goal' => $profile->main_goal,
            'target_exam' => $profile->target_exam,
            'weak_subjects' => $profile->weak_subjects ?: [],
            'study_times' => $profile->study_times ?: [],
            'preferred_study_time' => $profile->preferred_study_time,
            'parent_name' => $profile->parent_name,
            'parent_phone' => $profile->parent_phone,
            'recommendation_title' => $profile->recommendation_title,
            'recommendation_message' => $profile->recommendation_message,
            'recommended_actions' => $profile->recommended_actions ?: [],
            'completed_at' => $profile->completed_at?->toIso8601String(),
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
            'is_active' => method_exists($subscription, 'isActive') ? $subscription->isActive() : true,
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

        if (!$role) {
            return;
        }

        $user->roles()->syncWithoutDetaching([$role->id]);

        if (Schema::hasColumn('users', 'role_id') && empty($user->role_id)) {
            $user->forceFill(['role_id' => $role->id])->save();
        }
    }

    private function fallbackClassId(): ?int
    {
        if (!Schema::hasTable('school_classes')) {
            return null;
        }

        return SchoolClass::query()->where('is_active', true)->orderBy('order')->orderBy('id')->value('id')
            ?: SchoolClass::query()->orderBy('id')->value('id');
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

    private function makeMobileEmail(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: (string) time();
        $email = 'mobile_' . $digits . '@timahacademy.local';
        $counter = 1;

        while (User::query()->where('email', $email)->exists()) {
            $counter++;
            $email = 'mobile_' . $digits . '_' . $counter . '@timahacademy.local';
        }

        return $email;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9+]/', '', trim($phone));
    }
}
