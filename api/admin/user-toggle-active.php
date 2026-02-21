<?php
/**
 * Saxho.net — API: Toggle user active/inactive
 * POST /api/admin/user-toggle-active
 */

// Admin check
if (!is_admin()) {
    json_response(['success' => false, 'error' => 'Unauthorized'], 403);
}

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
    $input = $_POST;
}

// CSRF
$csrfToken = $input[CSRF_TOKEN_NAME] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response([
        'success'    => false,
        'error'      => t('contact.error_csrf'),
        'csrf_token' => csrf_token(),
    ], 403);
}

$id = !empty($input['id']) ? (int)$input['id'] : 0;

if ($id <= 0) {
    json_response([
        'success'    => false,
        'error'      => 'ID manquant',
        'csrf_token' => csrf_token(),
    ], 422);
}

// Cannot toggle yourself
if ($id === current_user_id()) {
    json_response([
        'success'    => false,
        'error'      => 'Vous ne pouvez pas desactiver votre propre compte.',
        'csrf_token' => csrf_token(),
    ], 422);
}

$db = getDB();

// Verify exists
$stmt = $db->prepare('SELECT id, is_active FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    json_response([
        'success'    => false,
        'error'      => 'Utilisateur introuvable.',
        'csrf_token' => csrf_token(),
    ], 404);
}

// Toggle is_active
try {
    $newVal = $user['is_active'] ? 0 : 1;
    $stmt = $db->prepare('UPDATE users SET is_active = ? WHERE id = ?');
    $stmt->execute([$newVal, $id]);
} catch (\PDOException $e) {
    error_log('User toggle active failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'is_active'  => $newVal,
    'csrf_token' => csrf_token(),
]);
