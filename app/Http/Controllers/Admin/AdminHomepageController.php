<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageMessage;
use App\Models\HomepageSetting;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminHomepageController extends Controller
{
    public function edit()
    {
        $setting = HomepageSetting::query()->firstOrCreate(
            ['key' => 'homepage'],
            ['value' => HomepageSetting::defaults()]
        );

        return view('admin.homepage.edit', [
            'homepage' => $setting->value ?? HomepageSetting::defaults(),
            'messages' => HomepageMessage::query()->orderByDesc('is_featured')->orderBy('sort_order')->latest()->get(),
            'classes' => SchoolClass::query()->orderBy('order')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_badge' => ['required', 'string', 'max:120'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['required', 'string', 'max:500'],
            'hero_primary_cta_label' => ['required', 'string', 'max:120'],
            'hero_primary_cta_link' => ['required', 'string', 'max:255'],
            'hero_secondary_cta_label' => ['required', 'string', 'max:120'],
            'hero_secondary_cta_link' => ['required', 'string', 'max:255'],
            'hero_contact_cta_label' => ['nullable', 'string', 'max:120'],
            'hero_contact_cta_link' => ['nullable', 'string', 'max:255'],
            'hero_help_cta_label' => ['nullable', 'string', 'max:120'],
            'hero_help_cta_link' => ['nullable', 'string', 'max:255'],
            'hero_reassurance' => ['nullable', 'string', 'max:600'],
            'trust_items_json' => ['nullable', 'string'],
            'why_choose_json' => ['nullable', 'string'],
            'audiences_json' => ['nullable', 'string'],
            'faq_json' => ['nullable', 'string'],
            'pricing_json' => ['nullable', 'string'],
            'footer_json' => ['nullable', 'string'],
            'support_title' => ['required', 'string', 'max:200'],
            'support_text' => ['required', 'string', 'max:500'],
            'support_email' => ['nullable', 'string', 'max:120'],
            'support_phone' => ['nullable', 'string', 'max:120'],
            'support_whatsapp' => ['nullable', 'string', 'max:120'],
            'support_hours' => ['nullable', 'string', 'max:120'],
            'support_contact_link' => ['nullable', 'string', 'max:255'],
            'support_help_link' => ['nullable', 'string', 'max:255'],
            'support_faq_link' => ['nullable', 'string', 'max:255'],
            'support_info_link' => ['nullable', 'string', 'max:255'],
            'featured_class_ids' => ['nullable', 'array'],
            'featured_class_ids.*' => ['integer'],
            'sections_json' => ['nullable', 'string'],
        ]);

        $value = HomepageSetting::defaults();
        $value['hero'] = [
            'badge' => $validated['hero_badge'],
            'title' => $validated['hero_title'],
            'subtitle' => $validated['hero_subtitle'],
            'primary_cta_label' => $validated['hero_primary_cta_label'],
            'primary_cta_link' => $validated['hero_primary_cta_link'],
            'secondary_cta_label' => $validated['hero_secondary_cta_label'],
            'secondary_cta_link' => $validated['hero_secondary_cta_link'],
            'contact_cta_label' => $validated['hero_contact_cta_label'] ?? "Contacter l'équipe",
            'contact_cta_link' => $validated['hero_contact_cta_link'] ?? '#help-support',
            'help_cta_label' => $validated['hero_help_cta_label'] ?? "Centre d'aide",
            'help_cta_link' => $validated['hero_help_cta_link'] ?? '#mini-faq',
            'reassurance' => collect(explode('|', $validated['hero_reassurance'] ?? ''))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->all(),
        ];

        foreach ([
            'trust_items_json' => 'trust_items',
            'why_choose_json' => 'why_choose',
            'audiences_json' => 'audiences',
            'faq_json' => 'faq',
            'pricing_json' => 'pricing',
            'footer_json' => 'footer',
            'sections_json' => 'sections',
        ] as $field => $key) {
            if (! empty($validated[$field])) {
                $decoded = json_decode($validated[$field], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $value[$key] = $decoded;
                }
            }
        }

        $value['support'] = [
            'title' => $validated['support_title'],
            'text' => $validated['support_text'],
            'email' => $validated['support_email'] ?? null,
            'phone' => $validated['support_phone'] ?? null,
            'whatsapp' => $validated['support_whatsapp'] ?? null,
            'hours' => $validated['support_hours'] ?? null,
            'contact_link' => $validated['support_contact_link'] ?? '#help-support',
            'help_link' => $validated['support_help_link'] ?? '#help-support',
            'faq_link' => $validated['support_faq_link'] ?? '#mini-faq',
            'info_link' => $validated['support_info_link'] ?? '#help-support',
        ];

        $value['featured_class_ids'] = array_values(array_filter($validated['featured_class_ids'] ?? []));

        HomepageSetting::query()->updateOrCreate(
            ['key' => 'homepage'],
            ['value' => $value]
        );

        return back()->with('success', 'Contenus homepage mis à jour.');
    }

    public function storeMessage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'author_label' => ['nullable', 'string', 'max:120'],
            'role_tag' => ['required', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:280'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_anonymous' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        HomepageMessage::query()->create([
            ...$data,
            'is_anonymous' => (bool) $request->boolean('is_anonymous', true),
            'is_published' => (bool) $request->boolean('is_published', false),
            'is_featured' => (bool) $request->boolean('is_featured', false),
        ]);

        return back()->with('success', 'Message utilisateur ajouté.');
    }

    public function updateMessage(Request $request, HomepageMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'author_label' => ['nullable', 'string', 'max:120'],
            'role_tag' => ['required', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:280'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_anonymous' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $message->update([
            ...$data,
            'is_anonymous' => (bool) $request->boolean('is_anonymous', false),
            'is_published' => (bool) $request->boolean('is_published', false),
            'is_featured' => (bool) $request->boolean('is_featured', false),
        ]);

        return back()->with('success', 'Message utilisateur mis à jour.');
    }

    public function deleteMessage(HomepageMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Message utilisateur supprimé.');
    }
}
