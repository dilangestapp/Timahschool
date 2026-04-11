@extends('layouts.admin')

@section('title', $tdSet->title)
@section('page_title', 'Détail TD')
@section('page_subtitle', 'Lecture du TD, de sa source éventuelle, du corrigé et de la traçabilité de production.')

@section('content')
<section class="admin-section">
    <div class="admin-section__head"><h2>{{ $tdSet->title }}</h2></div>
    <div class="admin-detail-grid">
        <div><strong>Classe</strong><span>{{ $tdSet->schoolClass->name ?? '-' }}</span></div>
        <div><strong>Matière</strong><span>{{ $tdSet->subject->name ?? '-' }}</span></div>
        <div><strong>Statut</strong><span class="admin-badge admin-badge--{{ $tdSet->status }}">{{ $tdSet->status }}</span></div>
        <div><strong>Accès</strong><span>{{ $tdSet->access_level === 'premium' ? 'Premium' : 'Gratuit' }}</span></div>
        <div><strong>Origine</strong><span>{{ $tdSet->generation_mode === 'prepare_for_chatgpt' ? 'Préparé pour ChatGPT' : 'Création manuelle' }}</span></div>
        <div><strong>Auteur</strong><span>{{ $tdSet->author->full_name ?? $tdSet->author->name ?? '-' }}</span></div>
    </div>

    @if($tdSet->summary)
        <div class="admin-rich-block"><h3>Résumé</h3><p>{{ $tdSet->summary }}</p></div>
    @endif
    <div class="admin-rich-block"><h3>Énoncé</h3><div class="admin-html-block">{!! $tdSet->instructions_html !!}</div></div>
    @if($tdSet->correction_html)
        <div class="admin-rich-block"><h3>Corrigé</h3><div class="admin-html-block">{!! $tdSet->correction_html !!}</div></div>
    @endif

    @if($tdSet->source)
        <div class="admin-rich-block">
            <h3>Source liée</h3>
            <a class="btn btn--ghost" href="{{ route('admin.td.sources.show', $tdSet->source) }}">Ouvrir la source préparée</a>
        </div>
    @endif
</section>
@endsection
