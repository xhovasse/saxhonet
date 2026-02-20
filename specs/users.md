# Spécifications — Système utilisateurs & authentification

## Vue d'ensemble des rôles

| Rôle | Accès | Description |
|------|-------|-------------|
| **Visiteur** | Public | Navigue le site, voit le portfolio en mode flouté |
| **Membre** | Authentifié | Portfolio débloqué (contenu générique, non confidentiel), peut exprimer un intérêt |
| **Administrateur** | Back-office | Gestion complète : projets, utilisateurs, contenus, demandes |

---

## 1. Administrateur (back-office)

### Accès
- URL séparée : `/admin/`
- Authentification renforcée (login + mot de passe + MFA)
- Un seul administrateur principal (Xavier Hovasse), extensible si besoin

### Fonctionnalités
- **Gestion des projets portfolio** : créer, modifier, supprimer, changer le statut/visibilité
- **Gestion des utilisateurs** : visualiser la liste des membres inscrits, consulter leurs profils, révoquer des accès
- **Gestion des demandes** : consulter les demandes de contribution et d'investissement reçues, avec les coordonnées complétées par le membre
- **Gestion du blog** : créer, modifier, publier, supprimer des articles
- **Gestion du contenu** : modifier les textes du site (via le CMS)
- **Tableau de bord** : statistiques basiques (nombre d'inscrits, demandes en cours, projets actifs)

---

## 2. Inscription d'un visiteur

### Parcours d'inscription

```
Visiteur → Clic "S'inscrire" → Formulaire d'inscription → Validation email → Création MFA → Compte actif
```

### Formulaire d'inscription

| Champ | Obligatoire | Type | Notes |
|-------|:-----------:|------|-------|
| Prénom | Oui | VARCHAR(100) | |
| Nom | Oui | VARCHAR(100) | |
| Email | Oui | VARCHAR(255) | Unique, sert de login |
| Mot de passe | Oui | VARCHAR(255) | Hashé (bcrypt/argon2), min 8 car., 1 majuscule, 1 chiffre |
| Entreprise | Non | VARCHAR(255) | Nom de la société |
| Fonction / Poste | Non | VARCHAR(255) | |
| Téléphone | Non | VARCHAR(20) | |
| Pays | Non | VARCHAR(100) | |
| Acceptation CGU | Oui | BOOLEAN | Case à cocher obligatoire |

### Validation email
- Un email de confirmation est envoyé avec un lien de validation (token à durée limitée, 24h)
- Le compte n'est actif qu'après validation du lien
- Possibilité de renvoyer le lien

### MFA (Authentification multi-facteurs)
- Mise en place après la première connexion (ou pendant l'inscription)
- Méthode : TOTP (Time-based One-Time Password) — compatible Google Authenticator, Authy, etc.
- Affichage d'un QR code à scanner + code de secours à sauvegarder
- MFA obligatoire pour tous les membres (données sensibles sur le portfolio)

### Sécurité
- Protection contre le brute force : limitation des tentatives de connexion (5 essais, puis blocage temporaire 15 min)
- Tokens de session sécurisés (httpOnly, secure, SameSite)
- CSRF protection sur tous les formulaires
- Mots de passe hashés avec bcrypt ou argon2id
- Sessions expirées après inactivité (30 min configurable)

---

## 3. Connexion d'un membre

### Parcours de connexion

```
Membre → Login (email + mot de passe) → Vérification MFA (code TOTP) → Session active
```

### Fonctionnalités disponibles
- Mot de passe oublié (lien de réinitialisation par email, token à durée limitée)
- "Se souvenir de moi" (cookie sécurisé, durée configurable, 30 jours max)
- Déconnexion explicite

---

## 4. Expérience membre connecté

### Portfolio débloqué

Une fois connecté, le membre voit les projets **sans flou** avec le contenu complet stocké en BDD :

| Information | Visiteur (flouté) | Membre (visible) |
|-------------|:-----------------:|:----------------:|
| Domaine / secteur | ✅ Visible | ✅ Visible |
| Phase générique | ✅ Visible | ✅ Visible |
| Pictogramme | ✅ Visible | ✅ Visible |
| Nom du projet | ❌ Flouté | ✅ Visible |
| Pitch / description | ❌ Flouté | ✅ Visible |
| Marché cible | ❌ Flouté | ✅ Visible |
| Problème adressé | ❌ Flouté | ✅ Visible |
| Solution proposée | ❌ Flouté | ✅ Visible |
| Détail d'avancement | ❌ Flouté | ✅ Visible |
| Investissement recherché | ❌ Flouté | ✅ Visible |
| Compétences recherchées | ❌ Flouté | ✅ Visible |

> **Important** : Même en mode membre, le contenu reste **générique et non confidentiel**. Aucun secret industriel, aucune donnée propriétaire n'est exposée sur la plateforme. Les détails confidentiels ne sont partagés qu'après signature d'un NDA, hors plateforme.

### Actions disponibles sur chaque projet

Deux boutons distincts sur chaque fiche projet :

#### Bouton 1 : "Contribuer en compétences"
Pour les personnes qui souhaitent apporter leur expertise, leur savoir-faire ou leur temps de travail au projet.

#### Bouton 2 : "Contribuer en investissement"
Pour les personnes qui souhaitent participer au financement du développement du projet.

> La distinction entre les deux est visuelle et claire : deux boutons différents, deux icônes différentes, deux couleurs différentes.

---

## 5. Processus d'expression d'intérêt

### Déclenchement
Le membre clique sur "Contribuer en compétences" ou "Contribuer en investissement" sur un projet.

### Formulaire d'expression d'intérêt

Le formulaire est **pré-rempli** avec les informations connues du membre (issues de son inscription).

| Champ | Pré-rempli | Obligatoire | Notes |
|-------|:----------:|:-----------:|-------|
| Prénom | ✅ Oui | Oui | Depuis le profil |
| Nom | ✅ Oui | Oui | Depuis le profil |
| Email | ✅ Oui | Oui | Depuis le profil |
| Entreprise | ✅ Si renseigné | Oui | Obligatoire à cette étape |
| Fonction / Poste | ✅ Si renseigné | Oui | Obligatoire à cette étape |
| Téléphone | ✅ Si renseigné | Oui | Obligatoire à cette étape |
| Adresse postale | ❌ Non | Oui | Nouveau champ, nécessaire pour le NDA |
| Pays | ✅ Si renseigné | Oui | |
| Projet concerné | ✅ Automatique | Oui | Nom du projet pré-sélectionné |
| Type de contribution | ✅ Automatique | Oui | "Compétences" ou "Investissement" selon le bouton cliqué |
| Message / motivation | ❌ Non | Non | Champ libre pour décrire son intérêt |

**Si type = Compétences** (champs supplémentaires) :
| Champ | Obligatoire | Notes |
|-------|:-----------:|-------|
| Domaine d'expertise | Oui | Texte libre ou sélection multiple |
| Disponibilité | Non | Ex: "2 jours/semaine", "mission ponctuelle" |
| Lien LinkedIn / CV | Non | URL ou upload |

**Si type = Investissement** (champs supplémentaires) :
| Champ | Obligatoire | Notes |
|-------|:-----------:|-------|
| Fourchette d'investissement envisagée | Non | Select : < 10k€ / 10-50k€ / 50-100k€ / > 100k€ / À discuter |
| Expérience investissement | Non | Texte libre |
| Structure d'investissement | Non | Nom de fonds, BA, personnel, etc. |

### Après soumission

1. **Stockage en BDD** : la demande est enregistrée avec tous les champs + date + projet + type
2. **Email à l'administrateur** : notification avec le résumé de la demande
3. **Email au membre** : accusé de réception confirmant que sa demande a été prise en compte
4. **Message affiché** : "Merci pour votre intérêt. Nous allons prendre contact avec vous prochainement. Un accord de confidentialité (NDA) vous sera transmis avant tout échange d'informations détaillées."

### Suite du processus (hors plateforme)

```
Demande reçue → Contact par Saxho → Envoi NDA → Signature NDA → Échanges confidentiels
```

Ce processus se fait **en dehors de la plateforme** : échanges par email, appels, rencontres. La plateforme ne sert qu'à initier le contact de manière structurée.

---

## 6. Profil membre

### Page "Mon profil"

Le membre peut :
- Consulter et modifier ses informations personnelles
- Changer son mot de passe
- Gérer son MFA (regénérer les codes de secours)
- Voir l'historique de ses demandes d'intérêt (projet, type, date, statut)
- Supprimer son compte

### Statuts des demandes (visibles par le membre)
- **Envoyée** — Demande soumise, en attente de traitement
- **En cours** — Saxho a pris contact
- **Finalisée** — NDA signé ou processus abouti
- **Déclinée** — Demande non retenue

> Le statut est mis à jour manuellement par l'administrateur dans le back-office.

---

## 7. Schéma de base de données (tables utilisateurs)

### Table `users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(255) DEFAULT NULL,
    job_title VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    role ENUM('member', 'admin') DEFAULT 'member',
    email_verified TINYINT(1) DEFAULT 0,
    email_token VARCHAR(255) DEFAULT NULL,
    email_token_expires DATETIME DEFAULT NULL,
    mfa_secret VARCHAR(255) DEFAULT NULL,
    mfa_enabled TINYINT(1) DEFAULT 0,
    mfa_backup_codes TEXT DEFAULT NULL,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1
);
```

### Table `interest_requests`
```sql
CREATE TABLE interest_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    type ENUM('competence', 'investment') NOT NULL,
    -- Coordonnées (snapshot au moment de la demande)
    contact_company VARCHAR(255) NOT NULL,
    contact_job_title VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_address TEXT NOT NULL,
    contact_country VARCHAR(100) NOT NULL,
    -- Détails
    message TEXT DEFAULT NULL,
    -- Si compétences
    expertise_domain TEXT DEFAULT NULL,
    availability VARCHAR(255) DEFAULT NULL,
    linkedin_cv_url VARCHAR(500) DEFAULT NULL,
    -- Si investissement
    investment_range ENUM('less_10k', '10k_50k', '50k_100k', 'more_100k', 'to_discuss') DEFAULT NULL,
    investment_experience TEXT DEFAULT NULL,
    investment_structure VARCHAR(255) DEFAULT NULL,
    -- Suivi
    status ENUM('submitted', 'in_progress', 'finalized', 'declined') DEFAULT 'submitted',
    admin_notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

### Table `password_resets`
```sql
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Table `sessions`
```sql
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    data TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
