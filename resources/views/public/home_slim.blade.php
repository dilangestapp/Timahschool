@extends('layouts.public')

@section('title', 'TIMAH ACADEMY - Préparation aux examens')
@section('meta_description', 'TIMAH ACADEMY aide les élèves à préparer les examens officiels avec TD, corrigés, cours, quiz et suivi de progression.')

@php
    $defaults = \App\Models\HomepageSetting::defaults();
    $hero = $homepage['hero'] ?? [];
    $plans = collect($homepage['pricing'] ?? $defaults['pricing'] ?? [])->take(3)->values();
    $faqItems = collect($homepage['faq'] ?? $defaults['faq'] ?? [])->take(4)->values();
    $registerLink = Route::has('register') ? route('register') : '#';
    $loginLink = Route::has('login') ? route('login') : '#';
    $activeClassesCount = $classes->count();
    $generalClassesCount = $classGroups->get('enseignement_general', collect())->count();
    $technicalClassesCount = $classGroups->get('enseignement_technique', collect())->count();
    $examItems = collect($homeExamCountdowns ?? [])->values();
@endphp

@push('styles')
<style>
    .home-lite{display:grid;gap:38px;padding:26px 0 44px}.home-lite .lite-section{scroll-margin-top:110px}.lite-hero{display:grid;grid-template-columns:1.12fr .88fr;gap:22px;align-items:stretch}.lite-card{border:1px solid var(--line);border-radius:30px;background:linear-gradient(180deg,var(--panel),var(--panel-soft));box-shadow:var(--shadow);overflow:hidden}.lite-hero-main{padding:38px;background:radial-gradient(circle at top right,rgba(29,109,255,.14),transparent 34%),linear-gradient(180deg,var(--panel),var(--panel-soft))}.lite-eyebrow{display:inline-flex;align-items:center;width:max-content;min-height:34px;padding:0 13px;border-radius:999px;background:rgba(15,118,110,.10);color:#115e59;font-size:.78rem;font-weight:950;text-transform:uppercase;letter-spacing:.06em}.lite-hero h1{margin:16px 0 12px;font-size:clamp(2.2rem,5vw,4.2rem);line-height:.98;letter-spacing:-.065em;max-width:760px}.lite-hero h1 span{color:var(--primary)}.lite-lead{margin:0;color:var(--muted);font-size:1.05rem;line-height:1.75;max-width:720px}.lite-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:22px}.lite-points{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:22px}.lite-point{border:1px solid var(--line);border-radius:18px;background:rgba(255,255,255,.58);padding:13px}.lite-point strong{display:block;color:var(--text);font-size:.96rem}.lite-point span{display:block;color:var(--muted);font-size:.84rem;margin-top:4px}.lite-hero-side{padding:22px;display:grid;gap:12px}.lite-side-box{border:1px solid var(--line);border-radius:22px;background:rgba(255,255,255,.58);padding:16px}.lite-side-box small{display:block;color:var(--muted);font-weight:850;text-transform:uppercase;font-size:.73rem;letter-spacing:.06em}.lite-side-box strong{display:block;margin-top:6px;font-size:1.35rem;letter-spacing:-.04em}.lite-side-box p{margin:8px 0 0;color:var(--muted);line-height:1.5}.lite-section-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-end;flex-wrap:wrap;margin-bottom:16px}.lite-section-head h2{margin:0;font-size:clamp(1.45rem,3vw,2.1rem);line-height:1.08;letter-spacing:-.05em}.lite-section-head p{margin:6px 0 0;color:var(--muted);max-width:720px;line-height:1.6}.exam-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.exam-lite{position:relative;padding:17px;border-radius:24px;border:1px solid var(--line);background:linear-gradient(180deg,var(--panel),var(--panel-soft));box-shadow:var(--shadow-xs);display:grid;gap:10px;overflow:hidden}.exam-lite:before{content:"";position:absolute;right:-44px;top:-44px;width:120px;height:120px;border-radius:999px;background:rgba(15,118,110,.11)}.exam-lite>*{position:relative}.exam-lite .badge{width:max-content;min-height:27px;padding:0 10px;border-radius:999px;background:rgba(15,118,110,.10);color:#115e59;font-size:.72rem;font-weight:950;text-transform:uppercase}.exam-lite h3{margin:0;font-size:1.02rem;letter-spacing:-.03em}.exam-lite .date{color:var(--muted);font-size:.86rem}.exam-lite .timer{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}.exam-lite .timer div{border-radius:14px;background:rgba(15,118,110,.07);padding:9px;text-align:center}.exam-lite .timer strong{display:block;font-size:1.28rem;line-height:1;letter-spacing:-.05em}.exam-lite .timer span{color:var(--muted);font-size:.7rem;font-weight:850}.how-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.how-card{padding:20px;border-radius:24px;border:1px solid var(--line);background:linear-gradient(180deg,var(--panel),var(--panel-soft));box-shadow:var(--shadow-xs)}.how-card b{display:grid;place-items:center;width:38px;height:38px;border-radius:14px;background:rgba(15,118,110,.10);color:#115e59;margin-bottom:12px}.how-card h3{margin:0 0 8px;letter-spacing:-.03em}.how-card p{margin:0;color:var(--muted);line-height:1.58}.classes-lite{display:grid;grid-template-columns:.95fr 1.05fr;gap:16px}.class-summary{padding:22px;border-radius:26px;border:1px solid var(--line);background:linear-gradient(180deg,var(--panel),var(--panel-soft));box-shadow:var(--shadow-xs)}.class-summary h3{margin:0 0 10px}.class-summary .big{font-size:2.1rem;font-weight:950;letter-spacing:-.06em}.class-summary p{color:var(--muted);line-height:1.62}.class-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.class-mini{border:1px solid var(--line);border-radius:18px;background:rgba(255,255,255,.58);padding:14px}.class-mini strong{display:block}.class-mini span{display:block;color:var(--muted);font-size:.85rem;margin-top:3px}.pricing-lite{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.price-card{padding:20px;border-radius:24px;border:1px solid var(--line);background:linear-gradient(180deg,var(--panel),var(--panel-soft));box-shadow:var(--shadow-xs);display:grid;gap:10px}.price-card h3{margin:0}.price-card .price{font-size:1.55rem;font-weight:950;color:#115e59;letter-spacing:-.05em}.price-card p{margin:0;color:var(--muted);line-height:1.55}.faq-lite{display:grid;gap:10px}.faq-lite details{border:1px solid var(--line);border-radius:18px;background:rgba(255,255,255,.58);padding:14px}.faq-lite summary{cursor:pointer;font-weight:900;color:var(--text)}.faq-lite p{margin:10px 0 0;color:var(--muted);line-height:1.6}.final-cta{padding:26px;border-radius:30px;border:1px solid var(--line);background:linear-gradient(135deg,#0f172a,#12336d 55%,#0f766e);color:#fff;display:flex;justify-content:space-between;gap:18px;align-items:center;flex-wrap:wrap;box-shadow:var(--shadow-lg)}.final-cta h2{margin:0 0 8px;color:#fff;letter-spacing:-.05em}.final-cta p{margin:0;color:rgba(255,255,255,.82);line-height:1.6}.final-cta .btn--ghost{background:rgba(255,255,255,.14);color:#fff;border-color:rgba(255,255,255,.22)}html[data-theme='dark'] .lite-point,html[data-theme='dark'] .lite-side-box,html[data-theme='dark'] .class-mini,html[data-theme='dark'] .faq-lite details{background:rgba(15,23,42,.42)}html[data-theme='dark'] .lite-eyebrow,html[data-theme='dark'] .exam-lite .badge{background:rgba(45,212,191,.14);color:#99f6e4}@media(max-width:1050px){.lite-hero,.classes-lite{grid-template-columns:1fr}.lite-points,.exam-strip,.pricing-lite{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:720px){.home-lite{gap:28px;padding-top:18px}.lite-hero-main{padding:24px}.lite-points,.exam-strip,.how-grid,.classes-lite,.class-list,.pricing-lite{grid-template-columns:1fr}.lite-actions .btn,.final-cta .btn{width:100%}.final-cta{display:grid}.lite-hero-side{padding:16px}}
</style>
@endpush

@section('content')
<div class="home-lite container">
    <section class="lite-hero lite-section">
        <div class="lite-card lite-hero-main">
            <span class="lite-eyebrow">Préparation examens 2026</span>
            <h1>TD, corrigés et suivi pour <span>préparer les examens</span>.</h1>
            <p class="lite-lead">TIMAH ACADEMY centralise les TD, corrigés, cours, quiz et rappels utiles pour aider chaque élève à travailler régulièrement, selon sa classe et son examen.</p>
            <div class="lite-actions">
                <a href="{{ $registerLink }}" class="btn btn--primary">Créer un compte</a>
                <a href="{{ $loginLink }}" class="btn btn--ghost">Se connecter</a>
                <a href="#classes" class="btn btn--ghost">Voir les classes</a>
            </div>
            <div class="lite-points">
                <div class="lite-point"><strong>TD corrigés</strong><span>des entraînements par classe</span></div>
                <div class="lite-point"><strong>Quiz</strong><span>pour contrôler le niveau</span></div>
                <div class="lite-point"><strong>Progression</strong><span>suivi des activités</span></div>
                <div class="lite-point"><strong>Rappels</strong><span>examens et nouveautés</span></div>
            </div>
        </div>
        <aside class="lite-card lite-hero-side">
            <div class="lite-side-box"><small>Classes actives</small><strong>{{ $activeClassesCount }}</strong><p>Des contenus organisés par niveau et par matière.</p></div>
            <div class="lite-side-box"><small>Enseignement général</small><strong>{{ $generalClassesCount }}</strong><p>3e, Première, Terminale et séries associées.</p></div>
            <div class="lite-side-box"><small>Objectif</small><strong>Réviser utile</strong><p>Moins de dispersion, plus d’exercices et de corrigés.</p></div>
        </aside>
    </section>

    @if($examItems->isNotEmpty())
        <section class="lite-section" id="exam-countdowns">
            <div class="lite-section-head">
                <div><h2>Examens officiels 2026</h2><p>Les élèves voient aussi automatiquement le compte à rebours correspondant à leur classe dans leur tableau de bord.</p></div>
            </div>
            <div class="exam-strip">
                @foreach($examItems as $exam)
                    <article class="exam-lite" data-home-exam data-target="{{ $exam['target_iso'] }}">
                        <span class="badge">{{ $exam['badge'] }}</span>
                        <h3>{{ $exam['short_label'] }}</h3>
                        <div class="date">Début : {{ $exam['start_label'] }}</div>
                        <div class="timer">
                            <div><strong data-days>{{ $exam['days'] }}</strong><span>jours</span></div>
                            <div><strong data-hours>{{ $exam['hours'] }}</strong><span>heures</span></div>
                            <div><strong data-minutes>{{ $exam['minutes'] }}</strong><span>min</span></div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="lite-section" id="how-it-works">
        <div class="lite-section-head"><div><h2>Comment ça marche ?</h2><p>Un fonctionnement simple pour que l’élève sache rapidement quoi faire.</p></div></div>
        <div class="how-grid">
            <article class="how-card"><b>1</b><h3>Choisir sa classe</h3><p>L’élève accède aux contenus adaptés à son niveau : TD, cours, quiz et rappels.</p></article>
            <article class="how-card"><b>2</b><h3>Traiter les TD</h3><p>Le corrigé peut être bloqué par un délai pour encourager le vrai travail avant consultation.</p></article>
            <article class="how-card"><b>3</b><h3>Suivre sa progression</h3><p>Le tableau de bord affiche les contenus ouverts, les rappels et les activités récentes.</p></article>
        </div>
    </section>

    <section class="lite-section" id="classes">
        <div class="lite-section-head"><div><h2>Classes et niveaux</h2><p>Un aperçu clair, sans surcharge. Les contenus détaillés sont accessibles après connexion.</p></div></div>
        <div class="classes-lite">
            <div class="class-summary">
                <div class="big">{{ $activeClassesCount }}</div>
                <h3>classe(s) disponible(s)</h3>
                <p>La plateforme est organisée pour l’enseignement général et peut évoluer vers les autres parcours selon les besoins.</p>
                <div class="lite-actions"><a href="{{ $registerLink }}" class="btn btn--primary">Commencer</a></div>
            </div>
            <div class="class-list">
                @forelse($featuredClasses->take(6) as $class)
                    <div class="class-mini"><strong>{{ $class->name }}</strong><span>{{ $class->system === 'enseignement_technique' ? 'Enseignement technique' : 'Enseignement général' }}</span></div>
                @empty
                    <div class="class-mini"><strong>Classes bientôt disponibles</strong><span>Les niveaux seront configurés depuis l’administration.</span></div>
                @endforelse
            </div>
        </div>
    </section>

    @if($plans->isNotEmpty())
        <section class="lite-section" id="pricing">
            <div class="lite-section-head"><div><h2>Abonnements</h2><p>Des accès simples pour consulter les contenus selon le plan choisi.</p></div></div>
            <div class="pricing-lite">
                @foreach($plans as $plan)
                    <article class="price-card">
                        <h3>{{ $plan['name'] ?? 'Plan' }}</h3>
                        <div class="price">{{ $plan['price'] ?? 'Prix à définir' }}</div>
                        <p>{{ $plan['description'] ?? 'Accès aux contenus pédagogiques de la plateforme.' }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($faqItems->isNotEmpty())
        <section class="lite-section" id="mini-faq">
            <div class="lite-section-head"><div><h2>Questions utiles</h2><p>Les réponses essentielles, sans longue lecture.</p></div></div>
            <div class="faq-lite">
                @foreach($faqItems as $item)
                    <details>
                        <summary>{{ $item['question'] ?? $item['title'] ?? 'Question' }}</summary>
                        <p>{{ $item['answer'] ?? $item['text'] ?? 'Réponse bientôt disponible.' }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    <section class="final-cta" id="help-support">
        <div>
            <h2>Prêt à organiser les révisions ?</h2>
            <p>Créez un compte, choisissez la classe et commencez avec les TD, cours, quiz et rappels.</p>
        </div>
        <div class="lite-actions">
            <a href="{{ $registerLink }}" class="btn btn--primary">Créer un compte</a>
            <a href="{{ $loginLink }}" class="btn btn--ghost">Connexion</a>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function updateHomeExams() {
        document.querySelectorAll('[data-home-exam]').forEach(function (card) {
            var target = new Date(card.dataset.target);
            if (isNaN(target.getTime())) return;
            var diff = Math.max(0, Math.floor((target.getTime() - Date.now()) / 1000));
            var days = Math.floor(diff / 86400);
            var hours = Math.floor((diff % 86400) / 3600);
            var minutes = Math.floor((diff % 3600) / 60);
            var d = card.querySelector('[data-days]');
            var h = card.querySelector('[data-hours]');
            var m = card.querySelector('[data-minutes]');
            if (d) d.textContent = days;
            if (h) h.textContent = hours;
            if (m) m.textContent = minutes;
        });
    }
    updateHomeExams();
    setInterval(updateHomeExams, 60000);
});
</script>
@endsection
