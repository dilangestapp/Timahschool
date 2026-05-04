<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MobileTdController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        if (!Schema::hasTable('td_sets')) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Le module TD n’est pas encore initialisé.',
                'items' => [],
            ]);
        }

        $user->loadMissing('studentProfile.schoolClass');
        $classId = $user->studentProfile?->school_class_id;

        $items = TdSet::query()
            ->with(['schoolClass', 'subject'])
            ->where('status', TdSet::STATUS_PUBLISHED)
            ->when($classId, fn ($query) => $query->where('school_class_id', $classId))
            ->latest('published_at')
            ->latest('id')
            ->take(50)
            ->get()
            ->map(fn ($td) => $this->serializeTd($td, $user));

        return response()->json([
            'status' => 'ok',
            'message' => $items->isEmpty() ? 'Aucun TD publié pour votre classe pour le moment.' : 'TD chargés.',
            'items' => $items,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $td = TdSet::query()->with(['schoolClass', 'subject'])->find($id);
        if (!$td || $td->status !== TdSet::STATUS_PUBLISHED) {
            return response()->json(['status' => 'not_found', 'message' => 'TD introuvable.'], 404);
        }

        if ($response = $this->ensureStudentCanAccessTd($td, $user)) {
            return $response;
        }

        $attempt = $this->openAttempt($td, $user);

        return response()->json([
            'status' => 'ok',
            'message' => 'TD chargé.',
            'item' => $this->serializeTd($td, $user, true, $attempt),
        ]);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $this->userFromBearer($request);
        if (!$user) {
            return $this->unauthenticated();
        }

        $td = TdSet::query()->find($id);
        if (!$td || $td->status !== TdSet::STATUS_PUBLISHED) {
            return response()->json(['status' => 'not_found', 'message' => 'TD introuvable.'], 404);
        }

        if ($response = $this->ensureStudentCanAccessTd($td, $user)) {
            return $response;
        }

        $attempt = $this->openAttempt($td, $user);
        $unlockAt = $attempt->correction_unlocked_at ?: now()->addMinutes($td->correctionDelayMinutes());

        $attempt->update($this->attemptAttributes([
            'status' => TdAttempt::STATUS_COMPLETED,
            'opened_at' => $attempt->opened_at ?: now(),
            'completed_at' => now(),
            'submitted_at' => now(),
            'correction_unlocked_at' => $unlockAt,
        ]));

        return response()->json([
            'status' => 'ok',
            'message' => 'TD marqué comme terminé.',
            'attempt' => [
                'status' => TdAttempt::STATUS_COMPLETED,
                'completed_at' => now()->toIso8601String(),
                'correction_unlocked_at' => $unlockAt?->toIso8601String(),
                'can_see_correction' => $td->correctionIsAvailableFor($user, $attempt->fresh()),
            ],
        ]);
    }

    private function openAttempt(TdSet $td, User $user): TdAttempt
    {
        $delayMinutes = $td->correctionDelayMinutes();

        $attempt = TdAttempt::query()->firstOrCreate(
            ['td_set_id' => $td->id, 'student_id' => $user->id],
            $this->attemptAttributes([
                'status' => TdAttempt::STATUS_OPENED,
                'opened_at' => now(),
                'correction_unlocked_at' => now()->addMinutes($delayMinutes),
            ])
        );

        $updates = [];
        if (!$attempt->opened_at) {
            $updates['opened_at'] = now();
        }
        if (!$attempt->correction_unlocked_at) {
            $updates['correction_unlocked_at'] = ($attempt->opened_at ?: now())->copy()->addMinutes($delayMinutes);
        }
        if ($attempt->status !== TdAttempt::STATUS_COMPLETED) {
            $updates['status'] = TdAttempt::STATUS_OPENED;
        }
        if ($updates) {
            $attempt->update($this->attemptAttributes($updates));
            $attempt->refresh();
        }

        return $attempt;
    }

    private function serializeTd(TdSet $td, User $user, bool $withContent = false, ?TdAttempt $attempt = null): array
    {
        $attempt = $attempt ?: TdAttempt::query()
            ->where('td_set_id', $td->id)
            ->where('student_id', $user->id)
            ->latest('id')
            ->first();

        $canSeeCorrection = $td->correctionIsAvailableFor($user, $attempt);

        $data = [
            'id' => $td->id,
            'title' => $td->title,
            'chapter_label' => $td->chapter_label,
            'difficulty' => $td->difficulty,
            'access_level' => $td->access_level,
            'status' => $td->status,
            'subject' => $td->subject?->name,
            'class' => $td->schoolClass?->name,
            'published_at' => $td->published_at?->toIso8601String(),
            'correction_delay_minutes' => $td->correctionDelayMinutes(),
            'has_document' => $td->hasDocument(),
            'has_editable_version' => (bool) $td->has_editable_version,
            'has_correction' => $td->hasCorrectionContent(),
            'can_see_correction' => $canSeeCorrection,
            'attempt' => $attempt ? [
                'status' => $attempt->status,
                'opened_at' => $attempt->opened_at?->toIso8601String(),
                'completed_at' => $attempt->completed_at?->toIso8601String(),
                'correction_unlocked_at' => $attempt->correction_unlocked_at?->toIso8601String(),
            ] : null,
        ];

        if ($withContent) {
            $data['content'] = [
                'html' => $td->editable_html,
                'text' => $td->editable_text,
                'document_url' => $td->document_path ? url('/student/td/' . $td->id . '/document') : null,
            ];
            $data['correction'] = [
                'available' => $canSeeCorrection,
                'html' => $canSeeCorrection ? $td->correction_html : null,
                'document_url' => ($canSeeCorrection && $td->correction_document_path) ? url('/student/td/' . $td->id . '/correction-document') : null,
                'locked_message' => $canSeeCorrection ? null : 'Le corrigé sera disponible après validation du TD et fin du temps de traitement.',
            ];
        }

        return $data;
    }

    private function ensureStudentCanAccessTd(TdSet $td, User $user): ?JsonResponse
    {
        $profile = $user->studentProfile;
        if (!$profile) {
            return response()->json(['status' => 'forbidden', 'message' => 'Profil élève introuvable.'], 403);
        }

        if ((int) $td->school_class_id !== (int) $profile->school_class_id) {
            return response()->json(['status' => 'forbidden', 'message' => 'Ce TD n’appartient pas à votre classe.'], 403);
        }

        if (!$td->canStudentAccess($user)) {
            return response()->json([
                'status' => 'subscription_required',
                'message' => 'Ce TD est réservé aux abonnés actifs. Contact WhatsApp abonnement : 670 00 00 00.',
            ], 402);
        }

        return null;
    }

    private function attemptAttributes(array $attributes): array
    {
        static $columns = null;
        if ($columns === null) {
            $columns = Schema::hasTable('td_attempts') ? collect(Schema::getColumnListing('td_attempts'))->flip()->all() : [];
        }

        return collect($attributes)
            ->filter(fn ($value, $key) => array_key_exists($key, $columns))
            ->all();
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

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'status' => 'unauthenticated',
            'message' => 'Session mobile expirée. Veuillez vous reconnecter.',
        ], 401);
    }
}
