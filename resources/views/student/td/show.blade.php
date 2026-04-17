@extends('layouts.student')

@section('title', $td->title)

@section('content')
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

        @if($td->has_editable_version && $td->editable_html)
            <div class="td-content sun-editor-editable">{!! $td->editable_html !!}</div>
        @elseif(!$td->document_path)
            <div class="empty-state">Aucun contenu rédigé ni document joint pour ce TD.</div>
        @endif

        <div class="td-inline-actions" style="margin-top:20px;">
            <form method="POST" action="{{ route('student.td.complete', $td) }}">@csrf<button class="btn btn--primary">J'ai terminé ce TD</button></form>
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
        <div class="empty-state">Le corrigé est bien lié à ce TD et sera affiché ici dès que vos conditions d’accès seront remplies.</div>
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/suneditor@2.47.0/dist/css/suneditor.min.css">
@endsection
