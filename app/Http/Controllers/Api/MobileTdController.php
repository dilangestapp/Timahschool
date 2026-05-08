<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TdAttempt;
use App\Models\TdSet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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
            ->unique('id')
            ->values()
            ->map(fn ($td) => $this->serializeTd($td, $user));

        return response()->json([
            'status' => 'ok',
            'message' => $items->isEmpty() ? 'Aucun TD PDF publié pour votre classe pour le moment.' : 'TD PDF chargés.',
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
            'message' => 'TD PDF chargé.',
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
            'message' => 'TD marqué comme terminé. Le corrigé PDF sera disponible selon le temps prévu.',
            'attempt' => [
                'status' => TdAttempt::STATUS_COMPLETED,
                'completed_at' => now()->toIso8601String(),
                'correction_unlocked_at' => $unlockAt?->toIso8601String(),
                'can_see_correction' => $td->correctionIsAvailableFor($user, $attempt->fresh()),
                'correction_label' => 'Corrigé disponible après ' . $this->formatDateTime($unlockAt),
            ],
        ]);
    }

    public function document(Request $request, int $id): Response|JsonResponse|RedirectResponse
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

        if ($td->document_path && Storage::disk('public')->exists($td->document_path)) {
            return Storage::disk('public')->response($td->document_path, $td->document_name ?: basename($td->document_path));
        }

        if (!empty($td->document_drive_url)) {
            return redirect()->away($td->document_drive_url);
        }

        return response()->json(['status' => 'not_found', 'message' => 'Document PDF du TD introuvable. Le fichier doit être remplacé par l’administration.'], 404);
    }

    public function correctionDocument(Request $request, int $id): Response|JsonResponse|RedirectResponse
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

        $attempt = TdAttempt::query()
            ->where('td_set_id', $td->id)
            ->where('student_id', $user->id)
            ->latest('id')
            ->first();

        if (!$td->correctionIsAvailableFor($user, $attempt)) {
            return response()->json([
                'status' => 'locked',
                'message' => 'Le corrigé PDF sera disponible après validation du TD et fin du temps de traitement.',
            ], 423);
        }

        if ($td->correction_document_path && Storage::disk('public')->exists($td->correction_document_path)) {
            return Storage::disk('public')->response($td->correction_document_path, $td->correction_document_name ?: basename($td->correction_document_path));
        }

        if (!empty($td->correction_document_drive_url)) {
            return redirect()->away($td->correction_document_drive_url);
        }

        return response()->json(['status' => 'not_found', 'message' => 'Corrigé PDF introuvable. Le fichier doit être remplacé par l’administration.'], 404);
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

        $publishedAt = $td->published_at;
        $isAvailableNow = !$publishedAt || now()->greaterThanOrEqualTo($publishedAt);
        $canSeeCorrection = $td->correctionIsAvailableFor($user, $attempt);
        $documentUrl = $td->hasDocument() ? url('/api/mobile/td/' . $td->id . '/document') : null;
        $correctionUrl = ($canSeeCorrection && $td->hasCorrectionDocument()) ? url('/api/mobile/td/' . $td->id . '/correction-document') : null;
        $availabilityStatus = $isAvailableNow ? 'available' : 'scheduled';
        $availabilityLabel = $isAvailableNow ? 'Disponible maintenant' : 'Programmé pour ' . $this->formatDateTime($publishedAt);
        $availabilityDetail = $isAvailableNow
            ? 'Ouvre le PDF, traite le TD sur ton cahier, puis valide “J’ai terminé”.'
            : 'Ce TD sera visible à la date et à l’heure programmées par TIMAH ACADEMY.';
        $correctionStatus = 'locked';
        $correctionLabel = 'Corrigé verrouillé';
        $correctionDetail = 'Traite le TD sur cahier, puis clique sur “J’ai terminé”.';

        if (!$td->hasCorrectionContent()) {
            $correctionStatus = 'none';
            $correctionLabel = 'Aucun corrigé PDF ajouté';
            $correctionDetail = 'Le corrigé sera ajouté par TIMAH ACADEMY si nécessaire.';
        } elseif ($canSeeCorrection) {
            $correctionStatus = 'available';
            $correctionLabel = 'Corrigé disponible';
            $correctionDetail = 'Tu peux maintenant ouvrir le corrigé PDF.';
        } elseif ($attempt?->status === TdAttempt::STATUS_COMPLETED && $attempt->correction_unlocked_at) {
            $correctionStatus = 'waiting';
            $correctionLabel = 'Corrigé disponible après ' . $this->formatDateTime($attempt->correction_unlocked_at);
            $correctionDetail = 'Ton TD est marqué comme terminé. Le corrigé se débloquera automatiquement après le délai prévu.';
        } elseif ($td->correctionDelayMinutes() > 0) {
            $correctionDetail = 'Le corrigé se débloque après validation du TD + ' . $td->correctionDelayMinutes() . ' minutes.';
        }

        $data = [
            'id' => $td->id,
            'title' => $td->title,
            'chapter_label' => $td->chapter_label,
            'difficulty' => $td->difficulty,
            'access_level' => $td->access_level,
            'status' => $td->status,
            'subject' => $td->subject?->name,
            'class' => $td->schoolClass?->name,
            'published_at' => $publishedAt?->toIso8601String(),
            'published_label' => $publishedAt ? 'Publié le ' . $this->formatDateTime($publishedAt) : 'Publication immédiate',
            'is_available_now' => $isAvailableNow,
            'availability_status' => $availabilityStatus,
            'availability_label' => $availabilityLabel,
            'availability_detail' => $availabilityDetail,
            'student_instructions' => 'Traite ce TD sur ton cahier. Lorsque tu as terminé, valide le TD pour débloquer le corrigé selon le délai prévu.',
            'action_hint' => $isAvailableNow ? 'Ouvrir le sujet PDF' : 'TD non encore ouvert',
            'correction_delay_minutes' => $td->correctionDelayMinutes(),
            'correction_delay_label' => $td->correctionDelayMinutes() > 0 ? 'Corrigé après validation + ' . $td->correctionDelayMinutes() . ' min' : 'Corrigé après validation',
            'correction_status' => $correctionStatus,
            'correction_label' => $correctionLabel,
            'correction_detail' => $correctionDetail,
            'display_mode' => 'pdf_document',
            'has_document' => $td->hasDocument(),
            'document_storage' => $td->hasLocalDocument() ? 'local' : ($td->hasDriveDocument() ? 'google_drive' : null),
            'document_name' => $td->document_name,
            'document_mime' => $td->document_mime,
            'document_size' => $td->document_size,
            'document_url' => $documentUrl,
            'has_editable_version' => false,
            'has_correction' => $td->hasCorrectionContent(),
            'can_see_correction' => $canSeeCorrection,
            'correction_storage' => $td->hasLocalCorrectionDocument() ? 'local' : ($td->hasDriveCorrectionDocument() ? 'google_drive' : null),
            'correction_document_name' => $td->correction_document_name,
            'correction_document_url' => $correctionUrl,
            'attempt' => $attempt ? [
                'status' => $attempt->status,
                'opened_at' => $attempt->opened_at?->toIso8601String(),
                'completed_at' => $attempt->completed_at?->toIso8601String(),
                'correction_unlocked_at' => $attempt->correction_unlocked_at?->toIso8601String(),
            ] : null,
        ];

        if ($withContent) {
            $data['content'] = [
                'type' => 'pdf',
                'document_url' => $documentUrl,
                'document_name' => $td->document_name,
                'message' => $td->hasDocument()
                    ? 'Ouvrez le document PDF pour traiter le TD.'
                    : 'Aucun document PDF n’a encore été ajouté pour ce TD.',
            ];
            $data['correction'] = [
                'type' => 'pdf',
                'available' => $canSeeCorrection,
                'status' => $correctionStatus,
                'label' => $correctionLabel,
                'detail' => $correctionDetail,
                'document_url' => $correctionUrl,
                'document_name' => $td->correction_document_name,
                'locked_message' => $canSeeCorrection ? null : $correctionDetail,
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

    private function formatDateTime($date): string
    {
        if (!$date) {
            return '';
        }

        return $date->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i');
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
