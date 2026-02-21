# CLAUDE.md — Projet Saxho.net

## Identite du projet

- **Nom** : Saxho.net
- **Entreprise** : SAXHO SARL (SIREN 532 963 915)
- **Siege** : 47 Avenue de la Liberation, 13850 Greasque
- **Dirigeants** : Xavier & Salama Hovasse
- **Marque deposee** : "SAXHO — De l'idee au succes"
- **Domaine** : saxho.net

---

## Vision & positionnement

Saxho se repositionne comme un **cabinet specialise dans l'accompagnement a l'innovation** pour les entreprises. Le message central :

> L'innovation d'aujourd'hui est le chiffre d'affaires de demain.

Saxho aide les entreprises a surmonter le paradoxe fondamental : **comment investir sur le futur tout en executant le present ?** Chaque entreprise sait intuitivement qu'innover est vital pour se differencier, s'adapter aux evolutions et exploiter les opportunites — mais passer du principe a la pratique est bien plus difficile qu'on ne le croit.

Saxho propose une **gamme de services progressive et accessible**, adaptee a chaque niveau de maturite et chaque type de problematique.

---

## Ecosysteme du groupe

Saxho est la societe mere d'un ecosysteme coherent :

- **IXILA** (Aix-en-Provence) — Conseil en gestion de projets, PMO, expertise Microsoft Project/SharePoint. Fondee en 1998.
- **PM Side** (Marseille) — Editeur SaaS de solutions de gestion de projets (hebergement Microsoft Project Server).

**Le lien** : Le management de projet, specialite des filiales, est un levier tres efficace pour gerer l'innovation. L'ecosysteme Saxho combine ainsi la vision strategique (innovation) avec l'excellence operationnelle (gestion de projets).

> Note : Les filiales sont mentionnees sur le site mais pas mises en avant — elles renforcent la credibilite sans ecraser le message principal d'innovation.

---

## Offre de services (5 niveaux progressifs)

### 1. Apporteur d'idees
Les entreprises, absorbees par l'operationnel, perdent le recul necessaire pour identifier des solutions. Saxho apporte un regard exterieur pour imaginer des innovations, des ameliorations, des nouveaux produits ou services.

### 2. Facilitation d'ideation
Animation de sessions d'ideation en interne chez le client, avec une methodologie eprouvee pour favoriser l'emergence de nouvelles idees et concepts par les equipes elles-memes.

### 3. Processus d'innovation
Accompagnement pour installer un flux d'innovation continue, integre au fonctionnement normal de l'entreprise. L'objectif : greffer des processus d'innovation bien articules avec le reste de l'organisation, pour une creativite permanente.

### 4. Task Force Innovation
**C'est le projet du client.** L'entreprise externalise une operation d'innovation ponctuelle dediee a un projet precis, pour s'affranchir de l'inertie et des lourdeurs organisationnelles. Saxho anime et conduit le projet en mode commando, dans un cadre externalise libere de toute contrainte. Le projet reste la propriete du client, avec un cadre juridique strict de protection et de confidentialite.

### 5. Incubateur Saxho
**C'est le projet de Saxho.** Saxho developpe ses propres idees en appliquant sa methodologie, reunit competences et collaborateurs pour aller de la conception a la mise sur le marche. Le projet incube est ensuite transmis a une structure dediee (creee ou rachetee) pour l'exploitation. L'exterieur peut contribuer (competences, ressources) ou investir (financement).

---

## Architecture du site

### Pages principales

| Page | Description | Statut |
|------|-------------|--------|
| **Accueil** | Hero + neural canvas, paradoxe, resolution (5 services), portfolio preview, CTA | Fait |
| **A propos** | Histoire de Saxho, ecosysteme (IXILA, PM Side), equipe, valeurs | Fait |
| **Services** | Les 5 niveaux detailles, escalier progressif, badges S4/S5 | Fait |
| **Portfolio** | Projets incubes, mode floute (visiteur) / complet (membre) | A faire |
| **Blog** | Articles, reflexions sur l'innovation, retours d'experience | A faire |
| **Contact** | Formulaire de contact + sidebar infos, coordonnees | Fait |

### Fonctionnalites

- **Systeme d'utilisateurs** : inscription (email + mot de passe + MFA/TOTP), connexion, profil membre
- **Portfolio a double affichage** : mode floute (visiteur) / mode complet (membre connecte) — inspiration LinkedIn Premium
- **Expression d'interet** : "Contribuer en competences" ou "Contribuer en investissement" sur chaque projet, avec formulaire pre-rempli → declenche un processus NDA hors plateforme
- **Back-office (CMS maison)** : administration du contenu, projets, utilisateurs, demandes
- **Formulaire de contact** : envoi d'email SMTP + stockage en base de donnees + CSRF auto-refresh
- **Blog** : systeme de publication d'articles avec categories, commentaires, partage
- **Multilingue** : francais + anglais (+ autres langues possibles)
- **Responsive** : adapte mobile, tablette, desktop

---

## Stack technique

| Composant | Technologie |
|-----------|------------|
| **Front-end** | HTML5, CSS3 (custom properties, animations), JavaScript vanilla (IIFE, var) |
| **Back-end** | PHP 8+ |
| **Base de donnees** | MySQL |
| **Hebergement** | Hostinger mutualise (PHP 8+, MySQL, SSH) |
| **Deploiement** | `git push` vers GitHub → bouton "Deploy" sur le panel Hostinger |
| **Gestion des langues** | Systeme i18n maison (fichiers JSON, fonction `t()`) |
| **Document root** | La racine du projet = document root (pas de dossier `public/`) |

---

## Email & SMTP

| Parametre | Valeur |
|-----------|--------|
| **SMTP Host** | `smtp.hostinger.com` |
| **SMTP Port** | `465` (SSL direct) |
| **SMTP User** | `contact@mail.saxho.net` |
| **SMTP From** | `contact@mail.saxho.net` |
| **Admin email** (destinataire) | `contact@saxho.net` |
| **Domaine email Hostinger** | `mail.saxho.net` |

**Note** : `contact@mail.saxho.net` et `contact@saxho.net` sont **deux comptes email distincts** sur Hostinger.

**Implementation** : Raw sockets PHP (`stream_socket_client()`) dans `functions.php`, pas de dependance externe. Logging detaille dans `_app/logs/smtp.log` via `app_log()`.

---

## Design system

### Polices (self-hosted, woff2)

| Police | Variable CSS | Usage |
|--------|-------------|-------|
| **Space Grotesk** (Variable) | `--ff-display` | Titres, headings |
| **Inter** (Variable) | `--ff-body` | Corps de texte |
| **JetBrains Mono** (Regular) | `--ff-mono` | Code, numeros |
| **Outfit** (Variable, fw 600) | — | Logo uniquement (hard-coded dans `layout.css`) |

### Palette de couleurs

| Token | Valeur | Usage |
|-------|--------|-------|
| `--c-primary` | `#1B3A9E` | Bleu profond — CTAs, liens, accents |
| `--c-primary-light` | `#3A7DFF` | Hover, variante claire |
| `--c-primary-dark` | `#122970` | Gradients, variante sombre |
| `--c-secondary` | `#FF6B4A` | Orange chaud — titres cartes paradox |
| `--c-tertiary` | `#F5A623` | Ambre — badges, accents secondaires |
| `--c-accent` | `#A63D6B` | Rose berry — "o" du logo, pulses neuraux |
| `--c-dark` | `#0D0D1A` | Fond hero, sections sombres |
| `--c-light` | `#F8F7F4` | Fond clair principal |
| `--c-surface` | `#F0EDE8` | Fond cartes, surfaces elevees |
| `--c-white` | `#FFFFFF` | Texte sur fond sombre |

### Logo

**Texte "saxho"** en Outfit 600, le "o" en `--c-accent` (#A63D6B).
- Pas de logo image en production. Decision validee apres test d'un logo PNG ampoule (rejete : disproportionne en petit, trop "startup" pour un cabinet).
- Les PNGs `logo-noir.png` et `logo-blanc.png` restent dans `assets/img/` pour usage print/reseaux sociaux.
- Markup : `<span class="logo__text">saxh<span class="logo__accent">o</span></span>`

### Direction artistique

- **Moderne et epure** mais pas froid — de la chaleur et de la personnalite
- **Creatif et distinctif** — sortir des sites generiques produits en masse
- **Professionnel mais dynamique** — couleurs gaies, animations subtiles
- **Experience unique** — canvas neural interactif dans le hero
- Animations CSS/JS fluides et elegantes (pas de surcharge)
- Micro-interactions et effets de survol (glow neural, reveal progressif)
- Mise en page audacieuse mais lisible
- Pas de template generique — design sur mesure

---

## Structure des fichiers

```
Saxho.net/                              <- Document root (= racine publique Hostinger)
|-- CLAUDE.md                           <- Instructions projet (ce fichier)
|-- .htaccess                           <- Reecritures URL -> index.php
|-- .gitignore
|-- index.php                           <- Front controller unique
|
|-- _app/                               <- Backend prive (bloque par .htaccess)
|   |-- .htaccess                       <- "Deny from all"
|   |-- includes/
|   |   |-- config.php                  <- Constantes : DB, SITE_URL, session, MFA, SMTP (gitignore)
|   |   |-- config.example.php          <- Template config (sans secrets)
|   |   |-- db.php                      <- Connexion PDO
|   |   |-- auth.php                    <- Helpers authentification
|   |   |-- i18n.php                    <- Detection langue + chargement traductions
|   |   |-- functions.php               <- CSRF, flash, email SMTP, slugify, app_log...
|   |   +-- router.php                  <- Definition des routes + resolution URL
|   |-- lang/
|   |   |-- fr.json                     <- Traductions francaises
|   |   +-- en.json                     <- Traductions anglaises
|   |-- logs/
|   |   |-- .htaccess                   <- "Deny from all"
|   |   +-- .gitkeep                    <- Garde le dossier dans git (logs gitignore)
|   +-- test-smtp.php                   <- Script test SMTP (CLI via SSH)
|
|-- pages/                              <- Vues de pages (incluses par index.php)
|   |-- home.php                        <- Homepage
|   |-- about.php                       <- A propos
|   |-- services.php                    <- Services (escalier 5 niveaux)
|   |-- contact.php                     <- Contact (formulaire + sidebar)
|   |-- blog.php                        <- Liste articles blog
|   |-- article.php                     <- Article individuel + commentaires
|   |-- login.php                       <- Connexion
|   |-- register.php                    <- Inscription
|   |-- verify-email.php                <- Verification email
|   |-- forgot-password.php             <- Mot de passe oublie
|   |-- reset-password.php              <- Reinitialisation mot de passe
|   |-- mfa-setup.php                   <- Configuration MFA/TOTP
|   |-- mfa-verify.php                  <- Verification MFA
|   |-- profile.php                     <- Profil utilisateur
|   +-- 404.php                         <- Page erreur
|
|-- templates/                          <- Partials de layout
|   |-- head.php                        <- <head> : meta, CSS, fonts, $pageCss
|   |-- header.php                      <- Header fixe + navigation + lang switcher
|   +-- footer.php                      <- Footer 4 colonnes
|
|-- api/                                <- Endpoints JSON (sans layout)
|   |-- contact.php                     <- Traitement formulaire contact (SMTP + DB)
|   +-- auth/                           <- Endpoints authentification
|       |-- register.php                <- Inscription
|       |-- login.php                   <- Connexion
|       |-- logout.php                  <- Deconnexion
|       |-- verify-email.php            <- Verification email
|       |-- forgot-password.php         <- Demande reset password
|       |-- reset-password.php          <- Reset password
|       |-- mfa-setup.php              <- Configuration MFA
|       +-- mfa-verify.php             <- Verification MFA
|
|-- assets/
|   |-- css/
|   |   |-- variables.css               <- Design tokens (custom properties)
|   |   |-- reset.css                   <- Reset CSS
|   |   |-- layout.css                  <- Header, footer, grid, containers
|   |   |-- components.css              <- Boutons, badges, cartes, formulaires
|   |   |-- animations.css              <- Keyframes, reveal, stagger delays
|   |   |-- responsive.css              <- Media queries globales
|   |   |-- home.css                    <- Styles homepage (+ responsive overrides)
|   |   |-- about.css                   <- Styles a propos
|   |   |-- services.css                <- Styles services
|   |   |-- contact.css                 <- Styles contact
|   |   |-- blog.css                    <- Styles blog (liste + article)
|   |   |-- auth.css                    <- Styles auth (login, register, etc.)
|   |   +-- admin.css                   <- Styles back-office admin
|   |-- js/
|   |   |-- app.js                      <- Core JS (header scroll, burger menu, nav-is-open)
|   |   |-- animations.js               <- IntersectionObserver reveal (toutes pages)
|   |   |-- typed.js                    <- Effet typing hero (home seulement)
|   |   |-- neural.js                   <- Canvas neural interactif (home hero)
|   |   |-- bulb-field.js               <- Animation ampoules-bulles (home resolution)
|   |   |-- flow-field.js               <- Animation vagues (services)
|   |   |-- contact-form.js             <- Validation + soumission AJAX + CSRF refresh
|   |   +-- admin-blog.js               <- JS back-office blog
|   |-- fonts/
|   |   |-- SpaceGrotesk-Variable.woff2
|   |   |-- Inter-Variable.woff2
|   |   |-- JetBrainsMono-Regular.woff2
|   |   +-- Outfit-Variable.woff2
|   +-- img/
|       |-- logo-noir.png               <- Logo ampoule fond clair (print/social)
|       |-- logo-blanc.png              <- Logo ampoule fond sombre (print/social)
|       +-- uploads/
|
|-- admin/                              <- Back-office (protege par auth admin)
|   |-- index.php                       <- Dashboard admin
|   +-- blog.php                        <- Gestion articles blog (CRUD)
|
|-- specs/                              <- Specifications
|   |-- pages.md                        <- Specs detaillees des pages
|   |-- users.md                        <- Systeme utilisateurs + auth + DB
|   +-- dev-notes.md                    <- Pieges, conventions, lecons apprises
|
+-- sql/
    |-- schema.sql                      <- Schema base de donnees
    +-- seed.sql                        <- Donnees initiales
```

---

## Regles de developpement

- **Securite** : requetes preparees (PDO), protection XSS (`e()`), CSRF tokens, validation des entrees
- **Performance** : CSS/JS minifies en production, images optimisees, lazy loading
- **Accessibilite** : HTML semantique, ARIA, contraste suffisant
- **SEO** : balises meta, Open Graph, sitemap, URLs propres
- **Code propre** : PHP 8+, BEM pour CSS, IIFE + var pour JS, separation logique/presentation
- **Responsive** : mobile-first. Breakpoints : 480px / 768px / 1024px / 1440px
- **Git** : commits clairs, branches par fonctionnalite
- **Cache busting** : `?v=X.Y` sur tous les CSS/JS. Version actuelle : `?v=2.1`. Incrementer apres chaque modification.

> **IMPORTANT** : Voir `specs/dev-notes.md` pour les pieges connus, conventions detaillees et lecons apprises.

---

## Progres du projet

### Phase 0 — Infrastructure
- [x] Config, routing, i18n, auth helpers, DB schema
- [x] Front controller `index.php` + `.htaccess`
- [x] Templates (head, header, footer)
- [x] Design tokens (`variables.css`), reset, layout, components
- [x] Animations (reveal, stagger) + responsive global
- [x] Traductions FR/EN (`fr.json`, `en.json`)

### Phase 1 — Homepage
- [x] Hero avec typing effect + canvas neural interactif
- [x] Section paradoxe (3 cartes : dilemme, tension, inertie)
- [x] Section resolution (5 service cards en grille)
- [x] Portfolio preview (3 cartes floutees)
- [x] Section ecosysteme (IXILA, PM Side)
- [x] CTA final
- [x] Responsive overrides dans `home.css` (fix cascade CSS)
- [x] Crackling neural au hover des cartes paradox

### Phase 2 — Pages interieures
- [x] Page A propos (`about.php` + `about.css`)
- [x] Page Services (`services.php` + `services.css`) — escalier + details
- [x] Page Contact (`contact.php` + `contact.css`) — formulaire + sidebar
- [x] Page 404
- [x] Animation flow field (vagues) sur Services (`flow-field.js`)

### Phase 3 — Portfolio _(a faire)_
- [ ] Page liste portfolio (`portfolio.php` + `portfolio.css`)
- [ ] Page detail projet (`project.php` + `project.css`)
- [ ] Mode floute visiteur / complet membre
- [ ] Expression d'interet (competences / investissement)

### Phase 4 — Blog
- [x] Page liste articles (`blog.php` + `blog.css`)
- [x] Page article individuel (`article.php`)
- [x] Systeme de categories avec illustrations
- [x] Commentaires sur articles
- [x] Boutons de partage social
- [x] Enrichissement visuel (category cards, hovers, typographie)

### Phase 5 — Auth front-end
- [x] Login (`login.php` + `auth.css`)
- [x] Register (`register.php`) avec validation
- [x] Verify-email
- [x] Forgot/reset password
- [x] MFA setup/verify (TOTP)
- [x] Page profil

### Phase 6 — Back-office admin _(en cours)_
- [x] Dashboard admin (`admin/index.php` + `admin.css`)
- [x] Gestion articles blog (`admin/blog.php` + `admin-blog.js`)
- [ ] Gestion projets
- [ ] Gestion utilisateurs
- [ ] Gestion demandes

### Phase 7 — API endpoints
- [x] API auth (register, login, logout, verify, MFA) — `api/auth/`
- [x] API contact (formulaire) — `api/contact.php`
- [ ] API interest (expression d'interet)
- [ ] API profil

### Phase 8 — Corrections & polish
- [x] Fix animation vagues : 3 couleurs → 2 (bleu clair invisible sur fond clair)
- [x] Fix menu hamburger mobile : z-index stacking context (`nav-is-open`)
- [x] Fix email SMTP : implementation robuste avec logging detaille
- [x] Fix SMTP config serveur : config.php prod avait valeurs vides
- [x] Fix SMTP auth : mot de passe ajoute manuellement sur serveur
- [x] CSRF token auto-refresh sur formulaire contact
- [x] Systeme de logging applicatif (`app_log()` → `_app/logs/`)

### Phase 9 — Finalisation _(a faire)_
- [ ] SEO (meta, OG, sitemap, robots.txt)
- [ ] Performance (minification, images, lazy load)
- [ ] Tests cross-browser
- [ ] Deploiement production final
