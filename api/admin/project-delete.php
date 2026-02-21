<?php
/**
 * Saxho.net — API: Delete project
 * POST /api/admin/project-delete
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
$stmt = $db->prepare('SELECT id FROM projects WHERE id = ?');
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    json_response([
        'success'    => false,
        'error'      => 'Projet introuvable.',
        'csrf_token' => csrf_token(),
    ], 404);
}

// Delete
try {
    $stmt = $db->prepare('DELETE FROM projects WHERE id = ?');
    $stmt->execute([$id]);
} catch (\PDOException $e) {
    error_log('Project delete failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'message'    => 'Projet supprime avec succes.',
    'csrf_token' => csrf_token(),
]);
