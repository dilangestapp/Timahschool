@extends('layouts.teacher')

@section('title', 'TB Département')
@section('page_title', 'TB Département / Filière')
@section('page_subtitle', 'Espace séparé de gestion du département.')

@section('content')
@php
    $department = null;
    $responsibility = null;
    if (\Illuminate\Support\Facades\Schema::hasTable('pedagogical_responsibilities') && \Illuminate\Support\Facades\Schema::hasTable('teaching_departments')) {
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
        }
    }
@endphp

<style>
    .dept-box{background:#fff;border:1px solid #e2e8f0;border-radius:22px;padding:22px;margin-bottom:18px;box-shadow:0 12px 28px rgba(15,23,42,.06)}
    .dept-hero{background:linear-gradient(135deg,#0f172a,#1d4ed8);color:white}.dept-hero p{color:#dbeafe}.dept-hero h2{font-size:2.5rem;margin:8px 0}
    .dept-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px}.dept-btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 16px;border-radius:14px;text-decoration:none;font-weight:900;background:#fff;color:#0f172a}.dept-btn.green{background:#16a34a;color:#fff}.dept-btn.blue{background:#2563eb;color:#fff}
    .dept-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.dept-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:18px}.dept-card strong{display:block;font-size:1.2rem;color:#0f172a}.dept-card span{color:#64748b}@media(max-width:900px){.dept-grid{grid-template-columns:1fr}.dept-btn{width:100%}}
</style>

@if(!$responsibility)
    <section class="dept-box dept-hero"><h2>Accès réservé</h2><p>Ce tableau de bord est réservé au responsable de département / filière.</p></section>
@else
    <section class="dept-box dept-hero">
        <span>Responsable de département / filière</span>
        <h2>{{ $department->name ?? 'Département non lié' }}</h2>
        <p>Les classes et matières se gèrent dans un espace séparé du responsable. Les modifications sont enregistrées dans les vraies tables et visibles aussi chez l’admin.</p>
        <div class="dept-actions">
            <a class="dept-btn" href="{{ route('teacher.dashboard') }}">← Retour enseignant</a>
            @if(\Illuminate\Support\Facades\Route::has('department.classes.index'))<a class="dept-btn green" href="{{ route('department.classes.index') }}">Gérer les classes</a>@endif
            @if(\Illuminate\Support\Facades\Route::has('department.subjects.index'))<a class="dept-btn blue" href="{{ route('department.subjects.index') }}">Gérer les matières</a>@endif
            @if(\Illuminate\Support\Facades\Route::has('responsibilities.followups.index'))<a class="dept-btn" href="{{ route('responsibilities.followups.index') }}">Suivi pédagogique</a>@endif
        </div>
    </section>
    <section class="dept-grid">
        <article class="dept-card"><strong>Classes du département</strong><span>Créer, modifier et lier une classe sans ouvrir l’admin.</span></article>
        <article class="dept-card"><strong>Matières du département</strong><span>Créer, modifier et lier une matière sans ouvrir l’admin.</span></article>
        <article class="dept-card"><strong>Impact système</strong><span>Les données modifiées ici apparaissent aussi côté admin.</span></article>
    </section>
@endif
@endsection
