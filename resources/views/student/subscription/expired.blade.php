@extends('layouts.student')

@section('title', 'Abonnement expiré')

@section('content')
<div class="summary-box" style="max-width:680px; margin:0 auto; text-align:center;">
    <div style="font-size:3rem; margin-bottom:10px;">⛔</div>
    <h1 style="margin:0 0 10px; font-size:2rem;">Votre abonnement a expiré</h1>
    <p class="muted" style="margin:0 0 22px;">Pour continuer à accéder aux cours, aux quiz et à tout le contenu de Timah School, veuillez renouveler votre abonnement.</p>
    <a href="{{ route('student.subscription.index') }}" class="btn btn--primary">Renouveler mon abonnement</a>
    <div class="legal-note">Un problème ? Contactez le support.</div>
</div>
@endsection
