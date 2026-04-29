@extends('layouts.student')

@section('title', 'Reçu de paiement')

@php
    $studentName = $payment->user->full_name ?? $payment->user->name ?? $payment->user->username ?? 'Élève';
    $planName = $subscription->plan_name ?? $payment->plan->name ?? 'Abonnement TIMAH ACADEMY';
    $amount = number_format((float) $payment->amount, 0, ',', ' ');
    $currency = $payment->currency ?? 'XAF';
    $paidAt = $payment->paid_at ?: $payment->updated_at;
    $startsAt = $subscription?->starts_at;
    $endsAt = $subscription?->ends_at;
    $method = match ($payment->payment_method) {
        'mtn_momo' => 'MTN Mobile Money',
        'orange_money' => 'Orange Money',
        default => $payment->payment_method ?: 'Paiement mobile',
    };
@endphp

@push('styles')
<style>
    .receipt-page {
        display: grid;
        gap: 20px;
    }

    .receipt-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .receipt-actions__text h1 {
        margin: 0;
        font-size: clamp(1.6rem, 3vw, 2.4rem);
        letter-spacing: -0.04em;
    }

    .receipt-actions__text p {
        margin: 6px 0 0;
        color: var(--muted);
    }

    .receipt-actions__buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .receipt-sheet {
        position: relative;
        overflow: hidden;
        border-radius: 30px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, .10), transparent 30%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow-lg);
    }

    .receipt-sheet::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 9px;
        background: linear-gradient(90deg, #2563eb, #7c3aed, #f59e0b, #16a34a);
    }

    .receipt-header {
        padding: 34px 34px 22px;
        display: flex;
        justify-content: space-between;
        gap: 22px;
        align-items: flex-start;
        border-bottom: 1px solid var(--line);
    }

    .receipt-brand {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .receipt-brand__mark {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        font-weight: 900;
        letter-spacing: -0.04em;
        box-shadow: 0 18px 34px rgba(37, 99, 235, .22);
        flex: 0 0 58px;
    }

    .receipt-brand strong {
        display: block;
        font-size: 1.2rem;
        letter-spacing: -0.03em;
    }

    .receipt-brand span {
        display: block;
        color: var(--muted);
        font-size: .92rem;
        margin-top: 2px;
    }

    .receipt-number {
        text-align: right;
        display: grid;
        gap: 6px;
    }

    .receipt-number span {
        color: var(--muted);
        font-size: .86rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .receipt-number strong {
        font-size: 1.2rem;
        letter-spacing: -0.02em;
    }

    .receipt-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(22, 163, 74, .10);
        color: #15803d;
        border: 1px solid rgba(22, 163, 74, .18);
        font-size: .82rem;
        font-weight: 900;
        width: fit-content;
        margin-left: auto;
    }

    .receipt-body {
        padding: 30px 34px 34px;
        display: grid;
        gap: 22px;
    }

    .receipt-summary {
        display: grid;
        grid-template-columns: 1fr .85fr;
        gap: 18px;
        align-items: stretch;
    }

    .receipt-box {
        border: 1px solid var(--line);
        border-radius: 24px;
        padding: 22px;
        background: rgba(255,255,255,.56);
    }

    html[data-theme='dark'] .receipt-box {
        background: rgba(15, 23, 42, .22);
    }

    .receipt-box h2 {
        margin: 0 0 14px;
        font-size: 1.1rem;
        letter-spacing: -0.03em;
    }

    .receipt-main-amount {
        display: grid;
        gap: 8px;
    }

    .receipt-main-amount span {
        color: var(--muted);
        font-size: .92rem;
        font-weight: 700;
    }

    .receipt-main-amount strong {
        font-size: clamp(2.2rem, 5vw, 3.4rem);
        line-height: 1;
        letter-spacing: -0.06em;
    }

    .receipt-lines {
        display: grid;
        gap: 12px;
    }

    .receipt-line {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        padding: 13px 0;
        border-bottom: 1px dashed var(--line);
    }

    .receipt-line:last-child {
        border-bottom: 0;
    }

    .receipt-line span {
        color: var(--muted);
        font-size: .92rem;
    }

    .receipt-line strong {
        text-align: right;
        font-size: .94rem;
        color: var(--text);
    }

    .receipt-footer {
        border-top: 1px solid var(--line);
        padding-top: 18px;
        color: var(--muted);
        line-height: 1.7;
        font-size: .92rem;
    }

    .receipt-signature {
        margin-top: 16px;
        display: flex;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        color: var(--muted);
        font-size: .9rem;
    }

    @media (max-width: 860px) {
        .receipt-summary {
            grid-template-columns: 1fr;
        }

        .receipt-header {
            display: grid;
        }

        .receipt-number {
            text-align: left;
        }

        .receipt-status {
            margin-left: 0;
        }

        .receipt-body,
        .receipt-header {
            padding-left: 20px;
            padding-right: 20px;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .student-sidebar,
        .student-topbar,
        .receipt-actions {
            display: none !important;
        }

        .student-app,
        .student-main,
        .student-content {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            min-height: auto !important;
        }

        .receipt-sheet {
            box-shadow: none !important;
            border-radius: 18px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="receipt-page">
    <div class="receipt-actions">
        <div class="receipt-actions__text">
            <h1>Reçu de paiement</h1>
            <p>Votre paiement a été confirmé et votre abonnement est actif.</p>
        </div>

        <div class="receipt-actions__buttons">
            <a href="{{ route('student.subscription.index') }}" class="topbar-btn">Retour abonnement</a>
            <button type="button" onclick="window.print()" class="topbar-btn topbar-btn--primary">Imprimer / Enregistrer PDF</button>
        </div>
    </div>

    <section class="receipt-sheet">
        <div class="receipt-header">
            <div class="receipt-brand">
                <div class="receipt-brand__mark">TA</div>
                <div>
                    <strong>TIMAH ACADEMY</strong>
                    <span>Reçu officiel d’abonnement</span>
                </div>
            </div>

            <div class="receipt-number">
                <span>Numéro du reçu</span>
                <strong>{{ $receiptNumber }}</strong>
                <div class="receipt-status">Paiement confirmé</div>
            </div>
        </div>

        <div class="receipt-body">
            <div class="receipt-summary">
                <div class="receipt-box">
                    <h2>Montant payé</h2>
                    <div class="receipt-main-amount">
                        <span>Total réglé</span>
                        <strong>{{ $amount }} {{ $currency }}</strong>
                    </div>
                </div>

                <div class="receipt-box">
                    <h2>Informations de paiement</h2>
                    <div class="receipt-lines">
                        <div class="receipt-line">
                            <span>Date</span>
                            <strong>{{ $paidAt ? $paidAt->format('d/m/Y à H:i') : 'Non précisée' }}</strong>
                        </div>

                        <div class="receipt-line">
                            <span>Moyen</span>
                            <strong>{{ $method }}</strong>
                        </div>

                        <div class="receipt-line">
                            <span>Téléphone</span>
                            <strong>{{ $payment->phone_number ?: 'Non précisé' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="receipt-box">
                <h2>Détails de l’abonnement</h2>
                <div class="receipt-lines">
                    <div class="receipt-line">
                        <span>Abonné</span>
                        <strong>{{ $studentName }}</strong>
                    </div>

                    <div class="receipt-line">
                        <span>Formule</span>
                        <strong>{{ $planName }}</strong>
                    </div>

                    <div class="receipt-line">
                        <span>Début</span>
                        <strong>{{ $startsAt ? $startsAt->format('d/m/Y à H:i') : 'Non précisé' }}</strong>
                    </div>

                    <div class="receipt-line">
                        <span>Fin</span>
                        <strong>{{ $endsAt ? $endsAt->format('d/m/Y à H:i') : 'Non précisé' }}</strong>
                    </div>

                    <div class="receipt-line">
                        <span>Référence TIMAH</span>
                        <strong>{{ $payment->notchpay_reference }}</strong>
                    </div>

                    <div class="receipt-line">
                        <span>Référence opérateur</span>
                        <strong>{{ $payment->notchpay_transaction_id ?: 'Non précisée' }}</strong>
                    </div>
                </div>
            </div>

            <div class="receipt-footer">
                Ce reçu confirme le paiement effectué pour l’abonnement TIMAH ACADEMY indiqué ci-dessus.
                Il peut être imprimé ou enregistré en PDF depuis le navigateur.

                <div class="receipt-signature">
                    <span>TIMAH ACADEMY</span>
                    <span>Reçu généré le {{ now()->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
