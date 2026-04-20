@extends('layouts.admin')

@section('title', 'Paramètres plateforme')
@section('page_title', 'Paramètres plateforme')
@section('page_subtitle', 'Gérez les textes globaux, les dashboards et maintenant le logo principal de la plateforme.')

@php
    $general = $general ?? [];
    $adminDashboard = $adminDashboard ?? [];
    $teacherDashboard = $teacherDashboard ?? [];
    $studentDashboard = $studentDashboard ?? [];
    $homepage = $homepage ?? [];
    $currentLogoUrl = \App\Models\PlatformSetting::logoUrl($general['logo_path'] ?? null);
@endphp

@push('styles')
<style>
    .settings-grid {
        display: grid;
        gap: 18px;
    }

    .settings-hero {
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 18px;
    }

    .settings-hero__card,
    .settings-card,
    .settings-note,
    .settings-homepage {
        background: var(--admin-panel, rgba(255,255,255,.88));
        border: 1px solid var(--admin-border, #dbe3f0);
        border-radius: 24px;
        padding: 22px;
        box-shadow: 0 16px 34px rgba(15,23,42,.06);
    }

    .settings-hero__card h2,
    .settings-card h3,
    .settings-homepage h3 {
        margin: 0 0 8px;
        font-size: 1.1rem;
        letter-spacing: -0.02em;
    }

    .settings-hero__card p,
    .settings-card p,
    .settings-homepage p,
    .settings-note p {
        margin: 0;
        color: var(--admin-muted, #64748b);
        line-height: 1.6;
    }

    .settings-hero__stats {
        display: grid;
        gap: 12px;
    }

    .settings-stat {
        min-height: 92px;
        border-radius: 20px;
        padding: 16px 18px;
        border: 1px solid var(--admin-border, #dbe3f0);
        background: linear-gradient(180deg, rgba(255,255,255,.76), rgba(255,255,255,.54));
        display: grid;
        gap: 6px;
        align-content: center;
    }

    .settings-stat strong {
        font-size: 1.6rem;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .settings-stat span {
        color: var(--admin-muted, #64748b);
        font-size: .92rem;
        font-weight: 700;
    }

    .settings-form {
        display: grid;
        gap: 18px;
    }

    .settings-section {
        display: grid;
        gap: 16px;
    }

    .settings-section__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .settings-section__head h3 {
        margin: 0;
        font-size: 1.05rem;
        letter-spacing: -0.02em;
    }

    .settings-section__head p {
        margin: 6px 0 0;
        color: var(--admin-muted, #64748b);
        max-width: 760px;
        line-height: 1.55;
    }

    .settings-pill {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(79,70,229,.10);
        color: var(--admin-primary, #4f46e5);
        border: 1px solid rgba(79,70,229,.16);
        font-size: .78rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .settings-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .settings-field {
        display: grid;
        gap: 8px;
    }

    .settings-field--full {
        grid-column: 1 / -1;
    }

    .settings-field label {
        font-size: .88rem;
        font-weight: 800;
        color: var(--admin-ink, #0f172a);
    }

    .settings-field input,
    .settings-field textarea,
    .settings-field select {
        width: 100%;
        border-radius: 16px;
        border: 1px solid var(--admin-border, #dbe3f0);
        background: rgba(255,255,255,.84);
        color: var(--admin-ink, #0f172a);
        padding: 14px 15px;
        outline: none;
        transition: .2s ease;
        line-height: 1.5;
    }

    .settings-field input[type="file"] {
        padding: 12px 14px;
        background: rgba(79,70,229,.04);
    }

    .settings-field textarea {
        min-height: 104px;
        resize: vertical;
    }

    .settings-field input:focus,
    .settings-field textarea:focus,
    .settings-field select:focus {
        border-color: var(--admin-primary, #4f46e5);
        box-shadow: 0 0 0 4px rgba(79,70,229,.10);
    }

    .settings-field small {
        color: var(--admin-muted, #64748b);
        line-height: 1.45;
    }

    .settings-logo-box {
        display: grid;
        gap: 14px;
        padding: 16px;
        border: 1px dashed var(--admin-border, #dbe3f0);
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,.48));
    }

    .settings-logo-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 160px;
        border-radius: 20px;
        border: 1px solid var(--admin-border, #dbe3f0);
        background:
            radial-gradient(circle at top right, rgba(79,70,229,.08), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,.92), rgba(246,248,252,.78));
        overflow: hidden;
        padding: 18px;
    }

    .settings-logo-preview img {
        max-width: 100%;
        max-height: 120px;
        object-fit: contain;
        display: block;
    }

    .settings-logo-empty {
        text-align: center;
        color: var(--admin-muted, #64748b);
        font-size: .9rem;
        line-height: 1.5;
    }

    .settings-checkbox {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .9rem;
        font-weight: 700;
        color: var(--admin-ink, #0f172a);
    }

    .settings-checkbox input {
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: var(--admin-primary, #4f46e5);
    }

    .settings-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding-top: 8px;
    }

    .settings-actions__left {
        color: var(--admin-muted, #64748b);
        font-size: .92rem;
    }

    .settings-actions__right {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .settings-homepage__list {
        display: grid;
        gap: 10px;
        margin-top: 16px;
    }

    .settings-homepage__item {
        border: 1px solid var(--admin-border, #dbe3f0);
        background: linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,.54));
        border-radius: 18px;
        padding: 14px 16px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .settings-homepage__item strong {
        display: block;
        margin-bottom: 4px;
        font-size: .95rem;
    }

    .settings-homepage__item span {
        color: var(--admin-muted, #64748b);
        font-size: .88rem;
        line-height: 1.5;
    }

    @media (max-width: 980px) {
        .settings-hero,
        .settings-fields {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="settings-grid">
    <section class="settings-hero">
        <div class="settings-hero__card">
            <h2>Centre de configuration</h2>
            <p>
                Ici, l’administrateur peut modifier les textes globaux de la plateforme,
                les titres visibles sur les dashboards et maintenant téléverser directement
                le logo principal sans toucher au code.
            </p>
        </div>

        <div class="settings-hero__stats">
            <div class="settings-stat">
                <strong>Logo</strong>
                <span>upload direct depuis cette page</span>
            </div>
            <div class="settings-stat">
                <strong>3</strong>
                <span>dashboards déjà pilotables</span>
            </div>
            <div class="settings-stat">
                <strong>1</strong>
                <span>centre unique de paramètres</span>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="settings-form" enctype="multipart/form-data">
        @csrf

        <section class="settings-card settings-section">
            <div class="settings-section__head">
                <div>
                    <h3>Paramètres généraux</h3>
                    <p>Informations globales visibles sur la plateforme, le support, les libellés partagés et le logo officiel.</p>
                </div>
                <span class="settings-pill">Général</span>
            </div>

            <div class="settings-fields">
                <div class="settings-field">
                    <label for="general_platform_name">Nom de la plateforme</label>
                    <input type="text" id="general_platform_name" name="general_platform_name" value="{{ old('general_platform_name', $general['platform_name'] ?? 'TIMAH ACADEMY') }}" required>
                </div>

                <div class="settings-field">
                    <label for="general_platform_slogan">Slogan</label>
                    <input type="text" id="general_platform_slogan" name="general_platform_slogan" value="{{ old('general_platform_slogan', $general['platform_slogan'] ?? 'Plateforme éducative moderne et premium') }}">
                </div>

                <div class="settings-field settings-field--full">
                    <label>Logo de la plateforme</label>

                    <div class="settings-logo-box">
                        <div class="settings-logo-preview">
                            @if($currentLogoUrl)
                                <img src="{{ $currentLogoUrl }}" alt="Logo actuel">
                            @else
                                <div class="settings-logo-empty">
                                    Aucun logo téléversé pour le moment.<br>
                                    Le système utilise encore le logo par défaut.
                                </div>
                            @endif
                        </div>

                        <div class="settings-field">
                            <input type="file" name="general_logo" accept=".png,.jpg,.jpeg,.webp,.svg">
                            <small>Formats acceptés : PNG, JPG, JPEG, WEBP, SVG. Taille max : 4 Mo.</small>
                        </div>

                        <label class="settings-checkbox">
                            <input type="checkbox" name="general_remove_logo" value="1">
                            Supprimer le logo actuel et revenir au logo par défaut
                        </label>
                    </div>
                </div>

                <div class="settings-field">
                    <label for="general_support_email">Email support</label>
                    <input type="text" id="general_support_email" name="general_support_email" value="{{ old('general_support_email', $general['support_email'] ?? '') }}">
                </div>

                <div class="settings-field">
                    <label for="general_support_phone">Téléphone support</label>
                    <input type="text" id="general_support_phone" name="general_support_phone" value="{{ old('general_support_phone', $general['support_phone'] ?? '') }}">
                </div>

                <div class="settings-field">
                    <label for="general_support_whatsapp">WhatsApp support</label>
                    <input type="text" id="general_support_whatsapp" name="general_support_whatsapp" value="{{ old('general_support_whatsapp', $general['support_whatsapp'] ?? '') }}">
                </div>

                <div class="settings-field">
                    <label for="general_primary_color">Couleur principale</label>
                    <input type="text" id="general_primary_color" name="general_primary_color" value="{{ old('general_primary_color', $general['primary_color'] ?? '#315efb') }}">
                </div>

                <div class="settings-field">
                    <label for="general_secondary_color">Couleur secondaire</label>
                    <input type="text" id="general_secondary_color" name="general_secondary_color" value="{{ old('general_secondary_color', $general['secondary_color'] ?? '#7c3aed') }}">
                </div>

                <div class="settings-field settings-field--full">
                    <label for="general_footer_text">Texte de pied de page</label>
                    <textarea id="general_footer_text" name="general_footer_text">{{ old('general_footer_text', $general['footer_text'] ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="settings-card settings-section">
            <div class="settings-section__head">
                <div>
                    <h3>Dashboard administrateur</h3>
                    <p>Titres et textes visibles sur la page d’accueil du compte admin.</p>
                </div>
                <span class="settings-pill">Admin</span>
            </div>

            <div class="settings-fields">
                <div class="settings-field">
                    <label for="admin_page_title">Titre principal</label>
                    <input type="text" id="admin_page_title" name="admin_page_title" value="{{ old('admin_page_title', $adminDashboard['page_title'] ?? 'Centre de pilotage') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_page_subtitle">Sous-titre principal</label>
                    <input type="text" id="admin_page_subtitle" name="admin_page_subtitle" value="{{ old('admin_page_subtitle', $adminDashboard['page_subtitle'] ?? 'Vue d’ensemble de la plateforme, des contenus et de l’activité.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_modules_title">Titre bloc modules</label>
                    <input type="text" id="admin_modules_title" name="admin_modules_title" value="{{ old('admin_modules_title', $adminDashboard['modules_title'] ?? 'Modules à piloter') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_modules_text">Texte bloc modules</label>
                    <input type="text" id="admin_modules_text" name="admin_modules_text" value="{{ old('admin_modules_text', $adminDashboard['modules_text'] ?? 'Accédez rapidement aux espaces clés de gestion.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_decision_title">Titre bloc décisions</label>
                    <input type="text" id="admin_decision_title" name="admin_decision_title" value="{{ old('admin_decision_title', $adminDashboard['decision_title'] ?? 'Décisions rapides') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_decision_text">Texte bloc décisions</label>
                    <input type="text" id="admin_decision_text" name="admin_decision_text" value="{{ old('admin_decision_text', $adminDashboard['decision_text'] ?? 'Gardez la main sur les contenus, les enseignants et les abonnements.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_indicators_title">Titre bloc indicateurs</label>
                    <input type="text" id="admin_indicators_title" name="admin_indicators_title" value="{{ old('admin_indicators_title', $adminDashboard['indicators_title'] ?? 'Indicateurs') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_indicators_text">Texte bloc indicateurs</label>
                    <input type="text" id="admin_indicators_text" name="admin_indicators_text" value="{{ old('admin_indicators_text', $adminDashboard['indicators_text'] ?? 'Suivez les chiffres utiles pour piloter la plateforme.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_recent_td_title">Titre bloc TD récents</label>
                    <input type="text" id="admin_recent_td_title" name="admin_recent_td_title" value="{{ old('admin_recent_td_title', $adminDashboard['recent_td_title'] ?? 'Derniers TD') }}" required>
                </div>

                <div class="settings-field">
                    <label for="admin_recent_messages_title">Titre bloc messages récents</label>
                    <input type="text" id="admin_recent_messages_title" name="admin_recent_messages_title" value="{{ old('admin_recent_messages_title', $adminDashboard['recent_messages_title'] ?? 'Derniers messages') }}" required>
                </div>
            </div>
        </section>

        <section class="settings-card settings-section">
            <div class="settings-section__head">
                <div>
                    <h3>Dashboard enseignant</h3>
                    <p>Messages d’accueil et libellés principaux de l’espace enseignant.</p>
                </div>
                <span class="settings-pill">Enseignant</span>
            </div>

            <div class="settings-fields">
                <div class="settings-field">
                    <label for="teacher_page_title">Titre principal</label>
                    <input type="text" id="teacher_page_title" name="teacher_page_title" value="{{ old('teacher_page_title', $teacherDashboard['page_title'] ?? 'Bonjour et bon travail') }}" required>
                </div>

                <div class="settings-field">
                    <label for="teacher_page_subtitle">Sous-titre principal</label>
                    <input type="text" id="teacher_page_subtitle" name="teacher_page_subtitle" value="{{ old('teacher_page_subtitle', $teacherDashboard['page_subtitle'] ?? 'Retrouvez vos classes, vos TD et les questions à traiter.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="teacher_assignments_title">Titre bloc affectations</label>
                    <input type="text" id="teacher_assignments_title" name="teacher_assignments_title" value="{{ old('teacher_assignments_title', $teacherDashboard['assignments_title'] ?? 'Mes affectations') }}" required>
                </div>

                <div class="settings-field">
                    <label for="teacher_assignments_button">Libellé bouton affectations</label>
                    <input type="text" id="teacher_assignments_button" name="teacher_assignments_button" value="{{ old('teacher_assignments_button', $teacherDashboard['assignments_button'] ?? 'Voir mes classes') }}" required>
                </div>

                <div class="settings-field settings-field--full">
                    <label for="teacher_assignments_empty">Texte si aucune affectation</label>
                    <input type="text" id="teacher_assignments_empty" name="teacher_assignments_empty" value="{{ old('teacher_assignments_empty', $teacherDashboard['assignments_empty'] ?? 'Aucune affectation disponible pour le moment.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="teacher_latest_td_title">Titre bloc TD récents</label>
                    <input type="text" id="teacher_latest_td_title" name="teacher_latest_td_title" value="{{ old('teacher_latest_td_title', $teacherDashboard['latest_td_title'] ?? 'Derniers TD publiés') }}" required>
                </div>

                <div class="settings-field">
                    <label for="teacher_latest_td_empty">Texte si aucun TD récent</label>
                    <input type="text" id="teacher_latest_td_empty" name="teacher_latest_td_empty" value="{{ old('teacher_latest_td_empty', $teacherDashboard['latest_td_empty'] ?? 'Aucun TD publié récemment.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="teacher_latest_questions_title">Titre bloc questions récentes</label>
                    <input type="text" id="teacher_latest_questions_title" name="teacher_latest_questions_title" value="{{ old('teacher_latest_questions_title', $teacherDashboard['latest_questions_title'] ?? 'Questions récentes') }}" required>
                </div>

                <div class="settings-field">
                    <label for="teacher_latest_questions_empty">Texte si aucune question</label>
                    <input type="text" id="teacher_latest_questions_empty" name="teacher_latest_questions_empty" value="{{ old('teacher_latest_questions_empty', $teacherDashboard['latest_questions_empty'] ?? 'Aucune question récente pour le moment.') }}" required>
                </div>
            </div>
        </section>

        <section class="settings-card settings-section">
            <div class="settings-section__head">
                <div>
                    <h3>Dashboard élève</h3>
                    <p>Textes visibles sur la page d’accueil élève, les blocs d’accompagnement et les raccourcis.</p>
                </div>
                <span class="settings-pill">Élève</span>
            </div>

            <div class="settings-fields">
                <div class="settings-field">
                    <label for="student_hero_badge">Badge hero</label>
                    <input type="text" id="student_hero_badge" name="student_hero_badge" value="{{ old('student_hero_badge', $studentDashboard['hero_badge'] ?? '✨ Tableau de bord élève') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_hero_title">Titre hero</label>
                    <input type="text" id="student_hero_title" name="student_hero_title" value="{{ old('student_hero_title', $studentDashboard['hero_title'] ?? 'Bonjour, prêt à continuer') }}" required>
                </div>

                <div class="settings-field settings-field--full">
                    <label for="student_hero_highlight">Mot mis en avant</label>
                    <input type="text" id="student_hero_highlight" name="student_hero_highlight" value="{{ old('student_hero_highlight', $studentDashboard['hero_highlight'] ?? 'avec confiance ?') }}" required>
                </div>

                <div class="settings-field settings-field--full">
                    <label for="student_hero_text">Texte hero</label>
                    <textarea id="student_hero_text" name="student_hero_text" required>{{ old('student_hero_text', $studentDashboard['hero_text'] ?? 'Retrouvez votre classe, vos TD, vos cours et les repères essentiels dans un espace plus clair, plus attractif et mieux organisé pour votre progression.') }}</textarea>
                </div>

                <div class="settings-field">
                    <label for="student_workspace_title">Titre bloc espace de travail</label>
                    <input type="text" id="student_workspace_title" name="student_workspace_title" value="{{ old('student_workspace_title', $studentDashboard['workspace_title'] ?? 'Votre espace de travail') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_workspace_text">Texte bloc espace de travail</label>
                    <input type="text" id="student_workspace_text" name="student_workspace_text" value="{{ old('student_workspace_text', $studentDashboard['workspace_text'] ?? 'Utilisez ce tableau de bord pour accéder rapidement à vos TD, consulter vos cours, poser une question et suivre votre rythme de travail.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_goal_title">Titre objectif</label>
                    <input type="text" id="student_goal_title" name="student_goal_title" value="{{ old('student_goal_title', $studentDashboard['goal_title'] ?? 'Objectif du moment') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_goal_text">Texte objectif</label>
                    <input type="text" id="student_goal_text" name="student_goal_text" value="{{ old('student_goal_text', $studentDashboard['goal_text'] ?? 'Gardez un bon rythme sur vos TD récents et revenez régulièrement sur les matières où vous devez encore progresser.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_progress_title">Titre progression</label>
                    <input type="text" id="student_progress_title" name="student_progress_title" value="{{ old('student_progress_title', $studentDashboard['progress_title'] ?? 'Progression') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_progress_text">Texte progression</label>
                    <input type="text" id="student_progress_text" name="student_progress_text" value="{{ old('student_progress_text', $studentDashboard['progress_text'] ?? 'Indication visuelle simple de votre activité récente.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_activity_title">Titre dernières activités</label>
                    <input type="text" id="student_activity_title" name="student_activity_title" value="{{ old('student_activity_title', $studentDashboard['activity_title'] ?? 'Dernières activités') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_activity_text">Texte dernières activités</label>
                    <input type="text" id="student_activity_text" name="student_activity_text" value="{{ old('student_activity_text', $studentDashboard['activity_text'] ?? 'Les dernières publications de votre classe, prêtes à être consultées.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_td_title">Titre bloc TD</label>
                    <input type="text" id="student_td_title" name="student_td_title" value="{{ old('student_td_title', $studentDashboard['td_title'] ?? 'Mes TD') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_td_text">Texte bloc TD</label>
                    <input type="text" id="student_td_text" name="student_td_text" value="{{ old('student_td_text', $studentDashboard['td_text'] ?? 'Accédez aux TD disponibles, aux corrigés et aux publications récentes.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_refs_title">Titre repères rapides</label>
                    <input type="text" id="student_refs_title" name="student_refs_title" value="{{ old('student_refs_title', $studentDashboard['refs_title'] ?? 'Repères rapides') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_refs_text">Texte repères rapides</label>
                    <input type="text" id="student_refs_text" name="student_refs_text" value="{{ old('student_refs_text', $studentDashboard['refs_text'] ?? 'Gardez sous les yeux les informations essentielles liées à votre espace.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_advice_title">Titre conseil</label>
                    <input type="text" id="student_advice_title" name="student_advice_title" value="{{ old('student_advice_title', $studentDashboard['advice_title'] ?? 'Conseil du moment') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_advice_text">Texte conseil</label>
                    <input type="text" id="student_advice_text" name="student_advice_text" value="{{ old('student_advice_text', $studentDashboard['advice_text'] ?? 'Avancez régulièrement, posez vos questions et gardez un rythme simple mais constant.') }}" required>
                </div>

                <div class="settings-field settings-field--full">
                    <label for="student_advice_note">Note conseil</label>
                    <input type="text" id="student_advice_note" name="student_advice_note" value="{{ old('student_advice_note', $studentDashboard['advice_note'] ?? 'Une petite progression régulière vaut mieux qu’une grande pause.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_shortcut_td_title">Titre raccourci TD</label>
                    <input type="text" id="student_shortcut_td_title" name="student_shortcut_td_title" value="{{ old('student_shortcut_td_title', $studentDashboard['shortcut_td_title'] ?? 'Accéder à mes TD') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_shortcut_td_text">Texte raccourci TD</label>
                    <input type="text" id="student_shortcut_td_text" name="student_shortcut_td_text" value="{{ old('student_shortcut_td_text', $studentDashboard['shortcut_td_text'] ?? 'Continuez votre travail sans perdre le fil.') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_shortcut_messages_title">Titre raccourci messagerie</label>
                    <input type="text" id="student_shortcut_messages_title" name="student_shortcut_messages_title" value="{{ old('student_shortcut_messages_title', $studentDashboard['shortcut_messages_title'] ?? 'Ouvrir la messagerie') }}" required>
                </div>

                <div class="settings-field">
                    <label for="student_shortcut_messages_text">Texte raccourci messagerie</label>
                    <input type="text" id="student_shortcut_messages_text" name="student_shortcut_messages_text" value="{{ old('student_shortcut_messages_text', $studentDashboard['shortcut_messages_text'] ?? 'Écrivez à votre enseignant ou demandez de l’aide.') }}" required>
                </div>
            </div>
        </section>

        <section class="settings-homepage">
            <div class="settings-section__head">
                <div>
                    <h3>Homepage</h3>
                    <p>La homepage reste éditable via son espace dédié. Ici vous avez juste une vue d’ensemble des données actuellement chargées.</p>
                </div>
                <a href="{{ route('admin.homepage.edit') }}" class="btn btn--primary">Ouvrir l’éditeur homepage</a>
            </div>

            <div class="settings-homepage__list">
                <div class="settings-homepage__item">
                    <div>
                        <strong>Hero principal</strong>
                        <span>{{ $homepage['hero']['title'] ?? 'Non défini' }}</span>
                    </div>
                    <span class="settings-pill">Hero</span>
                </div>

                <div class="settings-homepage__item">
                    <div>
                        <strong>Nombre de témoignages</strong>
                        <span>{{ count($homepage['messages'] ?? []) }} message(s) actuellement configuré(s).</span>
                    </div>
                    <span class="settings-pill">Témoignages</span>
                </div>

                <div class="settings-homepage__item">
                    <div>
                        <strong>Nombre de classes</strong>
                        <span>{{ count($homepage['classes'] ?? []) }} classe(s) disponible(s) dans la configuration homepage.</span>
                    </div>
                    <span class="settings-pill">Classes</span>
                </div>

                <div class="settings-homepage__item">
                    <div>
                        <strong>FAQ</strong>
                        <span>{{ count($homepage['faq'] ?? []) }} question(s) FAQ actuellement chargée(s).</span>
                    </div>
                    <span class="settings-pill">FAQ</span>
                </div>
            </div>
        </section>

        <section class="settings-note">
            <p>
                Les champs ci-dessus sont sécurisés : l’administrateur modifie les contenus,
                mais ne touche ni à la structure Blade, ni à la logique PHP, ni aux routes.
            </p>
        </section>

        <div class="settings-actions">
            <div class="settings-actions__left">
                Le logo téléversé ici devient le logo principal utilisé dans la plateforme.
            </div>

            <div class="settings-actions__right">
                <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost">Retour au dashboard</a>
                <button type="submit" class="btn btn--primary">Enregistrer les paramètres</button>
            </div>
        </div>
    </form>
</div>
@endsection
