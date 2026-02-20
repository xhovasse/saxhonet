<?php
/**
 * Saxho.net — API: Reset password
 * POST /api/auth/reset-password
 *
 * Validates the reset token and sets a new password.
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
    json_response(['error' => t('auth.error_generic')], 403);
}

// Read input
$token           = trim($input['token'] ?? '');
$email           = trim($input['email'] ?? '');
$password        = $input['password'] ?? '';
$passwordConfirm = $input['password_confirm'] ?? '';

// Basic validation
$errors = [];

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = t('auth.error_email_invalid');
}

if ($token === '') {
    $errors['token'] = t('auth.error_token_invalid');
}

// Password validation: min 8 chars, 1 uppercase, 1 digit
if (mb_strlen($password) < 8) {
    $errors['password'] = t('auth.error_password_short');
} elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors['password'] = t('auth.error_password_weak');
}

if ($password !== $passwordConfirm) {
    $errors['password_confirm'] = t('auth.error_password_mismatch');
}

if (!empty($errors)) {
    json_response([
        'error'      => 'Validation failed',
        'errors'     => $errors,
        'csrf_token' => csrf_token(),
    ], 422);
}

$db = getDB();

// Find user by email
$stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND is_active = 1');
$stmt->execute([strtolower($email)]);
$user = $stmt->fetch();

if (!$user) {
    json_response([
        'error'      => t('auth.error_token_invalid'),
        'csrf_token' => csrf_token(),
    ], 400);
}

// Verify reset token: hash the raw token and match against stored hash
$tokenHash = hash('sha256', $token);

$stmt = $db->prepare(
    'SELECT id FROM password_resets
     WHERE user_id = ? AND token = ? AND expires_at > NOW() AND used = 0'
);
$stmt->execute([$user['id'], $tokenHash]);
$reset = $stmt->fetch();

if (!$reset) {
    json_response([
        'error'      => t('auth.error_token_invalid'),
        'csrf_token' => csrf_token(),
    ], 400);
}

// Update password and reset lockout
try {
    $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $stmt = $db->prepare(
        'UPDATE users SET password_hash = ?, login_attempts = 0, locked_until = NULL, updated_at = NOW()
         WHERE id = ?'
    );
    $stmt->execute([$newHash, $user['id']]);

    // Mark reset token as used
    $stmt = $db->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
    $stmt->execute([$reset['id']]);

    json_response([
        'success'    => true,
        'message'    => t('auth.reset_success'),
        'csrf_token' => csrf_token(),
    ]);
} catch (\PDOException $e) {
    error_log('Password reset failed: ' . $e->getMessage());
    json_response([
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}
