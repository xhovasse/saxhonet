<?php
/**
 * Saxho.net — API: Image upload
 * POST /api/admin/upload
 */

// Admin check
if (!is_admin()) {
    json_response(['success' => false, 'error' => 'Unauthorized'], 403);
}

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// CSRF — pour les uploads multipart, le token est dans $_POST
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response([
        'success'    => false,
        'error'      => t('contact.error_csrf'),
        'csrf_token' => csrf_token(),
    ], 403);
}

// Check file
if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    json_response([
        'success'    => false,
        'error'      => t('admin.upload_error'),
        'csrf_token' => csrf_token(),
    ], 400);
}

$file = $_FILES['image'];

// Validate type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
    json_response([
        'success'    => false,
        'error'      => t('admin.upload_error_type'),
        'csrf_token' => csrf_token(),
    ], 400);
}

// Validate size
if ($file['size'] > MAX_UPLOAD_SIZE) {
    json_response([
        'success'    => false,
        'error'      => t('admin.upload_error_size'),
        'csrf_token' => csrf_token(),
    ], 400);
}

// Generate unique filename
$extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$ext = $extensions[$mimeType] ?? 'jpg';
$filename = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

// Ensure upload directory exists
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// Move file
$destination = UPLOAD_PATH . '/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    json_response([
        'success'    => false,
        'error'      => t('admin.upload_error'),
        'csrf_token' => csrf_token(),
    ], 500);
}

json_response([
    'success'    => true,
    'filename'   => $filename,
    'url'        => UPLOAD_URL . '/' . $filename,
    'csrf_token' => csrf_token(),
]);
