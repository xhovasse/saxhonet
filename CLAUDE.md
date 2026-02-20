# CLAUDE.md — Projet Saxho.net

## Identité du projet

- **Nom** : Saxho.net
- **Entreprise** : SAXHO SARL (SIREN 532 963 915)
- **Siège** : 47 Avenue de la Libération, 13850 Gréasque
- **Dirigeants** : Xavier & Salama Hovasse
- **Marque déposée** : "SAXHO — De l'idée au succès"
- **Domaine** : saxho.net

---

## Vision & positionnement

Saxho se repositionne comme un **cabinet spécialisé dans l'accompagnement à l'innovation** pour les entreprises. Le message central :

> L'innovation d'aujourd'hui est le chiffre d'affaires de demain.

Saxho aide les entreprises à surmonter le paradoxe fondamental : **comment investir sur le futur tout en exécutant le présent ?** Chaque entreprise sait intuitivement qu'innover est vital pour se différencier, s'adapter aux évolutions et exploiter les opportunités — mais passer du principe à la pratique est bien plus difficile qu'on ne le croit.

Saxho propose une **gamme de services progressive et accessible**, adaptée à chaque niveau de maturité et chaque type de problématique.

---

## Écosystème du groupe

Saxho est la société mère d'un écosystème cohérent :

- **IXILA** (Aix-en-Provence) — Conseil en gestion de projets, PMO, expertise Microsoft Project/SharePoint. Fondée en 1998.
- **PM Side** (Marseille) — Éditeur SaaS de solutions de gestion de projets (hébergement Microsoft Project Server).

**Le lien** : Le management de projet, spécialité des filiales, est un levier très efficace pour gérer l'innovation. L'écosystème Saxho combine ainsi la vision stratégique (innovation) avec l'excellence opérationnelle (gestion de projets).

> Note : Les filiales sont mentionnées sur le site mais pas mises en avant — elles renforcent la crédibilité sans écraser le message principal d'innovation.

---

## Offre de services (5 niveaux progressifs)

### 1. Apporteur d'idées
Les entreprises, absorbées par l'opérationnel, perdent le recul nécessaire pour identifier des solutions. Saxho apporte un regard extérieur pour imaginer des innovations, des améliorations, des nouveaux produits ou services.

### 2. Facilitation d'idéation
Animation de sessions d'idéation en interne chez le client, avec une méthodologie éprouvée pour favoriser l'émergence de nouvelles idées et concepts par les équipes elles-mêmes.

### 3. Processus d'innovation
Accompagnement pour installer un flux d'innovation continue, intégré au fonctionnement normal de l'entreprise. L'objectif : greffer des processus d'innovation bien articulés avec le reste de l'organisation, pour une créativité permanente.

### 4. Task Force Innovation
**C'est le projet du client.** L'entreprise externalise une opération d'innovation ponctuelle dédiée à un projet précis, pour s'affranchir de l'inertie et des lourdeurs organisationnelles. Saxho anime et conduit le projet en mode commando, dans un cadre externalisé libéré de toute contrainte. Le projet reste la propriété du client, avec un cadre juridique strict de protection et de confidentialité.

### 5. Incubateur Saxho
**C'est le projet de Saxho.** Saxho développe ses propres idées en appliquant sa méthodologie, réunit compétences et collaborateurs pour aller de la conception à la mise sur le marché. Le projet incubé est ensuite transmis à une structure dédiée (créée ou rachetée) pour l'exploitation. L'extérieur peut contribuer (compétences, ressources) ou investir (financement).

---

## Architecture du site

### Pages principales

| Page | Description |
|------|-------------|
| **Accueil** | Vision, accroche, présentation de la proposition de valeur, call-to-action |
| **À propos** | Histoire de Saxho, l'écosystème (IXILA, PM Side), l'équipe, les valeurs |
| **Services** | Les 5 niveaux de services détaillés, approche progressive |
| **Portfolio** | Projets incubés par Saxho, ouverts à contribution/investissement |
| **Blog / Actualités** | Articles, réflexions sur l'innovation, retours d'expérience |
| **Contact** | Formulaire de contact, coordonnées, localisation |

### Fonctionnalités

- **Système d'utilisateurs** : inscription (email + mot de passe + MFA/TOTP), connexion, profil membre
- **Portfolio à double affichage** : mode flouté (visiteur) / mode complet (membre connecté) — inspiration LinkedIn Premium
- **Expression d'intérêt** : "Contribuer en compétences" ou "Contribuer en investissement" sur chaque projet, avec formulaire pré-rempli → déclenche un processus NDA hors plateforme
- **Back-office (CMS maison)** : administration du contenu, projets, utilisateurs, demandes
- **Formulaire de contact** : envoi d'email + stockage en base de données
- **Blog** : système de publication d'articles avec catégories
- **Multilingue** : français + anglais (+ autres langues possibles)
- **Responsive** : adapté mobile, tablette, desktop

---

## Stack technique

| Composant | Technologie |
|-----------|------------|
| **Front-end** | HTML5, CSS3 (animations, transitions), JavaScript vanilla ou léger |
| **Back-end** | PHP 8+ |
| **Base de données** | MySQL |
| **Hébergement** | Mutualisé (PHP/MySQL) |
| **Déploiement** | Git → GitHub → serveur |
| **Gestion des langues** | Système i18n maison (fichiers de traduction JSON ou PHP) |

---

## Design & style visuel

### Direction artistique
- **Moderne et épuré** mais pas froid — de la chaleur et de la personnalité
- **Créatif et distinctif** — sortir des sites génériques produits en masse
- **Professionnel mais dynamique** — couleurs gaies, animations subtiles
- **Expérience unique** — quelque chose qu'on ne voit pas tous les jours

### Principes
- Animations CSS/JS fluides et élégantes (pas de surcharge)
- Typographie soignée et expressive
- Palette de couleurs vivante mais harmonieuse
- Micro-interactions et effets de survol
- Mise en page audacieuse mais lisible
- Pas de template générique — design sur mesure

### Identité visuelle
- Logo : à créer (le nom "Saxho" est défini)
- Charte graphique : à définir (couleurs, polices, iconographie)
- Devise : "De l'idée au succès"

---

## Structure des fichiers (convention)

```
Saxho.net/
├── CLAUDE.md                 # Ce fichier
├── specs/                    # Spécifications détaillées
│   ├── architecture.md
│   ├── database.md
│   └── pages.md
├── public/                   # Fichiers publics (document root)
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── fonts/
│   ├── pages/
│   └── api/
├── admin/                    # Back-office
│   ├── index.php
│   └── ...
├── includes/                 # PHP partagé (config, fonctions, classes)
│   ├── config.php
│   ├── db.php
│   ├── i18n.php
│   └── functions.php
├── lang/                     # Fichiers de traduction
│   ├── fr.json
│   ├── en.json
│   └── ...
└── sql/                      # Scripts SQL d'initialisation
    └── schema.sql
```

---

## Règles de développement

- **Sécurité** : requêtes préparées (PDO), protection XSS, CSRF, validation des entrées
- **Performance** : CSS/JS minifiés en production, images optimisées, lazy loading
- **Accessibilité** : HTML sémantique, ARIA, contraste suffisant
- **SEO** : balises meta, Open Graph, sitemap, URLs propres
- **Code propre** : PHP 8+, nommage cohérent, séparation logique/présentation
- **Responsive first** : mobile-first design
- **Git** : commits clairs, branches par fonctionnalité

---

## Prochaines étapes

1. ✅ Créer le CLAUDE.md (ce fichier)
2. ⬜ Valider avec l'utilisateur
3. ⬜ Définir la charte graphique (couleurs, typo, logo)
4. ⬜ Rédiger les spécifications détaillées (specs/)
5. ⬜ Concevoir le schéma de base de données
6. ⬜ Développer la structure de base (includes/, config)
7. ⬜ Développer les pages une par une
8. ⬜ Développer le back-office
9. ⬜ Intégrer le système multilingue
10. ⬜ Tests et déploiement
