<?php
/**
 * Saxho.net — API: MFA verification (TOTP or backup code)
 * POST /api/auth/mfa-verify
 *
 * Requires MFA-pending session state (credentials OK, MFA not yet completed).
 */

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Require MFA-pending state (API-safe check — no redirect)
if (!is_mfa_pending()) {
    json_response(['error' => 'Authentication required'], 401);
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

$code = trim($input['code'] ?? '');
$type = $input['type'] ?? 'totp';

if ($code === '') {
    json_response([
        'error'      => t('auth.error_mfa_code'),
        'csrf_token' => csrf_token(),
    ], 422);
}

$db = getDB();

// Fetch user from session user_id
$stmt = $db->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    json_response([
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 400);
}

// ─────────────────────────────────────────────
// Type: totp — verify time-based code
// ─────────────────────────────────────────────
if ($type === 'totp') {
    if (!TOTP::verify($user['mfa_secret'], $code)) {
        json_response([
            'error'      => t('auth.error_mfa_code'),
            'csrf_token' => csrf_token(),
        ], 422);
    }

    // MFA passed — complete authentication
    complete_mfa_session();

    $redirect = $_SESSION['login_redirect'] ?? '/';
    unset($_SESSION['login_redirect']);

    json_response([
        'success'    => true,
        'redirect'   => $redirect,
        'csrf_token' => csrf_token(),
    ]);
}

// ─────────────────────────────────────────────
// Type: backup — verify one-time backup code
// ─────────────────────────────────────────────
if ($type === 'backup') {
    $hashedCodes = json_decode($user['mfa_backup_codes'], true);

    if (!is_array($hashedCodes) || empty($hashedCodes)) {
        json_response([
            'error'      => t('auth.error_mfa_code'),
            'csrf_token' => csrf_token(),
        ], 422);
    }

    $index = TOTP::verifyBackupCode($code, $hashedCodes);

    if ($index === false) {
        json_response([
            'error'      => t('auth.error_mfa_code'),
            'csrf_token' => csrf_token(),
        ], 422);
    }

    // Remove the used backup code from the array
    array_splice($hashedCodes, $index, 1);

    try {
        $stmt = $db->prepare(
            'UPDATE users SET mfa_backup_codes = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([json_encode(array_values($hashedCodes)), $user['id']]);
    } catch (\PDOException $e) {
        error_log('MFA backup code update failed: ' . $e->getMessage());
        // Non-blocking — the code was valid, proceed with login
    }

    // MFA passed — complete authentication
    complete_mfa_session();

    $redirect = $_SESSION['login_redirect'] ?? '/';
    unset($_SESSION['login_redirect']);

    json_response([
        'success'    => true,
        'redirect'   => $redirect,
        'csrf_token' => csrf_token(),
    ]);
}

// Unknown type
json_response([
    'error'      => t('auth.error_mfa_code'),
    'csrf_token' => csrf_token(),
], 400);
