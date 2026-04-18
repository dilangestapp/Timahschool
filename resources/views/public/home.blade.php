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
                'message' => 'Les quiz m’aident à travailler plus régulièrement avant les contrôles.',
            ],
            (object) [
                'is_anonymous' => true,
                'author_label' => 'Anonyme',
                'role_tag' => 'Parent',
                'message' => 'Je vois mieux la progression de mon enfant et je peux mieux l’encadrer.',
            ],
            (object) [
                'is_anonymous' => false,
                'author_label' => 'Mme N.',
                'role_tag' => 'Enseignante',
                'message' => 'Les TD et le suivi rendent l’accompagnement plus clair et plus simple.',
            ],
            (object) [
                'is_anonymous' => false,
                'author_label' => 'Coach pédagogique',
                'role_tag' => 'Support',
                'message' => 'La plateforme aide à orienter les élèves vers un vrai rythme de progression.',
            ],
            (object) [
                'is_anonymous' => true,
                'author_label' => 'Anonyme',
                'role_tag' => 'Élève',
                'message' => 'Je retrouve vite mes contenus et je sais quoi faire chaque semaine.',
            ],
            (object) [
                'is_anonymous' => false,
                'author_label' => 'Parent impliqué',
                'role_tag' => 'Parent',
                'message' => 'Le rendu est plus sérieux qu’un simple site de cours posé à la va-vite.',
            ],
        ]);

    $laneOne = $displayMessages->values();
    $laneTwo = $displayMessages->reverse()->values();

    $audiences = collect($homepage['audiences'] ?? $defaults['audiences'] ?? [])->values();

    $hasParentAudience = $audiences->contains(function ($item) {
        $title = strtolower((string) ($item['title'] ?? ''));
        $text = strtolower((string) ($item['text'] ?? ''));

        return str_contains($title, 'parent') || str_contains($text, 'parent');
    });

    if (! $hasParentAudience) {
        $audiences->push([
            'title' => 'Pour les parents',
            'text' => 'Suivre plus facilement les progrès, les besoins d’encadrement et le rythme de travail.',
        ]);
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

    $registerLink = Route::has('register') ? route('register') : '#';

    $supportContactLink = ! empty($support['contact_link']) && $support['contact_link'] !== '#'
        ? $support['contact_link']
        : $registerLink;

    $supportHelpLink = ! empty($support['help_link']) ? $support['help_link'] : '#mini-faq';
    $supportFaqLink = ! empty($support['faq_link']) ? $support['faq_link'] : '#mini-faq';
    $supportInfoLink = ! empty($support['info_link']) && $support['info_link'] !== '#'
        ? $support['info_link']
        : $supportContactLink;

    $activeClassesCount = $classes->count();
    $featuredClassesCount = $featuredClasses->count();
    $generalClassesCount = $classGroups->get('enseignement_general', collect())->count();
    $technicalClassesCount = $classGroups->get('enseignement_technique', collect())->count();
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
        width: 460px;
        height: 460px;
        top: -150px;
        right: -150px;
        background: radial-gradient(circle, rgba(29, 109, 255, 0.18), transparent 70%);
    }

    .home-shell::after {
        width: 360px;
        height: 360px;
        left: -150px;
        top: 620px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.12), transparent 72%);
    }

    .home-shell .container {
        position: relative;
        z-index: 1;
    }

    .home-shell section[id] {
        scroll-margin-top: 112px;
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

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 34px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.76);
        color: var(--primary);
        font-weight: 900;
        font-size: .82rem;
        letter-spacing: -0.01em;
        width: fit-content;
    }

    html[data-theme='dark'] .eyebrow {
        background: rgba(14, 27, 49, 0.92);
    }

    .chip,
    .meta-pill,
    .support-pill,
    .audience-pill {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.72);
        color: var(--text);
        font-size: .82rem;
        font-weight: 800;
    }

    html[data-theme='dark'] .chip,
    html[data-theme='dark'] .meta-pill,
    html[data-theme='dark'] .support-pill,
    html[data-theme='dark'] .audience-pill {
        background: rgba(14, 27, 49, 0.94);
    }

    .home-hero {
        padding: 30px 0 34px;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: 1.06fr .94fr;
        gap: 24px;
        align-items: stretch;
    }

    .hero-main,
    .hero-side {
        position: relative;
        border: 1px solid var(--line);
        border-radius: 34px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .hero-main {
        padding: 42px;
        background:
            radial-gradient(circle at top right, rgba(29, 109, 255, 0.18), transparent 30%),
            radial-gradient(circle at 20% 100%, rgba(56, 189, 248, 0.10), transparent 28%),
            linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.80));
    }

    .hero-main::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(135deg, rgba(255,255,255,.32), transparent 40%),
            linear-gradient(180deg, transparent, rgba(29,109,255,.03));
        pointer-events: none;
    }

    .hero-main > * {
        position: relative;
        z-index: 1;
    }

    .hero-side {
        padding: 24px;
        background:
            radial-gradient(circle at top left, rgba(29, 109, 255, 0.14), transparent 24%),
            linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.82));
    }

    html[data-theme='dark'] .hero-main {
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, .18), transparent 30%),
            radial-gradient(circle at 20% 100%, rgba(56, 189, 248, .10), transparent 28%),
            linear-gradient(180deg, rgba(14,27,49,.99), rgba(16,32,58,.92));
    }

    html[data-theme='dark'] .hero-side {
        background:
            radial-gradient(circle at top left, rgba(110, 161, 255, .14), transparent 24%),
            linear-gradient(180deg, rgba(14,27,49,.98), rgba(16,32,58,.92));
    }

    .hero-kicker-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .hero-proof {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.72);
        color: var(--muted);
        font-size: .82rem;
        font-weight: 800;
    }

    html[data-theme='dark'] .hero-proof {
        background: rgba(14, 27, 49, 0.94);
    }

    .hero-proof b {
        color: var(--text);
    }

    .hero-title {
        margin: 18px 0 14px;
        font-size: clamp(2.45rem, 4vw, 4.45rem);
        line-height: 0.98;
        letter-spacing: -0.06em;
        max-width: 10ch;
    }

    .hero-title .accent {
        display: block;
        color: var(--primary);
    }

    .hero-subtitle {
        margin: 0;
        max-width: 62ch;
        color: var(--muted);
        font-size: 1.06rem;
        line-height: 1.75;
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

    .hero-reassurance span {
        display: inline-flex;
        align-items: center;
        min-height: 36px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.74);
        font-size: .84rem;
        font-weight: 800;
    }

    html[data-theme='dark'] .hero-reassurance span {
        background: rgba(14, 27, 49, 0.94);
    }

    .hero-mini-story {
        margin-top: 20px;
        padding: 16px 18px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background: rgba(29,109,255,.06);
        display: grid;
        gap: 10px;
    }

    .hero-mini-story strong {
        font-size: .98rem;
        letter-spacing: -0.02em;
    }

    .hero-mini-story p {
        margin: 0;
        color: var(--muted);
        font-size: .94rem;
        line-height: 1.7;
    }

    .hero-metrics {
        margin-top: 24px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .hero-metric {
        padding: 18px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.74);
        box-shadow: var(--shadow-xs);
    }

    html[data-theme='dark'] .hero-metric {
        background: rgba(12, 26, 47, 0.94);
    }

    .hero-metric strong {
        display: block;
        font-size: 1.45rem;
        font-weight: 900;
        letter-spacing: -0.04em;
        line-height: 1;
    }

    .hero-metric span {
        display: block;
        margin-top: 7px;
        color: var(--muted);
        font-size: .88rem;
    }

    .hero-strip {
        margin-top: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .dashboard-stack-shell {
        position: relative;
    }

    .dashboard-glow {
        position: absolute;
        inset: -20px -16px auto auto;
        width: 180px;
        height: 180px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(29,109,255,.18), transparent 70%);
        pointer-events: none;
    }

    .dashboard-card {
        position: relative;
        border-radius: 30px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .dashboard-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,.08), transparent 35%);
        pointer-events: none;
    }

    .dashboard-top {
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--line);
        background: rgba(29, 109, 255, 0.05);
    }

    .dashboard-dots {
        display: inline-flex;
        gap: 6px;
    }

    .dashboard-dots span {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: rgba(29, 109, 255, 0.16);
    }

    .dashboard-body {
        padding: 18px;
        display: grid;
        gap: 14px;
    }

    .dashboard-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .summary-box,
    .dashboard-box {
        padding: 14px;
        border-radius: 20px;
        border: 1px solid var(--line);
        background: var(--panel);
        box-shadow: var(--shadow-xs);
    }

    .summary-box small,
    .dashboard-box small {
        color: var(--muted);
        font-weight: 800;
    }

    .summary-box strong {
        display: block;
        margin-top: 6px;
        font-size: 1.2rem;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .dashboard-layout {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 12px;
    }

    .dashboard-stack {
        display: grid;
        gap: 12px;
    }

    .progress-head {
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
        background: linear-gradient(90deg, var(--primary), #79b0ff);
    }

    .mini-list {
        display: grid;
        gap: 10px;
    }

    .mini-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: var(--panel-soft);
        color: var(--muted);
    }

    .mini-row strong {
        color: var(--text);
    }

    .floating-note {
        position: absolute;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 14px;
        border-radius: 16px;
        border: 1px solid var(--line);
        background: rgba(255,255,255,.94);
        box-shadow: var(--shadow);
        font-size: .84rem;
        font-weight: 900;
        backdrop-filter: blur(12px);
    }

    html[data-theme='dark'] .floating-note {
        background: rgba(14, 27, 49, 0.97);
    }

    .floating-note--top {
        top: 18px;
        right: 18px;
    }

    .floating-note--mid {
        left: -10px;
        top: 44%;
    }

    .floating-note--bottom {
        left: 18px;
        bottom: 18px;
    }

    .messages-section {
        padding-top: 18px;
    }

    .message-wrap {
        display: grid;
        gap: 16px;
    }

    .message-track {
        overflow: hidden;
        mask-image: linear-gradient(to right, transparent, #000 6%, #000 94%, transparent);
    }

    .message-lane {
        display: flex;
        gap: 14px;
        width: max-content;
        animation: timahScroll 34s linear infinite;
        will-change: transform;
    }

    .message-track--reverse .message-lane {
        animation-direction: reverse;
        animation-duration: 38s;
    }

    .message-track:hover .message-lane {
        animation-play-state: paused;
    }

    .message-card {
        min-width: 300px;
        max-width: 320px;
        padding: 18px;
        display: grid;
        gap: 14px;
        border-radius: 24px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
    }

    .message-top {
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
        border: 1px solid var(--line);
        background: rgba(29, 109, 255, 0.10);
        color: var(--primary);
        font-weight: 900;
    }

    .message-meta {
        min-width: 0;
        display: grid;
        gap: 2px;
    }

    .message-meta strong {
        font-size: .98rem;
        line-height: 1.2;
    }

    .message-meta span {
        color: var(--muted);
        font-size: .84rem;
        font-weight: 700;
    }

    .message-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.7;
    }

    .trust-grid {
        display: grid;
        grid-template-columns: 1.08fr repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .trust-featured,
    .trust-card {
        border: 1px solid var(--line);
        border-radius: 28px;
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
        font-size: 1.55rem;
        line-height: 1.06;
        letter-spacing: -0.04em;
    }

    .trust-featured p {
        margin: 0;
        color: var(--muted);
    }

    .trust-checks {
        display: grid;
        gap: 10px;
    }

    .trust-check {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 18px;
        border: 1px solid var(--line);
        background: rgba(29,109,255,.06);
    }

    .trust-check strong {
        font-size: 1rem;
    }

    .trust-card {
        padding: 18px;
        display: grid;
        gap: 10px;
        align-content: start;
        min-height: 172px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .trust-card:hover,
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
        font-weight: 900;
        letter-spacing: -0.02em;
    }

    .trust-note {
        color: var(--muted);
        font-size: .92rem;
    }

    .class-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
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
        font-weight: 900;
        cursor: pointer;
        transition: .2s ease;
    }

    .class-tab:hover {
        color: var(--text);
        background: var(--panel-soft);
    }

    .class-tab.is-active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, var(--primary), #4f86ff);
        box-shadow: 0 14px 28px rgba(29,109,255,.20);
    }

    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }

    .class-card {
        padding: 20px;
        display: grid;
        gap: 14px;
        border-radius: 26px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .class-card__top,
    .class-badges,
    .class-actions,
    .class-preview {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .class-system {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0 11px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: rgba(29,109,255,.08);
        color: var(--primary);
        font-size: .76rem;
        font-weight: 900;
    }

    .class-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        border: 1px solid var(--line);
        font-size: .72rem;
        font-weight: 900;
    }

    .class-badge--popular {
        color: #0f766e;
        background: rgba(15,118,110,.08);
    }

    .class-badge--recommended {
        color: #1d4ed8;
        background: rgba(29,78,216,.08);
    }

    .class-badge--guided {
        color: #7c3aed;
        background: rgba(124,58,237,.08);
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

    .class-empty {
        padding: 20px;
        border-radius: 24px;
        border: 1px dashed var(--line);
        background: var(--panel);
        color: var(--muted);
    }

    .dark-showcase {
        padding: 34px;
        border-radius: 34px;
        color: #e9f0ff;
        background:
            radial-gradient(circle at top right, rgba(110, 161, 255, .20), transparent 28%),
            linear-gradient(180deg, #0d1a36, #081224);
        box-shadow: var(--shadow-lg);
    }

    .dark-grid {
        display: grid;
        grid-template-columns: .9fr 1.1fr;
        gap: 22px;
        align-items: start;
    }

    .dark-intro {
        display: grid;
        gap: 16px;
        align-content: start;
    }

    .dark-intro p {
        margin: 0;
        color: rgba(232,239,255,.78);
    }

    .dark-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .dark-chips span {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.06);
        font-size: .84rem;
        font-weight: 800;
    }

    .dark-stats {
        display: grid;
        gap: 12px;
    }

    .dark-stat {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.05);
    }

    .dark-stat strong {
        font-size: 1.02rem;
    }

    .dark-stat span {
        color: rgba(232,239,255,.72);
    }

    .why-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .why-card {
        padding: 18px;
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.05);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .why-card h3 {
        margin: 0 0 10px;
        color: #fff;
        font-size: 1.06rem;
        letter-spacing: -0.02em;
    }

    .why-card p {
        margin: 0;
        color: rgba(232,239,255,.78);
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
        border-radius: 26px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .audience-card h3 {
        margin: 0;
        font-size: 1.08rem;
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
        border-radius: 30px;
        border: 1px solid var(--line);
        background: linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .pricing-card--highlight {
        border: 2px solid var(--primary);
        box-shadow: 0 18px 40px rgba(29,109,255,.20);
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
        font-size: .74rem;
        font-weight: 900;
    }

    .pricing-card h3 {
        margin: 0;
        font-size: 1.18rem;
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
        background: rgba(29,109,255,.06);
        font-size: .84rem;
        font-weight: 800;
    }

    .faq-grid {
        display: grid;
        grid-template-columns: 1.06fr .94fr;
        gap: 18px;
        align-items: start;
    }

    .faq-list {
        display: grid;
        gap: 14px;
    }

    .faq-card {
        padding: 18px 20px;
        border-radius: 24px;
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
        color: var(--primary);
        font-size: 1.2rem;
        line-height: 1;
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

    .faq-side-card {
        padding: 22px;
        border-radius: 26px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(29,109,255,.14), transparent 30%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow);
    }

    .faq-side-card h3,
    .support-card h2 {
        margin: 0 0 10px;
        letter-spacing: -0.03em;
    }

    .faq-side-card p,
    .support-card p {
        margin: 0;
        color: var(--muted);
    }

    .support-card {
        padding: 26px;
        border-radius: 30px;
        border: 1px solid var(--line);
        background:
            radial-gradient(circle at top right, rgba(29,109,255,.12), transparent 26%),
            linear-gradient(180deg, var(--panel), var(--panel-soft));
        box-shadow: var(--shadow-lg);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .support-grid {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 22px;
        align-items: start;
    }

    .support-main,
    .support-side {
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

    .support-side-box {
        padding: 18px;
        border-radius: 22px;
        border: 1px solid var(--line);
        background: rgba(29,109,255,.06);
        display: grid;
        gap: 12px;
    }

    .support-side-box strong {
        letter-spacing: -0.02em;
    }

    .support-list {
        display: grid;
        gap: 10px;
    }

    .support-list div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        color: var(--muted);
    }

    .support-list div span:last-child {
        color: var(--text);
        font-weight: 800;
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
        .hero-grid,
        .trust-grid,
        .dark-grid,
        .faq-grid,
        .support-grid {
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
    }

    @media (max-width: 980px) {
        .floating-note--mid {
            left: 12px;
            top: auto;
            bottom: 76px;
        }
    }

    @media (max-width: 900px) {
        .home-hero {
            padding-top: 22px;
        }

        .hero-main,
        .hero-side,
        .dark-showcase,
        .support-card {
            border-radius: 26px;
        }

        .hero-main,
        .hero-side,
        .dark-showcase,
        .support-card {
            padding-left: 20px;
            padding-right: 20px;
        }

        .hero-metrics,
        .dashboard-summary,
        .dashboard-layout,
        .why-grid {
            grid-template-columns: 1fr;
        }

        .floating-note {
            position: static;
            margin-top: 12px;
        }

        .hero-kicker-row {
            align-items: flex-start;
        }
    }

    @media (max-width: 720px) {
        .hero-main,
        .hero-side,
        .dark-showcase,
        .support-card {
            padding: 20px 18px;
        }

        .hero-title {
            font-size: clamp(2rem, 11vw, 3.15rem);
            max-width: none;
        }

        .message-card {
            min-width: 272px;
            max-width: 272px;
        }

        .classes-grid,
        .audience-grid {
            grid-template-columns: 1fr;
        }

        .hero-actions,
        .class-actions,
        .support-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .hero-actions .btn,
        .class-actions .btn,
        .support-actions .btn {
            width: 100%;
        }

        .hero-proof {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="home-shell">
    <section class="home-hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-main reveal">
                    <div class="hero-kicker-row">
                        <span class="eyebrow">✨ {{ $hero['badge'] ?? 'Essai gratuit 24h' }}</span>
                        <span class="hero-proof"><b>{{ $activeClassesCount > 0 ? $activeClassesCount : '12+' }}</b> classes déjà prêtes à explorer</span>
                    </div>

                    <h1 class="hero-title">
                        {{ $hero['title'] ?? 'Réussissez avec une plateforme claire, moderne et efficace' }}
                        <span class="accent">qui donne vraiment envie d’apprendre.</span>
                    </h1>

                    <p class="hero-subtitle">
                        {{ $hero['subtitle'] ?? 'Cours structurés, quiz interactifs, TD corrigés et suivi de progression pour aider chaque élève à avancer avec confiance.' }}
                    </p>

                    <div class="hero-actions">
                        <a href="{{ $hero['primary_cta_link'] ?? $registerLink }}" class="btn btn--primary">
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
                            <span>✔ {{ $pill }}</span>
                        @endforeach
                    </div>

                    <div class="hero-mini-story">
                        <strong>Une entrée plus claire dans la plateforme</strong>
                        <p>
                            Dès l’arrivée sur la page, l’élève, le parent ou l’enseignant doit comprendre :
                            où cliquer, quoi attendre, et pourquoi TIMAH ACADEMY peut réellement aider à mieux progresser.
                        </p>
                    </div>

                    <div class="hero-strip">
                        <span class="chip">Secondaire général</span>
                        <span class="chip">Enseignement technique</span>
                        <span class="chip">Suivi de progression</span>
                        <span class="chip">Support réactif</span>
                    </div>

                    <div class="hero-metrics">
                        <article class="hero-metric">
                            <strong>{{ $activeClassesCount > 0 ? $activeClassesCount : '12+' }}</strong>
                            <span>classes actives disponibles</span>
                        </article>

                        <article class="hero-metric">
                            <strong>{{ $featuredClassesCount > 0 ? $featuredClassesCount : '9+' }}</strong>
                            <span>classes mises en avant</span>
                        </article>

                        <article class="hero-metric">
                            <strong>{{ $displayMessages->count() > 0 ? $displayMessages->count() : '6+' }}</strong>
                            <span>retours visibles sur la plateforme</span>
                        </article>
                    </div>
                </div>

                <div class="hero-side reveal">
                    <div class="dashboard-stack-shell">
                        <div class="dashboard-glow"></div>

                        <div class="dashboard-card">
                            <div class="dashboard-top">
                                <div class="dashboard-dots">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>

                                <strong>Dashboard élève</strong>
                                <small class="muted">Activité récente</small>
                            </div>

                            <div class="dashboard-body">
                                <div class="dashboard-summary">
                                    <article class="summary-box">
                                        <small>Progression</small>
                                        <strong>78%</strong>
                                    </article>

                                    <article class="summary-box">
                                        <small>Quiz validés</small>
                                        <strong>17/20</strong>
                                    </article>

                                    <article class="summary-box">
                                        <small>TD terminés</small>
                                        <strong>11</strong>
                                    </article>
                                </div>

                                <div class="dashboard-layout">
                                    <div class="dashboard-stack">
                                        <article class="dashboard-box">
                                            <div class="progress-head">
                                                <strong>Objectif de la semaine</strong>
                                                <span class="muted">82%</span>
                                            </div>
                                            <div class="progress-track">
                                                <span style="width: 82%;"></span>
                                            </div>
                                        </article>

                                        <article class="dashboard-box">
                                            <small>Révisions par matière</small>

                                            <div class="mini-list" style="margin-top: 10px;">
                                                <div class="mini-row">
                                                    <span>Mathématiques</span>
                                                    <strong>Très bon rythme</strong>
                                                </div>
                                                <div class="mini-row">
                                                    <span>Physique</span>
                                                    <strong>À renforcer</strong>
                                                </div>
                                                <div class="mini-row">
                                                    <span>Français</span>
                                                    <strong>Bonne stabilité</strong>
                                                </div>
                                            </div>
                                        </article>
                                    </div>

                                    <article class="dashboard-box">
                                        <small>À faire aujourd’hui</small>

                                        <div class="mini-list" style="margin-top: 10px;">
                                            <div class="mini-row">
                                                <span>Quiz de révision</span>
                                                <strong>Commencer</strong>
                                            </div>
                                            <div class="mini-row">
                                                <span>TD corrigé</span>
                                                <strong>Disponible</strong>
                                            </div>
                                            <div class="mini-row">
                                                <span>Suivi des scores</span>
                                                <strong>En direct</strong>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            </div>
                        </div>

                        <div class="floating-note floating-note--top">⚡ +3 quiz cette semaine</div>
                        <div class="floating-note floating-note--mid">📚 cours + quiz + TD</div>
                        <div class="floating-note floating-note--bottom">🎯 Objectif du mois : 82%</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($isMessagesEnabled)
        <section class="section section--tight messages-section reveal">
            <div class="container">
                <div class="section-head">
                    <div class="section-head__text">
                        <span class="eyebrow">Témoignages visibles</span>
                        <h2 class="section-title">Une homepage plus vivante et plus crédible</h2>
                        <p class="section-subtitle">
                            Cette section doit rassurer, humaniser la plateforme et montrer qu’elle sert
                            réellement aux élèves, aux parents et aux enseignants.
                        </p>
                    </div>

                    <div class="section-head__chips">
                        <span class="chip">Élèves</span>
                        <span class="chip">Parents</span>
                        <span class="chip">Enseignants</span>
                        <span class="chip">Support</span>
                    </div>
                </div>

                <div class="message-wrap">
                    <div class="message-track">
                        <div class="message-lane">
                            @foreach ($laneOne->concat($laneOne) as $message)
                                @php
                                    $displayName = $message->is_anonymous ? 'Anonyme' : ($message->author_label ?: 'Utilisateur');
                                    $avatar = strtoupper(substr($displayName, 0, 1));
                                @endphp

                                <article class="message-card">
                                    <div class="message-top">
                                        <span class="message-avatar">{{ $avatar }}</span>

                                        <div class="message-meta">
                                            <strong>{{ $displayName }}</strong>
                                            <span>{{ $message->role_tag ?: 'Utilisateur' }}</span>
                                        </div>
                                    </div>

                                    <p>“{{ $message->message }}”</p>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="message-track message-track--reverse">
                        <div class="message-lane">
                            @foreach ($laneTwo->concat($laneTwo) as $message)
                                @php
                                    $displayName = $message->is_anonymous ? 'Anonyme' : ($message->author_label ?: 'Utilisateur');
                                    $avatar = strtoupper(substr($displayName, 0, 1));
                                @endphp

                                <article class="message-card">
                                    <div class="message-top">
                                        <span class="message-avatar">{{ $avatar }}</span>

                                        <div class="message-meta">
                                            <strong>{{ $displayName }}</strong>
                                            <span>{{ $message->role_tag ?: 'Utilisateur' }}</span>
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
        <section class="section section--tight reveal">
            <div class="container">
                <div class="trust-grid">
                    <article class="trust-featured">
                        <span class="eyebrow">Confiance et repères</span>

                        <h3>Un bloc de preuves sociales qui rassure vraiment</h3>

                        <p>
                            Les chiffres doivent donner du poids à la plateforme, pas seulement remplir de petites cartes.
                            Ici, ils participent au discours commercial et à la crédibilité générale.
                        </p>

                        <div class="trust-checks">
                            <div class="trust-check">
                                <span>Enseignement général</span>
                                <strong>{{ $generalClassesCount > 0 ? $generalClassesCount : '—' }}</strong>
                            </div>
                            <div class="trust-check">
                                <span>Enseignement technique</span>
                                <strong>{{ $technicalClassesCount > 0 ? $technicalClassesCount : '—' }}</strong>
                            </div>
                            <div class="trust-check">
                                <span>Classes mises en avant</span>
                                <strong>{{ $featuredClassesCount > 0 ? $featuredClassesCount : '—' }}</strong>
                            </div>
                        </div>
                    </article>

                    @foreach ($trustItems as $item)
                        <article class="trust-card">
                            <span class="trust-value">{{ $item['value'] ?? '' }}</span>
                            <span class="trust-title">{{ $item['title'] ?? '' }}</span>
                            <span class="trust-note">Un indicateur visible pour renforcer le sérieux et la confiance.</span>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($isClassesEnabled)
        <section id="classes" class="section reveal">
            <div class="container">
                <div class="section-head">
                    <div class="section-head__text">
                        <span class="eyebrow">Classes disponibles</span>
                        <h2 class="section-title">Explorer rapidement la bonne classe et le bon parcours</h2>
                        <p class="section-subtitle">
                            L’utilisateur doit sentir immédiatement que l’entrée dans la plateforme est simple,
                            structurée et adaptée à son niveau ou à sa filière.
                        </p>
                    </div>

                    <div class="section-head__chips">
                        <span class="chip">{{ $generalClassesCount }} général</span>
                        <span class="chip">{{ $technicalClassesCount }} technique</span>
                        <span class="chip">{{ $activeClassesCount }} classes actives</span>
                    </div>
                </div>

                <div class="class-tabs">
                    <button type="button" class="class-tab is-active" data-class-filter="all">Toutes</button>

                    @foreach ($classTabs as $key => $label)
                        <button type="button" class="class-tab" data-class-filter="{{ $key }}">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="classes-grid">
                    @forelse ($featuredClasses as $class)
                        @php
                            $classGroup = ($class->system ?? null) === 'enseignement_technique'
                                ? 'enseignement_technique'
                                : 'enseignement_general';

                            $systemLabel = $classGroupLabels[$classGroup] ?? 'Enseignement général';
                            $classDescription = $class->description ?: 'Cours, quiz, TD et progression structurée pour aider l’élève à travailler avec méthode.';
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

                            <div class="class-preview">
                                <span class="meta-pill">Cours</span>
                                <span class="meta-pill">Quiz</span>
                                <span class="meta-pill">TD</span>
                                <span class="meta-pill">Progression</span>
                            </div>

                            <div class="class-actions">
                                <a href="{{ $registerLink }}" class="btn btn--primary">Commencer</a>
                                <a href="{{ $registerLink }}" class="btn btn--ghost">Voir détails</a>
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
                    <div class="dark-grid">
                        <div class="dark-intro">
                            <span class="eyebrow">Pourquoi choisir TIMAH ACADEMY</span>

                            <h2 class="section-title" style="color:#fff; margin-bottom:0;">
                                Une plateforme pensée pour convaincre et pour faire progresser.
                            </h2>

                            <p>
                                Le bon rendu ne suffit pas. Il faut aussi une vraie logique pédagogique,
                                une lisibilité forte et une structure assez crédible pour donner envie d’entrer dans l’outil.
                            </p>

                            <div class="dark-chips">
                                <span>Cours structurés</span>
                                <span>Quiz interactifs</span>
                                <span>TD corrigés</span>
                                <span>Suivi continu</span>
                            </div>

                            <div class="dark-stats">
                                <div class="dark-stat">
                                    <strong>Expérience claire</strong>
                                    <span>Navigation simple et directe</span>
                                </div>
                                <div class="dark-stat">
                                    <strong>Progression visible</strong>
                                    <span>Repères utiles pour l’élève</span>
                                </div>
                                <div class="dark-stat">
                                    <strong>Utilité immédiate</strong>
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
                            Il faut parler clairement aux élèves, aux parents, aux enseignants
                            et garder la porte ouverte aux établissements pour la suite.
                        </p>
                    </div>
                </div>

                <div class="audience-grid">
                    @foreach ($audiences as $item)
                        <article class="audience-card">
                            <span class="audience-pill">Public cible</span>
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
                        <h2 class="section-title">Des offres plus lisibles et plus vendeuses</h2>
                        <p class="section-subtitle">
                            L’utilisateur doit comprendre vite quelle formule choisir,
                            pourquoi elle est utile et où se trouve l’offre la plus attractive.
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

                            <a href="{{ $registerLink }}" class="btn btn--primary btn--full">Choisir</a>
                        </article>
                    @endforeach
                </div>

                <div class="pricing-footnote">
                    <span>Activation rapide</span>
                    <span>Essai gratuit possible</span>
                    <span>Accompagnement disponible</span>
                    <span>Choix guidé</span>
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
                                <h2 class="section-title">Réponses rapides aux questions importantes</h2>
                                <p class="section-subtitle">
                                    Une FAQ bien traitée réduit les hésitations et complète le travail commercial de la homepage.
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
                        <article class="faq-side-card">
                            <h3>Ce que la homepage doit transmettre</h3>
                            <p>
                                Clarté, sérieux, modernité, confiance, utilité réelle et envie d’entrer dans la plateforme.
                            </p>
                        </article>

                        <article class="faq-side-card">
                            <h3>Besoin d’un accompagnement direct ?</h3>
                            <p>
                                Si une réponse ne suffit pas, l’équipe peut orienter l’utilisateur vers la bonne classe,
                                la bonne formule ou le bon point d’entrée.
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
                    <div class="support-grid">
                        <div class="support-main">
                            <span class="eyebrow">Support / Contact</span>

                            <h2>{{ $support['title'] ?? 'Besoin d’aide pour bien démarrer ?' }}</h2>

                            <p>
                                {{ $support['text'] ?? "Notre équipe vous accompagne pour choisir la meilleure formule, comprendre la plateforme et démarrer sans stress." }}
                            </p>

                            <div class="support-meta">
                                @if (!empty($support['email']))
                                    <span class="support-pill">Email : {{ $support['email'] }}</span>
                                @endif

                                @if (!empty($support['phone']))
                                    <span class="support-pill">Tél : {{ $support['phone'] }}</span>
                                @endif

                                @if (!empty($support['whatsapp']))
                                    <span class="support-pill">WhatsApp : {{ $support['whatsapp'] }}</span>
                                @endif

                                @if (!empty($support['hours']))
                                    <span class="support-pill">Horaires : {{ $support['hours'] }}</span>
                                @endif
                            </div>

                            <div class="support-actions">
                                <a href="{{ $supportContactLink }}" class="btn btn--primary">Contacter l’entreprise</a>
                                <a href="{{ $supportHelpLink }}" class="btn btn--ghost">Aide / support</a>
                                <a href="{{ $supportFaqLink }}" class="btn btn--ghost">FAQ</a>
                                <a href="{{ $supportInfoLink }}" class="btn">Demander des informations</a>
                            </div>
                        </div>

                        <div class="support-side">
                            <div class="support-side-box">
                                <strong>Ce que cette zone doit inspirer</strong>

                                <div class="support-list">
                                    <div>
                                        <span>Réponse rapide</span>
                                        <span>Oui</span>
                                    </div>
                                    <div>
                                        <span>Orientation claire</span>
                                        <span>Oui</span>
                                    </div>
                                    <div>
                                        <span>Accompagnement réel</span>
                                        <span>Oui</span>
                                    </div>
                                    <div>
                                        <span>Confiance avant action</span>
                                        <span>Oui</span>
                                    </div>
                                </div>
                            </div>

                            <div class="support-side-box">
                                <strong>Promesse de TIMAH ACADEMY</strong>
                                <p class="muted">
                                    Donner accès à des contenus utiles, mieux présentés et pensés pour une progression réelle.
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
