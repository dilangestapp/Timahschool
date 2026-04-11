@extends('layouts.student')

@section('title', 'Paiement - ' . $plan->name)

@section('content')
@php
    $durationLabel = match($plan->duration_unit) {
        'day'   => $plan->duration_value . ' ' . ($plan->duration_value > 1 ? 'jours' : 'jour'),
        'week'  => $plan->duration_value . ' ' . ($plan->duration_value > 1 ? 'semaines' : 'semaine'),
        'month' => $plan->duration_value . ' ' . ($plan->duration_value > 1 ? 'mois' : 'mois'),
        'year'  => $plan->duration_value . ' ' . ($plan->duration_value > 1 ? 'ans' : 'an'),
        default => $plan->duration_value . ' ' . $plan->duration_unit,
    };
@endphp

<section class="checkout-layout">
    <div class="summary-box">
        <div class="panel__head" style="padding:0 0 18px; border-bottom:1px solid #edf2fa; margin-bottom:22px;">
            <div>
                <h1 style="margin:0 0 6px; font-size:2rem;">Finaliser votre abonnement</h1>
                <div class="muted">Complétez le formulaire puis confirmez le paiement sécurisé NotchPay.</div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert--error" style="margin-bottom:18px;">{{ session('error') }}</div>
        @endif

        <form action="{{ route('student.subscription.pay', $plan) }}" method="POST" id="paymentForm" class="form-grid">
            @csrf

            <div class="form-group">
                <label for="plan_name">Plan sélectionné</label>
                <input type="text" id="plan_name" value="{{ $plan->name }}" readonly>
            </div>

            <div class="form-group">
                <label for="amount">Montant à payer</label>
                <input type="text" id="amount" value="{{ $plan->formatted_price }}" readonly>
            </div>

            <div class="form-group">
                <label for="duration">Durée</label>
                <input type="text" id="duration" value="{{ $durationLabel }}" readonly>
            </div>

            <div class="form-group">
                <label for="account">Compte élève</label>
                <input type="text" id="account" value="{{ auth()->user()->email ?: auth()->user()->username }}" readonly>
            </div>

            <div class="form-group">
                <label for="phone">Numéro Mobile Money</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="6XXXXXXXX" required inputmode="numeric" maxlength="9">
                @error('phone')<div class="field-error">{{ $message }}</div>@enderror
                <small>Entrez un numéro MTN Mobile Money ou Orange Money valide.</small>
            </div>

            <div class="form-group">
                <label style="display:block; margin-bottom:12px;">Choisir le moyen de paiement</label>

                <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px;">
                    <label for="channel_mtn" style="display:block; cursor:pointer; margin:0;">
                        <input type="radio" id="channel_mtn" name="channel" value="mtn_momo" {{ old('channel') === 'mtn_momo' ? 'checked' : '' }} required style="display:none;">
                        <span class="channel-card">
                            <strong>MTN MoMo</strong>
                            <small>Cliquer pour sélectionner</small>
                        </span>
                    </label>

                    <label for="channel_orange" style="display:block; cursor:pointer; margin:0;">
                        <input type="radio" id="channel_orange" name="channel" value="orange_money" {{ old('channel') === 'orange_money' ? 'checked' : '' }} required style="display:none;">
                        <span class="channel-card channel-card--orange">
                            <strong>Orange Money</strong>
                            <small>Cliquer pour sélectionner</small>
                        </span>
                    </label>
                </div>

                @error('channel')<div class="field-error" style="margin-top:8px;">{{ $message }}</div>@enderror
            </div>

            @if(!empty($plan->features))
                <div class="form-group">
                    <label>Ce que ce plan débloque</label>
                    <div style="border:1px solid #dbe7ff; background:#f8fbff; border-radius:18px; padding:18px 16px;">
                        <ul class="feature-list" style="margin:0;">
                            @foreach($plan->features as $feature)
                                <li><span style="color:#16a34a;">✔</span> <span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <button type="submit" id="submitBtn" class="btn btn--primary btn--full">Payer {{ $plan->formatted_price }} avec NotchPay</button>
            <div class="legal-note">Paiement sécurisé par <strong>NotchPay</strong>. Après validation, vous serez redirigé vers l'étape de confirmation.</div>
        </form>
    </div>

    <aside class="summary-box">
        <div class="panel__head" style="padding:0 0 18px; border-bottom:1px solid #edf2fa; margin-bottom:22px;">
            <h2 style="margin:0;">Résumé</h2>
        </div>

        <div class="key-value">
            <span class="muted">Plan</span>
            <strong>{{ $plan->name }}</strong>
        </div>
        <div class="key-value">
            <span class="muted">Total</span>
            <strong style="font-size:1.7rem; color:var(--primary);">{{ $plan->formatted_price }}</strong>
        </div>
        <div class="key-value">
            <span class="muted">Paiement</span>
            <strong>NotchPay / Mobile Money</strong>
        </div>
        <div class="key-value">
            <span class="muted">Activation</span>
            <strong>Après confirmation du paiement</strong>
        </div>
    </aside>
</section>

<style>
.channel-card{
    display:flex;
    flex-direction:column;
    gap:4px;
    border:2px solid #f4d35e;
    background:#fff8e1;
    color:#92400e;
    border-radius:18px;
    padding:16px;
    min-height:74px;
    transition:all .2s ease;
}
.channel-card--orange{
    border-color:#fdba74;
    background:#fff7ed;
    color:#9a3412;
}
input[type="radio"]:checked + .channel-card{
    border-color:#2563eb;
    background:#eff6ff;
    color:#0f172a;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}
input[type="radio"]:checked + .channel-card.channel-card--orange{
    border-color:#ea580c;
    background:#fff7ed;
    box-shadow:0 0 0 3px rgba(234,88,12,.12);
}
</style>

<script>
document.getElementById('paymentForm').addEventListener('submit', function () {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Redirection en cours...';
});
</script>
@endsection
