<?php
/**
 * Saxho.net — API: User login
 * POST /api/auth/login
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

// Extract inputs
$email      = trim($input['email'] ?? '');
$password   = $input['password'] ?? '';
$rememberMe = !empty($input['remember_me']);

// Basic validation
if ($email === '' || $password === '') {
    json_response([
        'success'    => false,
        'error'      => t('auth.error_credentials'),
        'csrf_token' => csrf_token(),
    ], 422);
}

// Find user by email
$db   = getDB();
$stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // User not found — generic error (anti-enumeration)
    json_response([
        'success'    => false,
        'error'      => t('auth.error_credentials'),
        'csrf_token' => csrf_token(),
    ], 401);
}

// Check account lockout
if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
    $remainingSeconds = strtotime($user['locked_until']) - time();
    $remainingMinutes = max(1, (int) ceil($remainingSeconds / 60));
    json_response([
        'success'    => false,
        'error'      => t('auth.error_locked', ['minutes' => $remainingMinutes]),
        'csrf_token' => csrf_token(),
    ], 429);
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    // Increment login attempts
    $attempts = (int) $user['login_attempts'] + 1;

    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        // Lock the account
        $lockUntil = date('Y-m-d H:i:s', time() + LOCKOUT_DURATION);
        $stmt = $db->prepare('UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?');
        $stmt->execute([$attempts, $lockUntil, $user['id']]);

        $remainingMinutes = max(1, (int) ceil(LOCKOUT_DURATION / 60));
        json_response([
            'success'    => false,
            'error'      => t('auth.error_locked', ['minutes' => $remainingMinutes]),
            'csrf_token' => csrf_token(),
        ], 429);
    } else {
        $stmt = $db->prepare('UPDATE users SET login_attempts = ? WHERE id = ?');
        $stmt->execute([$attempts, $user['id']]);
    }

    json_response([
        'success'    => false,
        'error'      => t('auth.error_credentials'),
        'csrf_token' => csrf_token(),
    ], 401);
}

// Password is correct — check email verified
if (!$user['email_verified']) {
    json_response([
        'success'    => false,
        'error'      => t('auth.error_not_verified'),
        'csrf_token' => csrf_token(),
    ], 403);
}

// Determine redirect destination
$redirect = trim($input['redirect'] ?? '');
if ($redirect === '' || !str_starts_with($redirect, '/')) {
    $redirect = '/';
}

// Check MFA
if ($user['mfa_enabled']) {
    // MFA enabled — start partial session, redirect to MFA verify
    start_login_session($user);
    // Store redirect for after MFA completion
    $_SESSION['login_redirect'] = $redirect;

    json_response([
        'success'    => true,
        'mfa'        => true,
        'redirect'   => '/mfa-verify',
        'csrf_token' => csrf_token(),
    ]);
}

// No MFA — create full session
create_session($user);

// Handle remember me
if ($rememberMe) {
    $rememberToken = bin2hex(random_bytes(32));
    $hashedToken   = hash('sha256', $rememberToken);
    $stmt = $db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
    $stmt->execute([$hashedToken, $user['id']]);

    setcookie('saxho_remember', $rememberToken, [
        'expires'  => time() + REMEMBER_LIFETIME,
        'path'     => '/',
        'secure'   => true,
        'httponly'  => true,
        'samesite' => 'Strict',
    ]);
}

json_response([
    'success'    => true,
    'redirect'   => $redirect,
    'csrf_token' => csrf_token(),
]);
