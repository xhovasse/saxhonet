<?php
/**
 * Saxho.net — API: MFA setup (generate, enable, disable)
 * POST /api/auth/mfa-setup
 *
 * Requires full authentication (not MFA-pending state).
 */

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Require full auth (API-safe check — no redirect)
if (!is_logged_in()) {
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

$action = $input['action'] ?? '';
$db = getDB();

// Fetch current user
$stmt = $db->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
$stmt->execute([current_user_id()]);
$user = $stmt->fetch();

if (!$user) {
    json_response([
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 400);
}

// ─────────────────────────────────────────────
// Action: generate — create a temp secret
// ─────────────────────────────────────────────
if ($action === 'generate') {
    $secret = TOTP::generateSecret();

    // Store temporarily in session
    $_SESSION['mfa_temp_secret'] = $secret;

    // Generate provisioning URI for QR code
    $uri = TOTP::getProvisioningUri($secret, $user['email']);

    json_response([
        'success'    => true,
        'secret'     => $secret,
        'uri'        => $uri,
        'csrf_token' => csrf_token(),
    ]);
}

// ─────────────────────────────────────────────
// Action: enable — verify code and activate MFA
// ─────────────────────────────────────────────
if ($action === 'enable') {
    $code = trim($input['code'] ?? '');

    if ($code === '') {
        json_response([
            'error'      => t('auth.error_mfa_code'),
            'csrf_token' => csrf_token(),
        ], 422);
    }

    // Retrieve temp secret from session
    $secret = $_SESSION['mfa_temp_secret'] ?? '';
    if ($secret === '') {
        json_response([
            'error'      => t('auth.error_generic'),
            'csrf_token' => csrf_token(),
        ], 400);
    }

    // Verify the TOTP code against the temp secret
    if (!TOTP::verify($secret, $code)) {
        json_response([
            'error'      => t('auth.error_mfa_code'),
            'csrf_token' => csrf_token(),
        ], 422);
    }

    // Generate backup codes
    $plainCodes  = TOTP::generateBackupCodes();
    $hashedCodes = array_map([TOTP::class, 'hashBackupCode'], $plainCodes);

    // Persist MFA config to database
    try {
        $stmt = $db->prepare(
            'UPDATE users SET mfa_secret = ?, mfa_enabled = 1, mfa_backup_codes = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$secret, json_encode($hashedCodes), $user['id']]);

        // Clean up session temp secret
        unset($_SESSION['mfa_temp_secret']);

        json_response([
            'success'      => true,
            'message'      => t('auth.mfa_enabled'),
            'backup_codes' => $plainCodes,
            'csrf_token'   => csrf_token(),
        ]);
    } catch (\PDOException $e) {
        error_log('MFA enable failed: ' . $e->getMessage());
        json_response([
            'error'      => t('auth.error_generic'),
            'csrf_token' => csrf_token(),
        ], 500);
    }
}

// ─────────────────────────────────────────────
// Action: disable — verify password and deactivate MFA
// ─────────────────────────────────────────────
if ($action === 'disable') {
    $password = $input['password'] ?? '';

    if ($password === '' || !password_verify($password, $user['password_hash'])) {
        json_response([
            'error'      => t('auth.error_current_password'),
            'csrf_token' => csrf_token(),
        ], 422);
    }

    try {
        $stmt = $db->prepare(
            'UPDATE users SET mfa_secret = NULL, mfa_enabled = 0, mfa_backup_codes = NULL, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$user['id']]);

        json_response([
            'success'    => true,
            'message'    => t('auth.mfa_disabled'),
            'csrf_token' => csrf_token(),
        ]);
    } catch (\PDOException $e) {
        error_log('MFA disable failed: ' . $e->getMessage());
        json_response([
            'error'      => t('auth.error_generic'),
            'csrf_token' => csrf_token(),
        ], 500);
    }
}

// Unknown action
json_response([
    'error'      => t('auth.error_generic'),
    'csrf_token' => csrf_token(),
], 400);
