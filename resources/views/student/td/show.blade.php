@extends('layouts.student')

@section('title', $td->title)

@section('content')
@php
    $remainingSeconds = (int) ($correctionSecondsRemaining ?? 0);
    $isCompleted = $attempt && $attempt->status === \App\Models\TdAttempt::STATUS_COMPLETED;
@endphp

<section class="panel td-show-panel">
    <div class="panel__head td-show-panel__head">
        <div>
            <h2>{{ $td->title }}</h2>
            <span class="muted">{{ $td->subject->name ?? '-' }} · {{ $td->schoolClass->name ?? '-' }} · {{ $td->chapter_label ?: 'Sans chapitre' }}</span>
        </div>
        <div class="td-badge-group">
            <span class="td-badge td-badge--{{ $td->access_level }}">{{ $td->access_level }}</span>
            <span class="td-badge td-badge--difficulty">{{ $td->difficulty }}</span>
        </div>
    </div>
    <div class="panel__body td-show-body">
        @if($td->document_path)
            <div class="td-doc-box">
                <strong>Document source du TD</strong>
                <div class="muted">{{ $td->document_name }} — {{ $td->humanDocumentSize() }}</div>
                <div class="td-inline-actions"><a class="btn btn--ghost" href="{{ route('student.td.document', $td) }}">Ouvrir / télécharger</a></div>
            </div>
        @endif

        @if($td->hasCorrectionContent() && !$canSeeCorrection)
            <div class="td-countdown-card" data-countdown-seconds="{{ $remainingSeconds }}">
                <div>
                    <span class="td-countdown-label">Corrigé verrouillé</span>
                    <h3>Temps minimum de travail avant correction</h3>
                    <p>
                        Le compte à rebours commence dès la première ouverture du TD. Même si vous cliquez sur « J’ai terminé ce TD », le corrigé restera bloqué jusqu’à la fin du délai.
                    </p>
                </div>
                <div class="td-countdown-timer">
                    <strong data-countdown-display>{{ gmdate('H:i:s', max(0, $remainingSeconds)) }}</strong>
                    <span>{{ $isCompleted ? 'TD terminé' : 'Terminez aussi le TD' }}</span>
                </div>
            </div>
        @endif

        @if($td->has_editable_version && $td->editable_html)
            <div class="td-content sun-editor-editable">{!! $td->editable_html !!}</div>
        @elseif(!$td->document_path)
            <div class="empty-state">Aucun contenu rédigé ni document joint pour ce TD.</div>
        @endif

        <div class="td-inline-actions" style="margin-top:20px;">
            @if($isCompleted)
                <span class="td-completed-pill">TD déjà marqué comme terminé</span>
            @else
                <form method="POST" action="{{ route('student.td.complete', $td) }}">@csrf<button class="btn btn--primary">J'ai terminé ce TD</button></form>
            @endif
        </div>
    </div>
</section>

@if($canSeeCorrection && $td->hasCorrectionContent())
<section class="panel td-show-panel">
    <div class="panel__head"><h2>Corrigé</h2></div>
    <div class="panel__body">
        @if($td->correction_html)
            <div class="td-content sun-editor-editable">{!! $td->correction_html !!}</div>
        @endif
        @if($td->correction_document_path)
            <div class="td-doc-box" style="margin-top:18px;">
                <strong>Document corrigé</strong>
                <div class="muted">{{ $td->correction_document_name }} — {{ $td->humanCorrectionDocumentSize() }}</div>
                <div class="td-inline-actions"><a class="btn btn--ghost" href="{{ route('student.td.correction_document', $td) }}">Ouvrir / télécharger</a></div>
            </div>
        @endif
    </div>
</section>
@elseif($td->hasCorrectionContent())
<section class="panel td-show-panel">
    <div class="panel__head"><h2>Corrigé</h2></div>
    <div class="panel__body">
        <div class="empty-state">
            @if(!$isCompleted)
                Cliquez sur « J’ai terminé ce TD » après votre travail. Le corrigé sera affiché seulement après la fin du compte à rebours.
            @elseif($remainingSeconds > 0)
                TD terminé. Le corrigé sera débloqué automatiquement à la fin du compte à rebours.
            @else
                Le corrigé sera disponible dans quelques instants. Rechargez la page si nécessaire.
            @endif
        </div>
    </div>
</section>
@endif

<section class="panel td-show-panel">
    <div class="panel__head"><h2>Questions sur ce TD</h2></div>
    <div class="panel__body">
        @if($thread)
            <div class="td-thread-list">
                @foreach($thread->messages as $message)
                    <div class="td-thread-bubble td-thread-bubble--{{ $message->sender_role === 'student' ? 'me' : 'other' }}">
                        <div class="td-thread-meta">{{ $message->sender->full_name ?? $message->sender->name ?? $message->sender->username ?? '-' }} · {{ $message->created_at->format('d/m/Y H:i') }}</div>
                        <div class="td-thread-body">{!! $message->message_html !!}</div>
                        @if($message->attachment_path)
                            <div class="td-thread-attachment"><a href="{{ route('student.td.attachment', $message) }}">{{ $message->attachment_name ?: 'Pièce jointe' }}</a></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('student.td.ask', $td) }}" enctype="multipart/form-data" class="td-question-form">
            @csrf
            <textarea name="message_html" placeholder="Posez votre question sur ce TD..." required></textarea>
            <div class="td-inline-actions">
                <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.txt">
                <button class="btn btn--primary">Envoyer la question</button>
            </div>
        </form>
    </div>
</section>

<style>
    .td-countdown-card {
        margin: 18px 0;
        padding: 18px;
        border-radius: 22px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        background: linear-gradient(135deg, rgba(15,118,110,.12), rgba(245,158,11,.12));
        border: 1px solid rgba(15,118,110,.18);
        box-shadow: 0 16px 34px rgba(15,23,42,.08);
    }

    .td-countdown-label,
    .td-completed-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 900;
        color: #115e59;
        background: #ccfbf1;
    }

    .td-countdown-card h3 {
        margin: 10px 0 6px;
        font-size: 1.1rem;
    }

    .td-countdown-card p {
        margin: 0;
        color: var(--muted, #64748b);
        line-height: 1.55;
    }

    .td-countdown-timer {
        min-width: 132px;
        padding: 16px;
        border-radius: 20px;
        text-align: center;
        background: rgba(255,255,255,.74);
        border: 1px solid rgba(255,255,255,.62);
    }

    .td-countdown-timer strong {
        display: block;
        font-size: 1.55rem;
        letter-spacing: -.04em;
        color: #0f766e;
    }

    .td-countdown-timer span {
        display: block;
        margin-top: 4px;
        font-size: .78rem;
        color: #475569;
        font-weight: 800;
    }

    @media (max-width: 720px) {
        .td-countdown-card {
            grid-template-columns: 1fr;
        }
        .td-countdown-timer {
            width: 100%;
        }
    }
</style>

<script>
(() => {
    const box = document.querySelector('[data-countdown-seconds]');
    if (!box) return;

    let seconds = parseInt(box.getAttribute('data-countdown-seconds') || '0', 10);
    const display = box.querySelector('[data-countdown-display]');

    const formatTime = (value) => {
        const safe = Math.max(0, value);
        const h = String(Math.floor(safe / 3600)).padStart(2, '0');
        const m = String(Math.floor((safe % 3600) / 60)).padStart(2, '0');
        const s = String(safe % 60).padStart(2, '0');
        return `${h}:${m}:${s}`;
    };

    const tick = () => {
        if (display) display.textContent = formatTime(seconds);
        if (seconds <= 0) {
            window.location.reload();
            return;
        }
        seconds -= 1;
        setTimeout(tick, 1000);
    };

    tick();
})();
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/suneditor@2.47.0/dist/css/suneditor.min.css">
@endsection
