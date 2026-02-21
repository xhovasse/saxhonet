<?php
/**
 * Saxho.net — API: Delete contact message
 * POST /api/admin/message-delete
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
$stmt = $db->prepare('SELECT id FROM contact_messages WHERE id = ?');
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    json_response([
        'success'    => false,
        'error'      => 'Message introuvable.',
        'csrf_token' => csrf_token(),
    ], 404);
}

// Delete
try {
    $stmt = $db->prepare('DELETE FROM contact_messages WHERE id = ?');
    $stmt->execute([$id]);
} catch (\PDOException $e) {
    error_log('Message delete failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'message'    => 'Message supprime avec succes.',
    'csrf_token' => csrf_token(),
]);
