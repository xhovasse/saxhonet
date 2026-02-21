# Notes de developpement — Saxho.net

> Ce fichier capture les pieges connus, conventions de code et decisions de design
> pour eviter de refaire les memes erreurs dans les prochaines sessions de dev.

---

## 1. Pieges connus (CRITICAL)

### 1.1 Cascade CSS — fichiers per-page vs responsive.css

**Probleme** : Les fichiers CSS specifiques a une page (`home.css`, `services.css`...) sont charges
APRES `responsive.css` via la variable `$pageCss` dans `templates/head.php`.

Consequence : toute regle SANS media query dans le fichier per-page **ecrase** les media queries
equivalentes de `responsive.css`.

**Exemple concret** :
```
responsive.css : @media (max-width: 767px) { .paradox__grid { grid-template-columns: 1fr } }
home.css :       .paradox__grid { grid-template-columns: repeat(3, 1fr) }  <- ECRASE la MQ !
```

**Solution** : Dupliquer les media queries responsive necessaires en **bas** du fichier per-page
concerne. Voir `home.css` (lignes 358-412) pour l'exemple de reference.

**Ordre de chargement CSS** (head.php) :
1. `variables.css` — design tokens
2. `reset.css`
3. `layout.css`
4. `components.css`
5. `animations.css`
6. `responsive.css`
7. `$pageCss` — **charge en dernier**, ecrase tout ce qui precede

### 1.2 Cache busting — version obligatoire

**Probleme** : Hostinger et les navigateurs mettent en cache agressivement les fichiers statiques.
Sans cache busting, les modifications CSS/JS ne sont pas visibles apres deploiement.

**Solution** : Tous les CSS et JS ont un parametre `?v=X.Y`. Apres chaque modification :

1. Incrementer la version dans `templates/head.php` (7 occurrences de `?v=`)
2. Incrementer la version dans `index.php` (5 occurrences de `?v=`)
3. Commit + deploy

**Version actuelle** : `?v=2.1`

**Astuce** : Rechercher `?v=1.` dans les deux fichiers pour trouver toutes les occurrences.

### 1.3 Bash sur macOS — chemins complets

**Probleme** : Dans l'environnement Claude Code sur macOS, certaines commandes bash necessitent
des chemins complets pour fonctionner correctement.

**Commandes courantes** :
```bash
/bin/cp source destination
/bin/mkdir -p dossier
/bin/mv source destination
```

**Git push avec SSH** :
```bash
GIT_SSH_COMMAND="/usr/bin/ssh" /usr/bin/git push origin main
```

### 1.4 Font Outfit — non referencee dans variables.css

**Probleme** : La police Outfit est chargee dans `head.php` (preload + @font-face) mais n'a
**pas** de variable CSS dans `variables.css`. Elle est utilisee uniquement dans `layout.css`
pour `.logo__text` avec un fallback hard-code :

```css
font-family: 'Outfit', var(--ff-display);
```

**Consequence** : Si on change `--ff-display` (Space Grotesk), le logo reste en Outfit. C'est voulu.

### 1.5 Deploiement Hostinger

**Flux** :
1. `git add` + `git commit` en local
2. `git push origin main` vers GitHub
3. Aller sur le panel Hostinger → Git → cliquer "Deploy"
4. Le site est mis a jour

**Pas de CI/CD automatique** — le deploy est manuel via le bouton Hostinger.

### 1.6 config.php — PAS deploye via git

**Probleme critique** : `_app/includes/config.php` est dans `.gitignore` (contient les secrets).
Il existe **deux versions independantes** :

- **Locale** (dev) : dans le dossier Dropbox, avec les secrets de dev
- **Serveur** (prod) : sur Hostinger, avec les secrets de production

**Consequence** : Toute modification de config.php (nouvelles constantes, changements de valeurs)
doit etre faite **a la main** sur le serveur via :
- Hostinger File Manager (hPanel → Files → File Manager)
- Chemin serveur : `/home/u473667317/domains/saxho.net/public_html/_app/includes/config.php`

**Piege vecu** : SMTP ne marchait pas en production parce que le `config.php` serveur avait
`USE_SMTP=false`, `SMTP_HOST` vide, `SMTP_PASS` vide — alors que la version locale etait correcte.

**Bonne pratique** : Quand on ajoute une nouvelle constante dans config.php :
1. L'ajouter dans le config local
2. L'ajouter aussi dans `config.example.php` (template sans secrets)
3. **Rappeler** qu'il faut l'ajouter manuellement sur le serveur

### 1.7 Z-index stacking contexts — menu mobile

**Probleme** : Un element enfant avec `position: fixed` a l'interieur d'un parent avec
`position: fixed` + `z-index` cree un **stacking context**. L'enfant ne peut pas depasser
le z-index du parent, meme avec `z-index: 999999`.

**Cas concret** : Le menu hamburger mobile (`.header__nav`) est enfant de `.site-header`
(z-index: 100). L'overlay du menu ne pouvait pas couvrir le contenu de la page.

**Solution** : Ajouter une classe JS `nav-is-open` sur le parent `.site-header` qui
eleve son z-index a 200 (var `--z-overlay`). Les enfants utilisent des z-index relatifs :
- Nav overlay : z-index 1
- Actions (lang/auth) : z-index 2
- Burger : z-index 3

**Fichiers concernes** : `layout.css`, `responsive.css`, `app.js`

### 1.8 SMTP — implementation et diagnostic

**Implementation** : `send_email_smtp()` dans `functions.php` utilise des raw sockets
(`stream_socket_client()`) — pas de dependance PHPMailer/SwiftMailer.

**Config SMTP Hostinger** :
- Host : `smtp.hostinger.com`
- Port : `465` (SSL direct, PAS 587/STARTTLS)
- User : `contact@mail.saxho.net`
- From : `contact@mail.saxho.net`
- Destinataire admin : `contact@saxho.net` (compte email distinct)
- Domaine email Hostinger : `mail.saxho.net`

**Logging** : `app_log()` ecrit dans `_app/logs/smtp.log` (pas dans les logs systeme).
Chaque etape SMTP est loguee : connexion, banner, EHLO, AUTH, MAIL FROM, RCPT TO, DATA, resultat.

**CSRF et formulaire contact** : `csrf_verify()` detruit le token. Toutes les reponses API
(succes ET erreur) doivent retourner un nouveau token via `csrf_token()`. Le JS met a jour
le champ CSRF apres chaque soumission.

### 1.9 SSH depuis macOS

**Probleme** : La commande `ssh` n'est pas dans le PATH par defaut du Terminal sur certains Mac.

**Solution** : Utiliser le chemin complet `/usr/bin/ssh`.

**Connexion Hostinger** :
```bash
/usr/bin/ssh -p 65002 u473667317@191.96.63.86
```

**Alternative** : Creer des endpoints web securises par token pour diagnostiquer a distance
(ce qu'on a fait pour le SMTP). Toujours supprimer ces endpoints apres usage.

---

## 2. Conventions de code

### 2.1 CSS

- **Methodologie** : BEM (Block__Element--Modifier)
  - Exemples : `.paradox__card`, `.nav__link--active`, `.section--dark`
- **Variables** : Toutes dans `variables.css`. Prefixes : `--c-` (couleurs), `--fs-` (tailles), `--sp-` (espacement), `--ff-` (polices), `--fw-` (graisses)
- **Pas de `!important`** — jamais
- **Animations** : Keyframes dans `animations.css`. Classes `.reveal`, `.reveal-delay-1` a `.reveal-delay-5`
- **Responsive** : Media queries dans `responsive.css` + overrides dans les fichiers per-page si necessaire (voir piege 1.1)

### 2.2 PHP

- **Convention page** : Chaque page est un fichier dans `pages/`. En haut du fichier, declarer les CSS/JS specifiques :
  ```php
  <?php $pageCss = 'services.css'; $pageJs = 'contact-form.js'; ?>
  ```
- **Traductions** : Utiliser `t('cle.sous-cle')` pour traduire, `e()` pour echapper le HTML
  ```php
  <?= e(t('nav.about')) ?>
  ```
- **Parametres** : `t('footer.copyright', ['year' => date('Y')])` pour les traductions avec variables
- **Slug courant** : `$currentSlug` est disponible dans toutes les pages (defini par le router)
- **Langue courante** : `$lang` est disponible (defini par `i18n.php`)

### 2.3 JavaScript

- **Style** : IIFE (Immediately Invoked Function Expression) pour encapsuler
  ```javascript
  (function() {
      'use strict';
      var element = document.querySelector('.foo');
      // ...
  })();
  ```
- **Variables** : `var` uniquement (pas `let`/`const`) — compatibilite navigateurs anciens
- **Pas de bundler** : Fichiers JS individuels charges dans `index.php`
- **Pas de framework** : JavaScript vanilla uniquement

### 2.4 Traductions (i18n)

- Fichiers : `_app/lang/fr.json` et `_app/lang/en.json`
- Structure : cles en dot notation
  ```json
  {
      "nav": {
          "home": "Accueil",
          "about": "A propos"
      },
      "hero": {
          "title": "L'innovation d'aujourd'hui..."
      }
  }
  ```
- Acces en PHP : `t('nav.home')` retourne la traduction, `t('cle', ['var' => 'val'])` avec variables

### 2.5 Routing

- Defini dans `_app/includes/router.php`
- Routes publiques : `/`, `/about`, `/services`, `/portfolio`, `/project`, `/blog`, `/contact`
- Routes auth : `/login`, `/register`, `/verify-email`, `/forgot-password`, `/reset-password`, `/mfa-setup`, `/mfa-verify`, `/profile`
- Routes legales : `/legal`, `/privacy`
- Routes dynamiques : `blog/{slug}`, `project/{slug}`
- API : `api/contact`, `api/interest`, `api/auth/*`

---

## 3. Decisions de design validees

### 3.1 Logo

**Decision finale** : Texte "saxho" en Outfit 600, le "o" en couleur accent `#A63D6B`.

**Historique** : Un logo PNG avec ampoule remplacant le "o" a ete teste (fichiers `logo-noir.png`
et `logo-blanc.png`). Rejete car :
- Disproportionne a petite taille (header mobile/desktop)
- Trop "startup" pour un positionnement cabinet
- Problemes de double affichage (2 versions noir/blanc a gerer)

Les PNGs restent dans `assets/img/` pour usage print/reseaux sociaux.

### 3.2 Canvas neural (hero)

- **Scope** : Section `.hero` uniquement (pas sur toute la page)
- **Interaction** : Les noeuds reagissent au mouvement de la souris (attraction/repulsion)
- **Crackle** : Fonction `launchEdgeCrackle()` dans `neural.js` — declenchee au hover des cartes `.paradox__card`, envoie des pulses sur les noeuds du bas du canvas
- **Performance** : Canvas redimensionne au resize, animation stoppee si pas visible (IntersectionObserver)

### 3.3 Animation de fond — Flow field (vagues)

**Fichier** : `assets/js/flow-field.js` (charge sur la page Services)

**Couleurs** : 2 couleurs alternees (bleu profond + rose berry). On avait teste 3 couleurs
(ajout bleu clair #3A7DFF) mais la 3e couleur etait invisible sur fond clair → supprimee.

**Opacites** : `waveBaseOpacity: 0.20`, `ambientBaseOpacity: 0.12` — augmentees depuis les
valeurs initiales pour meilleure visibilite.

**Principe** : Les vagues n'utilisent PAS l'opacite CSS (qui s'applique sur tout le canvas).
L'opacite est controlee entierement en JS via `fillStyle` avec rgba.

### 3.4 Animation de fond — Bulb Field (ampoules-bulles)

**Fichier** : `assets/js/bulb-field.js` (charge sur la homepage, section resolution/5 services)

**Concept** : Des contours d'ampoules minimalistes naissent, grandissent comme des bulles de savon
(deformation ondulante), puis eclatent en particules fines. Symbolise l'emergence des idees,
leur maturation et leur eclosion.

**Couleurs** : 3 couleurs en alternance cyclique :
- Bleu profond (#1B3A9E / `--c-primary`)
- Rose berry (#A63D6B / `--c-accent`)
- Or doux (#D49B50)

**Interaction souris** : Legere repulsion des bulbes + naissance de nouveaux bulbes au passage.

**Effets visuels** :
- Deformation type bulle de savon (wobble sinusoidal)
- Reflet irise (gradient multicolore subtil sur le contour)
- Filament interieur (dessin quand le bulbe est assez grand)
- Particules d'eclosion (arcs + traits fins)
- Flottement vers le haut (les idees montent)

**Performance** : IntersectionObserver pour stopper l'animation hors viewport.

**Canvas** : `#bulb-canvas` dans la section `.resolution` de `home.php`.

### 3.5 Stagger reveal

- Delays : 200ms / 450ms / 700ms / 950ms / 1200ms (classes `.reveal-delay-1` a `.reveal-delay-5`)
- L'ecart de ~250ms entre chaque element rend l'arrivee progressive bien perceptible
- **Avant** : 100/200/300ms — trop rapide, pas perceptible

### 3.4 Section paradoxe — responsive

| Ecran | Layout |
|-------|--------|
| iPhone (<768px) | 1 colonne empilee |
| iPad (768-1023px) | 3 colonnes cote a cote |
| Desktop (>1024px) | 3 colonnes cote a cote |

### 3.5 Header

- Position fixe, transparent sur la homepage hero
- Devient opaque (fond `#F8F7F4` floute) au scroll (`is-scrolled`) ou sur pages interieures (`header--light`)
- Texte : blanc sur hero, noir sur fond clair

---

## 4. Ordre de chargement des assets

### CSS (templates/head.php)

```
variables.css  ->  reset.css  ->  layout.css  ->  components.css  ->  animations.css  ->  responsive.css  ->  [$pageCss]
```

### JS (index.php, fin du <body>)

```
app.js  ->  animations.js  ->  [typed.js + neural.js + bulb-field.js si home]  ->  [$pageJs]
```

### Fonts (preload dans head.php)

Toutes en woff2, self-hosted dans `assets/fonts/` :
- Space Grotesk Variable
- Inter Variable
- JetBrains Mono Regular
- Outfit Variable

---

## 5. Fichiers cles a connaitre

| Fichier | Role | Quand le modifier |
|---------|------|-------------------|
| `_app/includes/router.php` | Definit toutes les routes | Quand on ajoute une nouvelle page |
| `templates/head.php` | Charge les CSS + fonts | Quand on ajoute un CSS ou change la version cache |
| `index.php` | Front controller + charge JS | Quand on ajoute un JS ou change la version cache |
| `assets/css/variables.css` | Design tokens | Quand on change couleurs, tailles, espacement |
| `assets/css/responsive.css` | Media queries globales | Quand on ajuste le responsive (attention cascade!) |
| `_app/lang/fr.json` | Traductions FR | Quand on ajoute du contenu textuel |
| `_app/lang/en.json` | Traductions EN | Idem en anglais |
| `_app/includes/functions.php` | CSRF, email SMTP, flash, slugify, app_log | Quand on ajoute des helpers |
| `api/contact.php` | Endpoint formulaire contact (JSON) | Quand on modifie le traitement contact |
| `assets/js/flow-field.js` | Animation vagues (services) | Quand on ajuste les vagues |
| `assets/js/contact-form.js` | Validation + AJAX contact | Quand on modifie le formulaire |
| `_app/logs/smtp.log` | Log SMTP (gitignore) | Consultable pour debug email |
