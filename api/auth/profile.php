<?php
/**
 * Saxho.net — API: Profile management (update info, change password)
 * POST /api/auth/profile
 *
 * Requires full authentication.
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
// Action: update_info — update personal information
// ─────────────────────────────────────────────
if ($action === 'update_info') {
    $firstName = trim($input['first_name'] ?? '');
    $lastName  = trim($input['last_name'] ?? '');
    $company   = trim($input['company'] ?? '');
    $jobTitle  = trim($input['job_title'] ?? '');
    $phone     = trim($input['phone'] ?? '');
    $address   = trim($input['address'] ?? '');
    $country   = trim($input['country'] ?? '');

    // Validate required fields
    if ($firstName === '' || $lastName === '') {
        json_response([
            'error'      => t('auth.error_name_required'),
            'errors'     => ['first_name' => $firstName === '' ? t('auth.error_name_required') : ''],
            'csrf_token' => csrf_token(),
        ], 422);
    }

    // Sanitize lengths
    $firstName = mb_substr($firstName, 0, 100);
    $lastName  = mb_substr($lastName, 0, 100);
    $company   = $company !== '' ? mb_substr($company, 0, 255) : null;
    $jobTitle  = $jobTitle !== '' ? mb_substr($jobTitle, 0, 255) : null;
    $phone     = $phone !== '' ? mb_substr($phone, 0, 50) : null;
    $address   = $address !== '' ? mb_substr($address, 0, 500) : null;
    $country   = $country !== '' ? mb_substr($country, 0, 100) : null;

    try {
        $stmt = $db->prepare(
            'UPDATE users
             SET first_name = ?, last_name = ?, company = ?, job_title = ?, phone = ?, address = ?, country = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([$firstName, $lastName, $company, $jobTitle, $phone, $address, $country, $user['id']]);

        // Update session with new name
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;

        json_response([
            'success'    => true,
            'message'    => t('auth.profile_updated'),
            'csrf_token' => csrf_token(),
        ]);
    } catch (\PDOException $e) {
        error_log('Profile update failed: ' . $e->getMessage());
        json_response([
            'error'      => t('auth.error_generic'),
            'csrf_token' => csrf_token(),
        ], 500);
    }
}

// ─────────────────────────────────────────────
// Action: change_password — verify current and set new
// ─────────────────────────────────────────────
if ($action === 'change_password') {
    $currentPassword = $input['current_password'] ?? '';
    $newPassword     = $input['new_password'] ?? '';
    $passwordConfirm = $input['password_confirm'] ?? '';

    // Verify current password
    if ($currentPassword === '' || !password_verify($currentPassword, $user['password_hash'])) {
        json_response([
            'error'      => t('auth.error_current_password'),
            'csrf_token' => csrf_token(),
        ], 422);
    }

    // Validate new password: min 8 chars, 1 uppercase, 1 digit
    $errors = [];

    if (mb_strlen($newPassword) < 8) {
        $errors['new_password'] = t('auth.error_password_short');
    } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $errors['new_password'] = t('auth.error_password_weak');
    }

    if ($newPassword !== $passwordConfirm) {
        $errors['password_confirm'] = t('auth.error_password_mismatch');
    }

    if (!empty($errors)) {
        json_response([
            'error'      => 'Validation failed',
            'errors'     => $errors,
            'csrf_token' => csrf_token(),
        ], 422);
    }

    try {
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $db->prepare(
            'UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$newHash, $user['id']]);

        json_response([
            'success'    => true,
            'message'    => t('auth.password_changed'),
            'csrf_token' => csrf_token(),
        ]);
    } catch (\PDOException $e) {
        error_log('Password change failed: ' . $e->getMessage());
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
