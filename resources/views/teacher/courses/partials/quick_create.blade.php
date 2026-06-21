@if($assignments->isNotEmpty())
<details class="course-writer-card course-writer-collapsible">
    <summary class="course-writer-head course-writer-summary">
        <span><strong>+ Rédiger un nouveau cours</strong><br><small>Saisir directement, importer un fichier, enregistrer ou publier.</small></span>
        <span class="course-writer-summary-btn">Ouvrir l’éditeur</span>
    </summary>
    <form method="POST" action="{{ route('teacher.courses.store') }}" enctype="multipart/form-data" class="course-writer-form">
        @csrf
        <div class="course-writer-grid">
            <div>
                <label class="course-writer-label" for="teacher_assignment_id">Classe et matière</label>
                <select id="teacher_assignment_id" name="teacher_assignment_id" class="course-writer-select" required>
                    @foreach($assignments as $assignment)
                        <option value="{{ $assignment->id }}">{{ $assignment->schoolClass->name ?? '-' }} — {{ $assignment->subject->name ?? '-' }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="course-writer-label" for="order">Ordre</label><input id="order" type="number" name="order" class="course-writer-input" min="0" value="0"></div>
            <div class="course-writer-full"><label class="course-writer-label" for="title">Titre du cours</label><input id="title" type="text" name="title" class="course-writer-input" required placeholder="Exemple : Les bases de l’algorithme"></div>
            <div class="course-writer-full"><label class="course-writer-label" for="description">Résumé</label><textarea id="description" name="description" class="course-writer-textarea" placeholder="Présentez brièvement ce que l’élève va apprendre."></textarea></div>
            <div class="course-writer-full"><label class="course-writer-label" for="objectives">Objectifs pédagogiques</label><textarea id="objectives" name="objectives" class="course-writer-textarea" placeholder="À la fin de ce cours, l’élève doit être capable de..."></textarea></div>
            <div class="course-writer-full"><label class="course-writer-label" for="document">Importer un fichier</label><input id="document" type="file" name="document" class="course-writer-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.rtf,.odt"><small class="course-writer-help">Le cours peut être entièrement rédigé ici, ou accompagné d’un fichier.</small></div>
            <div class="course-writer-full"><label class="course-writer-label">Éditeur du cours</label>@include('teacher.courses.partials.writer', ['field'=>'content_html','target'=>'#content_html','value'=>''])</div>
        </div>
        <div class="course-word-actions"><button type="submit" name="status" value="draft" class="course-writer-secondary">Enregistrer brouillon</button><button type="submit" name="status" value="published" class="course-writer-primary">Publier le cours</button></div>
    </form>
</details>
@endif
