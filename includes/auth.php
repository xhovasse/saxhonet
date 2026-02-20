<?php
/**
 * Helpers d'authentification et de session
 */

/**
 * Initialiser la session de maniere securisee
 */
function init_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }

    session_name(SESSION_NAME);
    session_start();

    // Verifier l'expiration de session
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
            session_unset();
            session_destroy();
            session_start();
        }
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Verifier si l'utilisateur est connecte
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['auth_complete']);
}

/**
 * Verifier si l'utilisateur est admin
 */
function is_admin(): bool
{
    return is_logged_in() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Recuperer l'ID de l'utilisateur connecte
 */
function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Recuperer les infos de l'utilisateur connecte depuis la BDD
 */
function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
        $stmt->execute([current_user_id()]);
        $user = $stmt->fetch();
    }
    return $user ?: null;
}

/**
 * Creer une session apres authentification complete
 */
function create_session(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['auth_complete'] = true;
    $_SESSION['last_activity'] = time();

    // Mettre a jour le dernier login
    $db = getDB();
    $stmt = $db->prepare('UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?');
    $stmt->execute([$user['id']]);
}

/**
 * Detruire la session (logout)
 */
function destroy_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Exiger une connexion (rediriger sinon)
 */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Connexion requise pour acceder a cette page.');
        redirect(SITE_URL . '/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Exiger un acces admin (rediriger sinon)
 */
function require_admin(): void
{
    if (!is_admin()) {
        http_response_code(403);
        exit('Acces refuse.');
    }
}
