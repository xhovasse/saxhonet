<?php
/**
 * Saxho.net — API: Save blog category (create/edit)
 * POST /api/admin/category-save
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

$id     = !empty($input['id']) ? (int)$input['id'] : null;
$nameFr = trim($input['name_fr'] ?? '');
$nameEn = trim($input['name_en'] ?? '');

// Validation
if ($nameFr === '') {
    json_response([
        'success'    => false,
        'error'      => t('admin.error_category_name_required'),
        'csrf_token' => csrf_token(),
    ], 422);
}

$slug = slugify($nameFr);
$nameEn = $nameEn !== '' ? mb_substr($nameEn, 0, 100) : null;

$db = getDB();

try {
    if ($id) {
        // UPDATE
        $stmt = $db->prepare('UPDATE blog_categories SET name_fr = ?, name_en = ?, slug = ? WHERE id = ?');
        $stmt->execute([mb_substr($nameFr, 0, 100), $nameEn, $slug, $id]);
    } else {
        // INSERT
        $stmt = $db->prepare('INSERT INTO blog_categories (name_fr, name_en, slug) VALUES (?, ?, ?)');
        $stmt->execute([mb_substr($nameFr, 0, 100), $nameEn, $slug]);
        $id = (int)$db->lastInsertId();
    }
} catch (\PDOException $e) {
    error_log('Category save failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'id'         => $id,
    'message'    => t('admin.category_saved'),
    'csrf_token' => csrf_token(),
]);
