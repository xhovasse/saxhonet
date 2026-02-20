<?php
/**
 * Routeur simple
 * Mappe les URLs propres vers les fichiers de pages
 */

/**
 * Definir les routes disponibles
 */
function get_routes(): array
{
    return [
        // Pages publiques
        ''            => ['page' => 'home.php',           'title' => 'nav.home'],
        'about'       => ['page' => 'about.php',          'title' => 'nav.about'],
        'services'    => ['page' => 'services.php',       'title' => 'nav.services'],
        'portfolio'   => ['page' => 'portfolio.php',      'title' => 'nav.portfolio'],
        'project'     => ['page' => 'project.php',        'title' => 'Portfolio'],
        'blog'        => ['page' => 'blog.php',           'title' => 'nav.blog'],
        'blog-article'=> ['page' => 'blog-article.php',   'title' => 'Blog'],
        'contact'     => ['page' => 'contact.php',        'title' => 'nav.contact'],

        // Authentification
        'login'       => ['page' => 'auth/login.php',         'title' => 'auth.login'],
        'register'    => ['page' => 'auth/register.php',      'title' => 'auth.register'],
        'verify-email'=> ['page' => 'auth/verify-email.php',  'title' => 'auth.verify_email'],
        'forgot-password' => ['page' => 'auth/forgot-password.php', 'title' => 'auth.forgot_password'],
        'reset-password'  => ['page' => 'auth/reset-password.php',  'title' => 'auth.reset_password'],
        'mfa-setup'   => ['page' => 'auth/mfa-setup.php',     'title' => 'auth.mfa_setup'],
        'mfa-verify'  => ['page' => 'auth/mfa-verify.php',    'title' => 'auth.mfa_verify'],
        'profile'     => ['page' => 'auth/profile.php',       'title' => 'auth.profile'],

        // Pages legales
        'legal'       => ['page' => 'legal.php',          'title' => 'Mentions legales'],
        'privacy'     => ['page' => 'privacy.php',        'title' => 'Politique de confidentialite'],

        // API (retournent du JSON, pas de layout)
        'api/contact'     => ['page' => 'api/contact.php',     'api' => true],
        'api/interest'    => ['page' => 'api/interest.php',    'api' => true],
        'api/auth/register'      => ['page' => 'api/auth/register.php',      'api' => true],
        'api/auth/login'         => ['page' => 'api/auth/login.php',         'api' => true],
        'api/auth/logout'        => ['page' => 'api/auth/logout.php',        'api' => true],
        'api/auth/verify-email'  => ['page' => 'api/auth/verify-email.php',  'api' => true],
        'api/auth/forgot-password' => ['page' => 'api/auth/forgot-password.php', 'api' => true],
        'api/auth/reset-password'  => ['page' => 'api/auth/reset-password.php',  'api' => true],
        'api/auth/mfa-setup'     => ['page' => 'api/auth/mfa-setup.php',     'api' => true],
        'api/auth/mfa-verify'    => ['page' => 'api/auth/mfa-verify.php',    'api' => true],
        'api/auth/profile'       => ['page' => 'api/auth/profile.php',       'api' => true],
    ];
}

/**
 * Extraire le slug de route depuis l'URL
 */
function parse_route(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    // Retirer la query string
    $uri = strtok($uri, '?');

    // Retirer le prefixe du sous-repertoire si necessaire
    $basePath = parse_url(SITE_URL, PHP_URL_PATH) ?? '';
    if ($basePath && strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }

    // Nettoyer
    $uri = trim($uri, '/');

    return $uri;
}

/**
 * Resoudre la route courante
 */
function resolve_route(): array
{
    $slug = parse_route();
    $routes = get_routes();

    // Route exacte
    if (isset($routes[$slug])) {
        return array_merge($routes[$slug], ['slug' => $slug]);
    }

    // Route dynamique : blog/{slug}
    if (preg_match('#^blog/([a-z0-9\-]+)$#', $slug, $matches)) {
        return [
            'page'  => 'blog-article.php',
            'title' => 'Blog',
            'slug'  => $slug,
            'params' => ['article_slug' => $matches[1]],
        ];
    }

    // Route dynamique : project/{slug}
    if (preg_match('#^project/([a-z0-9\-]+)$#', $slug, $matches)) {
        return [
            'page'  => 'project.php',
            'title' => 'Portfolio',
            'slug'  => $slug,
            'params' => ['project_slug' => $matches[1]],
        ];
    }

    // 404
    return [
        'page'  => '404.php',
        'title' => '404',
        'slug'  => $slug,
        'is_404' => true,
    ];
}
