@extends('layouts.admin')

@section('title', 'Homepage configurable')
@section('page_title', 'Homepage TIMAH ACADEMY')
@section('page_subtitle', 'Modifiez les contenus clés de la homepage sans toucher au code.')

@section('content')
@php
    $hero = $homepage['hero'] ?? [];
    $support = $homepage['support'] ?? [];
@endphp

<div class="admin-grid admin-grid--two">
    <section class="admin-panel">
        <div class="admin-panel__head"><h2>Configuration globale homepage</h2></div>
        <div class="admin-panel__body">
            <form method="POST" action="{{ route('admin.homepage.update') }}" class="admin-form-grid" style="gap:14px;">
                @csrf
                <label>Badge hero<input type="text" name="hero_badge" value="{{ old('hero_badge', $hero['badge'] ?? '') }}" required></label>
                <label>Titre hero<input type="text" name="hero_title" value="{{ old('hero_title', $hero['title'] ?? '') }}" required></label>
                <label>Sous-titre hero<textarea name="hero_subtitle" rows="3" required>{{ old('hero_subtitle', $hero['subtitle'] ?? '') }}</textarea></label>

                <label>CTA principal (label)<input type="text" name="hero_primary_cta_label" value="{{ old('hero_primary_cta_label', $hero['primary_cta_label'] ?? '') }}" required></label>
                <label>CTA principal (lien)<input type="text" name="hero_primary_cta_link" value="{{ old('hero_primary_cta_link', $hero['primary_cta_link'] ?? '/register') }}" required></label>
                <label>CTA secondaire (label)<input type="text" name="hero_secondary_cta_label" value="{{ old('hero_secondary_cta_label', $hero['secondary_cta_label'] ?? '') }}" required></label>
                <label>CTA secondaire (lien)<input type="text" name="hero_secondary_cta_link" value="{{ old('hero_secondary_cta_link', $hero['secondary_cta_link'] ?? '#classes') }}" required></label>

                <label>CTA contact (label)<input type="text" name="hero_contact_cta_label" value="{{ old('hero_contact_cta_label', $hero['contact_cta_label'] ?? '') }}"></label>
                <label>CTA contact (lien)<input type="text" name="hero_contact_cta_link" value="{{ old('hero_contact_cta_link', $hero['contact_cta_link'] ?? '#help-support') }}"></label>
                <label>CTA aide (label)<input type="text" name="hero_help_cta_label" value="{{ old('hero_help_cta_label', $hero['help_cta_label'] ?? '') }}"></label>
                <label>CTA aide (lien)<input type="text" name="hero_help_cta_link" value="{{ old('hero_help_cta_link', $hero['help_cta_link'] ?? '#mini-faq') }}"></label>

                <label>Badges de réassurance (séparés par |)
                    <input type="text" name="hero_reassurance" value="{{ old('hero_reassurance', implode(' | ', $hero['reassurance'] ?? [])) }}">
                </label>

                <label>Trust items JSON<textarea name="trust_items_json" rows="5">{{ old('trust_items_json', json_encode($homepage['trust_items'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea></label>
                <label>Pourquoi choisir JSON<textarea name="why_choose_json" rows="5">{{ old('why_choose_json', json_encode($homepage['why_choose'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea></label>
                <label>Audiences JSON<textarea name="audiences_json" rows="5">{{ old('audiences_json', json_encode($homepage['audiences'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea></label>
                <label>FAQ JSON<textarea name="faq_json" rows="5">{{ old('faq_json', json_encode($homepage['faq'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea></label>
                <label>Tarifs JSON<textarea name="pricing_json" rows="5">{{ old('pricing_json', json_encode($homepage['pricing'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea></label>
                <label>Footer JSON<textarea name="footer_json" rows="5">{{ old('footer_json', json_encode($homepage['footer'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea></label>
                <label>Sections (ordre + activation) JSON<textarea name="sections_json" rows="5">{{ old('sections_json', json_encode($homepage['sections'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea></label>

                <label>Titre support<input type="text" name="support_title" value="{{ old('support_title', $support['title'] ?? '') }}" required></label>
                <label>Texte support<textarea name="support_text" rows="3" required>{{ old('support_text', $support['text'] ?? '') }}</textarea></label>
                <label>Email support<input type="text" name="support_email" value="{{ old('support_email', $support['email'] ?? '') }}"></label>
                <label>Téléphone support<input type="text" name="support_phone" value="{{ old('support_phone', $support['phone'] ?? '') }}"></label>
                <label>WhatsApp<input type="text" name="support_whatsapp" value="{{ old('support_whatsapp', $support['whatsapp'] ?? '') }}"></label>
                <label>Horaires<input type="text" name="support_hours" value="{{ old('support_hours', $support['hours'] ?? '') }}"></label>
                <label>Lien contact entreprise<input type="text" name="support_contact_link" value="{{ old('support_contact_link', $support['contact_link'] ?? '#help-support') }}"></label>
                <label>Lien aide/support<input type="text" name="support_help_link" value="{{ old('support_help_link', $support['help_link'] ?? '#help-support') }}"></label>
                <label>Lien FAQ<input type="text" name="support_faq_link" value="{{ old('support_faq_link', $support['faq_link'] ?? '#mini-faq') }}"></label>
                <label>Lien demande infos<input type="text" name="support_info_link" value="{{ old('support_info_link', $support['info_link'] ?? '#help-support') }}"></label>

                <div>
                    <strong>Classes mises en avant</strong>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:8px; margin-top:8px;">
                        @foreach($classes as $class)
                            <label style="display:flex; gap:8px; align-items:center;"><input type="checkbox" name="featured_class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, old('featured_class_ids', $homepage['featured_class_ids'] ?? []), true) ? 'checked' : '' }}> {{ $class->name }}</label>
                        @endforeach
                    </div>
                </div>

                <button class="btn btn--primary" type="submit">Enregistrer la homepage</button>
            </form>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel__head"><h2>Messages anonymes (modération)</h2></div>
        <div class="admin-panel__body" style="display:grid; gap:14px;">
            <form method="POST" action="{{ route('admin.homepage.messages.store') }}" class="admin-form-grid" style="gap:10px;">
                @csrf
                <label>Pseudo / Nom affiché<input type="text" name="author_label" placeholder="Anonyme ou pseudo"></label>
                <label>Tag
                    <select name="role_tag">
                        <option>Élève</option>
                        <option>Parent</option>
                        <option>Enseignant</option>
                    </select>
                </label>
                <label>Message<textarea name="message" rows="3" required></textarea></label>
                <label>Ordre<input type="number" name="sort_order" min="0" value="0"></label>
                <label><input type="checkbox" name="is_anonymous" value="1" checked> Afficher comme anonyme</label>
                <label><input type="checkbox" name="is_published" value="1"> Publier</label>
                <label><input type="checkbox" name="is_featured" value="1"> Mettre en avant</label>
                <button class="btn btn--primary" type="submit">Ajouter</button>
            </form>

            @foreach($messages as $message)
                <form method="POST" action="{{ route('admin.homepage.messages.update', $message) }}" class="admin-form-grid" style="padding:12px; border:1px solid var(--line); border-radius:12px; gap:8px;">
                    @csrf
                    <label>Pseudo<input type="text" name="author_label" value="{{ $message->author_label }}"></label>
                    <label>Tag<input type="text" name="role_tag" value="{{ $message->role_tag }}"></label>
                    <label>Message<textarea name="message" rows="2">{{ $message->message }}</textarea></label>
                    <label>Ordre<input type="number" name="sort_order" min="0" value="{{ $message->sort_order }}"></label>
                    <label><input type="checkbox" name="is_anonymous" value="1" {{ $message->is_anonymous ? 'checked' : '' }}> Anonyme</label>
                    <label><input type="checkbox" name="is_published" value="1" {{ $message->is_published ? 'checked' : '' }}> Publié</label>
                    <label><input type="checkbox" name="is_featured" value="1" {{ $message->is_featured ? 'checked' : '' }}> Mis en avant</label>
                    <div style="display:flex; gap:8px;">
                        <button class="btn btn--ghost" type="submit">Mettre à jour</button>
                        <button class="btn" type="submit" formaction="{{ route('admin.homepage.messages.delete', $message) }}" onclick="return confirm('Supprimer ce message ?')">Supprimer</button>
                    </div>
                </form>
            @endforeach
        </div>
    </section>
</div>
@endsection
