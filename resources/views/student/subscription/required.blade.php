@extends('layouts.student')

@section('title', 'Abonnement requis')

@section('content')
<div class="summary-box" style="max-width:680px; margin:0 auto; text-align:center;">
    <div style="font-size:3rem; margin-bottom:10px;">🔒</div>
    <h1 style="margin:0 0 10px; font-size:2rem;">Contenu réservé aux abonnés</h1>
    <p class="muted" style="margin:0 0 22px;">Ce contenu est accessible uniquement aux élèves abonnés. Souscrivez à un plan pour débloquer tous les cours et quiz.</p>
    <a href="{{ route('student.subscription.index') }}" class="btn btn--primary">Voir les plans disponibles</a>
</div>
@endsection
