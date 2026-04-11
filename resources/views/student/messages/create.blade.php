@extends('layouts.student')

@section('title', 'Écrire à un enseignant')

@section('content')
<section class="panel">
    <div class="panel__head">
        <h2>Contacter un enseignant</h2>
        <span class="muted">Seuls les enseignants liés à votre classe sont proposés.</span>
    </div>
    <div class="panel__body" style="padding:24px;">
        @if($assignments->isEmpty())
            <div class="empty-state">Aucun enseignant affecté à votre classe pour le moment.</div>
        @else
            <form method="POST" action="{{ route('student.messages.store') }}" enctype="multipart/form-data" class="form-grid">
                @csrf
                <div class="form-group">
                    <label>Destinataire (classe / matière)</label>
                    <select name="teacher_assignment_id" required>
                        <option value="">Choisir...</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->id }}">{{ $assignment->teacher->full_name ?? $assignment->teacher->name ?? $assignment->teacher->username ?? '-' }} — {{ $assignment->subject->name ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Objet</label>
                    <input type="text" name="title" value="{{ old('title') }}" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="6" style="width:100%; border-radius:16px; border:1px solid #dbe3f0; padding:16px;">{{ old('message') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Pièce jointe (PDF, Word, image)</label>
                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                </div>
                <div>
                    <button type="submit" class="btn btn--primary">Envoyer le message</button>
                </div>
            </form>
        @endif
    </div>
</section>
@endsection
