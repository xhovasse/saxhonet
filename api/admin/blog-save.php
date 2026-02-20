<?php
/**
 * Saxho.net — API: Save blog post (create/edit)
 * POST /api/admin/blog-save
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

// Extract inputs
$id          = !empty($input['id']) ? (int)$input['id'] : null;
$titleFr     = trim($input['title_fr'] ?? '');
$titleEn     = trim($input['title_en'] ?? '');
$slug        = trim($input['slug'] ?? '');
$categoryId  = !empty($input['category_id']) ? (int)$input['category_id'] : null;
$contentFr   = $input['content_fr'] ?? '';
$contentEn   = $input['content_en'] ?? '';
$excerptFr   = trim($input['excerpt_fr'] ?? '');
$excerptEn   = trim($input['excerpt_en'] ?? '');
$coverImage  = trim($input['cover_image'] ?? '');
$status      = ($input['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
$publishedAt = trim($input['published_at'] ?? '');

// Validation
$errors = [];

if ($titleFr === '') {
    $errors['title_fr'] = t('admin.error_title_required');
}

if ($contentFr === '') {
    $errors['content_fr'] = t('admin.error_content_required');
}

// Auto-generate slug if empty
if ($slug === '' && $titleFr !== '') {
    $slug = slugify($titleFr);
}

// Check slug uniqueness
if ($slug !== '') {
    $db = getDB();
    $slugQuery = 'SELECT id FROM blog_posts WHERE slug = ?';
    $slugParams = [$slug];
    if ($id) {
        $slugQuery .= ' AND id != ?';
        $slugParams[] = $id;
    }
    $stmt = $db->prepare($slugQuery);
    $stmt->execute($slugParams);
    if ($stmt->fetch()) {
        $errors['slug'] = t('admin.error_slug_taken');
    }
}

if (!empty($errors)) {
    json_response([
        'success'    => false,
        'errors'     => $errors,
        'csrf_token' => csrf_token(),
    ], 422);
}

// Calculate reading time
$readingTime = reading_time($contentFr);

// Handle published_at
if ($status === 'published' && $publishedAt === '') {
    $publishedAt = date('Y-m-d H:i:s');
} elseif ($publishedAt !== '') {
    // Ensure it's a valid datetime
    $publishedAt = date('Y-m-d H:i:s', strtotime($publishedAt));
} else {
    $publishedAt = null;
}

// Sanitize optional fields
$titleEn    = $titleEn !== '' ? mb_substr($titleEn, 0, 255) : null;
$contentEn  = $contentEn !== '' ? $contentEn : null;
$excerptFr  = $excerptFr !== '' ? mb_substr($excerptFr, 0, 500) : null;
$excerptEn  = $excerptEn !== '' ? mb_substr($excerptEn, 0, 500) : null;
$coverImage = $coverImage !== '' ? mb_substr($coverImage, 0, 255) : null;

$db = $db ?? getDB();

try {
    if ($id) {
        // UPDATE
        $stmt = $db->prepare(
            'UPDATE blog_posts SET
                title_fr = ?, title_en = ?, slug = ?, content_fr = ?, content_en = ?,
                excerpt_fr = ?, excerpt_en = ?, cover_image = ?, category_id = ?,
                status = ?, published_at = ?, reading_time = ?, updated_at = NOW()
             WHERE id = ?'
        );
        $stmt->execute([
            mb_substr($titleFr, 0, 255), $titleEn, $slug, $contentFr, $contentEn,
            $excerptFr, $excerptEn, $coverImage, $categoryId,
            $status, $publishedAt, $readingTime,
            $id
        ]);
    } else {
        // INSERT
        $authorId = current_user_id();
        $stmt = $db->prepare(
            'INSERT INTO blog_posts (title_fr, title_en, slug, content_fr, content_en,
                excerpt_fr, excerpt_en, cover_image, category_id, author_id,
                status, published_at, reading_time, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            mb_substr($titleFr, 0, 255), $titleEn, $slug, $contentFr, $contentEn,
            $excerptFr, $excerptEn, $coverImage, $categoryId, $authorId,
            $status, $publishedAt, $readingTime
        ]);
        $id = (int)$db->lastInsertId();
    }
} catch (\PDOException $e) {
    error_log('Blog save failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'id'         => $id,
    'slug'       => $slug,
    'message'    => t('admin.saved_success'),
    'csrf_token' => csrf_token(),
]);
