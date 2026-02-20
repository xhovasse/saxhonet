# Spécifications détaillées des pages — Saxho.net

## Principes généraux

- **Ton** : Impersonnel inspirant. Pas de "tu/vous", des constats universels, des vérités qui résonnent.
- **Navigation** : Header fixe avec logo + menu. Footer avec coordonnées, liens légaux, réseaux.
- **Multilingue** : Sélecteur de langue discret dans le header. Langues : FR, EN (extensible).
- **Animations** : Apparitions au scroll (fade-in, slide), micro-interactions au survol, transitions fluides. Jamais gratuites — toujours au service du propos.
- **Responsive** : Mobile-first. Breakpoints : 480px / 768px / 1024px / 1440px.

---

## 1. PAGE D'ACCUEIL (index.php)

### Structure narrative en 3 mouvements

#### MOUVEMENT 1 — Le constat universel
**Section héro (plein écran)**

- Accroche principale (grande typographie, animation d'apparition) :
  > L'innovation d'aujourd'hui est le chiffre d'affaires de demain.

- Sous-texte :
  > Tout le monde sait intuitivement qu'il est important d'innover. Créer de nouveaux produits, de nouveaux services. Se différencier. Rester adapté à un environnement qui change sans cesse. Exploiter les opportunités qui se présentent.

- Élément visuel : animation abstraite/géométrique évoquant la transformation, le mouvement, l'émergence (pas une banque d'images générique).

- Scroll indicator animé pour inviter à descendre.

#### MOUVEMENT 2 — Le paradoxe
**Section de transition (fond contrasté)**

- Titre de section :
  > Mais passer du principe à la pratique est plus difficile qu'on ne le croit.

- Contenu narratif (blocs ou colonnes animés au scroll) :
  - **Le dilemme** : Comment concilier des démarches inédites, dont les résultats restent incertains, avec l'exécution au long cours d'une activité régulière — la seule qui fournit le revenu pour rester viable ?
  - **La tension** : Comment investir sur le futur quand tous les regards sont portés sur le présent ?
  - **L'inertie** : Les entreprises sont tellement impliquées dans l'opérationnel et le concret qu'elles n'ont plus le recul nécessaire.

- Traitement visuel : contrastes, typographie expressive, peut-être un schéma animé illustrant la tension présent/futur.

#### MOUVEMENT 3 — L'ouverture
**Section résolution (retour au clair)**

- Titre :
  > C'est là qu'on intervient.

- Sous-texte :
  > Une gamme de services accessible, progressive, adaptée à chaque problématique. Pour passer de l'idée au succès.

- Aperçu des 5 services : 5 blocs/cartes avec icône, titre, une phrase. Chaque carte mène vers la page Services.
  1. Apporteur d'idées
  2. Facilitation d'idéation
  3. Processus d'innovation
  4. Task Force Innovation
  5. Incubateur Saxho

- CTA principal : Lien vers la page Services ou Contact.

### Sections complémentaires (sous le narratif)

#### Portfolio — Aperçu
- Titre : "Nos projets" ou "Ce qu'on construit"
- 3 projets vedettes issus du portfolio, en cards visuelles
- Lien "Voir tous les projets →"

#### Écosystème — Mention subtile
- Bande ou section légère :
  > Saxho s'appuie sur un écosystème de compétences : le management de projet, spécialité de nos filiales IXILA et PM Side, est un levier puissant pour piloter l'innovation.
- Logos/noms des filiales, discret, sans dominer la page.

#### CTA final
- Section de clôture avant le footer :
  > "Prêt à transformer vos idées en succès ?"
- Bouton Contact.

---

## 2. PAGE À PROPOS (about.php)

### Sections

#### L'histoire
- La genèse de Saxho (2011), la construction progressive d'un écosystème.
- Le pivot vers l'innovation comme activité propre.
- Le fil conducteur : "De l'idée au succès" — pas juste un slogan, une conviction.

#### L'écosystème
- **Saxho** : la vision, la stratégie d'innovation
- **IXILA** (depuis 1998) : l'excellence en gestion de projets et PMO
- **PM Side** : les outils SaaS pour piloter les projets
- Schéma visuel montrant les synergies (innovation ↔ management de projet)

#### Les valeurs / l'approche
- Regard extérieur et recul stratégique
- Méthodologies éprouvées
- Pragmatisme — du concret, pas de la théorie
- Progressivité — on s'adapte au niveau de maturité du client

#### L'équipe (optionnel — à confirmer)
- Présentation des fondateurs et/ou de l'équipe

---

## 3. PAGE SERVICES (services.php)

### Introduction
- Reprise du narratif : le problème → la solution progressive
- Schéma visuel du parcours progressif (escalier, timeline, ou chemin)

### Les 5 niveaux de service (sections détaillées)

Chaque service est une section complète avec :
- **Icône / illustration** distinctive
- **Titre**
- **Le problème adressé** (en quoi ce service répond à un besoin concret)
- **Ce qu'on fait** (description de la prestation)
- **Pour qui** (type d'entreprise / situation)
- **CTA** : "Parlons-en" → Contact

#### Service 1 : Apporteur d'idées
- Problème : Trop pris dans l'opérationnel pour avoir le recul
- Solution : Un regard extérieur qui imagine des solutions, innovations, améliorations
- Pour : Entreprises qui veulent évoluer mais ne savent pas par où commencer

#### Service 2 : Facilitation d'idéation
- Problème : Les bonnes idées sont souvent là, mais pas exprimées
- Solution : Sessions d'idéation structurées avec méthodologie éprouvée
- Pour : Entreprises qui veulent mobiliser l'intelligence collective interne

#### Service 3 : Processus d'innovation
- Problème : L'innovation reste ponctuelle, jamais systématique
- Solution : Installation d'un flux d'innovation continue, intégré à l'organisation
- Pour : Entreprises qui veulent une culture d'innovation durable

#### Service 4 : Task Force Innovation
- **C'est le projet du client.** L'entreprise souhaite externaliser une opération d'innovation ponctuelle dédiée à un projet précis.
- Problème : L'inertie organisationnelle, les lourdeurs administratives, les règles de fonctionnement internes freinent les projets innovants. Pour qu'un projet d'innovation soit couronné de succès, il faut aller vite, privilégier la créativité, avoir l'esprit libre — impossible quand on est en même temps préoccupé par le fonctionnement quotidien.
- Solution : Saxho prend en charge l'animation et la conduite du projet dans un cadre externalisé, libéré de toute contrainte organisationnelle. Mode task force / opération commando.
- Propriété : **Le projet reste la propriété de l'entreprise.** Un cadre juridique strict de protection et de confidentialité est établi. Saxho anime et conduit — le client possède.
- Pour : Entreprises qui ont un projet d'innovation précis à accélérer et veulent s'affranchir de leur propre inertie

#### Service 5 : Incubateur Saxho
- **C'est le projet de Saxho.** Nous sommes nous-mêmes à l'origine de nombreuses idées, et nous appliquons à nous-mêmes notre propre méthodologie.
- Concept : Saxho développe ses propres projets innovants, de l'idée jusqu'à la mise sur le marché. Des compétences et des collaborateurs sont réunis pour avancer sur la réalisation.
- Issue : La plupart du temps, un projet incubé est ensuite transmis à une structure créée pour l'occasion (ou à un acteur existant racheté) qui prend en charge la mise sur le marché puis l'exploitation du produit ou service.
- Ouverture : L'extérieur peut contribuer (compétences, ressources, savoir-faire) ou investir (financer le développement) dans les projets en cours.
- Pour : Contributeurs qui veulent participer à des projets innovants, et investisseurs qui veulent financer l'innovation de la conception à la mise sur le marché

### Schéma récapitulatif
- Visuel montrant la progressivité : de l'intervention ponctuelle à l'engagement structurel

---

## 4. PAGE PORTFOLIO (portfolio.php)

### Concept
Le portfolio est une vitrine des projets incubés par Saxho (Service 5 — Incubateur Saxho). Il fonctionne comme un **teasing** : montrer qu'il y a de l'activité, donner envie d'en savoir plus, sans révéler d'informations confidentielles.

**Analogie** : LinkedIn Premium — on aperçoit que quelqu'un a visité son profil, mais l'identité est floutée/générique tant qu'on n'a pas l'abonnement. Ici, les projets sont visibles mais floutés tant qu'on n'est pas inscrit.

**Logique de financement participatif privé** : contrairement aux plateformes publiques type Kickstarter, c'est un participatif restreint, dans un cadre privé et contrôlé.

### Double affichage : visiteur vs. membre

#### Mode visiteur (non connecté) — "Teaser"
- Les cartes projet sont affichées avec un **effet blur/flou**
- Informations visibles (non floutées) :
  - Domaine / secteur (ex: "Santé", "EdTech", "Mobilité")
  - Phase générique (ex: "En développement")
  - Un pictogramme ou illustration abstraite
- Informations floutées :
  - Nom du projet
  - Description / pitch
  - Détails d'avancement
  - Besoins en financement / compétences
- **Overlay d'incitation** : au survol ou au clic, un message type :
  > "Créez votre compte pour découvrir ce projet et les opportunités de contribution."
- **CTA** : "S'inscrire pour accéder" → page d'inscription

#### Mode membre (connecté) — "Détail"
- Les cartes projet sont entièrement lisibles
- Accès à la fiche projet complète (voir structure ci-dessous)
- Possibilité d'exprimer un intérêt (contribuer / investir)

### Structure d'une fiche projet (membre connecté)

| Champ | Description | Type BDD |
|-------|-------------|----------|
| **Nom du projet** | Titre du projet | VARCHAR(255) |
| **Visuel** | Image ou illustration du projet | VARCHAR(255) — chemin fichier |
| **Pitch** | Description courte (1-2 phrases) | TEXT |
| **Domaine** | Secteur d'activité (Santé, Tech, Énergie, etc.) | VARCHAR(100) |
| **Problème adressé** | Quel problème ce projet résout | TEXT |
| **Solution proposée** | En quoi consiste l'innovation | TEXT |
| **État d'avancement** | Phase actuelle du projet | ENUM (voir ci-dessous) |
| **Investissement recherché** | Montant ou fourchette de financement nécessaire | VARCHAR(100) |
| **Compétences recherchées** | Profils / expertises dont le projet a besoin | TEXT |
| **Date de lancement** | Date de démarrage du projet | DATE |
| **Statut** | Ouvert aux contributions / Complet / En pause | ENUM |

#### Phases d'avancement (indicateur visuel progressif)
1. **Idéation** — L'idée est formulée, le concept est défini
2. **Étude** — Analyse de faisabilité, étude de marché
3. **Prototype** — Premier prototype ou preuve de concept
4. **Développement** — Développement du produit/service
5. **Pré-lancement** — Tests, validation, préparation mise sur le marché
6. **Transmis** — Projet cédé à une structure d'exploitation

### Interactions membre
Deux boutons distincts, visuellement différenciés (icônes et couleurs différentes) :

- **"Contribuer en compétences"** → formulaire pré-rempli (données du profil) + champs spécifiques : domaine d'expertise, disponibilité, lien LinkedIn/CV
- **"Contribuer en investissement"** → formulaire pré-rempli (données du profil) + champs spécifiques : fourchette d'investissement, expérience, structure d'investissement

Après soumission : stockage en BDD + email admin + accusé réception membre + message mentionnant le processus NDA à venir.

Le contenu visible sur la plateforme reste **toujours non confidentiel**. Les échanges confidentiels se font hors plateforme, après signature d'un NDA.

> Voir `specs/users.md` pour le détail complet des formulaires, champs et processus.

### Filtrage (disponible pour les membres)
- Par domaine/secteur
- Par état d'avancement
- Par type de besoin (financement, compétences, les deux)
- Par statut (ouvert, complet, en pause)

### Administration (back-office)
- Formulaire de saisie/modification de projet avec tous les champs ci-dessus
- Upload d'image
- Gestion du statut et de la visibilité
- Consultation des demandes de contribution/investissement reçues

### 5 projets fictifs de démonstration

#### Projet 1 : "Pulse"
- **Domaine** : Santé / Bien-être
- **Pitch** : Une solution connectée qui transforme le suivi de la récupération sportive en un outil prédictif personnalisé.
- **Problème** : Les sportifs amateurs n'ont aucun moyen fiable de savoir quand leur corps est prêt pour un nouvel effort intense.
- **Solution** : Algorithme d'analyse combinant données biométriques et habitudes pour prédire les fenêtres de performance optimale.
- **Phase** : Prototype
- **Investissement recherché** : 80 000 € – 120 000 €
- **Compétences recherchées** : Data science, physiologie du sport, développement mobile
- **Statut** : Ouvert

#### Projet 2 : "Greenloop"
- **Domaine** : Économie circulaire / Industrie
- **Pitch** : Une plateforme B2B qui met en relation les déchets industriels d'une entreprise avec les besoins en matières premières d'une autre.
- **Problème** : Des tonnes de sous-produits industriels sont jetés alors qu'ils pourraient être la matière première d'un autre acteur.
- **Solution** : Marketplace intelligente avec matching automatique basé sur la composition chimique et la géolocalisation.
- **Phase** : Étude
- **Investissement recherché** : 150 000 € – 200 000 €
- **Compétences recherchées** : Chimie industrielle, logistique, développement web full-stack
- **Statut** : Ouvert

#### Projet 3 : "Topo"
- **Domaine** : EdTech / Formation
- **Pitch** : Un outil qui génère automatiquement des parcours de montée en compétences à partir de l'analyse des pratiques réelles d'un collaborateur.
- **Problème** : Les plans de formation sont souvent déconnectés des besoins réels et arrivent trop tard.
- **Solution** : Observation non intrusive des pratiques de travail + génération de micro-formations ciblées en temps réel.
- **Phase** : Idéation
- **Investissement recherché** : 50 000 € – 80 000 €
- **Compétences recherchées** : Machine learning, UX design, sciences de l'éducation
- **Statut** : Ouvert

#### Projet 4 : "Nébula"
- **Domaine** : Énergie / Smart Building
- **Pitch** : Un système de pilotage énergétique pour copropriétés qui optimise collectivement la consommation sans contraindre individuellement.
- **Problème** : Dans une copropriété, l'optimisation énergétique bute sur la multiplicité des décideurs et l'absence de vision globale.
- **Solution** : Capteurs partagés + algorithme d'optimisation collective avec bénéfice redistribué à chaque copropriétaire.
- **Phase** : Développement
- **Investissement recherché** : 200 000 € – 300 000 €
- **Compétences recherchées** : IoT, énergie bâtiment, droit de la copropriété, développement embarqué
- **Statut** : Ouvert

#### Projet 5 : "Passerelle"
- **Domaine** : Mobilité / Social
- **Pitch** : Une application de covoiturage hyper-local dédiée aux trajets domicile-travail en zones périurbaines mal desservies.
- **Problème** : Les zones périurbaines sans transport en commun génèrent une dépendance totale à la voiture individuelle.
- **Solution** : Matching de voisinage automatique basé sur les horaires réels de travail, avec micro-compensation financière.
- **Phase** : Pré-lancement
- **Investissement recherché** : 100 000 € – 150 000 €
- **Compétences recherchées** : Développement mobile, marketing territorial, partenariats collectivités
- **Statut** : Ouvert

> **Note** : Ces projets sont fictifs et servent à démontrer le concept. Ils seront remplacés ou modifiés par de vrais projets.

---

## 5. PAGE BLOG / ACTUALITÉS (blog.php)

### Fonctionnalités
- Liste d'articles avec pagination
- Catégories (Innovation, Méthodes, Retours d'expérience, Écosystème...)
- Page article individuelle (blog-article.php)
- Partage social
- Date de publication, temps de lecture estimé

### Structure d'un article
- Titre
- Image de couverture (optionnelle)
- Catégorie
- Date + temps de lecture
- Contenu (HTML riche : titres, paragraphes, images, citations, listes)
- Articles liés (suggestions)

### Administration (back-office)
- Créer / modifier / supprimer des articles
- Éditeur de texte riche (WYSIWYG ou Markdown)
- Gestion des catégories
- Upload d'images
- Brouillon / publié

---

## 6. PAGE CONTACT (contact.php)

### Formulaire
- Champs : Nom, Email, Entreprise (optionnel), Sujet (select), Message
- Sujets prédéfinis :
  - Demande d'information générale
  - Apporteur d'idées
  - Session d'idéation
  - Processus d'innovation
  - Hébergement de projet
  - Portfolio — Contribuer
  - Portfolio — Investir
  - Autre
- Envoi d'email + stockage en BDD
- Protection anti-spam (honeypot + rate limiting, pas de CAPTCHA externe)
- Message de confirmation après envoi

### Coordonnées
- Adresse : Gréasque + mention Aix-en-Provence et Marseille (écosystème)
- Email de contact
- Carte interactive (optionnel — OpenStreetMap pour éviter Google)

### Réseaux sociaux
- Liens vers les profils (à définir)

---

## Navigation & éléments communs

### Header
- Logo Saxho (à créer)
- Menu : Accueil | À propos | Services | Portfolio | Blog | Contact
- Sélecteur de langue (FR / EN)
- Menu burger en mobile

### Footer
- Logo + baseline "De l'idée au succès"
- Liens rapides (pages principales)
- Coordonnées
- Liens légaux (Mentions légales, Politique de confidentialité)
- Filiales : IXILA | PM Side (liens discrets)
- © Saxho 2025

---

## Interactions & animations remarquables

L'objectif est de créer une expérience **mémorable et distinctive** :

- **Héro animé** : texte qui se construit lettre par lettre ou mot par mot
- **Parallaxe subtil** : éléments de fond qui bougent à des vitesses différentes au scroll
- **Reveal au scroll** : sections qui apparaissent avec des animations variées (fade, slide, scale)
- **Cartes interactives** : effets 3D légers au survol (tilt)
- **Transitions de page** : si SPA partiel, transitions fluides entre les pages
- **Curseur personnalisé** : éventuel changement de curseur au survol d'éléments interactifs
- **Compteurs animés** : chiffres clés qui s'incrémentent au scroll
- **Fond dynamique** : éléments géométriques ou particules subtils en arrière-plan
- **Mode sombre** : toggle jour/nuit (optionnel mais distinctif)

> Le tout dosé avec soin : chaque animation doit avoir un sens et ne jamais nuire à la performance ou à la lisibilité.
