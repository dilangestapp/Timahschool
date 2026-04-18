@extends('layouts.public')

@section('title', 'TIMAH ACADEMY - Réussissez vos examens')
@section('meta_description', 'TIMAH ACADEMY accompagne les élèves avec des cours structurés, des quiz, des TD corrigés, un suivi de progression et des abonnements adaptés.')

@php
    $defaults = \App\Models\HomepageSetting::defaults();

    $hero = $homepage['hero'] ?? [];
    $trustItems = collect($homepage['trust_items'] ?? $defaults['trust_items'] ?? [])->values();
    $whyItems = collect($homepage['why_choose'] ?? $defaults['why_choose'] ?? [])->values();
    $faqItems = collect($homepage['faq'] ?? $defaults['faq'] ?? [])->values();
    $support = array_merge($defaults['support'] ?? [], $homepage['support'] ?? []);
    $footer = array_merge($defaults['footer'] ?? [], $homepage['footer'] ?? []);
    $sections = collect($homepage['sections'] ?? [])->keyBy('key');
    $plans = collect($homepage['pricing'] ?? $defaults['pricing'] ?? [])->values();

    $isMessagesEnabled = (bool) data_get($sections, 'messages.enabled', true);
    $isTrustEnabled = (bool) data_get($sections, 'trust.enabled', true);
    $isClassesEnabled = (bool) data_get($sections, 'classes.enabled', true);
    $isWhyEnabled = (bool) data_get($sections, 'why.enabled', true);
    $isAudiencesEnabled = (bool) data_get($sections, 'audiences.enabled', true);
    $isPricingEnabled = (bool) data_get($sections, 'pricing.enabled', true);
    $isFaqEnabled = (bool) data_get($sections, 'faq.enabled', true);
    $isSupportEnabled = (bool) data_get($sections, 'support.enabled', true);

    $classTabs = $classGroups->mapWithKeys(
        fn ($items, $key) => [$key => $classGroupLabels[$key] ?? ucfirst(str_replace('_', ' ', $key))]
    );

    $displayMessages = $messages->isNotEmpty()
        ? $messages->take(8)->values()
        : collect([
            (object) [
                'is_anonymous' => true,
                'author_label' => 'Anonyme',
                'role_tag' => 'Élève',
                'message' => 'Les quiz m’aident à travailler chaque semaine sans attendre les examens.',
            ],
            (object) [
                'is_anonymous' => true,
                'author_label' => 'Anonyme',
                'role_tag' => 'Parent',
                'message' => 'Je comprends mieux la progression de mon enfant et je peux l’accompagner.',
            ],
            (object) [
                'is_anonymous' => false,
                'author_label' => 'Mme N.',
                'role_tag' => 'Enseignante',
                'message' => 'La plateforme donne un cadre plus clair pour suivre les TD et les points faibles.',
            ],
            (object) [
                'is_anonymous' => true,
                'author_label' => 'Anonyme',
                'role_tag' => 'Élève',
                'message' => 'Le mélange cours + TD + quiz me permet de réviser avec plus de confiance.',
            ],
            (object) [
                'is_anonymous' => false,
                'author_label' => 'Parent actif',
                'role_tag' => 'Parent',
                'message' => 'Le rendu est simple à comprendre et l’accès aux contenus est rapide.',
            ],
            (object) [
                'is_anonymous' => false,
                'author_label' => 'Coach pédagogique',
                'role_tag' => 'Support',
                'message' => 'Les élèves peuvent avancer à leur rythme tout en gardant un cadre structuré.',
            ],
        ]);

    $messageLaneOne = $displayMessages->values();
    $messageLaneTwo = $displayMessages->reverse()->values();

    $audiences = collect($homepage['audiences'] ?? $defaults['audiences'] ?? [])->values();

    $hasParentAudience = $audiences->contains(function ($item) {
        $title = mb_strtolower((string) ($item['title'] ?? ''));
        $text = mb_strtolower((string) ($item['text'] ?? ''));

        return str_contains($title, 'parent') || str_contains($text, 'parent');
    });

    if (! $hasParentAudience) {
        $audiences = $audiences->splice(0)->prepend([
            'title' => 'Pour les parents',
            'text' => 'Suivez plus facilement le rythme, les progrès et les besoins d’accompagnement.',
        ])->values();
    }

    $audiences = $audiences->unique(fn ($item) => ($item['title'] ?? '') . '|' . ($item['text'] ?? ''))->take(4)->values();

    $heroReassurance = collect($hero['reassurance'] ?? [])->filter()->values();
    if ($heroReassurance->isEmpty()) {
        $heroReassurance = collect([
            'Sans engagement',
            'Cours + quiz + TD',
            'Suivi de progression',
            'Interface claire et moderne',
        ]);
    }

    $activeClassesCount = $classes->count();
    $featuredClassesCount = $featuredClasses->count();
    $generalClassesCount = $classGroups->get('enseignement_general', collect())->count();
    $technicalClassesCount = $classGroups->get('enseignement_technique', collect())->count();

    $supportContactLink = ! empty($support['contact_link']) && $support['contact_link'] !== '#'
        ? $support['contact_link']
        : (Route::has('register') ? route('register') : '#');

    $supportHelpLink = ! empty($support['help_link']) ? $support['help_link'] : '#mini-faq';
    $supportFaqLink = ! empty($support['faq_link']) ? $support['faq_link'] : '#mini-faq';
    $supportInfoLink = ! empty($support['info_link']) && $support['info_link'] !== '#'
        ? $support['info_link']
        : $supportContactLink;
@endphp

@push('styles')
<style>
    .home-shell {
        position: relative;
        overflow: clip;
    }

    .home-shell::before,
    .home-shell::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
        z-index: 0;
        filter: blur(10px);
    }

    .home-shell::before {
        width: 420px;
        height: 420px;
        top: -140px;
        right: -120px;
        background: radial-gradient(circle, rgba(29, 109, 255, 0.18), transparent 68%);
    }

    .home-shell::after {
        width: 320px;
        height: 320px;
        left: -120px;
        top: 520px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.12), transparent 68%);
    }

    .home-shell .container {
        position: relative;
        z-index: 1;
    }

    .home-shell section[id] {
        scroll-margin-top: 112px;
    }

    .home-hero {
        padding: 44px 0 34px;
    }

    .home-hero__grid {
        display: grid;
        grid-template-columns: 1.02fr .98fr;
        gap: 28px;
        align-items: stretch;
    }

    .hero-panel,
    .hero-visual {
        position: relative;
        border: 1px solid var(--line);
        border-radius: 30px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.90), rgba(255, 255, 255, 0.72));
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    html[data-theme='dark'] .hero-panel,
    html[data-theme='dark'] .hero-visual {
        background: linear-gradient(180deg, rgba(14, 27, 49, 0.96), rgba(16, 32, 58, 0.88));
    }

    .hero-panel {
        padding: 36px;
    }

    .hero-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top right, rgba(29, 109, 255, 0.14), transparent 34%),
            linear-gradient(180deg, transparent, rgba(29, 109, 255, 0.03));
        pointer-events: none;
    }

    .hero-panel > * {
        position: relative;
        z-index: 1;
    }

    .hero-title {
        margin: 18px 0 14px;
        font-size: clamp(2.3rem, 4vw, 4rem);
        line-height: 1.02;
        letter-spacing: -0.04em;
        max-width: 12ch;
    }

    .hero-title .accent {
        display: block;
        color: var(--primary);
    }

    .hero-subtitle {
        margin: 0;
        max-width: 60ch;
        color: var(--muted);
        font-size: 1.04rem;
    }

    .hero-actions {
        margin-top: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .hero-actions--secondary {
        margin-top: 12px;
    }

    .hero-reassurance {
        margin-top: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .hero-reassurance__pill,
    .stat-chip,
    .class-meta,
    .audience-tag,
    .support-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.72);
        color: var(--text);
        font-size: .84rem;
        font-weight: 700;
    }

    html[data-theme='dark'] .hero-reassurance__pill,
    html[data-theme='dark'] .stat-chip,
    html[data-theme='dark'] .class-meta,
    html[data-theme='dark'] .audience-tag,
    html[data-theme='dark'] .support-tag {
        background: rgba(14, 27, 49, 0.94);
    }

    .hero-bottom {
        margin-top: 28px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .hero-bottom__card {
        padding: 16px;
        border-radius: 20px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.72);
        box-shadow: var(--shadow-xs);
    }

    html[data-theme='dark'] .hero-bottom__card {
        background: rgba(12, 26, 47, 0.94);
    }

    .hero-bottom__value {
        display: block;
        font-size: 1.2rem;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .hero-bottom__label {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: .88rem;
    }

    .hero-visual {
        padding: 26px;
        min-height: 100%;
    }

    .hero-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 15% 14%, rgba(29, 109, 255, 0.16), transparent 24%),
            radial-gradient(circle at 100% 0%, rgba(56, 189, 248, 0.12), transparent 24%);
        pointer-events: none;
    }

    .dashboard-window {
        position: relative;
        border: 1px solid var(--line);
        border-radius: 28px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .dashboard-topbar {
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--line);
        background: rgba(29, 109, 255, 0.05);
    }

    .dashboard-topbar__dots {
        display: inline-flex;
        gap: 7px;
    }

    .dashboard-topbar__dots span {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: rgba(29, 109, 255, 0.16);
    }

    .dashboard-body {
        padding: 18px;
        display: grid;
        gap: 16px;
    }

    .dashboard-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .summary-card {
        padding: 14px;
        border: 1px solid var(--line);
        border-radius: 20px;
        background: var(--panel);
        box-shadow: var(--shadow-xs);
    }

    .summary-card strong {
        display: block;
        font-size: 1.2rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        margin-top: 5px;
    }

    .summary-card small {
        color: var(--muted);
        font-weight: 700;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 14px;
    }

    .dashboard-stack {
        display: grid;
        gap: 14px;
    }

    .dashboard-block {
        padding: 16px;
        border: 1px solid var(--line);
        border-radius: 22px;
        background: var(--panel);
        box-shadow: var(--shadow-xs);
    }

    .progress-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .progress-track {
        height: 12px;
        border-radius: 999px;
        background: rgba(29, 109, 255, 0.10);
        overflow: hidden;
    }

    .progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--primary), #70abff);
    }

    .mini-list {
        display: grid;
        gap: 10px;
    }

    .mini-list__item {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: var(--panel-soft);
        color: var(--muted);
    }

    .mini-list__item strong {
        color: var(--text);
    }

    .floating-badge {
        position: absolute;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.92);
        box-shadow: var(--shadow);
        font-weight: 800;
        font-size: .86rem;
    }

    html[data-theme='dark'] .floating-badge {
        background: rgba(14, 27, 49, 0.96);
    }

    .floating-badge--top {
        top: 26px;
        right: 22px;
    }

    .floating-badge--bottom {
        left: 18px;
        bottom: 22px;
    }

    .section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .section-head__text {
        display: grid;
        gap: 10px;
    }

    .section-head__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .message-section {
        padding-top: 18px;
    }

    .message-wrap {
        display: grid;
        gap: 18px;
    }

    .message-lane {
        position: relative;
        display: flex;
        gap: 14px;
        width: max-content;
        animation: timahScroll 34s linear infinite;
        will-change: transform;
    }

    .message-track {
        overflow: hidden;
        padding: 4px 0;
        mask-image: linear-gradient(to right, transparent, #000 6%, #000 94%, transparent);
    }

    .message-track:hover .message-lane {
        animation-play-state: paused;
    }

    .message-track--reverse .message-lane {
        animation-direction: reverse;
        animation-duration: 38s;
    }

    .message-card {
        min-width: 300px;
        max-width: 320px;
        display: grid;
        gap: 14px;
        padding: 18px;
        border-radius: 24px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
    }

    .message-card__top {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .message-avatar {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        color: var(--primary);
        background: rgba(29, 109, 255, 0.10);
        border: 1px solid var(--line);
    }

    .message-card__meta {
        min-width: 0;
        display: grid;
        gap: 2px;
    }

    .message-card__meta strong {
        font-size: .98rem;
        line-height: 1.2;
    }

    .message-card__role {
        color: var(--muted);
        font-size: .85rem;
        font-weight: 700;
    }

    .message-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .trust-section {
        padding-top: 10px;
    }

    .trust-grid {
        display: grid;
        grid-template-columns: 1.05fr repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .trust-featured,
    .trust-item {
        border: 1px solid var(--line);
        border-radius: 26px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
    }

    .trust-featured {
        padding: 24px;
        display: grid;
        gap: 16px;
    }

    .trust-featured h3 {
        margin: 0;
        font-size: 1.5rem;
        line-height: 1.08;
        letter-spacing: -0.03em;
    }

    .trust-featured p {
        margin: 0;
        color: var(--muted);
    }

    .trust-highlight-list {
        display: grid;
        gap: 10px;
    }

    .trust-highlight-list div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
        background: rgba(29, 109, 255, 0.06);
        border: 1px solid var(--line);
    }

    .trust-item {
        padding: 18px;
        display: grid;
        gap: 10px;
        align-content: start;
        min-height: 170px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .trust-item:hover,
    .class-card:hover,
    .why-card:hover,
    .audience-card:hover,
    .pricing-card:hover,
    .faq-card:hover,
    .support-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .trust-value {
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
        color: var(--primary);
    }

    .trust-title {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .trust-note {
        color: var(--muted);
        font-size: .92rem;
    }

    .class-section {
        position: relative;
    }

    .class-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 6px;
        margin-bottom: 20px;
    }

    .class-tab {
        min-height: 42px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: var(--panel);
        color: var(--muted);
        font-size: .92rem;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .class-tab:hover {
        background: var(--panel-soft);
        color: var(--text);
    }

    .class-tab.is-active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        box-shadow: 0 14px 28px rgba(29, 109, 255, 0.20);
    }

    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(255px, 1fr));
        gap: 16px;
    }

    .class-card {
        padding: 18px;
        display: grid;
        gap: 14px;
        border: 1px solid var(--line);
        border-radius: 24px;
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .class-card__top,
    .class-badges,
    .class-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .class-system {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 32px;
        padding: 0 11px;
        border-radius: 999px;
        background: rgba(29, 109, 255, 0.08);
        color: var(--primary);
        font-size: .78rem;
        font-weight: 900;
        border: 1px solid var(--line);
    }

    .class-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        border: 1px solid var(--line);
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .class-badge--popular {
        color: #0f766e;
        background: rgba(15, 118, 110, 0.08);
    }

    .class-badge--recommended {
        color: #1d4ed8;
        background: rgba(29, 78, 216, 0.08);
    }

    .class-badge--guided {
        color: #7c3aed;
        background: rgba(124, 58, 237, 0.08);
    }

    .class-card h3 {
        margin: 0;
        font-size: 1.18rem;
        letter-spacing: -0.03em;
    }

    .class-card p {
        margin: 0;
        color: var(--muted);
    }

    .class-footer {
        margin-top: auto;
        display: grid;
        gap: 14px;
    }

    .class-preview {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .class-empty {
        padding: 20px;
        border-radius: 24px;
        border: 1px dashed var(--line-strong);
        background: var(--panel);
        color: var(--muted);
    }

    .dark-showcase {
        padding: 34px;
        border-radius: 32px;
        color: #e9f0ff;
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, 0.22), transparent 28%),
            linear-gradient(180deg, #0d1a36, #081224);
        box-shadow: var(--shadow-lg);
    }

    .dark-showcase__grid {
        display: grid;
        grid-template-columns: .92fr 1.08fr;
        gap: 22px;
        align-items: start;
    }

    .dark-showcase__intro {
        display: grid;
        gap: 16px;
        align-content: start;
    }

    .dark-showcase__intro p {
        margin: 0;
        color: rgba(232, 239, 255, 0.78);
    }

    .dark-showcase__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .dark-showcase__chips span {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.06);
        font-size: .84rem;
        font-weight: 800;
    }

    .dark-showcase__stats {
        display: grid;
        gap: 12px;
    }

    .dark-stat {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.05);
    }

    .dark-stat strong {
        font-size: 1.05rem;
    }

    .dark-stat span {
        color: rgba(232, 239, 255, 0.72);
    }

    .why-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .why-card {
        padding: 18px;
        border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .why-card h3 {
        margin: 0 0 10px;
        font-size: 1.08rem;
        letter-spacing: -0.02em;
        color: #fff;
    }

    .why-card p {
        margin: 0;
        color: rgba(232, 239, 255, 0.78);
    }

    .audience-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .audience-card {
        padding: 20px;
        display: grid;
        gap: 14px;
        border-radius: 24px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .audience-card h3 {
        margin: 0;
        font-size: 1.1rem;
        letter-spacing: -0.03em;
    }

    .audience-card p {
        margin: 0;
        color: var(--muted);
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .pricing-card {
        position: relative;
        padding: 22px;
        display: grid;
        gap: 12px;
        border-radius: 28px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .pricing-card--highlight {
        border: 2px solid var(--primary);
        box-shadow: 0 18px 40px rgba(29, 109, 255, 0.20);
        transform: translateY(-6px);
    }

    .pricing-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        color: #fff;
        font-size: .76rem;
        font-weight: 900;
        letter-spacing: -0.01em;
    }

    .pricing-card h3 {
        margin: 0;
        font-size: 1.2rem;
        letter-spacing: -0.03em;
    }

    .pricing-card p {
        margin: 0;
        color: var(--muted);
    }

    .pricing-footnote {
        margin-top: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .pricing-footnote span {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(29, 109, 255, 0.06);
        color: var(--text);
        font-size: .84rem;
        font-weight: 800;
    }

    .faq-grid {
        display: grid;
        grid-template-columns: 1.08fr .92fr;
        gap: 18px;
        align-items: start;
    }

    .faq-list {
        display: grid;
        gap: 14px;
    }

    .faq-card {
        padding: 18px 20px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .faq-card details {
        display: block;
    }

    .faq-card summary {
        cursor: pointer;
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    .faq-card summary::-webkit-details-marker {
        display: none;
    }

    .faq-card summary::after {
        content: "+";
        font-size: 1.2rem;
        line-height: 1;
        color: var(--primary);
    }

    .faq-card details[open] summary::after {
        content: "–";
    }

    .faq-card p {
        margin: 12px 0 0;
        color: var(--muted);
    }

    .faq-side {
        display: grid;
        gap: 14px;
    }

    .faq-side__card {
        padding: 22px;
        border-radius: 24px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(29, 109, 255, 0.14), transparent 30%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
    }

    .faq-side__card h3,
    .support-card h2 {
        margin: 0 0 10px;
        letter-spacing: -0.03em;
    }

    .faq-side__card p,
    .support-card p {
        margin: 0;
        color: var(--muted);
    }

    .support-card {
        padding: 24px;
        border-radius: 28px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(29, 109, 255, 0.12), transparent 26%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow-lg);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .support-layout {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 22px;
        align-items: start;
    }

    .support-content,
    .support-panel {
        display: grid;
        gap: 16px;
    }

    .support-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .support-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .support-panel__box {
        padding: 18px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background: rgba(29, 109, 255, 0.06);
        display: grid;
        gap: 12px;
    }

    .support-panel__box strong {
        letter-spacing: -0.02em;
    }

    .support-panel__list {
        display: grid;
        gap: 10px;
    }

    .support-panel__list div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        color: var(--muted);
    }

    .support-panel__list div span:last-child {
        color: var(--text);
        font-weight: 700;
    }

    .reveal {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity .7s ease, transform .7s ease;
    }

    .reveal.is-visible {
        opacity: 1;
        transform: none;
    }

    @keyframes timahScroll {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    @media (max-width: 1180px) {
        .home-hero__grid,
        .dark-showcase__grid,
        .support-layout,
        .faq-grid,
        .trust-grid {
            grid-template-columns: 1fr;
        }

        .audience-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pricing-grid {
            grid-template-columns: 1fr;
        }

        .pricing-card--highlight {
            transform: none;
        }

        .trust-grid {
            gap: 16px;
        }
    }

    @media (max-width: 900px) {
        .home-hero {
            padding-top: 28px;
        }

        .hero-panel,
        .hero-visual,
        .dark-showcase,
        .support-card {
            border-radius: 24px;
        }

        .hero-panel,
        .hero-visual,
        .dark-showcase,
        .support-card,
        .faq-card,
        .faq-side__card,
        .trust-featured,
        .trust-item,
        .class-card,
        .audience-card,
        .pricing-card {
            box-shadow: var(--shadow);
        }

        .hero-panel,
        .hero-visual,
        .dark-showcase {
            padding-left: 20px;
            padding-right: 20px;
        }

        .dashboard-summary,
        .dashboard-grid,
        .hero-bottom,
        .why-grid {
            grid-template-columns: 1fr;
        }

        .floating-badge {
            position: static;
            margin-top: 12px;
        }
    }

    @media (max-width: 720px) {
        .hero-panel {
            padding: 24px 18px;
        }

        .hero-visual {
            padding: 18px;
        }

        .hero-title {
            font-size: clamp(2rem, 11vw, 3rem);
        }

        .message-card {
            min-width: 272px;
            max-width: 272px;
        }

        .classes-grid,
        .audience-grid {
            grid-template-columns: 1fr;
        }

        .dark-showcase,
        .support-card {
            padding: 20px 18px;
        }

        .class-actions,
        .support-actions,
        .hero-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .class-actions .btn,
        .support-actions .btn,
        .hero-actions .btn {
            width: 100%;
        }

        .faq-card,
        .faq-side__card,
        .trust-featured,
        .trust-item,
        .class-card,
        .audience-card,
        .pricing-card {
            padding: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="home-shell">
    <section class="home-hero">
        <div class="container">
            <div class="home-hero__grid">
                <div class="hero-panel reveal">
                    <span class="eyebrow">✨ {{ $hero['badge'] ?? 'Essai gratuit 24h' }}</span>

                    <h1 class="hero-title">
                        {{ $hero['title'] ?? 'Réussissez avec une plateforme claire, moderne et efficace' }}
                        <span class="accent">pour apprendre avec méthode.</span>
                    </h1>

                    <p class="hero-subtitle">
                        {{ $hero['subtitle'] ?? 'Cours structurés, quiz interactifs, TD corrigés et suivi de progression pour aider chaque élève à avancer avec confiance.' }}
                    </p>

                    <div class="hero-actions">
                        <a href="{{ $hero['primary_cta_link'] ?? (Route::has('register') ? route('register') : '#') }}" class="btn btn--primary">
                            {{ $hero['primary_cta_label'] ?? 'Commencer maintenant' }}
                        </a>

                        <a href="{{ $hero['secondary_cta_link'] ?? '#classes' }}" class="btn btn--ghost">
                            {{ $hero['secondary_cta_label'] ?? 'Voir les classes' }}
                        </a>
                    </div>

                    <div class="hero-actions hero-actions--secondary">
                        <a href="{{ $hero['contact_cta_link'] ?? '#help-support' }}" class="btn">
                            {{ $hero['contact_cta_label'] ?? "Contacter l'équipe" }}
                        </a>

                        <a href="{{ $hero['help_cta_link'] ?? '#mini-faq' }}" class="btn">
                            {{ $hero['help_cta_label'] ?? "Centre d'aide" }}
                        </a>
                    </div>

                    <div class="hero-reassurance">
                        @foreach ($heroReassurance as $pill)
                            <span class="hero-reassurance__pill">✔ {{ $pill }}</span>
                        @endforeach
                    </div>

                    <div class="hero-bottom">
                        <article class="hero-bottom__card">
                            <span class="hero-bottom__value">{{ $activeClassesCount > 0 ? $activeClassesCount : '12+' }}</span>
                            <span class="hero-bottom__label">classes actives et prêtes à explorer</span>
                        </article>

                        <article class="hero-bottom__card">
                            <span class="hero-bottom__value">{{ $featuredClassesCount > 0 ? $featuredClassesCount : '9+' }}</span>
                            <span class="hero-bottom__label">classes mises en avant sur la homepage</span>
                        </article>

                        <article class="hero-bottom__card">
                            <span class="hero-bottom__value">{{ $displayMessages->count() > 0 ? $displayMessages->count() : '6+' }}</span>
                            <span class="hero-bottom__label">retours visibles d’élèves, parents et enseignants</span>
                        </article>
                    </div>
                </div>

                <div class="hero-visual reveal">
                    <div class="dashboard-window">
                        <div class="dashboard-topbar">
                            <div class="dashboard-topbar__dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <strong>Dashboard élève</strong>
                            <small class="muted">Activité récente</small>
                        </div>

                        <div class="dashboard-body">
                            <div class="dashboard-summary">
                                <article class="summary-card">
                                    <small>Progression</small>
                                    <strong>78%</strong>
                                </article>

                                <article class="summary-card">
                                    <small>Quiz validés</small>
                                    <strong>17/20</strong>
                                </article>

                                <article class="summary-card">
                                    <small>TD terminés</small>
                                    <strong>11</strong>
                                </article>
                            </div>

                            <div class="dashboard-grid">
                                <div class="dashboard-stack">
                                    <article class="dashboard-block">
                                        <div class="progress-row">
                                            <strong>Objectif de la semaine</strong>
                                            <span class="muted">Atteint à 82%</span>
                                        </div>
                                        <div class="progress-track">
                                            <span style="width: 82%;"></span>
                                        </div>
                                    </article>

                                    <article class="dashboard-block">
                                        <div class="progress-row">
                                            <strong>Révisions par matière</strong>
                                            <span class="muted">3 matières</span>
                                        </div>

                                        <div class="mini-list">
                                            <div class="mini-list__item">
                                                <span>Mathématiques</span>
                                                <strong>Très bon rythme</strong>
                                            </div>
                                            <div class="mini-list__item">
                                                <span>Physique</span>
                                                <strong>À renforcer</strong>
                                            </div>
                                            <div class="mini-list__item">
                                                <span>Français</span>
                                                <strong>Bonne stabilité</strong>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <article class="dashboard-block">
                                    <div class="progress-row">
                                        <strong>À faire aujourd’hui</strong>
                                        <span class="muted">3 actions</span>
                                    </div>

                                    <div class="mini-list">
                                        <div class="mini-list__item">
                                            <span>Quiz de révision</span>
                                            <strong>Commencer</strong>
                                        </div>
                                        <div class="mini-list__item">
                                            <span>TD corrigé</span>
                                            <strong>Disponible</strong>
                                        </div>
                                        <div class="mini-list__item">
                                            <span>Suivi de score</span>
                                            <strong>En direct</strong>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>

                    <div class="floating-badge floating-badge--top">⚡ +3 quiz cette semaine</div>
                    <div class="floating-badge floating-badge--bottom">🎯 Objectif du mois : 82%</div>
                </div>
            </div>
        </div>
    </section>

    @if ($isMessagesEnabled)
        <section class="section section--tight message-section reveal">
            <div class="container">
                <div class="section-head">
                    <div class="section-head__text">
                        <span class="eyebrow">Témoignages visibles</span>
                        <h2 class="section-title">Des retours qui rendent la plateforme vivante</h2>
                        <p class="section-subtitle">
                            Élèves, parents et enseignants doivent sentir qu’ils entrent dans une vraie plateforme utile,
                            humaine et sérieuse — pas dans un simple site figé.
                        </p>
                    </div>

                    <div class="section-head__chips">
                        <span class="stat-chip">Élèves</span>
                        <span class="stat-chip">Parents</span>
                        <span class="stat-chip">Enseignants</span>
                        <span class="stat-chip">Support</span>
                    </div>
                </div>

                <div class="message-wrap">
                    <div class="message-track">
                        <div class="message-lane">
                            @foreach ($messageLaneOne->concat($messageLaneOne) as $message)
                                @php
                                    $displayName = $message->is_anonymous ? 'Anonyme' : ($message->author_label ?: 'Utilisateur');
                                    $avatarLetter = mb_strtoupper(mb_substr($displayName, 0, 1));
                                @endphp

                                <article class="message-card">
                                    <div class="message-card__top">
                                        <span class="message-avatar">{{ $avatarLetter }}</span>

                                        <div class="message-card__meta">
                                            <strong>{{ $displayName }}</strong>
                                            <span class="message-card__role">{{ $message->role_tag ?: 'Utilisateur' }}</span>
                                        </div>
                                    </div>

                                    <p>“{{ $message->message }}”</p>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="message-track message-track--reverse">
                        <div class="message-lane">
                            @foreach ($messageLaneTwo->concat($messageLaneTwo) as $message)
                                @php
                                    $displayName = $message->is_anonymous ? 'Anonyme' : ($message->author_label ?: 'Utilisateur');
                                    $avatarLetter = mb_strtoupper(mb_substr($displayName, 0, 1));
                                @endphp

                                <article class="message-card">
                                    <div class="message-card__top">
                                        <span class="message-avatar">{{ $avatarLetter }}</span>

                                        <div class="message-card__meta">
                                            <strong>{{ $displayName }}</strong>
                                            <span class="message-card__role">{{ $message->role_tag ?: 'Utilisateur' }}</span>
                                        </div>
                                    </div>

                                    <p>“{{ $message->message }}”</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($isTrustEnabled)
        <section class="section section--tight trust-section reveal">
            <div class="container">
                <div class="trust-grid">
                    <article class="trust-featured">
                        <span class="eyebrow">Confiance et repères</span>
                        <h3>Une homepage qui rassure, informe et donne envie d’entrer dans la plateforme</h3>
                        <p>
                            Les chiffres ne doivent pas seulement “remplir un bloc”.
                            Ils doivent rassurer, prouver l’utilité et renforcer la crédibilité de TIMAH ACADEMY.
                        </p>

                        <div class="trust-highlight-list">
                            <div>
                                <span>Enseignement général</span>
                                <strong>{{ $generalClassesCount > 0 ? $generalClassesCount : '—' }}</strong>
                            </div>
                            <div>
                                <span>Enseignement technique</span>
                                <strong>{{ $technicalClassesCount > 0 ? $technicalClassesCount : '—' }}</strong>
                            </div>
                            <div>
                                <span>Classes mises en avant</span>
                                <strong>{{ $featuredClassesCount > 0 ? $featuredClassesCount : '—' }}</strong>
                            </div>
                        </div>
                    </article>

                    @foreach ($trustItems as $item)
                        <article class="trust-item">
                            <span class="trust-value">{{ $item['value'] ?? '' }}</span>
                            <span class="trust-title">{{ $item['title'] ?? '' }}</span>
                            <span class="trust-note">Des indicateurs visibles pour rassurer dès la première visite.</span>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($isClassesEnabled)
        <section id="classes" class="section class-section reveal">
            <div class="container">
                <div class="section-head">
                    <div class="section-head__text">
                        <span class="eyebrow">Classes disponibles</span>
                        <h2 class="section-title">Un accès clair par filière et par niveau</h2>
                        <p class="section-subtitle">
                            L’utilisateur doit immédiatement comprendre qu’il peut trouver sa classe,
                            entrer dans le bon parcours et commencer sans confusion.
                        </p>
                    </div>

                    <div class="section-head__chips">
                        <span class="stat-chip">{{ $generalClassesCount }} général</span>
                        <span class="stat-chip">{{ $technicalClassesCount }} technique</span>
                        <span class="stat-chip">{{ $activeClassesCount }} classes actives</span>
                    </div>
                </div>

                <div class="class-tabs">
                    <button type="button" class="class-tab is-active" data-class-filter="all">Toutes</button>

                    @foreach ($classTabs as $key => $label)
                        <button type="button" class="class-tab" data-class-filter="{{ $key }}">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="classes-grid" data-classes-grid>
                    @forelse ($featuredClasses as $class)
                        @php
                            $classGroup = ($class->system ?? null) === 'enseignement_technique'
                                ? 'enseignement_technique'
                                : 'enseignement_general';

                            $systemLabel = $classGroupLabels[$classGroup] ?? 'Enseignement général';
                            $classDescription = $class->description ?: 'Contenus structurés, quiz progressifs, TD et accompagnement pour avancer avec méthode.';
                        @endphp

                        <article class="class-card" data-class-group="{{ $classGroup }}">
                            <div class="class-card__top">
                                <span class="class-system">{{ $systemLabel }}</span>
                            </div>

                            <div class="class-badges">
                                @if ($loop->first)
                                    <span class="class-badge class-badge--popular">Populaire</span>
                                @endif

                                @if ($loop->iteration === 2 || $loop->iteration === 4)
                                    <span class="class-badge class-badge--recommended">Recommandé</span>
                                @endif

                                @if ($loop->iteration === 3 || $loop->iteration === 5)
                                    <span class="class-badge class-badge--guided">Suivi guidé</span>
                                @endif
                            </div>

                            <h3>{{ $class->name }}</h3>

                            <p>{{ $classDescription }}</p>

                            <div class="class-footer">
                                <div class="class-preview">
                                    <span class="class-meta">Cours</span>
                                    <span class="class-meta">Quiz</span>
                                    <span class="class-meta">TD</span>
                                    <span class="class-meta">Progression</span>
                                </div>

                                <div class="class-actions">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn--primary">Commencer</a>
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn--ghost">Voir détails</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="class-empty">
                            Les classes mises en avant apparaîtront ici dès qu’elles seront activées côté administration.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    @if ($isWhyEnabled)
        <section class="section section--tight reveal">
            <div class="container">
                <div class="dark-showcase">
                    <div class="dark-showcase__grid">
                        <div class="dark-showcase__intro">
                            <span class="eyebrow">Pourquoi choisir TIMAH ACADEMY</span>

                            <h2 class="section-title" style="color: #fff; margin-bottom: 0;">
                                Une plateforme pensée pour la réussite,
                                pas un simple empilement de pages.
                            </h2>

                            <p>
                                L’objectif est de réunir dans une seule expérience une vraie logique pédagogique,
                                une bonne lisibilité, une structure commerciale claire et un rendu suffisamment premium
                                pour donner confiance dès l’arrivée sur le site.
                            </p>

                            <div class="dark-showcase__chips">
                                <span>Cours structurés</span>
                                <span>Quiz interactifs</span>
                                <span>TD corrigés</span>
                                <span>Suivi continu</span>
                            </div>

                            <div class="dark-showcase__stats">
                                <div class="dark-stat">
                                    <strong>Expérience claire</strong>
                                    <span>Navigation simple et directe</span>
                                </div>
                                <div class="dark-stat">
                                    <strong>Progression visible</strong>
                                    <span>Un meilleur suivi pour l’élève</span>
                                </div>
                                <div class="dark-stat">
                                    <strong>Utilité concrète</strong>
                                    <span>Apprendre, réviser, s’exercer</span>
                                </div>
                            </div>
                        </div>

                        <div class="why-grid">
                            @foreach ($whyItems as $item)
                                <article class="why-card">
                                    <h3>{{ $item['title'] ?? '' }}</h3>
                                    <p>{{ $item['text'] ?? '' }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($isAudiencesEnabled)
        <section class="section reveal">
            <div class="container">
                <div class="section-head">
                    <div class="section-head__text">
                        <span class="eyebrow">Pour qui ?</span>
                        <h2 class="section-title">Une plateforme utile pour toute la chaîne éducative</h2>
                        <p class="section-subtitle">
                            Le discours doit être clair : TIMAH ACADEMY ne parle pas seulement aux élèves,
                            mais aussi aux parents, aux enseignants et, plus tard, aux établissements.
                        </p>
                    </div>
                </div>

                <div class="audience-grid">
                    @foreach ($audiences as $item)
                        <article class="audience-card">
                            <span class="audience-tag">Public cible</span>
                            <h3>{{ $item['title'] ?? '' }}</h3>
                            <p>{{ $item['text'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($isPricingEnabled)
        <section id="pricing" class="section section--tight reveal">
            <div class="container">
                <div class="section-head">
                    <div class="section-head__text">
                        <span class="eyebrow">Abonnements</span>
                        <h2 class="section-title">Des formules simples, lisibles et orientées conversion</h2>
                        <p class="section-subtitle">
                            L’offre doit être compréhensible immédiatement, avec un plan mis en avant,
                            des bénéfices concrets et un vrai sentiment de choix guidé.
                        </p>
                    </div>
                </div>

                <div class="pricing-grid">
                    @foreach ($plans as $plan)
                        <article class="pricing-card {{ !empty($plan['highlight']) ? 'pricing-card--highlight' : '' }}">
                            @if (!empty($plan['highlight']))
                                <span class="pricing-badge">Le plus choisi</span>
                            @endif

                            <h3>{{ $plan['title'] ?? '' }}</h3>
                            <div class="plan-price">{{ $plan['price'] ?? '' }}</div>
                            <p>{{ $plan['desc'] ?? '' }}</p>

                            <ul class="feature-list">
                                @foreach (($plan['features'] ?? []) as $feature)
                                    <li>
                                        <span>✔</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="btn btn--primary btn--full">Choisir</a>
                        </article>
                    @endforeach
                </div>

                <div class="pricing-footnote">
                    <span>Activation rapide</span>
                    <span>Choix guidé</span>
                    <span>Essai gratuit possible</span>
                    <span>Support disponible</span>
                </div>
            </div>
        </section>
    @endif

    @if ($isFaqEnabled)
        <section id="mini-faq" class="section reveal">
            <div class="container">
                <div class="faq-grid">
                    <div class="faq-list">
                        <div class="section-head" style="margin-bottom: 6px;">
                            <div class="section-head__text">
                                <span class="eyebrow">Mini FAQ</span>
                                <h2 class="section-title">Réponses rapides aux questions fréquentes</h2>
                                <p class="section-subtitle">
                                    Une FAQ propre renforce la confiance, réduit les hésitations et facilite la conversion.
                                </p>
                            </div>
                        </div>

                        @forelse ($faqItems as $item)
                            <article class="faq-card">
                                <details>
                                    <summary>{{ $item['question'] ?? '' }}</summary>
                                    <p>{{ $item['answer'] ?? '' }}</p>
                                </details>
                            </article>
                        @empty
                            <article class="faq-card">
                                <strong>FAQ bientôt disponible</strong>
                                <p>Ajoutez des questions/réponses depuis l’espace d’administration de la homepage.</p>
                            </article>
                        @endforelse
                    </div>

                    <div class="faq-side">
                        <article class="faq-side__card">
                            <h3>Besoin d’un accompagnement plus direct ?</h3>
                            <p>
                                Si une réponse n’apparaît pas ici, l’équipe peut orienter l’utilisateur vers la bonne classe,
                                la bonne formule ou la meilleure façon de démarrer.
                            </p>
                        </article>

                        <article class="faq-side__card">
                            <h3>Ce que la homepage doit transmettre</h3>
                            <p>
                                Clarté, sérieux, modernité, confiance, utilité immédiate et envie d’entrer dans la plateforme.
                            </p>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($isSupportEnabled)
        <section id="help-support" class="section section--tight reveal">
            <div class="container">
                <article class="support-card">
                    <div class="support-layout">
                        <div class="support-content">
                            <span class="eyebrow">Support / Contact</span>

                            <h2>{{ $support['title'] ?? 'Besoin d’aide pour bien démarrer ?' }}</h2>

                            <p>
                                {{ $support['text'] ?? "Notre équipe vous accompagne pour choisir la meilleure formule, comprendre la plateforme et démarrer sans stress." }}
                            </p>

                            <div class="support-meta">
                                @if (!empty($support['email']))
                                    <span class="support-tag">Email : {{ $support['email'] }}</span>
                                @endif

                                @if (!empty($support['phone']))
                                    <span class="support-tag">Tél : {{ $support['phone'] }}</span>
                                @endif

                                @if (!empty($support['whatsapp']))
                                    <span class="support-tag">WhatsApp : {{ $support['whatsapp'] }}</span>
                                @endif

                                @if (!empty($support['hours']))
                                    <span class="support-tag">Horaires : {{ $support['hours'] }}</span>
                                @endif
                            </div>

                            <div class="support-actions">
                                <a href="{{ $supportContactLink }}" class="btn btn--primary">Contacter l’entreprise</a>
                                <a href="{{ $supportHelpLink }}" class="btn btn--ghost">Aide / support</a>
                                <a href="{{ $supportFaqLink }}" class="btn btn--ghost">FAQ</a>
                                <a href="{{ $supportInfoLink }}" class="btn">Demander des informations</a>
                            </div>
                        </div>

                        <div class="support-panel">
                            <div class="support-panel__box">
                                <strong>Ce que cette zone doit inspirer</strong>

                                <div class="support-panel__list">
                                    <div>
                                        <span>Réponse rapide</span>
                                        <span>Oui</span>
                                    </div>
                                    <div>
                                        <span>Accompagnement</span>
                                        <span>Oui</span>
                                    </div>
                                    <div>
                                        <span>Orientation utilisateur</span>
                                        <span>Oui</span>
                                    </div>
                                    <div>
                                        <span>Confiance finale avant action</span>
                                        <span>Oui</span>
                                    </div>
                                </div>
                            </div>

                            <div class="support-panel__box">
                                <strong>Promesse de la plateforme</strong>
                                <p class="muted">
                                    Donner un accès simple à des contenus utiles, bien présentés et pensés pour la progression réelle.
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12
        });

        document.querySelectorAll('.reveal').forEach(function (node) {
            observer.observe(node);
        });

        document.querySelectorAll('[data-class-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                var filter = button.getAttribute('data-class-filter');

                document.querySelectorAll('[data-class-filter]').forEach(function (btn) {
                    btn.classList.remove('is-active');
                });

                button.classList.add('is-active');

                document.querySelectorAll('[data-class-group]').forEach(function (card) {
                    var group = card.getAttribute('data-class-group');
                    var show = filter === 'all' || group === filter;
                    card.style.display = show ? '' : 'none';
                });
            });
        });
    })();
</script>
@endpush
