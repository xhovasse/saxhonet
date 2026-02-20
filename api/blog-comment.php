<?php
/**
 * Saxho.net — API: Blog comment submission
 * POST /api/blog-comment
 */

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Require authentication
if (!is_logged_in()) {
    json_response(['error' => 'Authentication required'], 401);
}

// Parse JSON body with fallback to POST form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
    $input = $_POST;
}

// CSRF verification
$csrfToken = $input[CSRF_TOKEN_NAME] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response(['error' => t('blog.comment_error_csrf')], 403);
}

// Rate limiting: 5 comments per 10 minutes per user
$userId = current_user_id();
$db = getDB();

$stmt = $db->prepare(
    'SELECT COUNT(*) FROM blog_comments
     WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)'
);
$stmt->execute([$userId]);
$recentCount = (int)$stmt->fetchColumn();

if ($recentCount >= 5) {
    json_response([
        'error' => t('blog.comment_error_rate'),
        'csrf_token' => csrf_token(),
    ], 429);
}

// Validate input
$postId  = (int)($input['post_id'] ?? 0);
$content = trim($input['content'] ?? '');

// Verify post exists and is published
$stmt = $db->prepare('SELECT id FROM blog_posts WHERE id = ? AND status = "published"');
$stmt->execute([$postId]);
if (!$stmt->fetch()) {
    json_response([
        'error' => 'Post not found',
        'csrf_token' => csrf_token(),
    ], 404);
}

// Validate content
if ($content === '') {
    json_response([
        'error' => t('blog.comment_error_empty'),
        'errors' => ['content' => t('blog.comment_error_empty')],
        'csrf_token' => csrf_token(),
    ], 422);
}
if (mb_strlen($content) > 2000) {
    json_response([
        'error' => t('blog.comment_error_long'),
        'errors' => ['content' => t('blog.comment_error_long')],
        'csrf_token' => csrf_token(),
    ], 422);
}

// Sanitize
$content = mb_substr($content, 0, 2000);

// Insert
try {
    $stmt = $db->prepare(
        'INSERT INTO blog_comments (post_id, user_id, content, created_at)
         VALUES (?, ?, ?, NOW())'
    );
    $stmt->execute([$postId, $userId, $content]);

    // Return the new comment data for JS to inject into DOM
    $user = current_user();
    json_response([
        'success' => true,
        'message' => t('blog.comment_success'),
        'csrf_token' => csrf_token(),
        'comment' => [
            'author'  => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'content' => $content,
            'date'    => format_date(date('Y-m-d H:i:s'), $lang),
        ],
    ]);
} catch (\PDOException $e) {
    error_log('Blog comment insert failed: ' . $e->getMessage());
    json_response([
        'error' => t('blog.comment_error'),
        'csrf_token' => csrf_token(),
    ], 500);
}
