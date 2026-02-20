<?php
/**
 * Script unique pour creer config.php — SUPPRIMER APRES USAGE
 */

$configDir = __DIR__ . '/_app/includes';
$configPath = $configDir . '/config.php';

if (file_exists($configPath)) {
    echo "<h1 style='color:green;'>config.php existe deja !</h1>";
    echo "<p>Taille : " . filesize($configPath) . " octets</p>";
    echo "<p><strong>Supprimez ce fichier setup-config.php immediatement.</strong></p>";
    exit;
}

// Verifier que le formulaire a ete soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['db_pass'])) {
    $dbPass = $_POST['db_pass'];

    $configContent = '<?php
/**
 * Saxho.net — Configuration
 * FICHIER NON COMMITE (contient les secrets)
 */

// Base de donnees
define(\'DB_HOST\', \'localhost\');
define(\'DB_NAME\', \'u473667317_saxhonet\');
define(\'DB_USER\', \'u473667317_saxhosqladmin\');
define(\'DB_PASS\', \'' . addslashes($dbPass) . '\');
define(\'DB_CHARSET\', \'utf8mb4\');

// Site
define(\'SITE_URL\', \'https://saxho.net\');
define(\'SITE_NAME\', \'Saxho\');
define(\'SITE_TAGLINE\', \'De l\\\'idee au succes\');
define(\'ADMIN_EMAIL\', \'contact@saxho.net\');

// Chemins
define(\'ROOT_PATH\', dirname(__DIR__, 2));
define(\'PUBLIC_PATH\', ROOT_PATH);
define(\'INCLUDES_PATH\', ROOT_PATH . \'/_app/includes\');
define(\'LANG_PATH\', ROOT_PATH . \'/_app/lang\');
define(\'UPLOAD_PATH\', ROOT_PATH . \'/assets/img/uploads\');
define(\'UPLOAD_URL\', SITE_URL . \'/assets/img/uploads\');

// Langue
define(\'DEFAULT_LANG\', \'fr\');
define(\'SUPPORTED_LANGS\', [\'fr\', \'en\']);

// Sessions & securite
define(\'SESSION_LIFETIME\', 1800);
define(\'SESSION_NAME\', \'saxho_session\');
define(\'REMEMBER_LIFETIME\', 2592000);
define(\'MAX_LOGIN_ATTEMPTS\', 5);
define(\'LOCKOUT_DURATION\', 900);
define(\'CSRF_TOKEN_NAME\', \'csrf_token\');

// MFA
define(\'MFA_ISSUER\', \'Saxho\');

// Email
define(\'USE_SMTP\', false);
define(\'SMTP_HOST\', \'\');
define(\'SMTP_PORT\', 587);
define(\'SMTP_USER\', \'\');
define(\'SMTP_PASS\', \'\');
define(\'SMTP_FROM\', \'noreply@saxho.net\');
define(\'SMTP_FROM_NAME\', \'Saxho\');

// Upload
define(\'MAX_UPLOAD_SIZE\', 2 * 1024 * 1024);
define(\'ALLOWED_IMAGE_TYPES\', [\'image/jpeg\', \'image/png\', \'image/webp\']);

// Environnement
define(\'APP_ENV\', \'production\');
define(\'APP_DEBUG\', false);
';

    if (file_put_contents($configPath, $configContent)) {
        echo "<h1 style='color:green;'>&#10004; config.php cree avec succes !</h1>";
        echo "<p><strong>SUPPRIMEZ CE FICHIER setup-config.php MAINTENANT !</strong></p>";
        echo "<p><a href='/'>&#8594; Aller au site</a></p>";
    } else {
        echo "<h1 style='color:red;'>Erreur d'ecriture</h1>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Setup Config</title></head>
<body style="font-family:sans-serif;max-width:500px;margin:50px auto;">
<h1>Configuration Saxho.net</h1>
<form method="post">
    <label>Mot de passe MySQL :</label><br>
    <input type="password" name="db_pass" style="width:100%;padding:8px;margin:10px 0;" required><br>
    <button type="submit" style="padding:10px 20px;background:#1B3A9E;color:white;border:none;cursor:pointer;">Creer config.php</button>
</form>
</body>
</html>
