<?php
/**
 * Saxho.net — Configuration
 * Copier ce fichier en config.php et renseigner les valeurs
 */

// Base de donnees
define('DB_HOST', 'localhost');
define('DB_NAME', 'u473667317_saxhonet');
define('DB_USER', 'u473667317_admin');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site
define('SITE_URL', 'https://saxho.net');
define('SITE_NAME', 'Saxho');
define('SITE_TAGLINE', 'De l\'idee au succes');
define('ADMIN_EMAIL', 'contact@saxho.net');

// Chemins
define('ROOT_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', ROOT_PATH);
define('INCLUDES_PATH', ROOT_PATH . '/_app/includes');
define('LANG_PATH', ROOT_PATH . '/_app/lang');
define('UPLOAD_PATH', ROOT_PATH . '/assets/img/uploads');
define('UPLOAD_URL', SITE_URL . '/assets/img/uploads');

// Langue
define('DEFAULT_LANG', 'fr');
define('SUPPORTED_LANGS', ['fr', 'en']);

// Sessions & securite
define('SESSION_LIFETIME', 1800); // 30 min
define('SESSION_NAME', 'saxho_session');
define('REMEMBER_LIFETIME', 2592000); // 30 jours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 min
define('CSRF_TOKEN_NAME', 'csrf_token');

// MFA
define('MFA_ISSUER', 'Saxho');

// Email (SMTP Hostinger — SSL port 465)
define('USE_SMTP', true);
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USER', 'contact@mail.saxho.net');
define('SMTP_PASS', ''); // A REMPLIR
define('SMTP_FROM', 'contact@mail.saxho.net');
define('SMTP_FROM_NAME', 'Saxho');

// Upload
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Environnement
define('APP_ENV', 'development'); // 'development' ou 'production'
define('APP_DEBUG', true);
