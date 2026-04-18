<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function homepagePayload(): array
    {
        return static::query()->firstOrCreate(
            ['key' => 'homepage'],
            ['value' => self::defaults()]
        )->value ?? self::defaults();
    }

    public static function defaults(): array
    {
        return [
            'hero' => [
                'badge' => 'Essai gratuit 24h',
                'title' => "Réussissez avec une plateforme d'apprentissage claire, moderne et efficace",
                'subtitle' => "Cours structurés, quiz interactifs, TD corrigés et suivi de progression pour aider chaque élève à avancer avec méthode et confiance.",
                'primary_cta_label' => 'Commencer maintenant',
                'primary_cta_link' => '/register',
                'secondary_cta_label' => 'Voir les classes',
                'secondary_cta_link' => '#classes',
                'contact_cta_label' => "Contacter l'équipe",
                'contact_cta_link' => '#help-support',
                'help_cta_label' => "Centre d'aide",
                'help_cta_link' => '#mini-faq',
                'reassurance' => [
                    'Sans engagement',
                    'Cours + quiz + TD',
                    'Interface claire / sombre',
                    'Suivi de progression',
                ],
            ],
            'trust_items' => [
                ['title' => 'Classes disponibles', 'value' => '12+'],
                ['title' => 'Quiz interactifs', 'value' => '240+'],
                ['title' => 'Essai gratuit', 'value' => '24h'],
                ['title' => 'Support pédagogique', 'value' => '6j/7'],
                ['title' => 'Suivi de progression', 'value' => 'Temps réel'],
                ['title' => 'Accès sécurisé', 'value' => 'SSL'],
            ],
            'why_choose' => [
                ['title' => 'Cours structurés', 'text' => 'Chaque leçon suit une progression claire, pensée pour la réussite.'],
                ['title' => 'Quiz intelligents', 'text' => 'Évaluez vos acquis rapidement et identifiez vos lacunes.'],
                ['title' => 'Accompagnement', 'text' => 'Une expérience guidée avec un cadre rassurant et motivant.'],
                ['title' => 'Suivi continu', 'text' => 'Visualisez vos progrès semaine après semaine.'],
                ['title' => 'Accès sécurisé', 'text' => 'Vos données et paiements restent protégés.'],
            ],
            'audiences' => [
                ['title' => 'Pour les élèves', 'text' => 'Réviser efficacement avec des contenus clairs et progressifs.'],
                ['title' => 'Pour les enseignants', 'text' => 'Publier, suivre et accompagner plus simplement.'],
                ['title' => 'Pour les établissements', 'text' => 'Piloter la qualité pédagogique avec une plateforme fiable.'],
            ],
            'pricing' => [
                ['title' => 'Essentiel Mensuel', 'price' => '3 000 XAF', 'desc' => 'Démarrage intelligent avec les bases.', 'features' => ['Cours de classe', 'Quiz fondamentaux', 'Suivi simple'], 'highlight' => false],
                ['title' => 'Standard Trimestriel', 'price' => '13 500 XAF', 'desc' => 'Le meilleur équilibre progression / budget.', 'features' => ['Cours + quiz + TD', 'Suivi renforcé', 'Priorité support'], 'highlight' => true],
                ['title' => 'Premium Annuel', 'price' => '68 000 XAF', 'desc' => 'Vision long terme pour réussir toute l’année.', 'features' => ['Accès complet', 'Mises à jour continues', 'Accompagnement prioritaire'], 'highlight' => false],
            ],
            'faq' => [
                ['question' => "Comment fonctionne l'essai gratuit ?", 'answer' => "L'essai gratuit de 24h active toutes les fonctionnalités essentielles sans engagement."],
                ['question' => "Comment s'inscrire ?", 'answer' => "Créez votre compte, choisissez votre classe et commencez immédiatement."],
                ['question' => 'Comment accéder à ma classe ?', 'answer' => 'Après connexion, votre espace élève affiche vos cours, quiz et TD disponibles.'],
                ['question' => 'Le paiement est-il sécurisé ?', 'answer' => 'Oui, les transactions sont traitées via des partenaires de paiement sécurisés.'],
                ['question' => 'Qui contacter en cas de problème ?', 'answer' => "Notre support vous répond rapidement via email, téléphone ou WhatsApp."],
            ],
            'support' => [
                'title' => 'Besoin d’aide pour bien démarrer ?',
                'text' => "Notre équipe vous accompagne pour choisir la meilleure formule et lancer votre progression sans stress.",
                'email' => 'support@timahacademy.com',
                'phone' => '+237 6 00 00 00 00',
                'whatsapp' => '+237 6 00 00 00 00',
                'hours' => 'Lun - Sam • 08:00 - 18:30',
                'contact_link' => '#',
                'help_link' => '#mini-faq',
                'faq_link' => '#mini-faq',
                'info_link' => '#',
            ],
            'footer' => [
                'about' => "Apprendre aujourd'hui, réussir demain. Une plateforme pensée pour les élèves qui veulent progresser sérieusement.",
                'company_links' => [
                    ['label' => 'À propos', 'href' => '#'],
                    ['label' => 'Politique de confidentialité', 'href' => '#'],
                    ['label' => "Conditions d'utilisation", 'href' => '#'],
                    ['label' => 'Mentions légales', 'href' => '#'],
                ],
            ],
            'sections' => [
                ['key' => 'messages', 'enabled' => true, 'order' => 1],
                ['key' => 'trust', 'enabled' => true, 'order' => 2],
                ['key' => 'classes', 'enabled' => true, 'order' => 3],
                ['key' => 'why', 'enabled' => true, 'order' => 4],
                ['key' => 'audiences', 'enabled' => true, 'order' => 5],
                ['key' => 'pricing', 'enabled' => true, 'order' => 6],
                ['key' => 'faq', 'enabled' => true, 'order' => 7],
                ['key' => 'support', 'enabled' => true, 'order' => 8],
            ],
            'featured_class_ids' => [],
        ];
    }
}
