@extends('layouts.admin')
@section('title','Banque pédagogique')
@section('page_title','Banque pédagogique')
@section('page_subtitle','Stock organisé des TD, cours, quiz et évaluations par classe.')
@section('content')
<div class="admin-compact-page">
@if(session('success'))<div class="admin-success-box">{{ session('success') }}</div>@endif
@if(session('error'))<div class="admin-error-box">{{ session('error') }}</div>@endif
@if($errors->any())<div class="admin-error-box">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<section class="admin-list-panel" style="border:2px solid #bfdbfe;background:#eff6ff;">
<div class="admin-list-panel__head"><div><h2>Navigation rapide</h2><p>Après publication, un TD passe dans “Déjà utilisés / publiés”. Clique ici pour afficher directement l’historique.</p></div></div>
<div style="display:flex;gap:10px;flex-wrap:wrap;">
<a class="btn btn--primary" href="{{ route('admin.pedagogical-bank.index',['status'=>'used','content_type'=>$type,'school_class_id'=>$classId,'subject_id'=>$subjectId]) }}">Historique des TD publiés</a>
<a class="btn btn--ghost" href="{{ route('admin.pedagogical-bank.index',['status'=>'all','content_type'=>$type,'school_class_id'=>$classId,'subject_id'=>$subjectId]) }}">Voir toute la banque</a>
<a class="btn btn--ghost" href="{{ route('admin.pedagogical-bank.index',['status'=>'available','content_type'=>$type,'school_class_id'=>$classId,'subject_id'=>$subjectId]) }}">Disponibles</a>
<a class="btn btn--ghost" href="{{ route('admin.pedagogical-bank.index,['status'=>'archived','content_type'=>$type,'school_class_id'=>$classId,'subject_id'=>$subjectId]) }}">Archivés</a>
</div>
</section>

<section class="admin-list-panel">
<div class="admin-list-panel__head"><div><h2>Import rapide</h2><p>Ajoute le document principal et le corrigé si disponible. Tu peux corriger la classe et la matière après import.</p></div></div>
<form method="POST" action="{{ route('admin.pedagogical-bank.store') }}" enctype="multipart/form-data" class="admin-form">@csrf
<div class="admin-form-grid">
<div class="form-group"><label>Type</label><select name="content_type"><option value="td">TD</option><option value="course">Cours</option><option value="quiz">Quiz</option><option value="evaluation">Évaluation</option><option value="resource">Ressource</option></select></div>
<div class="form-group"><label>Titre facultatif</label><input name="title" placeholder="Titre automatique si vide"></div>
<div class="form-group"><label>Classe</label><select name="school_class_id"><option value="">Auto / à corriger après</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Matière</label><select name="subject_id"><option value="">Auto / à corriger après</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Document sujet</label><input type="file" name="document" required></div>
<div class="form-group"><label>Corrigé facultatif</label><input type="file" name="correction_document"></div>
</div><button class="btn btn--primary">Importer</button></form>
</section>

<section class="admin-list-panel">
<div class="admin-list-panel__head"><div><h2>Filtres</h2><p>Classe, type, matière, statut.</p></div></div>
<form method="GET" class="admin-form"><div class="admin-form-grid">
<div class="form-group"><label>Classe</label><select name="school_class_id"><option value="">Toutes</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string)$classId===(string)$class->id)>{{ $class->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Type</label><select name="content_type"><option value="all">Tous</option><option value="td" @selected($type==='td')>TD</option><option value="course" @selected($type==='course')>Cours</option><option value="quiz" @selected($type==='quiz')>Quiz</option><option value="evaluation" @selected($type==='evaluation')>Évaluation</option><option value="resource" @selected($type==='resource')>Ressource</option></select></div>
<div class="form-group"><label>Matière</label><select name="subject_id"><option value="">Toutes</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string)$subjectId===(string)$subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Statut</label><select name="status"><option value="available" @selected($status==='available')>Disponibles</option><option value="used" @selected($status==='used')>Déjà utilisés / publiés</option><option value="archived" @selected($status==='archived')>Archivés</option><option value="all" @selected($status==='all')>Tous</option></select></div>
</div><button class="btn btn--ghost">Filtrer</button></form>
</section>

@php
$sections = [
    ['title'=>'Disponibles','items'=>$availableItems,'tone'=>'available','bg'=>'#ffffff','border'=>'#dbeafe'],
    ['title'=>'Déjà utilisés / publiés','items'=>$usedItems,'tone'=>'used','bg'=>'#fff7ed','border'=>'#fed7aa'],
    ['title'=>'Archivés','items'=>$archivedItems,'tone'=>'archived','bg'=>'#f1f5f9','border'=>'#cbd5e1'],
];
@endphp

@foreach($sections as $section)
<section class="admin-list-panel">
<div class="admin-list-panel__head"><div><h2>{{ $section['title'] }}</h2></div></div>
@forelse($section['items'] as $item)
<article class="admin-clean-row" style="background:{{ $section['bg'] }};border:1px solid {{ $section['border'] }};border-radius:18px;padding:16px;margin-bottom:12px;align-items:flex-start;display:block;">
<div style="display:flex;gap:18px;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;">
<div class="admin-clean-title" style="flex:1;min-width:280px;"><strong>{{ $item->title }}</strong><span>{{ $item->code ?: 'Sans code' }} · {{ $item->type_label }} · {{ $item->schoolClass?->name ?: $item->inferred_class ?: 'Classe non définie' }} · {{ $item->subject?->name ?: $item->inferred_subject ?: 'Matière non définie' }}</span><p style="color:var(--muted)">Sujet : {{ $item->document_name ?: 'non ajouté' }}<br>Corrigé : {{ $item->correction_document_name ?: 'non ajouté' }}<br>Dernière publication : {{ $item->last_scheduled_at ? $item->last_scheduled_at->format('d/m/Y H:i') : 'non publiée' }}</p></div>
<div class="admin-clean-meta"><span class="admin-badge">{{ $item->status_label }}</span><br><small>{{ $item->times_used }} publication(s)</small></div>
</div>
@if($section['tone']==='used')
<div style="margin:10px 0;padding:12px;border-radius:14px;background:#ffedd5;color:#9a3412;font-weight:800;">✅ TD publié. Pour le voir côté Flutter, l’élève doit être dans cette classe exacte : {{ $item->schoolClass?->name ?: 'classe non définie' }}.</div>
@endif
<details style="margin-top:12px;"><summary class="btn btn--ghost" style="cursor:pointer;display:inline-block;">Modifier la fiche</summary>
<form method="POST" action="{{ route('admin.pedagogical-bank.update',$item) }}" enctype="multipart/form-data" class="admin-form" style="margin-top:12px;">@csrf
<div class="admin-form-grid">
<div class="form-group"><label>Titre</label><input name="title" value="{{ $item->title }}" required></div>
<div class="form-group"><label>Code</label><input name="code" value="{{ $item->code }}"></div>
<div class="form-group"><label>Type</label><select name="content_type"><option value="td" @selected($item->content_type==='td')>TD</option><option value="course" @selected($item->content_type==='course')>Cours</option><option value="quiz" @selected($item->content_type==='quiz')>Quiz</option><option value="evaluation" @selected($item->content_type==='evaluation')>Évaluation</option><option value="resource" @selected($item->content_type==='resource')>Ressource</option></select></div>
<div class="form-group"><label>Statut</label><select name="status"><option value="available" @selected($item->status==='available')>Disponible</option><option value="used" @selected($item->status==='used')>Déjà utilisé / publié</option><option value="archived" @selected($item->status==='archived')>Archivé</option></select></div>
<div class="form-group"><label>Classe</label><select name="school_class_id"><option value="">Choisir</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((int)$item->school_class_id===(int)$class->id)>{{ $class->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Matière</label><select name="subject_id"><option value="">Choisir</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((int)$item->subject_id===(int)$subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Thème</label><input name="theme" value="{{ $item->theme }}"></div>
<div class="form-group"><label>Remplacer le sujet</label><input type="file" name="document"></div>
<div class="form-group"><label>Remplacer le corrigé</label><input type="file" name="correction_document"></div>
</div><button class="btn btn--primary">Sauvegarder la fiche</button></form></details>
@if($item->content_type==='td')
<details style="margin-top:10px;"><summary class="btn btn--primary" style="cursor:pointer;display:inline-block;">Programmer / publier</summary>
<form method="POST" action="{{ route('admin.pedagogical-bank.schedule',$item) }}" class="admin-form" style="margin-top:12px;">@csrf
<div class="admin-form-grid">
<div class="form-group"><label>Classe de publication</label><select name="school_class_id"><option value="">Utiliser la classe de la fiche</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected((int)$item->school_class_id===(int)$class->id)>{{ $class->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Matière de publication</label><select name="subject_id"><option value="">Utiliser la matière de la fiche</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((int)$item->subject_id===(int)$subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Date/heure</label><input type="datetime-local" name="publish_at"></div>
<div class="form-group"><label>Délai corrigé en minutes</label><input type="number" name="correction_delay_minutes" value="60" min="0" max="1440"></div>
<div class="form-group"><label>Accès</label><select name="access_level"><option value="free">Essai gratuit / libre</option><option value="premium">Abonné</option></select></div>
</div><button class="btn btn--primary">Publier ce TD</button></form></details>
@endif
<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
@if($section['tone']!=='archived')<form method="POST" action="{{ route('admin.pedagogical-bank.archive',$item) }}">@csrf<button class="btn btn--ghost admin-btn-danger">Archiver</button></form>@else<form method="POST" action="{{ route('admin.pedagogical-bank.restore',$item) }}">@csrf<button class="btn btn--ghost">Remettre disponible</button></form>@endif
</div>
</article>
@empty<div class="admin-empty-box">Aucun contenu.</div>@endforelse
</section>
@endforeach
</div>
@endsection
