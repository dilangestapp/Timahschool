@extends('layouts.teacher')

@section('title', 'TB Département')
@section('page_title', 'TB Département / Filière')
@section('page_subtitle', 'Gestion pédagogique du département, des classes liées et des matières liées.')

@section('content')
@php
    $schemaReady = \Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities') && \Illuminate\Support\Facades\Schema::hasTable('teaching_departments');
    $responsibility = null;
    $department = null;
    $subject = null;
    $class = null;

    if ($schemaReady) {
        $responsibility = \Illuminate\Support\Facades\DB::table('pedagogical_responsibilities')
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('scope_type', 'department')
                    ->orWhere('role_title', 'like', '%Responsable de département%')
                    ->orWhere('role_title', 'like', '%filière%');
            })
            ->orderByDesc('id')
            ->first();

        if ($responsibility && $responsibility->teaching_department_id) {
            $department = \Illuminate\Support\Facades\DB::table('teaching_departments')->where('id', $responsibility->teaching_department_id)->first();
            if ($department && $department->subject_id && \Illuminate\Support\Facades\Schema::hasTable('subjects')) {
                $subject = \Illuminate\Support\Facades\DB::table('subjects')->where('id', $department->subject_id)->first();
            }
            if ($department && $department->school_class_id && \Illuminate\Support\Facades\Schema::hasTable('school_classes')) {
                $class = \Illuminate\Support\Facades\DB::table('school_classes')->where('id', $department->school_class_id)->first();
            }
        }
    }
@endphp

<style>
    .dept-wrap{display:grid;gap:18px}.dept-hero{background:linear-gradient(135deg,#0f172a,#1d4ed8);color:#fff;border-radius:26px;padding:22px;box-shadow:0 22px 55px rgba(15,23,42,.22)}.dept-hero h2{margin:6px 0;font-size:clamp(2rem,5vw,3rem)}.dept-hero p{color:#dbeafe;max-width:850px}.dept-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}.dept-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 14px;border-radius:13px;text-decoration:none;font-weight:900;background:#fff;color:#0f172a}.dept-btn--green{background:#16a34a;color:#fff}.dept-btn--blue{background:#2563eb;color:#fff}.dept-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.dept-card{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:18px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.dept-card span{display:block;color:#64748b;font-weight:800}.dept-card strong{display:block;margin-top:6px;color:#0f172a;font-size:1.35rem}.dept-card small{display:block;margin-top:6px;color:#64748b}.dept-panel{background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:18px;box-shadow:0 12px 28px rgba(15,23,42,.05)}.dept-list{display:grid;gap:10px}.dept-row{border:1px solid #e5e7eb;background:#f8fafc;border-radius:14px;padding:12px}.dept-row strong{display:block;color:#0f172a}.dept-row small{color:#64748b}.dept-empty{padding:18px;border-radius:16px;background:#f8fafc;color:#64748b}@media(max-width:900px){.dept-grid{grid-template-columns:1fr}.dept-btn,.dept-actions{width:100%}}
</style>

<div class="dept-wrap">
    @if(!$schemaReady)
        <section class="dept-hero"><h2>Migration nécessaire</h2><p>Les tables de supervision ne sont pas encore disponibles.</p></section>
    @elseif(!$responsibility)
        <section class="dept-hero"><h2>Accès réservé</h2><p>Ce tableau de bord est réservé au responsable de département / filière.</p><div class="dept-actions"><a class="dept-btn" href="{{ route('teacher.dashboard') }}">Retour</a></div></section>
    @else
        <section class="dept-hero">
            <span>Responsable de département / filière</span>
            <h2>{{ $department->name ?? 'Département non lié' }}</h2>
            <p>Ce TB permet de suivre le département et d’accéder directement à la gestion des classes et matières autorisées pour ce profil. Aucun accès système sensible n’est ouvert ici.</p>
            <div class="dept-actions">
                <a class="dept-btn" href="{{ route('teacher.dashboard') }}">← Retour enseignant</a>
                <a class="dept-btn dept-btn--green" href="{{ route('admin.classes.index') }}">Gérer les classes</a>
                <a class="dept-btn dept-btn--blue" href="{{ route('admin.subjects.index') }}">Gérer les matières</a>
                @if(\Illuminate\Support\Facades\Route::has('responsibilities.followups.index'))<a class="dept-btn" href="{{ route('responsibilities.followups.index') }}">Suivi pédagogique</a>@endif
            </div>
        </section>

        <section class="dept-grid">
            <article class="dept-card"><span>Département</span><strong>{{ $department->name ?? 'Non défini' }}</strong><small>{{ $department->code ?? 'Aucun code' }}</small></article>
            <article class="dept-card"><span>Matière liée</span><strong>{{ $subject->name ?? 'Aucune matière liée' }}</strong><small>À gérer dans la page Matières</small></article>
            <article class="dept-card"><span>Classe liée</span><strong>{{ $class->name ?? 'Aucune classe liée' }}</strong><small>À gérer dans la page Classes</small></article>
        </section>

        <section class="dept-panel">
            <h3>Manœuvres autorisées</h3>
            <div class="dept-list">
                <div class="dept-row"><strong>Classes</strong><small>Créer, modifier et organiser les classes.</small></div>
                <div class="dept-row"><strong>Matières</strong><small>Créer, modifier et organiser les matières.</small></div>
                <div class="dept-row"><strong>Suivi pédagogique</strong><small>Consulter les alertes et relances pédagogiques liées au suivi.</small></div>
            </div>
        </section>
    @endif
</div>
@endsection
