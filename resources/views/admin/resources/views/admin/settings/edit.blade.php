@extends('layouts.admin')

@section('title', 'Paramètres plateforme')
@section('page_title', 'Paramètres plateforme')
@section('page_subtitle', 'Centre de configuration global pour les textes, l’identité et les dashboards.')

@push('styles')
<style>
    .platform-settings {
        display: grid;
        gap: 20px;
    }

    .platform-settings .settings-nav {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .platform-settings .settings-tab {
        min-height: 42px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid var(--admin-border);
        background: rgba(255,255,255,.74);
        color: var(--admin-ink);
        font-weight: 800;
        cursor: pointer;
    }

    html[data-theme='dark'] .platform-settings .settings-tab {
        background: rgba(18,22,32,.72);
    }

    .platform-settings .settings-tab.is-active {
        background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
        color: #fff;
        border-color: transparent;
    }

    .platform-settings .settings-panel {
        display: none;
    }

    .platform-settings .settings-panel.is-active {
        display: grid;
        gap: 18px;
    }

    .platform-settings .settings-card {
        border: 1px solid var(--admin-border);
        border-radius: 24px;
        background: rgba(255,255,255,.72);
        box-shadow: 0 16px 34px rgba(15,23,42,.05);
        overflow: hidden;
    }

    html[data-theme='dark'] .platform-settings .settings-card {
        background: rgba(18,22,32,.72);
    }

    .platform-settings .settings-card__head {
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--admin-border);
    }

    .platform-settings .settings-card__head h2 {
        margin: 0 0 6px;
        font-size: 1.2rem;
        letter-spacing: -0.03em;
    }

    .platform-settings .settings-card__head p {
        margin: 0;
        color: var(--admin-muted);
        line-height: 1.65;
    }

    .platform-settings .settings-card__body {
        padding: 18px 20px 20px;
        display: grid;
        gap: 16px;
    }

    .platform-settings .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .platform-settings .settings-grid--single {
        grid-template-columns: 1fr;
    }

    .platform-settings .field {
        display: grid;
        gap: 8px;
    }

    .platform-settings .field label {
        font-weight: 800;
        font-size: .92rem;
    }

    .platform-settings .field input,
    .platform-settings .field textarea {
        width: 100%;
        border: 1px solid var(--admin-border);
        border-radius: 16px;
        background: rgba(255,255,255,.88);
        color: var(--admin-ink);
        padding: 14px 14px;
        outline: none;
    }

    html[data-theme='dark'] .platform-settings .field input,
    html[data-theme='dark'] .platform-settings .field textarea {
        background: rgba(10,13,20,.78);
        color: var(--admin-ink);
    }

    .platform-settings .field textarea {
        min-height: 120px;
        resize: vertical;
        line-height: 1.6;
    }

    .platform-settings .field small {
        color: var(--admin-muted);
        line-height: 1.55;
    }

    .platform-settings .settings-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .platform-settings .home-shortcuts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .platform-settings .home-shortcut {
        border: 1px solid var(--admin-border);
        border-radius: 20px;
        padding: 18px;
        background: rgba(255,255,255,.58);
        display: grid;
        gap: 10px;
    }

    html[data-theme='dark'] .platform-settings .home-shortcut {
        background: rgba(10,13,20,.58);
    }

    .platform-settings .home-shortcut strong {
        font-size: 1rem;
        letter-spacing: -0.02em;
    }

    .platform-settings .home-shortcut p {
        margin: 0;
        color: var(--admin-muted);
        line-height: 1.65;
    }

    @media (max-width: 900px) {
        .platform-settings .settings-grid,
        .platform-settings .home-shortcuts {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="platform-settings" id="platformSettings">
    <div class="settings-nav">
        <button type="button" class="settings-tab is-active" data-tab="general">Général</button>
        <button type="button" class="settings-tab" data-tab="home">Homepage</button>
        <button type="button" class="settings-tab" data-tab="admin">Dashboard admin</button>
        <button type="button" class="settings-tab" data-tab="teacher">Dashboard enseignant</button>
        <button type="button" class="settings-tab" data-tab="student">Dashboard élève</button>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <section class="settings-panel is-active" data-panel="general">
            <div class="settings-card">
                <div class="settings-card__head">
                    <h2>Paramètres généraux</h2>
                    <p>Identité de la plateforme, contacts et informations globales.</p>
                </div>

                <div class="settings-card__body">
                    <div class="settings-grid">
                        <div class="field">
                            <label for="general_platform_name">Nom de la plateforme</label>
                            <input id="general_platform_name" type="text" name="general_platform_name" value="{{ old('general_platform_name', $general['platform_name'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label for="general_platform_slogan">Slogan</label>
                            <input id="general_platform_slogan" type="text" name="general_platform_slogan" value="{{ old('general_platform_slogan', $general['platform_slogan'] ?? '') }}">
                        </div>

                        <div class="field">
                            <label for="general_support_email">Email support</label>
                            <input id="general_support_email" type="text" name="general_support_email" value="{{ old('general_support_email', $general['support_email'] ?? '') }}">
                        </div>

                        <div class="field">
                            <label for="general_support_phone">Téléphone support</label>
                            <input id="general_support_phone" type="text" name="general_support_phone" value="{{ old('general_support_phone', $general['support_phone'] ?? '') }}">
                        </div>

                        <div class="field">
                            <label for="general_support_whatsapp">WhatsApp support</label>
                            <input id="general_support_whatsapp" type="text" name="general_support_whatsapp" value="{{ old('general_support_whatsapp', $general['support_whatsapp'] ?? '') }}">
                        </div>

                        <div class="field">
                            <label for="general_primary_color">Couleur principale</label>
                            <input id="general_primary_color" type="text" name="general_primary_color" value="{{ old('general_primary_color', $general['primary_color'] ?? '#315efb') }}">
                        </div>

                        <div class="field">
                            <label for="general_secondary_color">Couleur secondaire</label>
                            <input id="general_secondary_color" type="text" name="general_secondary_color" value="{{ old('general_secondary_color', $general['secondary_color'] ?? '#7c3aed') }}">
                        </div>
                    </div>

                    <div class="field">
                        <label for="general_footer_text">Texte de pied de page</label>
                        <textarea id="general_footer_text" name="general_footer_text">{{ old('general_footer_text', $general['footer_text'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-panel" data-panel="home">
            <div class="settings-card">
                <div class="settings-card__head">
                    <h2>Homepage</h2>
                    <p>Le centre Paramètres donne accès au contrôle global. L’édition détaillée de la homepage reste dans son éditeur spécialisé pour éviter de casser la structure.</p>
                </div>

                <div class="settings-card__body">
                    <div class="home-shortcuts">
                        <div class="home-shortcut">
                            <strong>Éditeur complet de homepage</strong>
                            <p>Modifiez le hero, les sections, les FAQ, les témoignages, les classes, les abonnements et les messages utilisateurs.</p>
                            <a href="{{ route('admin.homepage.edit') }}" class="btn btn--primary">Ouvrir l’éditeur homepage</a>
                        </div>

                        <div class="home-shortcut">
                            <strong>Résumé actuel</strong>
                            <p><strong>Badge :</strong> {{ $homepage['hero']['badge'] ?? '-' }}</p>
                            <p><strong>Titre hero :</strong> {{ $homepage['hero']['title'] ?? '-' }}</p>
                            <p><strong>Support :</strong> {{ $homepage['support']['title'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-panel" data-panel="admin">
            <div class="settings-card">
                <div class="settings-card__head">
                    <h2>Dashboard administrateur</h2>
                    <p>Textes principaux visibles dans le tableau de bord admin.</p>
                </div>

                <div class="settings-card__body">
                    <div class="settings-grid">
                        <div class="field">
                            <label>Page title</label>
                            <input type="text" name="admin_page_title" value="{{ old('admin_page_title', $adminDashboard['page_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Page subtitle</label>
                            <input type="text" name="admin_page_subtitle" value="{{ old('admin_page_subtitle', $adminDashboard['page_subtitle'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Bloc modules - titre</label>
                            <input type="text" name="admin_modules_title" value="{{ old('admin_modules_title', $adminDashboard['modules_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Bloc modules - texte</label>
                            <input type="text" name="admin_modules_text" value="{{ old('admin_modules_text', $adminDashboard['modules_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Bloc décision - titre</label>
                            <input type="text" name="admin_decision_title" value="{{ old('admin_decision_title', $adminDashboard['decision_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Bloc décision - texte</label>
                            <input type="text" name="admin_decision_text" value="{{ old('admin_decision_text', $adminDashboard['decision_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Bloc indicateurs - titre</label>
                            <input type="text" name="admin_indicators_title" value="{{ old('admin_indicators_title', $adminDashboard['indicators_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Bloc indicateurs - texte</label>
                            <input type="text" name="admin_indicators_text" value="{{ old('admin_indicators_text', $adminDashboard['indicators_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Tableau 1</label>
                            <input type="text" name="admin_recent_td_title" value="{{ old('admin_recent_td_title', $adminDashboard['recent_td_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Tableau 2</label>
                            <input type="text" name="admin_recent_messages_title" value="{{ old('admin_recent_messages_title', $adminDashboard['recent_messages_title'] ?? '') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-panel" data-panel="teacher">
            <div class="settings-card">
                <div class="settings-card__head">
                    <h2>Dashboard enseignant</h2>
                    <p>Messages et titres principaux du tableau de bord enseignant.</p>
                </div>

                <div class="settings-card__body">
                    <div class="settings-grid">
                        <div class="field">
                            <label>Page title</label>
                            <input type="text" name="teacher_page_title" value="{{ old('teacher_page_title', $teacherDashboard['page_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Page subtitle</label>
                            <input type="text" name="teacher_page_subtitle" value="{{ old('teacher_page_subtitle', $teacherDashboard['page_subtitle'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Titre affectations</label>
                            <input type="text" name="teacher_assignments_title" value="{{ old('teacher_assignments_title', $teacherDashboard['assignments_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Bouton affectations</label>
                            <input type="text" name="teacher_assignments_button" value="{{ old('teacher_assignments_button', $teacherDashboard['assignments_button'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Message si aucune affectation</label>
                            <input type="text" name="teacher_assignments_empty" value="{{ old('teacher_assignments_empty', $teacherDashboard['assignments_empty'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Titre derniers TD</label>
                            <input type="text" name="teacher_latest_td_title" value="{{ old('teacher_latest_td_title', $teacherDashboard['latest_td_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Message vide derniers TD</label>
                            <input type="text" name="teacher_latest_td_empty" value="{{ old('teacher_latest_td_empty', $teacherDashboard['latest_td_empty'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Titre dernières questions TD</label>
                            <input type="text" name="teacher_latest_questions_title" value="{{ old('teacher_latest_questions_title', $teacherDashboard['latest_questions_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Message vide dernières questions</label>
                            <input type="text" name="teacher_latest_questions_empty" value="{{ old('teacher_latest_questions_empty', $teacherDashboard['latest_questions_empty'] ?? '') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-panel" data-panel="student">
            <div class="settings-card">
                <div class="settings-card__head">
                    <h2>Dashboard élève</h2>
                    <p>Textes visibles dans le dashboard élève moderne.</p>
                </div>

                <div class="settings-card__body">
                    <div class="settings-grid">
                        <div class="field">
                            <label>Hero badge</label>
                            <input type="text" name="student_hero_badge" value="{{ old('student_hero_badge', $studentDashboard['hero_badge'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Hero title</label>
                            <input type="text" name="student_hero_title" value="{{ old('student_hero_title', $studentDashboard['hero_title'] ?? '') }}" required>
                            <small>Tu peux utiliser <strong>:name</strong> pour le nom de l’élève.</small>
                        </div>

                        <div class="field">
                            <label>Hero highlight</label>
                            <input type="text" name="student_hero_highlight" value="{{ old('student_hero_highlight', $studentDashboard['hero_highlight'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Hero text</label>
                            <input type="text" name="student_hero_text" value="{{ old('student_hero_text', $studentDashboard['hero_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Votre espace - titre</label>
                            <input type="text" name="student_workspace_title" value="{{ old('student_workspace_title', $studentDashboard['workspace_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Votre espace - texte</label>
                            <input type="text" name="student_workspace_text" value="{{ old('student_workspace_text', $studentDashboard['workspace_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Objectif - titre</label>
                            <input type="text" name="student_goal_title" value="{{ old('student_goal_title', $studentDashboard['goal_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Objectif - texte</label>
                            <input type="text" name="student_goal_text" value="{{ old('student_goal_text', $studentDashboard['goal_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Progression - titre</label>
                            <input type="text" name="student_progress_title" value="{{ old('student_progress_title', $studentDashboard['progress_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Progression - texte</label>
                            <input type="text" name="student_progress_text" value="{{ old('student_progress_text', $studentDashboard['progress_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Activité - titre</label>
                            <input type="text" name="student_activity_title" value="{{ old('student_activity_title', $studentDashboard['activity_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Activité - texte</label>
                            <input type="text" name="student_activity_text" value="{{ old('student_activity_text', $studentDashboard['activity_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>TD récents - titre</label>
                            <input type="text" name="student_td_title" value="{{ old('student_td_title', $studentDashboard['td_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>TD récents - texte</label>
                            <input type="text" name="student_td_text" value="{{ old('student_td_text', $studentDashboard['td_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Repères rapides - titre</label>
                            <input type="text" name="student_refs_title" value="{{ old('student_refs_title', $studentDashboard['refs_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Repères rapides - texte</label>
                            <input type="text" name="student_refs_text" value="{{ old('student_refs_text', $studentDashboard['refs_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Conseil - titre</label>
                            <input type="text" name="student_advice_title" value="{{ old('student_advice_title', $studentDashboard['advice_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Conseil - texte</label>
                            <input type="text" name="student_advice_text" value="{{ old('student_advice_text', $studentDashboard['advice_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Conseil - note</label>
                            <input type="text" name="student_advice_note" value="{{ old('student_advice_note', $studentDashboard['advice_note'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Raccourci TD - titre</label>
                            <input type="text" name="student_shortcut_td_title" value="{{ old('student_shortcut_td_title', $studentDashboard['shortcut_td_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Raccourci TD - texte</label>
                            <input type="text" name="student_shortcut_td_text" value="{{ old('student_shortcut_td_text', $studentDashboard['shortcut_td_text'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Raccourci messagerie - titre</label>
                            <input type="text" name="student_shortcut_messages_title" value="{{ old('student_shortcut_messages_title', $studentDashboard['shortcut_messages_title'] ?? '') }}" required>
                        </div>

                        <div class="field">
                            <label>Raccourci messagerie - texte</label>
                            <input type="text" name="student_shortcut_messages_text" value="{{ old('student_shortcut_messages_text', $studentDashboard['shortcut_messages_text'] ?? '') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="settings-actions">
            <button type="submit" class="btn btn--primary">Enregistrer les paramètres</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const root = document.getElementById('platformSettings');
    if (!root) return;

    const tabs = Array.from(root.querySelectorAll('[data-tab]'));
    const panels = Array.from(root.querySelectorAll('[data-panel]'));

    const activate = (key) => {
        tabs.forEach((tab) => {
            tab.classList.toggle('is-active', tab.dataset.tab === key);
        });

        panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.panel === key);
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activate(tab.dataset.tab));
    });

    activate('general');
})();
</script>
@endpush
