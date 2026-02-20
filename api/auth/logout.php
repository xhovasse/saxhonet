<?php
/**
 * Saxho.net — API: User logout
 * POST /api/auth/logout
 */

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
    $input = $_POST;
}

// CSRF verification
$csrfToken = $input[CSRF_TOKEN_NAME] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response([
        'success'    => false,
        'error'      => t('contact.error_csrf'),
        'csrf_token' => csrf_token(),
    ], 403);
}

// Clear remember me cookie if present
if (isset($_COOKIE['saxho_remember'])) {
    setcookie('saxho_remember', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => true,
        'httponly'  => true,
        'samesite' => 'Strict',
    ]);
}

// Clear remember token in database if user was logged in
if (is_logged_in()) {
    $db   = getDB();
    $stmt = $db->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
    $stmt->execute([current_user_id()]);
}

// Destroy session
destroy_session();

// Restart a clean session for the new CSRF token
init_session();

// Si requete AJAX, retourner du JSON
if (is_ajax()) {
    json_response([
        'success'    => true,
        'message'    => t('auth.logout_success'),
        'redirect'   => '/',
        'csrf_token' => csrf_token(),
    ]);
}

// Sinon (formulaire classique depuis le header), flash + redirect
flash('success', t('auth.logout_success'));
redirect(SITE_URL . '/');
