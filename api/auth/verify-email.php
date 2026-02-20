<?php
/**
 * Saxho.net — API: Email verification
 * POST /api/auth/verify-email
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

// Resend verification email (from the verify-email page)
$resendEmail = trim($input['email'] ?? '');
if ($resendEmail !== '' && empty($input['token'])) {
    // Anti-enumeration: always return success
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT id, email, first_name FROM users WHERE email = ? AND email_verified = 0 AND is_active = 1'
    );
    $stmt->execute([strtolower($resendEmail)]);
    $resendUser = $stmt->fetch();

    if ($resendUser) {
        $newToken = bin2hex(random_bytes(32));
        $expires  = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $stmt = $db->prepare('UPDATE users SET email_token = ?, email_token_expires = ? WHERE id = ?');
        $stmt->execute([$newToken, $expires, $resendUser['id']]);

        $verifyUrl = SITE_URL . '/verify-email?token=' . $newToken . '&email=' . urlencode($resendUser['email']);
        $emailBody = email_template(
            t('auth.verify_email'),
            '<p>' . t('auth.verify_pending', ['email' => e($resendUser['email'])]) . '</p>'
                . '<p>' . t('auth.verify_check_inbox') . '</p>',
            $verifyUrl,
            t('auth.verify_button')
        );
        send_email($resendUser['email'], SITE_NAME . ' — ' . t('auth.verify_email'), $emailBody);
    }

    json_response([
        'success'    => true,
        'message'    => t('auth.forgot_sent'), // Message neutre anti-enumeration
        'csrf_token' => csrf_token(),
    ]);
}

// Extract token
$token = trim($input['token'] ?? '');

if ($token === '') {
    json_response([
        'success'    => false,
        'error'      => t('auth.error_token_invalid'),
        'csrf_token' => csrf_token(),
    ], 422);
}

// Find user with valid, unexpired token
$db   = getDB();
$stmt = $db->prepare(
    'SELECT id FROM users
     WHERE email_token = ? AND email_token_expires > NOW() AND email_verified = 0'
);
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    json_response([
        'success'    => false,
        'error'      => t('auth.error_token_invalid'),
        'csrf_token' => csrf_token(),
    ], 400);
}

// Mark email as verified and clear token
$stmt = $db->prepare(
    'UPDATE users SET email_verified = 1, email_token = NULL, email_token_expires = NULL WHERE id = ?'
);
$stmt->execute([$user['id']]);

json_response([
    'success'    => true,
    'message'    => t('auth.verify_success'),
    'redirect'   => '/login',
    'csrf_token' => csrf_token(),
]);
