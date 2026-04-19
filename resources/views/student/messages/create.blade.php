@extends('layouts.student')

@section('title', 'Nouveau message')

@push('styles')
<style>
    .student-message-create {
        display: grid;
        gap: 18px;
        max-width: 940px;
    }

    .student-message-create .compose-card {
        border: 1px solid var(--line);
        border-radius: 28px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .student-message-create .compose-head {
        padding: 18px 18px 16px;
        border-bottom: 1px solid var(--line);
        background: rgba(37, 99, 235, 0.03);
        display: grid;
        gap: 6px;
    }

    .student-message-create .compose-head h1 {
        margin: 0;
        font-size: clamp(1.45rem, 2.6vw, 2rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
    }

    .student-message-create .compose-head p {
        margin: 0;
        color: var(--muted);
        line-height: 1.6;
        font-size: .92rem;
    }

    .student-message-create .compose-body {
        padding: 18px;
        display: grid;
        gap: 16px;
    }

    .student-message-create .target-card {
        padding: 16px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(37,99,235,.08), transparent 28%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .student-message-create .target-avatar {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        color: #fff;
        font-weight: 900;
        font-size: 1rem;
        letter-spacing: -0.02em;
        box-shadow: var(--shadow-xs);
    }

    .student-message-create .target-text {
        display: grid;
        gap: 4px;
        min-width: 0;
    }

    .student-message-create .target-text strong {
        font-size: 1rem;
        letter-spacing: -0.02em;
    }

    .student-message-create .target-text span {
        color: var(--muted);
        font-size: .86rem;
        line-height: 1.5;
    }

    .student-message-create .form-grid {
        display: grid;
        gap: 16px;
    }

    .student-message-create .form-group {
        display: grid;
        gap: 8px;
    }

    .student-message-create .form-group label {
        font-size: .9rem;
        font-weight: 800;
        color: var(--text);
    }

    .student-message-create .form-input,
    .student-message-create .form-select,
    .student-message-create .form-textarea {
        width: 100%;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--text);
        outline: none;
        transition: .2s ease;
        box-sizing: border-box;
    }

    .student-message-create .form-input,
    .student-message-create .form-select {
        min-height: 50px;
        padding: 0 14px;
    }

    .student-message-create .form-textarea {
        min-height: 180px;
        padding: 14px;
        resize: vertical;
        line-height: 1.65;
    }

    .student-message-create .form-input:focus,
    .student-message-create .form-select:focus,
    .student-message-create .form-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37,99,235,.10);
    }

    .student-message-create .file-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .student-message-create .file-wrap input[type="file"] {
        width: 100%;
    }

    .student-message-create .compose-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .student-message-create .compose-hint {
        color: var(--muted);
        font-size: .85rem;
        line-height: 1.55;
    }

    @media (max-width: 720px) {
        .student-message-create .compose-body,
        .student-message-create .compose-head {
            padding-left: 14px;
            padding-right: 14px;
        }

        .student-message-create .compose-actions {
            align-items: stretch;
        }

        .student-message-create .compose-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<section class="student-message-create">
    <div class="compose-card">
        <div class="compose-head">
            <h1>Nouveau message</h1>
            <p>Envoyez rapidement une question à un enseignant de votre classe dans une interface plus simple.</p>
        </div>

        <div class="compose-body">
            @if($assignments->isEmpty())
                <div class="empty-state">Aucun enseignant affecté à votre classe pour le moment.</div>
            @else
                <form method="POST" action="{{ route('student.messages.store') }}" enctype="multipart/form-data" class="form-grid">
                    @csrf

                    @if(!empty($selectedAssignment))
                        @php
                            $teacherName = $selectedAssignment->teacher->full_name ?? $selectedAssignment->teacher->name ?? $selectedAssignment->teacher->username ?? 'Enseignant';
                            $subjectName = $selectedAssignment->subject->name ?? 'Matière';
                            $className = $selectedAssignment->schoolClass->name ?? 'Classe';
                            $avatar = collect(explode(' ', trim($teacherName)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
                            $avatar = $avatar !== '' ? $avatar : 'PR';
                        @endphp

                        <div class="target-card">
                            <span class="target-avatar">{{ $avatar }}</span>
                            <div class="target-text">
                                <strong>{{ $teacherName }}</strong>
                                <span>{{ $subjectName }} · {{ $className }}</span>
                                <span>Le message sera envoyé directement à cet enseignant.</span>
                            </div>
                        </div>

                        <input type="hidden" name="teacher_assignment_id" value="{{ $selectedAssignment->id }}">
                        <input type="hidden" name="title" value="{{ old('title', 'Message à ' . $teacherName . ' - ' . $subjectName) }}">
                    @else
                        <div class="form-group">
                            <label for="teacher_assignment_id">Destinataire</label>
                            <select id="teacher_assignment_id" name="teacher_assignment_id" class="form-select" required>
                                <option value="">Choisir un enseignant...</option>
                                @foreach($assignments as $assignment)
                                    <option value="{{ $assignment->id }}" {{ old('teacher_assignment_id') == $assignment->id ? 'selected' : '' }}>
                                        {{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? $assignment->teacher->username ?? '-' }} — {{ $assignment->subject->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Objet</label>
                            <input id="title" type="text" name="title" value="{{ old('title') }}" class="form-input" required>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" class="form-textarea" required>{{ old('message') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Pièce jointe</label>
                        <div class="file-wrap">
                            <input id="attachment" type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                            <span class="compose-hint">Formats acceptés : PDF, Word, image. Taille max : 5 Mo.</span>
                        </div>
                    </div>

                    <div class="compose-actions">
                        <span class="compose-hint">Conseil : posez une question claire et directe pour recevoir une réponse plus rapide.</span>
                        <button type="submit" class="btn btn--primary">Envoyer le message</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
