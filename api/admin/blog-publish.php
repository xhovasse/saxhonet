<?php
/**
 * Saxho.net — API: Publish/unpublish blog post
 * POST /api/admin/blog-publish
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

$id     = !empty($input['id']) ? (int)$input['id'] : 0;
$status = ($input['status'] ?? '') === 'published' ? 'published' : 'draft';

if ($id <= 0) {
    json_response([
        'success'    => false,
        'error'      => t('admin.error_not_found'),
        'csrf_token' => csrf_token(),
    ], 404);
}

$db = getDB();

// Verify exists
$stmt = $db->prepare('SELECT id, published_at FROM blog_posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    json_response([
        'success'    => false,
        'error'      => t('admin.error_not_found'),
        'csrf_token' => csrf_token(),
    ], 404);
}

// Update
try {
    $publishedAt = null;
    if ($status === 'published') {
        $publishedAt = $post['published_at'] ?: date('Y-m-d H:i:s');
    }

    $stmt = $db->prepare('UPDATE blog_posts SET status = ?, published_at = ?, updated_at = NOW() WHERE id = ?');
    $stmt->execute([$status, $publishedAt, $id]);
} catch (\PDOException $e) {
    error_log('Blog publish failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

$message = $status === 'published' ? t('admin.published_success') : t('admin.unpublished_success');

json_response([
    'success'    => true,
    'message'    => $message,
    'csrf_token' => csrf_token(),
]);
