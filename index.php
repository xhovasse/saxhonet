<?php
/**
 * Saxho.net — Front Controller
 * Toutes les requêtes passent par ce fichier
 */

// TEMPORAIRE : forcer affichage erreurs pour debug deploiement
// A RETIRER une fois le site fonctionnel
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Charger l'infrastructure
require_once __DIR__ . '/_app/includes/config.php';
require_once __DIR__ . '/_app/includes/db.php';
require_once __DIR__ . '/_app/includes/i18n.php';
require_once __DIR__ . '/_app/includes/functions.php';
require_once __DIR__ . '/_app/includes/auth.php';
require_once __DIR__ . '/_app/includes/router.php';

// Initialiser la session
init_session();

// Detecter la langue
$lang = detect_language();
$translations = load_translations($lang);

// Resoudre la route
$route = resolve_route();

// Si c'est une route API, executer directement (pas de layout HTML)
if (!empty($route['api'])) {
    $apiFile = __DIR__ . '/' . $route['page'];
    if (file_exists($apiFile)) {
        require $apiFile;
    } else {
        json_response(['error' => 'Endpoint not found'], 404);
    }
    exit;
}

// Variables de page pour le template
$pageTitle = '';
if (!empty($route['title'])) {
    // Si c'est une cle i18n, traduire
    $translated = t($route['title']);
    $pageTitle = ($translated !== $route['title']) ? $translated : $route['title'];
}
$pageDescription = t('site.description');
$pageOgImage = SITE_URL . '/assets/img/og-image.jpg';
$currentSlug = $route['slug'] ?? '';
$routeParams = $route['params'] ?? [];

// 404
if (!empty($route['is_404'])) {
    http_response_code(404);
}

// Construire la page avec le layout
$pageFile = __DIR__ . '/pages/' . $route['page'];
if (!file_exists($pageFile)) {
    // Fallback vers 404
    http_response_code(404);
    $pageFile = __DIR__ . '/pages/404.php';
    $pageTitle = '404';
}

// Pre-rendre la page dans un buffer pour capturer $pageCss, $pageDescription, etc.
// (ces variables sont definies dans les fichiers de pages)
$pageCss = '';
$pageJs = '';
$pageContent = '';

// Utiliser un error handler pour capturer les erreurs fatales dans le buffer
$_pageRenderError = null;
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$_pageRenderError) {
    $_pageRenderError = "PHP Error [$errno]: $errstr in $errfile on line $errline";
    return false; // Laisser aussi le handler par defaut s'en occuper
});

ob_start();
try {
    if (file_exists($pageFile)) {
        include $pageFile;
    } else {
        echo '<div class="container" style="padding: 100px 0; text-align: center;"><h1>Page en construction</h1></div>';
    }
} catch (\Throwable $e) {
    $_pageRenderError = 'Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
}
$pageContent = ob_get_clean() ?: '';
restore_error_handler();

// Si le contenu est vide et qu'il y a eu une erreur, afficher un message utile
if (empty(trim($pageContent)) && $_pageRenderError) {
    $pageContent = '<div class="container" style="padding:100px 20px;text-align:center;">'
        . '<h1 style="color:#EF4444;">Erreur de rendu</h1>'
        . '<p>' . htmlspecialchars($_pageRenderError) . '</p>'
        . '</div>';
} elseif (empty(trim($pageContent))) {
    $pageContent = '<div class="container" style="padding:100px 20px;text-align:center;">'
        . '<h1>Contenu vide</h1>'
        . '<p>Le fichier page a ete trouve mais n\'a rien produit.</p>'
        . '<p>Fichier : ' . htmlspecialchars($pageFile) . '</p>'
        . '<p>Existe : ' . (file_exists($pageFile) ? 'OUI' : 'NON') . '</p>'
        . '</div>';
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<?php include __DIR__ . '/templates/head.php'; ?>
<body>
    <a href="#main" class="skip-link"><?= $lang === 'fr' ? 'Aller au contenu' : 'Skip to content' ?></a>

    <?php include __DIR__ . '/templates/header.php'; ?>

    <?= render_flash() ?>

    <main id="main">
        <?= $pageContent ?>
    </main>

    <?php include __DIR__ . '/templates/footer.php'; ?>

    <script src="<?= SITE_URL ?>/assets/js/app.js?v=1.0"></script>
    <script src="<?= SITE_URL ?>/assets/js/animations.js?v=1.0"></script>
    <?php if ($currentSlug === '' || $currentSlug === 'home'): ?>
    <script src="<?= SITE_URL ?>/assets/js/typed.js?v=1.0"></script>
    <?php endif; ?>
    <?php if (!empty($pageJs)): ?>
    <script src="<?= SITE_URL ?>/assets/js/<?= $pageJs ?>?v=1.0"></script>
    <?php endif; ?>
</body>
</html>
