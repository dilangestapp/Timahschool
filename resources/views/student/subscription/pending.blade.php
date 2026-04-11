@extends('layouts.student')

@section('title', 'Paiement en cours')

@section('content')
<div class="summary-box" style="max-width:760px; margin:0 auto; text-align:center;">
    <div style="font-size:3rem; margin-bottom:10px;">⏳</div>
    <h1 style="margin:0 0 10px; font-size:2rem;">Paiement en cours de traitement</h1>
    <p class="muted" style="margin:0 0 8px;">Votre paiement a bien été initié. Veuillez vérifier votre téléphone et confirmer la transaction Mobile Money.</p>
    <p class="muted" style="margin:0 0 24px;">Une fois confirmé, votre abonnement sera activé automatiquement dans quelques instants.</p>

    <div class="alert" style="background:#fffbeb; border:1px solid #fde68a; color:#92400e; text-align:left; margin-bottom:22px;">
        <strong>Comment confirmer ?</strong>
        <ol style="margin:10px 0 0 18px; padding:0; display:grid; gap:6px;">
            <li>Ouvrez votre application Mobile Money.</li>
            <li>Confirmez la demande de paiement reçue.</li>
            <li>Revenez sur cette page, l'accès sera débloqué automatiquement.</li>
        </ol>
    </div>

    <div class="cta-box__actions">
        <a href="{{ route('student.dashboard') }}" class="btn btn--primary">Aller au tableau de bord</a>
        <a href="{{ route('student.subscription.index') }}" class="btn btn--ghost">Voir mes abonnements</a>
    </div>
</div>
@endsection
