@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie')
@section('page_subtitle', 'Version stable de la messagerie enseignant.')

@section('content')
@php
    $threads = collect($threads ?? []);
    $assignments = collect($assignments ?? []);
@endphp

<style>
    .msg-wrap{display:grid;gap:18px}.msg-hero{background:linear-gradient(135deg,#0f172a,#1d4ed8);color:#fff;border-radius:24px;padding:22px}.msg-hero h2{margin:0 0 8px;font-size:2rem}.msg-hero p{color:#dbeafe}.msg-grid{display:grid;grid-template-columns:360px 1fr;gap:16px}.msg-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.msg-thread{display:block;border:1px solid #e5e7eb;border-radius:14px;padding:12px;margin-bottom:10px;text-decoration:none;color:#0f172a;background:#f8fafc}.msg-thread.active{border-color:#2563eb;background:#eff6ff}.msg-thread strong{display:block}.msg-thread span{color:#64748b}.msg-empty{padding:18px;background:#f8fafc;border-radius:14px;color:#64748b}.msg-form{display:grid;gap:10px}.msg-form textarea,.msg-form input,.msg-form select{border:1px solid #cbd5e1;border-radius:12px;padding:10px}.msg-btn{border:0;border-radius:12px;background:#16a34a;color:#fff;padding:12px;font-weight:900;cursor:pointer}.msg-bubble{background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:12px;margin-bottom:10px}.msg-bubble.me{background:#dcfce7;border-color:#bbf7d0}.msg-meta{font-size:12px;color:#64748b;margin-top:5px}@media(max-width:900px){.msg-grid{grid-template-columns:1fr}}
</style>

<div class="msg-wrap">
    <section class="msg-hero">
        <h2>Messagerie enseignant</h2>
        <p>Cette version stable permet d’ouvrir la messagerie et d’envoyer des messages aux élèves affectés.</p>
    </section>

    @if($assignments->isNotEmpty())
        <section class="msg-card">
            <h3>Message à toute une classe</h3>
            <form method="POST" action="{{ route('teacher.messages.broadcast') }}" class="msg-form">
                @csrf
                <select name="school_class_id" required>
                    @foreach($assignments->unique('school_class_id') as $assignment)
                        <option value="{{ $assignment->school_class_id }}">{{ $assignment->schoolClass->name ?? 'Classe' }}</option>
                    @endforeach
                </select>
                <input type="text" name="message" placeholder="Message à envoyer à toute la classe" required>
                <button class="msg-btn" type="submit">Envoyer à la classe</button>
            </form>
        </section>
    @endif

    <section class="msg-grid">
        <aside class="msg-card">
            <h3>Élèves affectés</h3>
            @forelse($threads as $thread)
                @php
                    $student = $thread->student;
                    $name = $student->full_name ?? $student->name ?? $student->username ?? 'Élève';
                @endphp
                <a class="msg-thread {{ (int)($selectedStudentId ?? 0) === (int)$student->id ? 'active' : '' }}" href="{{ route('teacher.messages.index', ['student' => $student->id]) }}">
                    <strong>{{ $name }}</strong>
                    <span>{{ $student->studentProfile->schoolClass->name ?? 'Classe inconnue' }}</span>
                </a>
            @empty
                <div class="msg-empty">Aucun élève affecté à ce compte enseignant.</div>
            @endforelse
        </aside>

        <main class="msg-card">
            @if($selectedThread ?? null)
                @php
                    $student = $selectedThread->student;
                    $studentName = $student->full_name ?? $student->name ?? $student->username ?? 'Élève';
                    $messages = collect($selectedThread->messages ?? []);
                @endphp
                <h3>Conversation avec {{ $studentName }}</h3>
                @forelse($messages as $message)
                    <div class="msg-bubble {{ method_exists($message, 'isFromTeacher') && $message->isFromTeacher() ? 'me' : '' }}">
                        <div>{{ $message->message }}</div>
                        <div class="msg-meta">{{ $message->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                @empty
                    <div class="msg-empty">Aucun message avec cet élève.</div>
                @endforelse

                <form class="msg-form" method="POST" action="{{ route('teacher.messages.send') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="teacher_assignment_id" value="{{ $selectedThread->assignment->id ?? '' }}">
                    <textarea name="message" placeholder="Écrire un message"></textarea>
                    <input type="file" name="attachment">
                    <button class="msg-btn" type="submit">Envoyer</button>
                </form>
            @else
                <div class="msg-empty">Sélectionnez un élève pour ouvrir une conversation.</div>
            @endif
        </main>
    </section>
</div>
@endsection
