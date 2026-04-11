@extends('layouts.student')

@section('title', 'Mon abonnement')

@section('content')
@php
    $groupedPlans = collect($plans ?? [])->filter(function ($plan) {
        return is_object($plan) && isset($plan->name);
    })->groupBy(function ($plan) {
        $name = strtolower($plan->name ?? '');

        if (str_contains($name, 'essentiel')) {
            return 'Essentiel';
        }

        if (str_contains($name, 'standard')) {
            return 'Standard';
        }

        return 'Premium';
    });
@endphp

<section class="panel" style="padding:24px;">
    <div class="panel__head" style="padding:0 0 18px; border-bottom:1px solid #edf2fa; margin-bottom:22px; display:flex; justify-content:space-between; gap:20px; align-items:flex-start;">
        <div>
            <h1 style="margin:0; font-size:2rem;">Mon abonnement</h1>
            <div class="muted">Choisissez une formule adaptée à votre rythme.</div>
        </div>
        <div class="muted" style="max-width:420px;">
            Consultez vos cours, faites vos quiz et débloquez l'accès complet selon le plan qui vous convient.
        </div>
    </div>

    @if($currentSubscription && method_exists($currentSubscription, 'isActive') && $currentSubscription->isActive())
        <div class="alert" style="background:#ecfdf3; border:1px solid #bbf7d0; color:#166534; margin-bottom:24px;">
            <strong>Abonnement actif</strong><br>
            Plan : {{ $currentSubscription->plan_name ?? 'Essai Gratuit' }}
            @if(!empty($currentSubscription->ends_at))
                · Expire le {{ $currentSubscription->ends_at->format('d/m/Y à H:i') }}
            @endif
            @if(!empty($currentSubscription->is_trial))
                <div style="margin-top:6px;">Votre essai gratuit est actif. Choisissez dès maintenant une formule pour éviter l'interruption d'accès à la fin de l'essai.</div>
            @endif
        </div>
    @else
        <div class="alert" style="background:#fffbeb; border:1px solid #fde68a; color:#92400e; margin-bottom:24px;">
            <strong>Aucun abonnement actif</strong><br>
            Souscrivez à une formule pour accéder à tous les cours et quiz.
        </div>
    @endif

    <div style="border:1px solid #dbe7ff; background:#f8fbff; border-radius:24px; padding:24px; margin-bottom:26px;">
        <h2 style="margin:0 0 20px; font-size:1.15rem;">Formules disponibles</h2>

        <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:18px;">
            @foreach(['Essentiel', 'Standard', 'Premium'] as $family)
                <div>
                    <h3 style="margin:0 0 8px; font-size:1.35rem;">{{ $family }}</h3>
                    <div class="muted">
                        @if($family === 'Essentiel')
                            Accès aux cours et évaluations de base.
                        @elseif($family === 'Standard')
                            Cours + quiz + suivi plus confortable.
                        @else
                            Accès complet avec interaction et priorité.
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($groupedPlans->isEmpty())
        <div class="alert" style="background:#fff7ed; border:1px solid #fdba74; color:#9a3412;">
            Aucun plan disponible pour le moment. Exécutez le seeder des plans d'abonnement avant de continuer.
        </div>
    @else
        @foreach(['Essentiel', 'Standard', 'Premium'] as $family)
            @php
                $familyPlans = $groupedPlans->get($family, collect());
            @endphp

            @if($familyPlans->count() > 0)
                <div style="margin-bottom:28px;">
                    <h2 style="margin:0 0 14px; font-size:1.35rem;">{{ $family }}</h2>

                    <div class="subscription-grid">
                        @foreach($familyPlans as $plan)
                            <article class="plan-card {{ (!empty($plan->is_featured) && $plan->is_featured) ? 'plan-card--highlight' : '' }}">
                                @if(!empty($plan->is_featured) && $plan->is_featured)
                                    <div class="plan-badge">RECOMMANDÉ</div>
                                @endif

                                <h3 style="margin:0 0 8px; font-size:1.2rem;">{{ $plan->name }}</h3>

                                <div class="plan-price">
                                    {{ $plan->formatted_price ?? number_format((float) ($plan->price ?? 0), 0, ',', ' ') . ' XAF' }}
                                </div>

                                <div class="muted" style="margin-bottom:10px;">
                                    Durée :
                                    {{ $plan->duration_value ?? 1 }}
                                    @switch($plan->duration_unit ?? 'month')
                                        @case('day') {{ (($plan->duration_value ?? 1) > 1) ? 'jours' : 'jour' }} @break
                                        @case('week') {{ (($plan->duration_value ?? 1) > 1) ? 'semaines' : 'semaine' }} @break
                                        @case('month') mois @break
                                        @case('year') {{ (($plan->duration_value ?? 1) > 1) ? 'ans' : 'an' }} @break
                                        @default {{ $plan->duration_unit ?? 'mois' }}
                                    @endswitch
                                </div>

                                @if(!empty($plan->description))
                                    <p class="muted" style="margin:0 0 12px;">{{ $plan->description }}</p>
                                @endif

                                @php
                                    $features = [];
                                    if (!empty($plan->features) && is_array($plan->features)) {
                                        $features = $plan->features;
                                    }
                                @endphp

                                @if(!empty($features))
                                    <ul class="feature-list">
                                        @foreach($features as $feature)
                                            <li><span style="color:#16a34a;">✔</span> <span>{{ $feature }}</span></li>
                                        @endforeach
                                    </ul>
                                @endif

                                <a href="{{ route('student.subscription.checkout', $plan) }}" class="btn btn--primary btn--full">
                                    S'abonner à {{ $plan->formatted_price ?? number_format((float) ($plan->price ?? 0), 0, ',', ' ') . ' XAF' }}
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</section>
@endsection
