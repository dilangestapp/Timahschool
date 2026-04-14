@extends('layouts.teacher')

@section('title', 'Conversation TD')
@section('page_title', 'Conversation TD')
@section('page_subtitle', 'Répondez directement à l’élève dans le contexte exact du TD.')

@push('head')
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
@endpush

@section('content')
<section class="teacher-section">
    <div class="teacher-section__head"><h2>{{ $thread->tdSet->title ?? '-' }}</h2></div>
    <div class="teacher-detail-grid">
        <div><strong>Élève</strong><span>{{ $thread->student->full_name ?? $thread->student->name ?? '-' }}</span></div>
        <div><strong>Classe</strong><span>{{ $thread->schoolClass->name ?? '-' }}</span></div>
        <div><strong>Matière</strong><span>{{ $thread->subject->name ?? '-' }}</span></div>
        <div><strong>Statut</strong><span class="teacher-badge teacher-badge--{{ $thread->status }}">{{ $thread->status }}</span></div>
    </div>

    <div class="teacher-conversation">
        @forelse($thread->messages as $message)
            <article class="teacher-bubble {{ $message->sender_role === 'teacher' ? 'teacher-bubble--out' : 'teacher-bubble--in' }}">
                <div class="teacher-bubble__meta">{{ $message->sender->full_name ?? $message->sender->name ?? '-' }} • {{ $message->created_at->format('d/m/Y H:i') }}</div>
                @if($message->message_html)
                    <div class="teacher-bubble__body">{!! $message->message_html !!}</div>
                @endif
                @if($message->attachment_path)
                    <div class="teacher-doc-card"><a href="{{ route('teacher.td.questions.attachment', $message) }}">{{ $message->attachment_name }}</a></div>
                @endif
            </article>
        @empty
            <div class="teacher-empty-state">Aucun message dans cette conversation.</div>
        @endforelse
    </div>
</section>

<form method="POST" action="{{ route('teacher.td.questions.reply', $thread) }}" enctype="multipart/form-data" class="teacher-section">
    @csrf
    <div class="teacher-form-group teacher-form-group--full">
        <label for="message_html">Réponse</label>
        <textarea class="js-td-editor" name="message_html" id="message_html" rows="8"></textarea>
    </div>
    <div class="teacher-form-grid teacher-form-grid--two">
        <div class="teacher-form-group">
            <label for="attachment">Pièce jointe</label>
            <input type="file" name="attachment" id="attachment">
        </div>
        <div class="teacher-form-group">
            <label for="status">Statut de la conversation</label>
            <select name="status" id="status">
                <option value="answered">Répondu</option>
                <option value="open">Laisser ouvert</option>
                <option value="closed">Clore</option>
            </select>
        </div>
    </div>
    <div class="teacher-form-actions">
        <button type="submit" class="teacher-btn teacher-btn--primary">Envoyer la réponse</button>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
    document.querySelectorAll('.js-td-editor').forEach(function (textarea) {
        var wrapper = document.createElement('div');
        wrapper.className = 'teacher-editor';
        var editor = document.createElement('div');
        editor.innerHTML = textarea.value || '';
        textarea.style.display = 'none';
        textarea.parentNode.insertBefore(wrapper, textarea);
        wrapper.appendChild(editor);
        var quill = new Quill(editor, { theme: 'snow', modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] } });
        textarea.form.addEventListener('submit', function () { textarea.value = quill.root.innerHTML; });
    });
</script>
@endpush
