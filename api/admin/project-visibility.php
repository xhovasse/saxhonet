<?php
/**
 * Saxho.net — API: Toggle project visibility
 * POST /api/admin/project-visibility
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

$db = getDB();

// Verify exists
$stmt = $db->prepare('SELECT id, is_visible FROM projects WHERE id = ?');
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    json_response([
        'success'    => false,
        'error'      => 'Projet introuvable.',
        'csrf_token' => csrf_token(),
    ], 404);
}

// Toggle is_visible
try {
    $newVal = $project['is_visible'] ? 0 : 1;
    $stmt = $db->prepare('UPDATE projects SET is_visible = ? WHERE id = ?');
    $stmt->execute([$newVal, $id]);
} catch (\PDOException $e) {
    error_log('Project visibility toggle failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'is_visible' => $newVal,
    'csrf_token' => csrf_token(),
]);
