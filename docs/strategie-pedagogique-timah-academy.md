# Stratégie pédagogique TIMAH Academy

## Vision retenue

TIMAH Academy doit fonctionner comme une école de répétition en ligne structurée, avec un vrai suivi pédagogique, sans imposer à tous les élèves une présence quotidienne à heure fixe.

Le modèle retenu est :

- l'élève est libre de s'organiser selon sa connexion, son téléphone, son environnement familial et ses disponibilités ;
- l'élève doit respecter les délais de travail définis par la plateforme ;
- les cours sont suivis avec une logique de progression obligatoire ;
- les TD ont des fenêtres strictes de traitement et de soumission ;
- les contrôles continus se font à heure fixe pour limiter la triche ;
- les enseignants corrigent directement dans la plateforme ;
- les parents sont informés des manquements, sanctions, notes et progrès.

L'objectif n'est pas de créer une simple bibliothèque de PDF, mais une école de répétition en ligne avec discipline, suivi, correction, rapports et responsabilité.

## Position officielle sur l'IA

L'IA est mise de côté pour le fonctionnement pédagogique principal.

La plateforme doit d'abord reposer sur :

- des enseignants humains ;
- des TD structurés ;
- des corrections humaines ;
- des corrigés détaillés préparés par les enseignants ou l'administration ;
- un suivi clair des élèves ;
- une banque pédagogique humaine.

L'IA pourra revenir plus tard comme module optionnel, mais elle ne doit pas être le coeur de la relation pédagogique.

## Fonctionnement des cours

Les cours ne doivent pas obliger l'élève à être connecté à une heure fixe. Les difficultés de connexion rendent ce modèle trop fragile.

Le fonctionnement retenu est le suivant :

1. L'enseignant ou l'administration publie un cours.
2. Le cours apparaît dans le programme de la semaine.
3. L'élève reçoit une notification.
4. L'élève doit ouvrir et travailler le cours dans un délai prévu.
5. Si l'élève reste trop longtemps sans travailler, la plateforme déclenche des rappels et des sanctions progressives.

Règle pédagogique recommandée :

- un élève ne doit pas passer plus de deux jours sans activité sur un cours publié ;
- l'activité doit être mesurée par l'ouverture du cours, la progression, le temps de lecture, le TD lié et la consultation du corrigé après correction ;
- l'absence de connexion ponctuelle ne doit pas être confondue avec l'absence de travail.

Statuts possibles d'un cours côté élève :

- non ouvert ;
- ouvert ;
- en cours ;
- lu ;
- en retard ;
- non traité.

## Programme de la semaine

Le programme de la semaine devient un élément central de TIMAH Academy.

Chaque élève doit voir à l'avance :

- les cours prévus ;
- les TD prévus ;
- les contrôles continus ;
- les heures d'ouverture ;
- les heures de fermeture ;
- les durées de traitement ;
- les sanctions applicables ;
- les rappels importants.

Le programme de la semaine évite les surprises. L'élève sait avant l'heure qu'un TD ou un contrôle sera disponible.

## Babillard numérique

Le babillard doit devenir le tableau d'affichage officiel de l'école en ligne.

Il doit permettre de publier :

- les annonces générales ;
- le programme de la semaine ;
- les nouveaux cours ;
- les TD à venir ;
- les contrôles continus ;
- les résultats publiés ;
- les rappels de paiement ;
- les messages de direction ;
- les calendriers d'examen ;
- les sanctions collectives ou rappels disciplinaires.

Chaque annonce doit pouvoir cibler :

- toute la plateforme ;
- une classe ;
- une matière ;
- les enseignants ;
- les élèves ;
- les parents ;
- les abonnés actifs seulement.

## Fonctionnement des TD

### Règle fondamentale

On ne sépare pas l'ouverture du TD et le démarrage du traitement.

Dès que l'élève ouvre un TD disponible, son temps de traitement démarre automatiquement.

Cette règle est justifiée parce que :

- le TD est annoncé à l'avance dans le programme de la semaine ;
- l'élève reçoit une notification avant disponibilité ;
- l'élève sait que l'ouverture du TD engage sa tentative ;
- il n'y a pas de surprise ni d'injustice.

### Fenêtre officielle du TD

Chaque TD doit avoir :

- une date et heure d'ouverture ;
- une date et heure de fermeture ;
- une durée individuelle de traitement ;
- une règle de sanction ;
- une règle de rattrapage.

Exemple :

- TD disponible mercredi de 06h00 à 23h59 ;
- durée individuelle : 1h30 ;
- si l'élève ouvre à 10h00, il doit soumettre avant 11h30 ;
- si l'élève ouvre à 23h00, il doit soumettre avant 23h59, car la fermeture officielle arrive avant la durée complète.

Règle de calcul :

```text
limite_de_soumission = minimum(
    heure_ouverture_par_l_eleve + duree_du_TD,
    heure_fermeture_officielle_du_TD
)
```

### Soumission obligatoire dans le délai

L'élève doit soumettre sa copie pendant le délai autorisé.

Si le délai expire :

- la soumission est bloquée ;
- la tentative passe en statut expiré non soumis ;
- le TD est marqué comme manqué ;
- une alerte est envoyée aux parents ;
- l'enseignant est informé ;
- l'administration voit l'incident ;
- une sanction pédagogique est appliquée ;
- un rattrapage peut être demandé, mais seulement s'il est validé.

### Statuts recommandés pour les TD

- programmé ;
- disponible ;
- ouvert / en cours ;
- soumis ;
- expiré non soumis ;
- manqué ;
- rattrapage demandé ;
- rattrapage accepté ;
- rattrapage refusé ;
- corrigé ;
- note publiée.

### Différence entre TD non ouvert et TD ouvert non soumis

Il faut distinguer deux cas :

1. TD non ouvert : l'élève n'a pas commencé le travail prévu.
2. TD ouvert mais non soumis : l'élève a pris le sujet mais n'a pas rendu dans le délai.

Le deuxième cas est plus grave, car l'élève a eu accès au sujet.

## Contrôles continus

Les contrôles continus doivent être plus stricts que les TD.

Règle retenue :

- même jour ;
- même heure ;
- même durée ;
- tentative unique ;
- marge de connexion limitée ;
- soumission automatique ou blocage à la fin du temps.

Pourquoi :

- éviter qu'un élève compose le matin et transmette les réponses à un autre élève qui compose l'après-midi ;
- garantir une évaluation plus sérieuse ;
- donner de la valeur aux notes.

Statuts possibles :

- programmé ;
- ouvert ;
- en composition ;
- soumis ;
- terminé ;
- absent ;
- absence justifiée ;
- absence non justifiée ;
- rattrapage ;
- annulé.

## Sanctions pédagogiques

Les sanctions doivent être progressives, mais réelles.

### Cours non travaillé

- rappel simple ;
- avertissement ;
- information parent ;
- perte de points d'assiduité si répétition.

### TD non ouvert

- signalement dans le suivi ;
- rappel à l'élève ;
- information parent en cas de répétition ;
- perte de points d'assiduité.

### TD ouvert mais non soumis

- TD marqué comme manqué ;
- parent alerté directement ;
- sanction enregistrée ;
- rattrapage non automatique ;
- corrigé potentiellement bloqué selon la règle définie ;
- mention dans le rapport de progression.

### Contrôle continu manqué

- absence non justifiée ;
- note zéro ou statut absence selon décision de l'administration ;
- parent alerté ;
- rattrapage uniquement sur validation ;
- mention dans le rapport.

## Rattrapage

Le rattrapage ne doit pas être automatique.

Flux recommandé :

1. L'élève rate un TD ou un contrôle.
2. Il fait une demande de rattrapage.
3. Il indique le motif.
4. Il peut joindre une preuve si nécessaire.
5. L'enseignant ou l'administration accepte ou refuse.
6. Si accepté, une nouvelle fenêtre limitée est ouverte.
7. La tentative reste marquée comme rattrapage.

Motifs possibles :

- problème de connexion ;
- coupure d'électricité ;
- maladie ;
- indisponibilité justifiée ;
- autre motif.

## Soumission des TD manuscrits, photos et PDF

Les TD manuscrits doivent être traités dans la plateforme sans téléchargement/correction externe.

Flux retenu :

1. L'élève traite le TD sur cahier.
2. Il prend une photo ou envoie un PDF.
3. Il soumet dans le délai.
4. L'enseignant ouvre la copie dans TIMAH Academy.
5. Il corrige directement dans la plateforme.
6. Il coche, croise, note et ajoute des remarques rapides.
7. La plateforme calcule la note.
8. La copie corrigée est enregistrée.
9. L'élève voit sa copie corrigée et le corrigé complet officiel.

L'objectif est d'éviter que l'enseignant télécharge 500 copies, corrige à la main, puis réimporte les fichiers.

## Correction des copies par l'enseignant

### Principe

Pour un enseignant qui peut recevoir beaucoup de copies, la correction doit être rapide.

L'enseignant doit corriger directement depuis la plateforme avec :

- coches ;
- croix ;
- mention partiel ;
- notes par question ;
- remarques rapides ;
- appréciation globale.

### Interface de correction recommandée

Sur ordinateur :

- à gauche : copie de l'élève ;
- à droite : grille de correction ;
- en bas ou en haut : total, statut et bouton de validation.

La grille doit contenir :

- numéro de question ;
- barème ;
- bouton correct ;
- bouton faux ;
- bouton partiel ;
- note automatique ;
- remarque rapide.

Exemple :

```text
Question 1 : [✔] [✘] [Partiel] 2 / 2
Question 2 : [✔] [✘] [Partiel] 0 / 3
Question 3 : [✔] [✘] [Partiel] 2 / 5
```

### Annotation sur la copie

La plateforme doit permettre de marquer directement sur la copie :

- coche ;
- croix ;
- point d'interrogation ;
- remarque courte ;
- surlignage simple ;
- effacement.

L'annotation sert à montrer à l'élève où il a réussi ou échoué. La note officielle doit venir de la grille de correction.

### Mode correction rapide

Pour accélérer la correction :

- raccourcis clavier ;
- bouton copie suivante ;
- remarques préenregistrées ;
- note automatique par question ;
- filtrage copies non corrigées ;
- affichage du nombre restant.

## Corrigé officiel complet

L'élève ne doit pas ouvrir un corrigé question par question.

Il doit voir directement le corrigé complet officiel du TD.

La bonne interface côté élève :

- sa copie soumise et corrigée reste visible ;
- le corrigé complet officiel est affiché à côté ou juste en dessous selon l'écran ;
- l'élève compare facilement sa copie et le corrigé.

Sur ordinateur :

- à gauche : ma copie corrigée ;
- à droite : corrigé complet officiel.

Sur téléphone :

- onglet Ma copie ;
- onglet Corrigé complet ;
- possibilité de basculer rapidement.

Le corrigé complet ne doit pas être visible avant la fin de la fenêtre du TD et la publication officielle de la correction.

## QCM et exercices automatiques

Les QCM, vrai/faux, choix multiples et réponses fermées peuvent être corrigés automatiquement dans la plateforme.

Ces activités doivent être séparées des TD manuscrits.

Recommandation :

- QCM : correction automatique ;
- TD manuscrit/photo/PDF : correction visuelle par l'enseignant ;
- contrôle continu : correction automatique ou manuelle selon le type d'épreuve.

## Suivi des parents

Les parents doivent être informés automatiquement lorsque l'élève ne respecte pas les obligations importantes.

Alertes parentales recommandées :

- TD ouvert mais non soumis ;
- TD non ouvert ;
- contrôle continu manqué ;
- sanction appliquée ;
- note publiée ;
- rapport hebdomadaire disponible ;
- abonnement bientôt expiré.

Le message aux parents doit rester professionnel et factuel.

Exemple :

```text
Bonjour, votre enfant a ouvert le TD d'Informatique mais ne l'a pas soumis dans le délai prévu. Le TD est marqué comme manqué dans son suivi pédagogique.
```

## Score d'assiduité

TIMAH Academy doit avoir un score d'assiduité basé sur l'activité réelle, et non sur une simple présence à heure fixe.

Éléments possibles :

- cours consultés dans les délais ;
- TD soumis dans les délais ;
- contrôles continus faits à l'heure ;
- corrigés consultés ;
- questions posées ;
- retards ;
- absences ;
- rattrapages.

Ce score doit apparaître dans les rapports élèves et parents.

## Rapport de progression

La plateforme doit produire des rapports réguliers.

Contenu recommandé :

- cours publiés ;
- cours consultés ;
- TD prévus ;
- TD soumis ;
- TD manqués ;
- contrôles continus ;
- notes ;
- assiduité ;
- sanctions ;
- rattrapages ;
- observations de l'enseignant ;
- appréciation globale.

## Modules à ajouter progressivement

### Phase 1 : organisation pédagogique

- programme de la semaine ;
- fenêtres d'ouverture et fermeture des TD ;
- durée individuelle des TD ;
- suivi d'activité des cours ;
- statuts TD complets ;
- alertes parentales.

### Phase 2 : soumission et correction des TD

- soumission photo/PDF ;
- tentative TD verrouillée dès l'ouverture ;
- blocage après expiration ;
- correcteur visuel intégré ;
- coches/croix/remarques ;
- grille de correction ;
- note calculée automatiquement ;
- copie corrigée visible côté élève.

### Phase 3 : corrigés et rapports

- corrigé complet officiel ;
- affichage copie corrigée + corrigé complet ;
- rapport de progression ;
- score d'assiduité ;
- notifications parents ;
- suivi sanctions/rattrapages.

### Phase 4 : contrôle continu

- épreuves à heure fixe ;
- tentative unique ;
- marge de retard ;
- soumission automatique ;
- absence justifiée/non justifiée ;
- rattrapage contrôlé ;
- publication des notes.

## Tables et données à prévoir

À terme, il faudra prévoir ou compléter les structures suivantes :

- weekly_programs ;
- learning_activity_windows ;
- course_progresses ;
- td_attempts ;
- td_submissions ;
- td_submission_files ;
- td_questions ;
- td_grading_items ;
- td_corrections ;
- td_correction_annotations ;
- remediation_requests ;
- student_sanctions ;
- parent_notifications ;
- continuous_assessments ;
- assessment_attempts ;
- progress_reports.

## Règle produit finale

TIMAH Academy doit donner de la liberté à l'élève, mais pas du désordre.

La règle officielle devient :

```text
L'élève est libre de s'organiser, mais il doit respecter les fenêtres, les délais et les échéances. Les cours sont flexibles, les TD sont strictement bornés, les contrôles continus sont synchronisés, et chaque manquement est suivi, justifié ou sanctionné.
```

Cette stratégie permet de garder une plateforme adaptée aux réalités de connexion, tout en donnant aux parents et aux enseignants une vraie impression d'école organisée, sérieuse et contrôlée.
