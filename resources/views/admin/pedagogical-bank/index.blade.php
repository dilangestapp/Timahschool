@extends('layouts.admin')
@section('title','Banque pédagogique')
@section('page_title','Banque pédagogique')
@section('page_subtitle','Stock organisé des TD, cours, quiz et évaluations par classe.')
@section('content')
<div class="admin-compact-page">
<section class="admin-list-panel">
<div class="admin-list-panel__head"><div><h2>Import rapide</h2><p>Ajoute le document principal et le corrigé si disponible.</p></div></div>
<form method="POST" action="{{ route('admin.pedagogical-bank.store') }}" enctype="multipart/form-data" class="admin-form">@csrf
<div class="admin-form-grid">
<div class="form-group"><label>Type</label><select name="content_type"><option value="td">TD</option><option value="course">Cours</option><option value="quiz">Quiz</option><option value="evaluation">Évaluation</option><option value="resource">Ressource</option></select></div>
<div class="form-group"><label>Titre facultatif</label><input name="title" placeholder="Titre automatique si vide"></div>
<div class="form-group"><label>Classe facultative</label><select name="school_class_id"><option value="">Auto</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Matière facultative</label><select name="subject_id"><option value="">Auto</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Document</label><input type="file" name="document" required></div>
<div class="form-group"><label>Corrigé facultatif</label><input type="file" name="correction_document"></div>
</div><button class="btn btn--primary">Importer</button></form>
</section>

<section class="admin-list-panel">
<div class="admin-list-panel__head"><div><h2>Filtres</h2><p>Classe, type, matière, statut.</p></div></div>
<form method="GET" class="admin-form"><div class="admin-form-grid">
<div class="form-group"><label>Classe</label><select name="school_class_id"><option value="">Toutes</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string)$classId===(string)$class->id)>{{ $class->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Type</label><select name="content_type"><option value="all">Tous</option><option value="td" @selected($type==='td')>TD</option><option value="course" @selected($type==='course')>Cours</option><option value="quiz" @selected($type==='quiz')>Quiz</option><option value="evaluation" @selected($type==='evaluation')>Évaluation</option><option value="resource" @selected($type==='resource')>Ressource</option></select></div>
<div class="form-group"><label>Matière</label><select name="subject_id"><option value="">Toutes</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string)$subjectId===(string)$subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Statut</label><select name="status"><option value="available" @selected($status==='available')>Disponibles</option><option value="used" @selected($status==='used')>Déjà utilisés</option><option value="archived" @selected($status==='archived')>Archivés</option><option value="all" @selected($status==='all')>Tous</option></select></div>
</div><button class="btn btn--ghost">Filtrer</button></form>
</section>

@php
$sections = [
    ['title'=>'Disponibles','items'=>$availableItems,'tone'=>'available','bg'=>'#ffffff','border'=>'#dbeafe'],
    ['title'=>'Déjà utilisés','items'=>$usedItems,'tone'=>'used','bg'=>'#fff7ed','border'=>'#fed7aa'],
    ['title'=>'Archivés','items'=>$archivedItems,'tone'=>'archived','bg'=>'#f1f5f9','border'=>'#cbd5e1'],
];
@endphp

@foreach($sections as $section)
<section class="admin-list-panel">
<div class="admin-list-panel__head"><div><h2>{{ $section['title'] }}</h2></div></div>
@forelse($section['items'] as $item)
<article class="admin-clean-row" style="background:{{ $section['bg'] }};border:1px solid {{ $section['border'] }};border-radius:18px;padding:16px;margin-bottom:12px;align-items:flex-start;">
<div class="admin-clean-title"><strong>{{ $item->title }}</strong><span>{{ $item->code ?: 'Sans code' }} · {{ $item->type_label }} · {{ $item->schoolClass?->name ?: $item->inferred_class ?: 'Classe non définie' }} · {{ $item->subject?->name ?: $item->inferred_subject ?: 'Matière non définie' }}</span><p style="color:var(--muted)">Sujet : {{ $item->document_name ?: 'non ajouté' }}<br>Corrigé : {{ $item->correction_document_name ?: 'non ajouté' }}</p></div>
<div class="admin-clean-meta"><span class="admin-badge">{{ $item->status_label }}</span><br><small>{{ $item->times_used }} utilisation(s)</small></div>
<div style="min-width:220px;display:grid;gap:8px;">
@if($item->content_type==='td')
<details><summary class="btn btn--primary" style="cursor:pointer;text-align:center;">Programmer</summary><form method="POST" action="{{ route('admin.pedagogical-bank.schedule',$item) }}" class="admin-form" style="margin-top:10px;">@csrf<input type="datetime-local" name="publish_at"><input type="number" name="correction_delay_minutes" value="60" min="0" max="1440"><select name="access_level"><option value="free">Essai gratuit / libre</option><option value="premium">Abonné</option></select><button class="btn btn--primary">Publier</button></form></details>
@endif
@if($section['tone']!=='archived')<form method="POST" action="{{ route('admin.pedagogical-bank.archive',$item) }}">@csrf<button class="btn btn--ghost admin-btn-danger">Archiver</button></form>@else<form method="POST" action="{{ route('admin.pedagogical-bank.restore',$item) }}">@csrf<button class="btn btn--ghost">Remettre disponible</button></form>@endif
</div></article>
@empty<div class="admin-empty-box">Aucun contenu.</div>@endforelse
</section>
@endforeach
</div>
@endsection
