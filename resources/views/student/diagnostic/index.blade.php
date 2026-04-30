@extends('layouts.student')

@section('title', 'Diagnostic pédagogique')

@section('content')
@php
    $options = $question?->options ?? [];
    $progress = $session->total_questions > 0 ? min(100, round((($session->current_step - 1) / $session->total_questions) * 100)) : 0;
@endphp

<section style="display:grid;gap:20px;max-width:920px;margin:0 auto;">
    <div class="panel" style="padding:26px;border-radius:28px;background:linear-gradient(135deg,#eff6ff,#ffffff);border:1px solid var(--line);box-shadow:var(--shadow);">
        <div style="display:flex;justify-content:space-between;gap:18px;align-items:flex-start;flex-wrap:wrap;">
            <div>
                <h1 style="margin:0 0 8px;font-size:clamp(1.6rem,4vw,2.4rem);letter-spacing:-.05em;">Diagnostic pédagogique</h1>
                <p style="margin:0;color:var(--muted);line-height:1.7;max-width:680px;">Réponds simplement. Tes réponses vont aider TIMAH ACADEMY et les enseignants à mieux comprendre tes objectifs, tes difficultés et ta manière d’apprendre.</p>
            </div>
            <div style="padding:10px 14px;border-radius:999px;background:#dbeafe;color:#1d4ed8;font-weight:900;">Question {{ min($session->current_step, $session->total_questions) }} / {{ $session->total_questions }}</div>
        </div>

        <div style="height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-top:22px;">
            <div style="width:{{ $progress }}%;height:100%;background:linear-gradient(90deg,#2563eb,#7c3aed);border-radius:999px;"></div>
        </div>
    </div>

    @if($question)
        <form method="POST" action="{{ route('student.diagnostic.answer') }}" class="panel" style="padding:26px;border-radius:28px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow);display:grid;gap:22px;">
            @csrf
            <input type="hidden" name="question_id" value="{{ $question->id }}">

            <div>
                <div style="display:inline-flex;padding:8px 12px;border-radius:999px;background:var(--primary-soft);color:var(--primary);font-weight:900;font-size:.84rem;margin-bottom:14px;text-transform:uppercase;letter-spacing:.06em;">{{ str_replace('_', ' ', $question->category) }}</div>
                <h2 style="margin:0;font-size:clamp(1.25rem,3vw,1.9rem);letter-spacing:-.04em;line-height:1.2;">{{ $question->question }}</h2>
            </div>

            @if($question->type === 'choice')
                <div style="display:grid;gap:12px;">
                    @foreach($options as $option)
                        <label style="display:flex;align-items:center;gap:12px;padding:15px 16px;border:1px solid var(--line);border-radius:18px;background:var(--panel-soft);font-weight:800;cursor:pointer;">
                            <input type="radio" name="answer" value="{{ $option }}" required>
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            @elseif($question->type === 'multi_choice')
                <div style="display:grid;gap:12px;">
                    @foreach($options as $option)
                        <label style="display:flex;align-items:center;gap:12px;padding:15px 16px;border:1px solid var(--line);border-radius:18px;background:var(--panel-soft);font-weight:800;cursor:pointer;">
                            <input type="checkbox" name="answer[]" value="{{ $option }}">
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            @elseif($question->type === 'score')
                <div style="display:grid;grid-template-columns:repeat(10,minmax(0,1fr));gap:8px;">
                    @foreach(range(1,10) as $score)
                        <label style="display:grid;place-items:center;min-height:48px;border:1px solid var(--line);border-radius:15px;background:var(--panel-soft);font-weight:900;cursor:pointer;">
                            <input type="radio" name="answer" value="{{ $score }}" required style="display:none;">
                            <span>{{ $score }}</span>
                        </label>
                    @endforeach
                </div>
            @else
                <textarea name="answer" rows="6" required placeholder="Écris ta réponse ici..." style="width:100%;padding:16px;border:1px solid var(--line);border-radius:18px;background:var(--panel-soft);line-height:1.7;"></textarea>
            @endif

            @error('answer')
                <div class="alert alert--error">{{ $message }}</div>
            @enderror

            <div style="display:flex;justify-content:flex-end;gap:12px;flex-wrap:wrap;">
                <button class="topbar-btn topbar-btn--primary">Continuer</button>
            </div>
        </form>
    @else
        <div class="panel" style="padding:24px;border-radius:24px;background:var(--panel);border:1px solid var(--line);">Aucune question disponible pour le moment.</div>
    @endif
</section>
@endsection
