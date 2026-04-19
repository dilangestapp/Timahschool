@extends('layouts.admin')

@section('title', 'Homepage configurable')
@section('page_title', 'Homepage TIMAH ACADEMY')
@section('page_subtitle', 'Modifiez les contenus clés de la homepage dans une interface plus claire, plus mobile et plus praticable.')

@section('content')
@php
    $hero = $homepage['hero'] ?? [];
    $support = $homepage['support'] ?? [];
    $featuredClassIds = collect(old('featured_class_ids', $homepage['featured_class_ids'] ?? []))
        ->map(fn ($value) => (int) $value)
        ->all();
@endphp

@push('styles')
<style>
    .hp-editor {
        display: grid;
        gap: 18px;
    }

    .hp-editor__hero {
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 18px;
    }

    .hp-card,
    .hp-side-card,
    .hp-message-card {
        background: var(--admin-panel, rgba(255,255,255,.88));
        border: 1px solid var(--admin-border, #dbe3f0);
        border-radius: 24px;
        box-shadow: 0 16px 34px rgba(15,23,42,.06);
        overflow: hidden;
    }

    .hp-card__head,
    .hp-side-card__head {
        padding: 20px 22px 0;
    }

    .hp-card__head h2,
    .hp-side-card__head h2 {
        margin: 0 0 8px;
        font-size: 1.15rem;
        letter-spacing: -0.02em;
    }

    .hp-card__head p,
    .hp-side-card__head p {
        margin: 0;
        color: var(--admin-muted, #64748b);
        line-height: 1.6;
    }

    .hp-card__body,
    .hp-side-card__body {
        padding: 20px 22px 22px;
    }

    .hp-stats {
        display: grid;
        gap: 12px;
    }

    .hp-stat {
        min-height: 94px;
        border-radius: 20px;
        padding: 16px 18px;
        border: 1px solid var(--admin-border, #dbe3f0);
        background: linear-gradient(180deg, rgba(255,255,255,.74), rgba(255,255,255,.54));
        display: grid;
        align-content: center;
        gap: 6px;
    }

    .hp-stat strong {
        font-size: 1.6rem;
        line-height: 1;
        letter-spacing: -0.03em;
    }

    .hp-stat span {
        color: var(--admin-muted, #64748b);
        font-size: .9rem;
        font-weight: 700;
    }

    .hp-form {
        display: grid;
        gap: 16px;
    }

    .hp-group {
        border: 1px solid var(--admin-border, #dbe3f0);
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255,255,255,.66), rgba(255,255,255,.48));
        overflow: hidden;
    }

    .hp-group + .hp-group {
        margin-top: 2px;
    }

    .hp-group summary {
        list-style: none;
        cursor: pointer;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-weight: 900;
        font-size: .96rem;
        letter-spacing: -0.02em;
    }

    .hp-group summary::-webkit-details-marker {
        display: none;
    }

    .hp-group summary span:last-child {
        color: var(--admin-muted, #64748b);
        font-size: .82rem;
        font-weight: 700;
    }

    .hp-group[open] summary {
        border-bottom: 1px solid var(--admin-border, #dbe3f0);
        background: rgba(79,70,229,.04);
    }

    .hp-group__body {
        padding: 16px 18px 18px;
        display: grid;
        gap: 16px;
    }

    .hp-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .hp-field {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .hp-field--full {
        grid-column: 1 / -1;
    }

    .hp-field label,
    .hp-checkboxes__title,
    .hp-json-note strong {
        font-size: .88rem;
        font-weight: 800;
        color: var(--admin-ink, #0f172a);
    }

    .hp-field input,
    .hp-field textarea,
    .hp-field select {
        width: 100%;
        border-radius: 16px;
        border: 1px solid var(--admin-border, #dbe3f0);
        background: rgba(255,255,255,.88);
        color: var(--admin-ink, #0f172a);
        padding: 14px 15px;
        outline: none;
        transition: .2s ease;
        line-height: 1.55;
    }

    .hp-field textarea {
        min-height: 120px;
        resize: vertical;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: .88rem;
    }

    .hp-field input:focus,
    .hp-field textarea:focus,
    .hp-field select:focus {
        border-color: var(--admin-primary, #4f46e5);
        box-shadow: 0 0 0 4px rgba(79,70,229,.10);
    }

    .hp-field small,
    .hp-json-note {
        color: var(--admin-muted, #64748b);
        line-height: 1.5;
        font-size: .84rem;
    }

    .hp-json-note {
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px dashed var(--admin-border, #dbe3f0);
        background: rgba(79,70,229,.04);
    }

    .hp-checkboxes {
        display: grid;
        gap: 10px;
    }

    .hp-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .hp-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 46px;
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid var(--admin-border, #dbe3f0);
        background: rgba(255,255,255,.74);
        font-size: .92rem;
        font-weight: 700;
    }

    .hp-checkbox input {
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: var(--admin-primary, #4f46e5);
    }

    .hp-actions {
        position: sticky;
        bottom: 14px;
        z-index: 6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 14px;
        border-radius: 20px;
        border: 1px solid var(--admin-border, #dbe3f0);
        background: rgba(255,255,255,.86);
        backdrop-filter: blur(12px);
        box-shadow: 0 18px 40px rgba(15,23,42,.08);
    }

    .hp-actions__text {
        color: var(--admin-muted, #64748b);
        font-size: .9rem;
        line-height: 1.5;
    }

    .hp-actions__buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .hp-messages {
        display: grid;
        gap: 16px;
    }

    .hp-message-form {
        display: grid;
        gap: 14px;
    }

    .hp-message-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .hp-message-card {
        padding: 16px;
        display: grid;
        gap: 12px;
    }

    .hp-message-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .hp-message-card__title {
        display: grid;
        gap: 4px;
    }

    .hp-message-card__title strong {
        font-size: .96rem;
        letter-spacing: -0.02em;
    }

    .hp-message-card__title span {
        color: var(--admin-muted, #64748b);
        font-size: .84rem;
    }

    .hp-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .hp-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 900;
        border: 1px solid var(--admin-border, #dbe3f0);
        background: rgba(79,70,229,.08);
        color: var(--admin-primary, #4f46e5);
    }

    .hp-badge--success {
        background: rgba(22,163,74,.10);
        color: #15803d;
        border-color: rgba(22,163,74,.18);
    }

    .hp-badge--warning {
        background: rgba(245,158,11,.10);
        color: #b45309;
        border-color: rgba(245,158,11,.18);
    }

    .hp-inline-checks {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }

    .hp-inline-checks label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .88rem;
        font-weight: 700;
        color: var(--admin-ink, #0f172a);
    }

    .hp-inline-checks input {
        width: 17px;
        height: 17px;
        accent-color: var(--admin-primary, #4f46e5);
    }

    .hp-message-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    @media (max-width: 1100px) {
        .hp-editor__hero {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 780px) {
        .hp-fields,
        .hp-message-grid,
        .hp-checkbox-grid {
            grid-template-columns: 1fr;
        }

        .hp-card__head,
        .hp-card__body,
        .hp-side-card__head,
        .hp-side-card__body {
            padding-left: 16px;
            padding-right: 16px;
        }

        .hp-group summary,
        .hp-group__body,
        .hp-message-card {
            padding-left: 14px;
            padding-right: 14px;
        }

        .hp-actions {
            bottom: 10px;
            padding: 12px;
        }

        .hp-actions__buttons,
        .hp-actions__buttons .btn {
            width: 100%;
        }

        .hp-actions__buttons .btn {
            justify-content: center;
        }
    }
</style>
@endpush

<div class="hp-editor">
    <section class="hp-editor__hero">
        <div class="hp-card">
            <div class="hp-card__head">
                <h2>Éditeur homepage</h2>
                <p>
                    Cette version est pensée pour téléphone : sections repliables, champs plus larges,
                    textes séparés et zones JSON regroupées proprement.
                </p>
            </div>

            <div class="hp-card__body">
                <form method="POST" action="{{ route('admin.homepage.update') }}" class="hp-form">
                    @csrf

                    <details class="hp-group" open>
                        <summary>
                            <span>Hero principal</span>
                            <span>Badge, titre, CTA</span>
                        </summary>
                        <div class="hp-group__body">
                            <div class="hp-fields">
                                <div class="hp-field">
                                    <label for="hero_badge">Badge hero</label>
                                    <input id="hero_badge" type="text" name="hero_badge" value="{{ old('hero_badge', $hero['badge'] ?? '') }}" required>
                                </div>

                                <div class="hp-field">
                                    <label for="hero_title">Titre hero</label>
                                    <input id="hero_title" type="text" name="hero_title" value="{{ old('hero_title', $hero['title'] ?? '') }}" required>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="hero_subtitle">Sous-titre hero</label>
                                    <textarea id="hero_subtitle" name="hero_subtitle" rows="4" required>{{ old('hero_subtitle', $hero['subtitle'] ?? '') }}</textarea>
                                </div>

                                <div class="hp-field">
                                    <label for="hero_primary_cta_label">CTA principal (label)</label>
                                    <input id="hero_primary_cta_label" type="text" name="hero_primary_cta_label" value="{{ old('hero_primary_cta_label', $hero['primary_cta_label'] ?? '') }}" required>
                                </div>

                                <div class="hp-field">
                                    <label for="hero_primary_cta_link">CTA principal (lien)</label>
                                    <input id="hero_primary_cta_link" type="text" name="hero_primary_cta_link" value="{{ old('hero_primary_cta_link', $hero['primary_cta_link'] ?? '/register') }}" required>
                                </div>

                                <div class="hp-field">
                                    <label for="hero_secondary_cta_label">CTA secondaire (label)</label>
                                    <input id="hero_secondary_cta_label" type="text" name="hero_secondary_cta_label" value="{{ old('hero_secondary_cta_label', $hero['secondary_cta_label'] ?? '') }}" required>
                                </div>

                                <div class="hp-field">
                                    <label for="hero_secondary_cta_link">CTA secondaire (lien)</label>
                                    <input id="hero_secondary_cta_link" type="text" name="hero_secondary_cta_link" value="{{ old('hero_secondary_cta_link', $hero['secondary_cta_link'] ?? '#classes') }}" required>
                                </div>

                                <div class="hp-field">
                                    <label for="hero_contact_cta_label">CTA contact (label)</label>
                                    <input id="hero_contact_cta_label" type="text" name="hero_contact_cta_label" value="{{ old('hero_contact_cta_label', $hero['contact_cta_label'] ?? '') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="hero_contact_cta_link">CTA contact (lien)</label>
                                    <input id="hero_contact_cta_link" type="text" name="hero_contact_cta_link" value="{{ old('hero_contact_cta_link', $hero['contact_cta_link'] ?? '#help-support') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="hero_help_cta_label">CTA aide (label)</label>
                                    <input id="hero_help_cta_label" type="text" name="hero_help_cta_label" value="{{ old('hero_help_cta_label', $hero['help_cta_label'] ?? '') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="hero_help_cta_link">CTA aide (lien)</label>
                                    <input id="hero_help_cta_link" type="text" name="hero_help_cta_link" value="{{ old('hero_help_cta_link', $hero['help_cta_link'] ?? '#mini-faq') }}">
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="hero_reassurance">Badges de réassurance</label>
                                    <input id="hero_reassurance" type="text" name="hero_reassurance" value="{{ old('hero_reassurance', implode(' | ', $hero['reassurance'] ?? [])) }}">
                                    <small>Sépare les éléments avec le caractère <strong>|</strong>.</small>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="hp-group">
                        <summary>
                            <span>Blocs JSON avancés</span>
                            <span>Trust, FAQ, tarifs, audiences</span>
                        </summary>
                        <div class="hp-group__body">
                            <div class="hp-json-note">
                                <strong>Important :</strong> cette zone reste technique, mais elle est maintenant regroupée.
                                Modifie seulement le bloc que tu veux changer.
                            </div>

                            <div class="hp-fields">
                                <div class="hp-field hp-field--full">
                                    <label for="trust_items_json">Trust items JSON</label>
                                    <textarea id="trust_items_json" name="trust_items_json" rows="8">{{ old('trust_items_json', json_encode($homepage['trust_items'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="why_choose_json">Pourquoi choisir JSON</label>
                                    <textarea id="why_choose_json" name="why_choose_json" rows="8">{{ old('why_choose_json', json_encode($homepage['why_choose'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="audiences_json">Audiences JSON</label>
                                    <textarea id="audiences_json" name="audiences_json" rows="8">{{ old('audiences_json', json_encode($homepage['audiences'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="faq_json">FAQ JSON</label>
                                    <textarea id="faq_json" name="faq_json" rows="8">{{ old('faq_json', json_encode($homepage['faq'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="pricing_json">Tarifs JSON</label>
                                    <textarea id="pricing_json" name="pricing_json" rows="8">{{ old('pricing_json', json_encode($homepage['pricing'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="footer_json">Footer JSON</label>
                                    <textarea id="footer_json" name="footer_json" rows="8">{{ old('footer_json', json_encode($homepage['footer'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="sections_json">Sections (ordre + activation) JSON</label>
                                    <textarea id="sections_json" name="sections_json" rows="8">{{ old('sections_json', json_encode($homepage['sections'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="hp-group">
                        <summary>
                            <span>Support & contacts</span>
                            <span>Coordonnées, liens, aide</span>
                        </summary>
                        <div class="hp-group__body">
                            <div class="hp-fields">
                                <div class="hp-field">
                                    <label for="support_title">Titre support</label>
                                    <input id="support_title" type="text" name="support_title" value="{{ old('support_title', $support['title'] ?? '') }}" required>
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label for="support_text">Texte support</label>
                                    <textarea id="support_text" name="support_text" rows="4" required>{{ old('support_text', $support['text'] ?? '') }}</textarea>
                                </div>

                                <div class="hp-field">
                                    <label for="support_email">Email support</label>
                                    <input id="support_email" type="text" name="support_email" value="{{ old('support_email', $support['email'] ?? '') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="support_phone">Téléphone support</label>
                                    <input id="support_phone" type="text" name="support_phone" value="{{ old('support_phone', $support['phone'] ?? '') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="support_whatsapp">WhatsApp</label>
                                    <input id="support_whatsapp" type="text" name="support_whatsapp" value="{{ old('support_whatsapp', $support['whatsapp'] ?? '') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="support_hours">Horaires</label>
                                    <input id="support_hours" type="text" name="support_hours" value="{{ old('support_hours', $support['hours'] ?? '') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="support_contact_link">Lien contact entreprise</label>
                                    <input id="support_contact_link" type="text" name="support_contact_link" value="{{ old('support_contact_link', $support['contact_link'] ?? '#help-support') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="support_help_link">Lien aide/support</label>
                                    <input id="support_help_link" type="text" name="support_help_link" value="{{ old('support_help_link', $support['help_link'] ?? '#help-support') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="support_faq_link">Lien FAQ</label>
                                    <input id="support_faq_link" type="text" name="support_faq_link" value="{{ old('support_faq_link', $support['faq_link'] ?? '#mini-faq') }}">
                                </div>

                                <div class="hp-field">
                                    <label for="support_info_link">Lien demande infos</label>
                                    <input id="support_info_link" type="text" name="support_info_link" value="{{ old('support_info_link', $support['info_link'] ?? '#help-support') }}">
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="hp-group">
                        <summary>
                            <span>Classes mises en avant</span>
                            <span>Sélection simple</span>
                        </summary>
                        <div class="hp-group__body">
                            <div class="hp-checkboxes">
                                <div class="hp-checkboxes__title">Choisis les classes à mettre en avant sur la homepage</div>
                                <div class="hp-checkbox-grid">
                                    @foreach($classes as $class)
                                        <label class="hp-checkbox">
                                            <input
                                                type="checkbox"
                                                name="featured_class_ids[]"
                                                value="{{ $class->id }}"
                                                {{ in_array((int) $class->id, $featuredClassIds, true) ? 'checked' : '' }}
                                            >
                                            <span>{{ $class->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </details>

                    <div class="hp-actions">
                        <div class="hp-actions__text">
                            Enregistre seulement après avoir vérifié les champs modifiés.  
                            Le bouton reste visible même sur téléphone.
                        </div>

                        <div class="hp-actions__buttons">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn--ghost">Retour admin</a>
                            <button class="btn btn--primary" type="submit">Enregistrer la homepage</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <aside class="hp-side-card">
            <div class="hp-side-card__head">
                <h2>Messages anonymes</h2>
                <p>Ajoute, publie, réorganise ou modère les témoignages visibles sur la homepage.</p>
            </div>

            <div class="hp-side-card__body">
                <div class="hp-stats" style="margin-bottom:16px;">
                    <div class="hp-stat">
                        <strong>{{ count($messages) }}</strong>
                        <span>message(s) enregistrés</span>
                    </div>
                </div>

                <div class="hp-messages">
                    <form method="POST" action="{{ route('admin.homepage.messages.store') }}" class="hp-message-form">
                        @csrf

                        <div class="hp-message-grid">
                            <div class="hp-field">
                                <label for="author_label">Pseudo / Nom affiché</label>
                                <input id="author_label" type="text" name="author_label" placeholder="Anonyme ou pseudo">
                            </div>

                            <div class="hp-field">
                                <label for="role_tag">Tag</label>
                                <select id="role_tag" name="role_tag">
                                    <option>Élève</option>
                                    <option>Parent</option>
                                    <option>Enseignant</option>
                                </select>
                            </div>

                            <div class="hp-field hp-field--full">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" rows="4" required></textarea>
                            </div>

                            <div class="hp-field">
                                <label for="sort_order">Ordre</label>
                                <input id="sort_order" type="number" name="sort_order" min="0" value="0">
                            </div>
                        </div>

                        <div class="hp-inline-checks">
                            <label><input type="checkbox" name="is_anonymous" value="1" checked> Afficher comme anonyme</label>
                            <label><input type="checkbox" name="is_published" value="1"> Publier</label>
                            <label><input type="checkbox" name="is_featured" value="1"> Mettre en avant</label>
                        </div>

                        <button class="btn btn--primary" type="submit">Ajouter le message</button>
                    </form>

                    @foreach($messages as $message)
                        <form method="POST" action="{{ route('admin.homepage.messages.update', $message) }}" class="hp-message-card">
                            @csrf

                            <div class="hp-message-card__head">
                                <div class="hp-message-card__title">
                                    <strong>{{ $message->author_label ?: 'Anonyme' }}</strong>
                                    <span>{{ $message->role_tag ?: 'Sans tag' }}</span>
                                </div>

                                <div class="hp-badges">
                                    @if($message->is_anonymous)
                                        <span class="hp-badge">Anonyme</span>
                                    @endif
                                    @if($message->is_published)
                                        <span class="hp-badge hp-badge--success">Publié</span>
                                    @endif
                                    @if($message->is_featured)
                                        <span class="hp-badge hp-badge--warning">Mis en avant</span>
                                    @endif
                                </div>
                            </div>

                            <div class="hp-message-grid">
                                <div class="hp-field">
                                    <label>Pseudo</label>
                                    <input type="text" name="author_label" value="{{ $message->author_label }}">
                                </div>

                                <div class="hp-field">
                                    <label>Tag</label>
                                    <input type="text" name="role_tag" value="{{ $message->role_tag }}">
                                </div>

                                <div class="hp-field hp-field--full">
                                    <label>Message</label>
                                    <textarea name="message" rows="4">{{ $message->message }}</textarea>
                                </div>

                                <div class="hp-field">
                                    <label>Ordre</label>
                                    <input type="number" name="sort_order" min="0" value="{{ $message->sort_order }}">
                                </div>
                            </div>

                            <div class="hp-inline-checks">
                                <label><input type="checkbox" name="is_anonymous" value="1" {{ $message->is_anonymous ? 'checked' : '' }}> Anonyme</label>
                                <label><input type="checkbox" name="is_published" value="1" {{ $message->is_published ? 'checked' : '' }}> Publié</label>
                                <label><input type="checkbox" name="is_featured" value="1" {{ $message->is_featured ? 'checked' : '' }}> Mis en avant</label>
                            </div>

                            <div class="hp-message-actions">
                                <button class="btn btn--ghost" type="submit">Mettre à jour</button>
                                <button
                                    class="btn"
                                    type="submit"
                                    formaction="{{ route('admin.homepage.messages.delete', $message) }}"
                                    onclick="return confirm('Supprimer ce message ?')"
                                >
                                    Supprimer
                                </button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection
