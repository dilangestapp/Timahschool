@extends('layouts.public')

@section('title', 'TIMAH ACADEMY - Préparation aux examens')
@section('meta_description', 'TIMAH ACADEMY aide les élèves à préparer les examens officiels avec TD, corrigés, quiz, rappels et suivi de progression.')

@php
    $defaults = \App\Models\HomepageSetting::defaults();
    $plans = collect($homepage['pricing'] ?? $defaults['pricing'] ?? [])->take(3)->values();
    $baseFaqItems = collect($homepage['faq'] ?? $defaults['faq'] ?? [])->take(3)->values();
    $faqItems = collect([
        [
            'question' => 'Pourquoi le corrigé n’est-il pas toujours disponible immédiatement ?',
            'answer' => 'L’enseignant peut fixer un temps minimum de traitement. Cela oblige l’élève à travailler réellement le TD avant d’accéder au corrigé.',
        ],
    ])->merge($baseFaqItems)->take(4)->values();

    $registerLink = Route::has('register') ? route('register') : '#';
    $loginLink = Route::has('login') ? route('login') : '#';
    $activeClassesCount = $classes->count();
    $generalClassesCount = $classGroups->get('enseignement_general', collect())->count();
    $examItems = collect($homeExamCountdowns ?? [])->values();

    $priorityClasses = $classes->filter(function ($class) {
        $name = strtolower(str_replace(['é', 'è', 'ê', 'ë'], 'e', (string) $class->name));
        return str_contains($name, '3') || str_contains($name, 'troisieme') || str_contains($name, 'premiere') || str_contains($name, 'terminale');
    })->take(6)->values();

    if ($priorityClasses->isEmpty()) {
        $priorityClasses = $featuredClasses->take(6)->values();
    }

    $planBenefits = [
        0 => ['Accès aux contenus essentiels', 'TD de base disponibles', 'Révision simple et guidée'],
        1 => ['TD + corrigés selon accès', 'Quiz et suivi de progression', 'Meilleur choix pour réviser'],
        2 => ['Accès complet aux contenus', 'Suivi plus confortable', 'Préparation intensive examen'],
    ];
@endphp

@push('styles')
<style>
    .home-pro{display:grid;gap:30px;padding:22px 0 42px}.home-pro .section{scroll-margin-top:105px}.hero-pro{display:grid;grid-template-columns:1.08fr .92fr;gap:20px;align-items:stretch}.hero-panel,.hero-side,.pro-card,.exam-bar,.final-cta{border:1px solid rgba(148,163,184,.24);box-shadow:0 24px 70px rgba(15,23,42,.11);overflow:hidden}.hero-panel{position:relative;border-radius:34px;padding:34px;background:radial-gradient(circle at 12% 18%,rgba(49,87,255,.18),transparent 34%),radial-gradient(circle at 88% 12%,rgba(15,118,110,.16),transparent 30%),linear-gradient(135deg,rgba(255,255,255,.99),rgba(237,243,255,.94))}.hero-panel:before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.72),transparent 42%);pointer-events:none}.hero-panel>*{position:relative;z-index:1}.eyebrow{display:inline-flex;align-items:center;gap:8px;width:max-content;min-height:34px;padding:0 13px;border-radius:999px;background:rgba(15,118,110,.10);color:#0f766e;font-size:.76rem;font-weight:950;text-transform:uppercase;letter-spacing:.07em}.hero-panel h1{margin:15px 0 12px;max-width:820px;font-size:clamp(2.1rem,4.25vw,3.75rem);line-height:1.02;letter-spacing:-.062em;color:#0f172a}.hero-panel h1 span{background:linear-gradient(135deg,#3157ff,#7c3aed);-webkit-background-clip:text;background-clip:text;color:transparent}.hero-lead{max-width:690px;margin:0;color:#475569;font-size:1.04rem;line-height:1.72}.hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:22px}.hero-benefits{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:22px}.benefit-card{position:relative;min-height:118px;border-radius:22px;border:1px solid rgba(148,163,184,.24);padding:14px;background:rgba(255,255,255,.74);display:grid;gap:8px;align-content:start;overflow:hidden}.benefit-card:before{content:"";position:absolute;right:-30px;top:-34px;width:86px;height:86px;border-radius:999px;opacity:.16}.benefit-card:nth-child(1):before{background:#3157ff}.benefit-card:nth-child(2):before{background:#0f766e}.benefit-card:nth-child(3):before{background:#f59e0b}.benefit-card:nth-child(4):before{background:#7c3aed}.benefit-card i{font-style:normal;display:grid;place-items:center;width:38px;height:38px;border-radius:14px;background:rgba(255,255,255,.9);font-size:1.15rem}.benefit-card strong{font-size:.95rem;color:#0f172a}.benefit-card span{color:#64748b;font-size:.82rem;line-height:1.35}.hero-side{border-radius:34px;padding:20px;background:radial-gradient(circle at 95% 12%,rgba(245,158,11,.18),transparent 32%),linear-gradient(180deg,rgba(255,255,255,.97),rgba(245,248,255,.92));display:grid;gap:12px}.next-exam{border-radius:28px;padding:20px;background:linear-gradient(135deg,#0f172a,#17346d 54%,#0f766e);color:#fff;box-shadow:0 20px 46px rgba(15,23,42,.22)}.next-exam small{display:block;color:rgba(255,255,255,.72);font-weight:950;text-transform:uppercase;letter-spacing:.07em}.next-exam strong{display:block;margin-top:9px;font-size:1.55rem;letter-spacing:-.045em}.next-exam p{margin:8px 0 0;color:rgba(255,255,255,.82);line-height:1.55}.side-stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.side-stat{border-radius:24px;padding:17px;border:1px solid rgba(148,163,184,.24);background:rgba(255,255,255,.76)}.side-stat small{color:#64748b;font-weight:950;text-transform:uppercase;font-size:.72rem;letter-spacing:.07em}.side-stat strong{display:block;margin-top:8px;font-size:1.55rem;letter-spacing:-.05em;color:#0f172a}.side-note{border-radius:24px;padding:17px;border:1px solid rgba(15,118,110,.18);background:linear-gradient(135deg,rgba(15,118,110,.10),rgba(255,255,255,.80));color:#334155;line-height:1.6}.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:14px}.section-head h2{margin:0;font-size:clamp(1.45rem,2.7vw,2rem);line-height:1.08;letter-spacing:-.052em}.section-head p{margin:6px 0 0;color:#64748b;max-width:720px;line-height:1.6}.exam-bar{border-radius:30px;background:linear-gradient(135deg,rgba(15,23,42,.98),rgba(30,58,138,.94) 52%,rgba(15,118,110,.92));padding:18px;color:#fff}.exam-bar-head{display:flex;justify-content:space-between;gap:16px;align-items:center;flex-wrap:wrap;margin-bottom:14px}.exam-bar-head h2{margin:0;color:#fff;letter-spacing:-.045em}.exam-bar-head p{margin:4px 0 0;color:rgba(255,255,255,.76)}.exam-mini-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}.exam-mini{border:1px solid rgba(255,255,255,.16);border-radius:22px;background:rgba(255,255,255,.10);backdrop-filter:blur(12px);padding:14px;display:grid;gap:8px}.exam-mini span{width:max-content;padding:5px 9px;border-radius:999px;background:rgba(255,255,255,.14);font-size:.72rem;font-weight:950;text-transform:uppercase}.exam-mini strong{font-size:1.02rem}.exam-mini b{font-size:1.75rem;line-height:1;letter-spacing:-.06em}.exam-mini small{color:rgba(255,255,255,.74)}.steps-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.pro-card{position:relative;border-radius:26px;background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(245,248,255,.94));padding:20px}.pro-card:before{content:"";position:absolute;left:0;top:18px;bottom:18px;width:6px;border-radius:999px}.pro-card.blue:before{background:#3157ff}.pro-card.green:before{background:#0f766e}.pro-card.orange:before{background:#f59e0b}.pro-card.purple:before{background:#7c3aed}.pro-card .icon{display:grid;place-items:center;width:44px;height:44px;border-radius:16px;background:rgba(49,87,255,.10);font-size:1.25rem;margin-bottom:12px}.pro-card h3{margin:0 0 8px;letter-spacing:-.03em}.pro-card p{margin:0;color:#64748b;line-height:1.62}.classes-focus{display:grid;grid-template-columns:.72fr 1.28fr;gap:16px}.class-count{border-radius:30px;padding:24px;background:linear-gradient(135deg,rgba(15,118,110,.12),rgba(255,255,255,.96));border:1px solid rgba(15,118,110,.18);box-shadow:0 18px 45px rgba(15,23,42,.08)}.class-count .big{font-size:2.8rem;font-weight:950;letter-spacing:-.07em;color:#0f766e}.class-count h3{margin:6px 0 10px}.class-count p{color:#64748b;line-height:1.62}.class-priority-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.class-exam{min-height:150px;border-radius:26px;padding:18px;border:1px solid rgba(148,163,184,.24);background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(239,246,255,.92));box-shadow:0 18px 42px rgba(15,23,42,.08);display:grid;align-content:space-between}.class-exam:nth-child(1){border-color:rgba(15,118,110,.25);background:linear-gradient(135deg,rgba(15,118,110,.11),rgba(255,255,255,.92))}.class-exam:nth-child(2){border-color:rgba(245,158,11,.28);background:linear-gradient(135deg,rgba(245,158,11,.14),rgba(255,255,255,.92))}.class-exam:nth-child(3){border-color:rgba(49,87,255,.25);background:linear-gradient(135deg,rgba(49,87,255,.12),rgba(255,255,255,.92))}.class-exam strong{font-size:1.1rem}.class-exam span{color:#64748b}.pricing-lite{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.price-card{position:relative;border-radius:28px;border:1px solid rgba(148,163,184,.24);background:linear-gradient(135deg,rgba(255,255,255,.98),rgba(245,248,255,.94));box-shadow:0 18px 42px rgba(15,23,42,.08);padding:22px;display:grid;gap:12px;overflow:hidden}.price-card.recommended{transform:translateY(-6px);border-color:rgba(49,87,255,.34);box-shadow:0 24px 60px rgba(49,87,255,.16)}.price-card:before{content:"";position:absolute;right:-38px;top:-38px;width:120px;height:120px;border-radius:999px;background:rgba(49,87,255,.10)}.price-card:nth-child(1):before{background:rgba(15,118,110,.12)}.price-card:nth-child(2):before{background:rgba(49,87,255,.14)}.price-card:nth-child(3):before{background:rgba(124,58,237,.13)}.price-card>*{position:relative}.price-card h3{margin:0}.price{font-size:1.75rem;font-weight:950;letter-spacing:-.06em;color:#0f766e}.plan-badge{width:max-content;min-height:28px;padding:0 10px;border-radius:999px;background:rgba(49,87,255,.10);color:#3157ff;font-size:.72rem;font-weight:950;text-transform:uppercase}.price-card ul{margin:0;padding-left:18px;color:#475569;line-height:1.7}.faq-lite{display:grid;gap:10px}.faq-lite details{border-radius:20px;border:1px solid rgba(148,163,184,.24);background:rgba(255,255,255,.82);padding:16px;box-shadow:0 10px 24px rgba(15,23,42,.05)}.faq-lite summary{cursor:pointer;font-weight:950}.faq-lite p{margin:10px 0 0;color:#64748b;line-height:1.65}.final-cta{border-radius:32px;background:linear-gradient(135deg,#0f172a,#17346d 54%,#0f766e);padding:26px;color:#fff;display:flex;justify-content:space-between;gap:20px;align-items:center;flex-wrap:wrap}.final-cta h2{margin:0 0 8px;color:#fff;letter-spacing:-.052em}.final-cta p{margin:0;color:rgba(255,255,255,.78)}.final-cta .btn--ghost{background:#fff;color:#0f172a;border-color:#fff}.soft-colors .blue{background:linear-gradient(135deg,rgba(49,87,255,.11),rgba(255,255,255,.94))}.soft-colors .green{background:linear-gradient(135deg,rgba(15,118,110,.11),rgba(255,255,255,.94))}.soft-colors .orange{background:linear-gradient(135deg,rgba(245,158,11,.15),rgba(255,255,255,.94))}.soft-colors .purple{background:linear-gradient(135deg,rgba(124,58,237,.12),rgba(255,255,255,.94))}html[data-theme='dark'] .hero-panel,html[data-theme='dark'] .hero-side,html[data-theme='dark'] .pro-card,html[data-theme='dark'] .price-card,html[data-theme='dark'] .faq-lite details,html[data-theme='dark'] .class-exam,html[data-theme='dark'] .class-count{background:linear-gradient(135deg,rgba(15,23,42,.90),rgba(30,41,59,.76));color:#f8fafc}html[data-theme='dark'] .hero-panel h1,html[data-theme='dark'] .benefit-card strong,html[data-theme='dark'] .side-stat strong{color:#f8fafc}html[data-theme='dark'] .hero-lead,html[data-theme='dark'] .benefit-card span,html[data-theme='dark'] .side-note,html[data-theme='dark'] .pro-card p,html[data-theme='dark'] .section-head p,html[data-theme='dark'] .price-card ul,html[data-theme='dark'] .faq-lite p{color:#cbd5e1}html[data-theme='dark'] .benefit-card,html[data-theme='dark'] .side-stat{background:rgba(15,23,42,.55)}@media(max-width:1060px){.hero-pro,.classes-focus{grid-template-columns:1fr}.hero-benefits,.exam-mini-grid,.pricing-lite{grid-template-columns:repeat(2,minmax(0,1fr))}.class-priority-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:720px){.home-pro{gap:24px;padding-top:16px}.hero-panel{padding:24px}.hero-panel h1{font-size:clamp(2rem,12vw,3rem)}.hero-benefits,.exam-mini-grid,.steps-grid,.classes-focus,.class-priority-grid,.pricing-lite{grid-template-columns:1fr}.hero-actions .btn,.final-cta .btn{width:100%}.final-cta{display:grid}.side-stat-grid{grid-template-columns:1fr}.price-card.recommended{transform:none}}
</style>
@endpush

@section('content')
<div class="home-pro container">
    <section class="hero-pro section">
        <div class="hero-panel">
            <span class="eyebrow">Préparation examens 2026</span>
            <h1>Préparez vos examens avec des <span>TD corrigés</span> et un vrai suivi.</h1>
            <p class="hero-lead">TIMAH ACADEMY aide les élèves à réviser sans se disperser : TD, corrigés, quiz, rappels et progression selon la classe.</p>
            <div class="hero-actions">
                <a href="{{ $registerLink }}" class="btn btn--primary">Créer un compte</a>
                <a href="#exam-countdowns" class="btn btn--ghost">Voir les examens</a>
            </div>
            <div class="hero-benefits soft-colors">
                <div class="benefit-card blue"><i>📘</i><strong>TD corrigés</strong><span>Des entraînements par classe.</span></div>
                <div class="benefit-card green"><i>✅</i><strong>Corrigés contrôlés</strong><span>Déblocage après effort.</span></div>
                <div class="benefit-card orange"><i>⏱️</i><strong>Rappels</strong><span>Examens et nouveautés.</span></div>
                <div class="benefit-card purple"><i>📊</i><strong>Progression</strong><span>Suivi clair du travail.</span></div>
            </div>
        </div>
        <aside class="hero-side">
            <div class="next-exam">
                <small>Prochaine échéance</small>
                <strong>{{ $examItems->first()['short_label'] ?? 'Examens 2026' }}</strong>
                <p>Chaque élève retrouve automatiquement son compte à rebours dans son tableau de bord.</p>
            </div>
            <div class="side-stat-grid">
                <div class="side-stat"><small>Classes actives</small><strong>{{ $activeClassesCount }}</strong></div>
                <div class="side-stat"><small>Général</small><strong>{{ $generalClassesCount }}</strong></div>
            </div>
            <div class="side-note"><strong>Objectif :</strong> donner vite la bonne information, puis orienter l’élève vers ses TD, ses corrigés et ses rappels.</div>
        </aside>
    </section>

    @if($examItems->isNotEmpty())
        <section class="exam-bar section" id="exam-countdowns">
            <div class="exam-bar-head">
                <div><h2>Examens officiels 2026</h2><p>Un bandeau compact pour voir rapidement les échéances.</p></div>
            </div>
            <div class="exam-mini-grid">
                @foreach($examItems as $exam)
                    <article class="exam-mini" data-home-exam data-target="{{ $exam['target_iso'] }}">
                        <span>{{ $exam['badge'] }}</span>
                        <strong>{{ $exam['short_label'] }}</strong>
                        <b><span data-days>{{ $exam['days'] }}</span> j</b>
                        <small>{{ $exam['hours'] }} h · {{ $exam['minutes'] }} min · début {{ $exam['start_label'] }}</small>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="section">
        <div class="section-head"><div><h2>Comment ça marche ?</h2><p>Trois étapes simples pour comprendre l’usage de la plateforme.</p></div></div>
        <div class="steps-grid soft-colors">
            <article class="pro-card blue"><div class="icon">🎓</div><h3>Choisir sa classe</h3><p>L’élève accède aux contenus adaptés à son niveau : TD, cours, quiz et rappels.</p></article>
            <article class="pro-card green"><div class="icon">📝</div><h3>Traiter les TD</h3><p>Le temps minimum avant corrigé encourage un vrai travail personnel.</p></article>
            <article class="pro-card orange"><div class="icon">📈</div><h3>Suivre sa progression</h3><p>Le tableau de bord montre les TD ouverts, rappels et activités récentes.</p></article>
        </div>
    </section>

    <section class="section" id="classes">
        <div class="section-head"><div><h2>Classes d’examen à suivre</h2><p>On met d’abord en avant les niveaux les plus proches des examens officiels.</p></div></div>
        <div class="classes-focus">
            <div class="class-count"><div class="big">{{ $activeClassesCount }}</div><h3>classe(s) disponible(s)</h3><p>Les contenus détaillés sont accessibles après connexion, avec une priorité sur 3ème, Première et Terminale.</p><div class="hero-actions"><a href="{{ $registerLink }}" class="btn btn--primary">Commencer</a></div></div>
            <div class="class-priority-grid">
                @forelse($priorityClasses as $class)
                    <div class="class-exam"><strong>{{ $class->name }}</strong><span>{{ str_contains(strtolower($class->name), 'terminal') ? 'Objectif Bac' : (str_contains(strtolower($class->name), 'prem') ? 'Objectif Probatoire' : (str_contains($class->name, '3') ? 'Objectif BEPC' : 'Enseignement général')) }}</span></div>
                @empty
                    <div class="class-exam"><strong>3ème</strong><span>Objectif BEPC</span></div>
                    <div class="class-exam"><strong>Première</strong><span>Objectif Probatoire</span></div>
                    <div class="class-exam"><strong>Terminale</strong><span>Objectif Bac</span></div>
                @endforelse
            </div>
        </div>
    </section>

    @if($plans->isNotEmpty())
        <section class="section" id="pricing">
            <div class="section-head"><div><h2>Abonnements</h2><p>Des formules simples, avec une valeur claire pour l’élève.</p></div></div>
            <div class="pricing-lite">
                @foreach($plans as $index => $plan)
                    <article class="price-card {{ $index === 1 ? 'recommended' : '' }}">
                        @if($index === 1)<span class="plan-badge">Recommandé</span>@endif
                        <h3>{{ $plan['name'] ?? ($index === 0 ? 'Essentiel' : ($index === 1 ? 'Standard' : 'Premium')) }}</h3>
                        <div class="price">{{ $plan['price'] ?? 'Prix à définir' }}</div>
                        <ul>
                            @foreach($planBenefits[$index] ?? $planBenefits[0] as $benefit)
                                <li>{{ $benefit }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($faqItems->isNotEmpty())
        <section class="section" id="mini-faq">
            <div class="section-head"><div><h2>Questions utiles</h2><p>Les réponses essentielles, sans longue lecture.</p></div></div>
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

    <section class="final-cta section" id="help-support">
        <div><h2>Prêt à organiser les révisions ?</h2><p>Créez un compte, choisissez la classe et commencez avec les TD, corrigés, quiz et rappels.</p></div>
        <div class="hero-actions"><a href="{{ $registerLink }}" class="btn btn--primary">Créer un compte</a><a href="{{ $loginLink }}" class="btn btn--ghost">Connexion</a></div>
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
            var d = card.querySelector('[data-days]');
            if (d) d.textContent = days;
        });
    }
    updateHomeExams();
    setInterval(updateHomeExams, 60000);
});
</script>
@endsection
