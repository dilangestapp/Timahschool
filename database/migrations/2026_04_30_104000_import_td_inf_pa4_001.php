<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('td_sets') || !Schema::hasTable('school_classes') || !Schema::hasTable('subjects')) {
            return;
        }

        $now = now();

        $subjectId = DB::table('subjects')->where('slug', 'informatique')->value('id');
        if (!$subjectId) {
            $subjectId = DB::table('subjects')->insertGetId([
                'name' => 'Informatique',
                'slug' => 'informatique',
                'description' => 'Matière Informatique - TIMAH ACADEMY',
                'icon' => '💻',
                'color' => '#0f172a',
                'order' => 11,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $classData = [
            ['name' => 'Première A4 Allemand', 'slug' => 'premiere-a4-allemand', 'level' => 'secondaire_general', 'order' => 2],
            ['name' => 'Première A4 Espagnol', 'slug' => 'premiere-a4-espagnol', 'level' => 'secondaire_general', 'order' => 3],
        ];

        foreach ($classData as $item) {
            DB::table('school_classes')->updateOrInsert(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => 'Classe '.$item['name'].' - TIMAH ACADEMY',
                    'level' => $item['level'],
                    'order' => $item['order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $classes = DB::table('school_classes')->whereIn('slug', array_column($classData, 'slug'))->get();
        $teacher = Schema::hasColumn('users', 'username')
            ? DB::table('users')->where('username', 'informatique')->orWhere('email', 'informatique@timahacademy.cm')->first()
            : DB::table('users')->where('email', 'informatique@timahacademy.cm')->first();
        $authorId = $teacher?->id ?: DB::table('users')->orderBy('id')->value('id');
        if (!$authorId) {
            return;
        }

        $tdHtml = '<h2>TD-INF-PA4-001 — TD d’informatique</h2>
<h3>Thème : Numération, sécurité informatique, formulaires web, algorithmique et image numérique</h3>
<p><strong>Classe :</strong> Première A4<br><strong>Matière :</strong> Informatique</p>
<h3>Introduction</h3>
<p>Ce travail dirigé permet de vérifier les bases essentielles en informatique : la représentation des informations numériques, la protection des données, l’utilisation des formulaires web, l’analyse d’un algorithme simple et le traitement d’une image numérique.</p>
<h3>Exercice 1 : Numération et sécurité informatique</h3>
<ol><li>Définis les termes suivants : numération, codage, mot de passe fort.</li><li>Donne deux avantages de la sécurité informatique dans une organisation.</li><li>Cite un logiciel ou une application pouvant être utilisé pour produire une facture.</li><li>Donne deux exemples de documents qui peuvent être réalisés avec un logiciel de PAO.</li><li>Explique pourquoi il est important de protéger les fichiers contenant des informations personnelles.</li></ol>
<h3>Exercice 2 : Formulaire web et algorithme</h3>
<p>Un club scolaire veut organiser une journée de révision. Pour mieux répartir les élèves, un formulaire est créé afin de recueillir quelques informations.</p>
<h4>Document 1 : Formulaire d’inscription simplifié</h4>
<pre>FORMULAIRE DE PARTICIPATION
Nom complet :        [____________________]        (1)
Classe :             [ Première A4 ▼ ]              (2)
Souhaites-tu participer ?
                     ( ) Oui     ( ) Non            (3)
                     [ Valider ]   [ Effacer ]      (4)</pre>
<h4>Document 2 : Algorithme de traitement</h4>
<pre>Algorithme Participation
Variables
    age, jeunes, adultes : Entiers
Début
    jeunes ← 0
    adultes ← 0
    Pour i allant de 1 à 8 faire
        Écrire("Entrer l’âge du participant")
        Lire(age)
        Si age &lt; 18 alors
            jeunes ← jeunes + 1
        Sinon
            adultes ← adultes + 1
        FinSi
    FinPour
    Écrire("Nombre de jeunes : ", jeunes)
    Écrire("Nombre d’adultes : ", adultes)
Fin</pre>
<ol><li>Définis les termes algorithme et variable.</li><li>Identifie dans le Document 1 : zone de saisie, liste déroulante, boutons radio et bouton de commande.</li><li>Analyse le Document 2 : nom de l’algorithme, variables, condition, nombre d’exécutions et rôle des compteurs.</li><li>Écris la balise HTML qui permet de créer un formulaire.</li><li>Propose une amélioration du formulaire pour éviter les informations incomplètes.</li></ol>
<h3>Exercice 3 : Infographie et image numérique</h3>
<p>Une association scolaire possède une affiche papier annonçant une formation. Elle souhaite l’utiliser sur WhatsApp et sur une page web. L’affiche doit être transformée en image numérique.</p>
<pre>Largeur : 800 pixels
Hauteur : 600 pixels
Profondeur de couleur : 24 bits
Largeur réelle imprimée : 16 cm</pre>
<ol><li>Quelle opération permet de transformer une affiche papier en image numérique ?</li><li>Calcule le nombre total de pixels contenus dans l’image.</li><li>Calcule la taille brute de l’image en bits.</li><li>Calcule la taille brute de l’image en octets.</li><li>Cite deux opérations possibles sur une image à l’aide d’un logiciel d’infographie.</li><li>Cite deux types d’images numériques.</li><li>Explique la différence entre une image matricielle et une image vectorielle.</li></ol>
<p><strong>Numéro de suivi : TD-INF-PA4-001</strong></p>';

        $correctionHtml = '<h2>Corrigé du TD-INF-PA4-001</h2>
<h3>Exercice 1</h3>
<p><strong>Numération :</strong> système qui permet de représenter les nombres à l’aide de chiffres. En informatique, on utilise souvent le binaire composé de 0 et 1.</p>
<p><strong>Codage :</strong> action de représenter une information sous une forme compréhensible par une machine.</p>
<p><strong>Mot de passe fort :</strong> mot de passe difficile à deviner, composé de majuscules, minuscules, chiffres et caractères spéciaux.</p>
<p>La sécurité informatique protège les données et empêche les pertes, modifications frauduleuses ou accès non autorisés.</p>
<p>Un logiciel comme Excel, LibreOffice Calc ou un logiciel de gestion peut produire une facture.</p>
<p>Avec la PAO, on peut réaliser une affiche, un prospectus, une brochure, une carte d’invitation ou une carte de visite.</p>
<p>Les fichiers personnels doivent être protégés pour éviter le vol, la modification ou l’utilisation abusive des données.</p>
<h3>Exercice 2</h3>
<p><strong>Algorithme :</strong> suite ordonnée d’instructions permettant de résoudre un problème.</p>
<p><strong>Variable :</strong> espace mémoire contenant une valeur qui peut changer pendant l’exécution.</p>
<p>Dans le formulaire : zone de saisie = 1 ; liste déroulante = 2 ; boutons radio = 3 ; boutons de commande = 4.</p>
<p>Nom de l’algorithme : Participation. Variables : age, jeunes, adultes. Condition : age &lt; 18. La boucle s’exécute 8 fois.</p>
<p>La variable jeunes compte les participants mineurs. La variable adultes compte les participants de 18 ans ou plus.</p>
<pre>&lt;form&gt;
&lt;/form&gt;</pre>
<p>On peut améliorer le formulaire en rendant les champs obligatoires avec l’attribut required.</p>
<h3>Exercice 3</h3>
<p>L’opération qui transforme une affiche papier en image numérique est la numérisation.</p>
<p>Nombre total de pixels = 800 × 600 = <strong>480 000 pixels</strong>.</p>
<p>Taille en bits = 480 000 × 24 = <strong>11 520 000 bits</strong>.</p>
<p>Taille en octets = 11 520 000 ÷ 8 = <strong>1 440 000 octets</strong>, soit environ 1,44 Mo.</p>
<p>Deux opérations possibles : recadrage et redimensionnement. On peut aussi corriger les couleurs, ajouter du texte ou appliquer un filtre.</p>
<p>Deux types d’images numériques : images matricielles et images vectorielles.</p>
<p>Une image matricielle est formée de pixels et peut devenir floue si on l’agrandit. Une image vectorielle est formée de figures géométriques et peut être agrandie sans perte de qualité.</p>
<p><strong>Numéro de suivi : TD-INF-PA4-001</strong></p>';

        foreach ($classes as $class) {
            if (Schema::hasTable('class_subject')) {
                DB::table('class_subject')->updateOrInsert(
                    ['school_class_id' => $class->id, 'subject_id' => $subjectId],
                    ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
            }

            $assignment = Schema::hasTable('teacher_assignments')
                ? DB::table('teacher_assignments')->where('school_class_id', $class->id)->where('subject_id', $subjectId)->where('is_active', true)->orderByDesc('id')->first()
                : null;

            $slug = 'td-inf-pa4-001-'.$class->slug;
            DB::table('td_sets')->updateOrInsert(
                ['slug' => $slug],
                [
                    'school_class_id' => $class->id,
                    'subject_id' => $subjectId,
                    'teacher_assignment_id' => $assignment?->id,
                    'author_user_id' => $assignment?->teacher_id ?: $authorId,
                    'title' => 'TD-INF-PA4-001 - Numération, sécurité, formulaires web et image numérique',
                    'chapter_label' => 'Numération, sécurité informatique, algorithmique et infographie',
                    'summary' => 'TD d’informatique sur la numération, la sécurité, les formulaires web, l’algorithmique et le traitement d’image numérique.',
                    'instructions_html' => $tdHtml,
                    'correction_html' => $correctionHtml,
                    'difficulty' => 'medium',
                    'estimated_minutes' => 90,
                    'access_level' => 'free',
                    'td_type' => 'training',
                    'status' => 'published',
                    'correction_mode' => 'after_submit',
                    'source_type' => 'direct_import',
                    'source_label' => 'Import validé - TD-INF-PA4-001',
                    'rights_confirmed' => true,
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('td_sets')) {
            DB::table('td_sets')->where('slug', 'like', 'td-inf-pa4-001-%')->delete();
        }
    }
};
