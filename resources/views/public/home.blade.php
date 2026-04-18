@extends('layouts.public')

@section('title', 'TIMAH ACADEMY - Plateforme EdTech premium')

@section('content')
@php
    $defaults = \App\Models\HomepageSetting::defaults();
    $hero = $homepage['hero'] ?? [];
    $trustItems = collect($homepage['trust_items'] ?? $defaults['trust_items']);
    $whyItems = collect($homepage['why_choose'] ?? $defaults['why_choose']);
    $audiences = collect($homepage['audiences'] ?? $defaults['audiences']);
    $faqItems = collect($homepage['faq'] ?? $defaults['faq']);
    $support = array_merge($defaults['support'] ?? [], $homepage['support'] ?? []);
    $footer = array_merge($defaults['footer'] ?? [], $homepage['footer'] ?? []);
    $sections = collect($homepage['sections'] ?? [])->keyBy('key');
    $classTabs = $classGroups->mapWithKeys(fn($items, $key) => [$key => $classGroupLabels[$key] ?? ucfirst(str_replace('_', ' ', $key))]);
    $plans = collect($homepage['pricing'] ?? $defaults['pricing'] ?? [])->values()->all();
    $displayMessages = $messages->isNotEmpty() ? $messages : collect([
        (object) ['is_anonymous' => true, 'author_label' => 'Anonyme', 'role_tag' => 'Élève', 'message' => 'Les quiz m’aident à réviser plus vite avant les contrôles.'],
        (object) ['is_anonymous' => true, 'author_label' => 'Anonyme', 'role_tag' => 'Parent', 'message' => 'Je vois mieux la progression de mon enfant semaine après semaine.'],
        (object) ['is_anonymous' => false, 'author_label' => 'Prof Coach', 'role_tag' => 'Enseignant', 'message' => 'La plateforme facilite le suivi des exercices et des points faibles.'],
    ]);
@endphp

<style>
.home-shell{position:relative; overflow:hidden}
.home-shell .container{position:relative; z-index:1}
.home-hero{padding:72px 0 42px; background:radial-gradient(circle at 80% 10%, rgba(29,109,255,.18), transparent 45%), radial-gradient(circle at 10% 10%, rgba(11,31,77,.10), transparent 35%)}
.home-hero__grid{display:grid; grid-template-columns:1.05fr .95fr; gap:38px; align-items:center}
.home-hero__badge{display:inline-flex; align-items:center; gap:8px; border:1px solid color-mix(in srgb, var(--line) 70%, transparent); border-radius:999px; padding:7px 14px; font-size:.85rem; color:var(--primary)}
.home-hero__title{font-size:clamp(2rem, 4vw, 3.35rem); line-height:1.08; margin:16px 0; letter-spacing:-.02em}
.home-hero__subtitle{font-size:1.06rem; color:var(--muted); max-width:60ch}
.home-hero__actions{display:flex; flex-wrap:wrap; gap:12px; margin:24px 0 12px}
.home-hero__secondary{display:flex; flex-wrap:wrap; gap:10px}
.home-reassurance{display:flex; flex-wrap:wrap; gap:8px; margin-top:20px}
.home-reassurance span{background:color-mix(in srgb, var(--panel) 75%, var(--primary) 25%); border:1px solid var(--line); border-radius:999px; padding:6px 11px; font-size:.8rem}
.dashboard-mockup{background:linear-gradient(145deg, color-mix(in srgb, var(--panel) 92%, white 8%), color-mix(in srgb, var(--panel) 80%, var(--primary) 20%)); border:1px solid var(--line); border-radius:26px; box-shadow:var(--shadow-lg); padding:22px; position:relative; isolation:isolate; animation:softFloat 8s ease-in-out infinite}
.dashboard-mockup::after{content:""; position:absolute; inset:14px; border-radius:18px; border:1px solid color-mix(in srgb, var(--line) 70%, transparent); z-index:-1}
.mockup-top{display:flex; justify-content:space-between; align-items:center; margin-bottom:18px}
.mockup-cards{display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px}
.mockup-card{background:var(--panel); border:1px solid var(--line); border-radius:12px; padding:10px}
.progress-track{height:10px; border-radius:999px; background:color-mix(in srgb, var(--line) 70%, transparent); overflow:hidden}
.progress-track span{display:block; height:100%; width:78%; background:linear-gradient(90deg, #1D6DFF, #7DB6FF)}
.float-note{position:absolute; right:-10px; top:12%; background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:10px 12px; font-size:.82rem; box-shadow:var(--shadow)}
.float-note--bottom{left:-10px; right:auto; top:auto; bottom:12%}

.home-messages{padding:12px 0 34px}
.message-track{display:flex; gap:14px; overflow:hidden; mask-image:linear-gradient(to right, transparent, #000 7%, #000 93%, transparent)}
.message-lane{display:flex; gap:14px; min-width:max-content; animation:scrollLane 42s linear infinite}
.user-msg{min-width:280px; max-width:320px; border:1px solid var(--line); background:var(--panel); border-radius:16px; padding:16px; box-shadow:var(--shadow)}
.user-msg__meta{display:flex; justify-content:space-between; font-size:.78rem; margin-bottom:8px; color:var(--muted)}
.user-msg__tag{padding:3px 8px; border-radius:999px; border:1px solid var(--line); color:var(--primary)}

.trust-grid{display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin:24px 0}
.trust-item{border:1px solid var(--line); background:var(--panel); border-radius:16px; padding:14px; text-align:left; transition:.25s ease}
.trust-item strong{display:block; font-size:1.08rem; margin-bottom:6px; color:var(--primary)}
.trust-item:hover{transform:translateY(-4px)}

.class-tabs{display:flex; gap:10px; flex-wrap:wrap; margin:8px 0 20px}
.class-tab{border:1px solid var(--line); background:var(--panel); border-radius:999px; padding:8px 14px; cursor:pointer; font-size:.88rem}
.class-tab.is-active{background:var(--primary); color:#fff; border-color:transparent}
.classes-grid{display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:14px}
.class-card{border:1px solid var(--line); border-radius:16px; padding:16px; background:var(--panel); transition:.2s ease; display:flex; flex-direction:column; gap:10px}
.class-card:hover{transform:translateY(-5px); box-shadow:var(--shadow)}
.class-badges{display:flex; gap:8px; flex-wrap:wrap}
.class-badge{font-size:.72rem; padding:2px 8px; border-radius:999px; border:1px solid var(--line)}
.class-badge--popular{color:#0d9488}
.class-badge--recommended{color:#2563eb}
.class-badge--new{color:#7c3aed}
.class-actions{display:flex; gap:8px; margin-top:auto}

.section-dark{background:linear-gradient(180deg, #0b1b40, #0a1532); color:#eaf1ff; border-radius:24px; padding:28px}
.why-grid,.audience-grid,.pricing-grid,.faq-grid{display:grid; gap:14px}
.why-grid{grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); margin-top:14px}
.why-card,.audience-card,.pricing-card,.faq-item,.support-card{border:1px solid color-mix(in srgb, var(--line) 80%, transparent); border-radius:16px; padding:16px; background:var(--panel); transition:.25s ease}
.section-dark .why-card{background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.15)}
.why-card:hover,.audience-card:hover,.pricing-card:hover,.support-card:hover{transform:translateY(-4px)}

.pricing-grid{grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); margin-top:14px}
.pricing-card--highlight{border:2px solid #1D6DFF; box-shadow:0 16px 38px rgba(29,109,255,.22)}
.pricing-badge{display:inline-block; padding:4px 10px; border-radius:999px; background:#1D6DFF; color:#fff; font-size:.76rem; margin-bottom:8px}

.faq-item details summary{cursor:pointer; font-weight:600}
.faq-item details p{margin:10px 0 0; color:var(--muted)}

.support-wrap{display:grid; grid-template-columns:1fr auto; gap:18px; align-items:center}
.support-actions{display:flex; flex-wrap:wrap; gap:10px}
.support-meta{display:flex; gap:10px; flex-wrap:wrap; color:var(--muted); font-size:.86rem; margin-top:10px}

.reveal{opacity:0; transform:translateY(16px); transition:.6s ease}
.reveal.is-visible{opacity:1; transform:none}

@keyframes scrollLane{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
@keyframes softFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}

@media (max-width: 1024px){
  .home-hero__grid,.support-wrap{grid-template-columns:1fr}
  .trust-grid{grid-template-columns:repeat(3,1fr)}
}
@media (max-width: 720px){
  .trust-grid{grid-template-columns:repeat(2,1fr)}
  .home-hero{padding-top:48px}
}
</style>

<div class="home-shell">
<section class="home-hero">
    <div class="container">
        <div class="home-hero__grid">
            <div class="reveal">
                <span class="home-hero__badge">✨ {{ $hero['badge'] ?? 'Essai gratuit 24h' }}</span>
                <h1 class="home-hero__title">{{ $hero['title'] ?? '' }}</h1>
                <p class="home-hero__subtitle">{{ $hero['subtitle'] ?? '' }}</p>

                <div class="home-hero__actions">
                    <a href="{{ $hero['primary_cta_link'] ?? route('register') }}" class="btn btn--primary">{{ $hero['primary_cta_label'] ?? 'Commencer maintenant' }}</a>
                    <a href="{{ $hero['secondary_cta_link'] ?? '#classes' }}" class="btn btn--ghost">{{ $hero['secondary_cta_label'] ?? 'Voir les classes' }}</a>
                </div>

                <div class="home-hero__secondary">
                    <a href="{{ $hero['contact_cta_link'] ?? '#help-support' }}" class="btn">{{ $hero['contact_cta_label'] ?? "Contacter l'équipe" }}</a>
                    <a href="{{ $hero['help_cta_link'] ?? '#mini-faq' }}" class="btn">{{ $hero['help_cta_label'] ?? "Centre d'aide" }}</a>
                </div>

                <div class="home-reassurance">
                    @foreach(($hero['reassurance'] ?? []) as $pill)
                        <span>{{ $pill }}</span>
                    @endforeach
                </div>
            </div>

            <div class="reveal">
                <div class="dashboard-mockup">
                    <div class="mockup-top"><strong>Dashboard élève</strong><small>Activité en direct</small></div>
                    <div class="mockup-cards">
                        <div class="mockup-card"><small>Progression</small><strong>78%</strong></div>
                        <div class="mockup-card"><small>Score quiz</small><strong>17/20</strong></div>
                        <div class="mockup-card"><small>TD finis</small><strong>11</strong></div>
                    </div>
                    <small>Progression hebdo</small>
                    <div class="progress-track"><span></span></div>
                    <div class="float-note">⚡ +3 quiz cette semaine</div>
                    <div class="float-note float-note--bottom">🎯 Objectif du mois: 82%</div>
                </div>
            </div>
        </div>
    </div>
</section>

@if(($sections->get('messages')['enabled'] ?? true))
<section class="home-messages reveal">
    <div class="container">
        <h2 class="section-title" style="margin-bottom:10px;">Ce que disent nos utilisateurs</h2>
        <div class="message-track">
            <div class="message-lane">
                @foreach($displayMessages->concat($displayMessages) as $message)
                    <article class="user-msg">
                        <div class="user-msg__meta">
                            <span>{{ $message->is_anonymous ? 'Anonyme' : ($message->author_label ?: 'Utilisateur') }}</span>
                            <span class="user-msg__tag">{{ $message->role_tag }}</span>
                        </div>
                        <p>{{ $message->message }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

@if($sections->get('trust')['enabled'] ?? true)
<section class="section section--tight reveal">
    <div class="container">
        <div class="trust-grid">
            @foreach($trustItems as $item)
                <div class="trust-item"><strong>{{ $item['value'] ?? '' }}</strong><span>{{ $item['title'] ?? '' }}</span></div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($sections->get('classes')['enabled'] ?? true)
<section id="classes" class="section reveal">
    <div class="container">
        <h2 class="section-title">Classes disponibles</h2>
        <p class="section-subtitle">Une navigation claire par catégorie, des classes mises en avant et un accès immédiat.</p>
        <div class="class-tabs">
            <button class="class-tab is-active" data-class-filter="all">Toutes</button>
            @foreach($classTabs as $key => $label)
                <button class="class-tab" data-class-filter="{{ $key }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="classes-grid" data-classes-grid>
            @forelse($featuredClasses as $index => $class)
                <article class="class-card" data-class-level="{{ $class->level }}">
                    <div class="class-badges">
                        @if($index % 3 === 0)<span class="class-badge class-badge--popular">Populaire</span>@endif
                        @if($index % 4 === 0)<span class="class-badge class-badge--recommended">Recommandé</span>@endif
                        @if($index % 5 === 0)<span class="class-badge class-badge--new">Nouveau</span>@endif
                    </div>
                    <h3>{{ $class->name }}</h3>
                    <p class="muted">{{ $class->description ?: 'Contenus structurés, quiz et TD progressifs pour réussir avec méthode.' }}</p>
                    <div class="class-actions">
                        <a href="{{ route('register') }}" class="btn btn--primary">Commencer</a>
                        <a href="{{ route('register') }}" class="btn btn--ghost">Voir détails</a>
                    </div>
                </article>
            @empty
                <article class="class-card"><h3>Contenus bientôt disponibles</h3><p class="muted">Les classes seront affichées ici dès activation côté administration.</p></article>
            @endforelse
        </div>
    </div>
</section>
@endif

@if($sections->get('why')['enabled'] ?? true)
<section class="section section--tight reveal">
    <div class="container">
        <div class="section-dark">
            <h2>Pourquoi choisir TIMAH ACADEMY</h2>
            <div class="why-grid">
                @foreach($whyItems as $item)
                    <article class="why-card"><h3>{{ $item['title'] ?? '' }}</h3><p>{{ $item['text'] ?? '' }}</p></article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

@if($sections->get('audiences')['enabled'] ?? true)
<section class="section reveal">
    <div class="container">
        <h2 class="section-title">Pour qui ?</h2>
        <div class="audience-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
            @foreach($audiences as $item)
                <article class="audience-card"><h3>{{ $item['title'] ?? '' }}</h3><p class="muted">{{ $item['text'] ?? '' }}</p></article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($sections->get('pricing')['enabled'] ?? true)
<section id="pricing" class="section section--tight reveal">
    <div class="container">
        <h2 class="section-title">Abonnements flexibles</h2>
        <p class="section-subtitle">Choisissez une formule adaptée à votre rythme. Besoin d’aide ? Notre équipe vous conseille.</p>
        <div class="pricing-grid">
            @foreach($plans as $plan)
                <article class="pricing-card {{ $plan['highlight'] ? 'pricing-card--highlight' : '' }}">
                    @if($plan['highlight'])<span class="pricing-badge">Le plus choisi</span>@endif
                    <h3>{{ $plan['title'] }}</h3>
                    <div class="plan-price">{{ $plan['price'] }}</div>
                    <p class="muted">{{ $plan['desc'] }}</p>
                    <ul class="feature-list">
                        @foreach($plan['features'] as $feature)
                            <li><span>✔</span><span>{{ $feature }}</span></li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn--primary btn--full">Choisir</a>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($sections->get('faq')['enabled'] ?? true)
<section id="mini-faq" class="section reveal">
    <div class="container">
        <h2 class="section-title">Mini FAQ</h2>
        <div class="faq-grid">
            @forelse($faqItems as $item)
                <article class="faq-item">
                    <details>
                        <summary>{{ $item['question'] ?? '' }}</summary>
                        <p>{{ $item['answer'] ?? '' }}</p>
                    </details>
                </article>
            @empty
                <article class="faq-item"><strong>FAQ bientôt disponible</strong><p class="muted">Ajoutez des questions/réponses depuis l’espace admin homepage.</p></article>
            @endforelse
        </div>
    </div>
</section>
@endif

@if($sections->get('support')['enabled'] ?? true)
<section id="help-support" class="section reveal">
    <div class="container">
        <article class="support-card">
            <div class="support-wrap">
                <div>
                    <h2>{{ $support['title'] ?? 'Besoin d’aide ?' }}</h2>
                    <p class="muted">{{ $support['text'] ?? '' }}</p>
                    <div class="support-meta">
                        @if(!empty($support['email']))<span>Email : {{ $support['email'] }}</span>@endif
                        @if(!empty($support['phone']))<span>Tél : {{ $support['phone'] }}</span>@endif
                        @if(!empty($support['whatsapp']))<span>WhatsApp : {{ $support['whatsapp'] }}</span>@endif
                        @if(!empty($support['hours']))<span>Horaires : {{ $support['hours'] }}</span>@endif
                    </div>
                </div>
                <div class="support-actions">
                    <a class="btn btn--primary" href="{{ $support['contact_link'] ?? '#' }}">Contacter l’entreprise</a>
                    <a class="btn btn--ghost" href="{{ $support['help_link'] ?? '#mini-faq' }}">Aide / support</a>
                    <a class="btn btn--ghost" href="{{ $support['faq_link'] ?? '#mini-faq' }}">FAQ</a>
                    <a class="btn" href="{{ $support['info_link'] ?? '#' }}">Demander des informations</a>
                </div>
            </div>
        </article>
    </div>
</section>
@endif
</div>

<script>
(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) entry.target.classList.add('is-visible');
        });
    }, {threshold: 0.12});
    document.querySelectorAll('.reveal').forEach((node) => observer.observe(node));

    document.querySelectorAll('[data-class-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.getAttribute('data-class-filter');
            document.querySelectorAll('[data-class-filter]').forEach((btn) => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            document.querySelectorAll('[data-class-level]').forEach((card) => {
                const show = filter === 'all' || card.getAttribute('data-class-level') === filter;
                card.style.display = show ? '' : 'none';
            });
        });
    });
})();
</script>
@endsection
