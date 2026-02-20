<?php
/**
 * Saxho.net — API: Forgot password (request reset link)
 * POST /api/auth/forgot-password
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

// Extract email
$email = trim($input['email'] ?? '');

// Basic email validation (but always return the same success message)
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Still return success to prevent enumeration
    json_response([
        'success'    => true,
        'message'    => t('auth.forgot_sent'),
        'csrf_token' => csrf_token(),
    ]);
}

// Look up the user
$db   = getDB();
$stmt = $db->prepare('SELECT id, email, first_name FROM users WHERE email = ? AND email_verified = 1 AND is_active = 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Generate a plain-text token (sent in the email link)
    $token = bin2hex(random_bytes(32));

    // Hash the token for database storage
    $hashedToken = hash('sha256', $token);
    $expiresAt   = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Invalidate any previous unused reset tokens for this user
    $stmt = $db->prepare('UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0');
    $stmt->execute([$user['id']]);

    // Insert new reset token
    $stmt = $db->prepare(
        'INSERT INTO password_resets (user_id, token, expires_at, created_at)
         VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$user['id'], $hashedToken, $expiresAt]);

    // Build reset link (plain-text token in URL, hashed in DB)
    $resetUrl = SITE_URL . '/reset-password?token=' . $token . '&email=' . urlencode($user['email']);

    // Send email
    $emailSubject = SITE_NAME . ' — ' . t('auth.reset_password');
    $emailContent = '<p>' . t('auth.forgot_password_title') . '</p>'
        . '<p>' . e($user['first_name']) . ', ' . t('auth.verify_check_inbox') . '</p>';

    $emailBody = email_template(
        t('auth.reset_password'),
        $emailContent,
        $resetUrl,
        t('auth.reset_password')
    );

    $emailSent = send_email($user['email'], $emailSubject, $emailBody);
    if (!$emailSent) {
        error_log('Password reset email failed for: ' . $user['email']);
    }
}

// Always return the same response (anti-enumeration)
json_response([
    'success'    => true,
    'message'    => t('auth.forgot_sent'),
    'csrf_token' => csrf_token(),
]);
