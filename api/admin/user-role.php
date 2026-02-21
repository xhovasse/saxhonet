<?php
/**
 * Saxho.net — API: Change user role
 * POST /api/admin/user-role
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

// Cannot change own role
if ($id === current_user_id()) {
    json_response([
        'success'    => false,
        'error'      => 'Vous ne pouvez pas modifier votre propre role.',
        'csrf_token' => csrf_token(),
    ], 422);
}

$db = getDB();

// Verify exists
$stmt = $db->prepare('SELECT id, role FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    json_response([
        'success'    => false,
        'error'      => 'Utilisateur introuvable.',
        'csrf_token' => csrf_token(),
    ], 404);
}

// Toggle role
try {
    $newRole = $user['role'] === 'admin' ? 'member' : 'admin';
    $stmt = $db->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute([$newRole, $id]);
} catch (\PDOException $e) {
    error_log('User role change failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'role'       => $newRole,
    'csrf_token' => csrf_token(),
]);
