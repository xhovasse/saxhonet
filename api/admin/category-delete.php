<?php
/**
 * Saxho.net — API: Delete blog category
 * POST /api/admin/category-delete
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
        'error'      => t('admin.error_category_not_found'),
        'csrf_token' => csrf_token(),
    ], 404);
}

$db = getDB();

// Check if category has posts
$stmt = $db->prepare('SELECT COUNT(*) FROM blog_posts WHERE category_id = ?');
$stmt->execute([$id]);
$postCount = (int)$stmt->fetchColumn();

if ($postCount > 0) {
    json_response([
        'success'    => false,
        'error'      => t('admin.category_has_posts'),
        'csrf_token' => csrf_token(),
    ], 422);
}

// Delete
try {
    $stmt = $db->prepare('DELETE FROM blog_categories WHERE id = ?');
    $stmt->execute([$id]);
} catch (\PDOException $e) {
    error_log('Category delete failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'message'    => t('admin.category_deleted'),
    'csrf_token' => csrf_token(),
]);
