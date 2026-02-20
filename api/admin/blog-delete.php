<?php
/**
 * Saxho.net — API: Delete blog post
 * POST /api/admin/blog-delete
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
        'error'      => t('admin.error_not_found'),
        'csrf_token' => csrf_token(),
    ], 404);
}

$db = getDB();

// Verify exists
$stmt = $db->prepare('SELECT id, cover_image FROM blog_posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    json_response([
        'success'    => false,
        'error'      => t('admin.error_not_found'),
        'csrf_token' => csrf_token(),
    ], 404);
}

// Delete (CASCADE takes care of comments)
try {
    $stmt = $db->prepare('DELETE FROM blog_posts WHERE id = ?');
    $stmt->execute([$id]);
} catch (\PDOException $e) {
    error_log('Blog delete failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

// Optionally delete cover image file
if (!empty($post['cover_image'])) {
    $imagePath = UPLOAD_PATH . '/' . $post['cover_image'];
    if (file_exists($imagePath)) {
        @unlink($imagePath);
    }
}

json_response([
    'success'    => true,
    'message'    => t('admin.deleted_success'),
    'csrf_token' => csrf_token(),
]);
