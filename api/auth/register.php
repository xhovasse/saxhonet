<?php
/**
 * Saxho.net — API: User registration
 * POST /api/auth/register
 */

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
    $input = $_POST;
}

// CSRF verification
$csrfToken = $input[CSRF_TOKEN_NAME] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response([
        'success'    => false,
        'error'      => t('contact.error_csrf'),
        'csrf_token' => csrf_token(),
    ], 403);
}

// Extract and trim inputs
$email           = trim($input['email'] ?? '');
$password        = $input['password'] ?? '';
$passwordConfirm = $input['password_confirm'] ?? '';
$firstName       = trim($input['first_name'] ?? '');
$lastName        = trim($input['last_name'] ?? '');
$company         = trim($input['company'] ?? '');
$jobTitle        = trim($input['job_title'] ?? '');
$phone           = trim($input['phone'] ?? '');
$country         = trim($input['country'] ?? '');
$terms           = !empty($input['terms']);

// Validation
$errors = [];

// Email
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
    $errors['email'] = t('auth.error_email_invalid');
}

// Password length
if (mb_strlen($password) < 8) {
    $errors['password'] = t('auth.error_password_short');
} elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    // Password strength: at least 1 uppercase + 1 digit
    $errors['password'] = t('auth.error_password_weak');
}

// Password match
if ($password !== $passwordConfirm) {
    $errors['password_confirm'] = t('auth.error_password_mismatch');
}

// First name & last name
if ($firstName === '' || $lastName === '') {
    $errors['first_name'] = t('auth.error_name_required');
}

// Terms
if (!$terms) {
    $errors['terms'] = t('auth.error_terms');
}

// Check email uniqueness (only if email is valid so far)
if (empty($errors['email'])) {
    $db = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors['email'] = t('auth.error_email_taken');
    }
}

// Return validation errors
if (!empty($errors)) {
    json_response([
        'success'    => false,
        'errors'     => $errors,
        'csrf_token' => csrf_token(),
    ], 422);
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Generate email verification token
$emailToken        = bin2hex(random_bytes(32));
$emailTokenExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Sanitize optional fields
$company  = $company !== '' ? mb_substr($company, 0, 255) : null;
$jobTitle = $jobTitle !== '' ? mb_substr($jobTitle, 0, 255) : null;
$phone    = $phone !== '' ? mb_substr($phone, 0, 20) : null;
$country  = $country !== '' ? mb_substr($country, 0, 100) : null;

// Insert user
$db = $db ?? getDB();
try {
    $stmt = $db->prepare(
        'INSERT INTO users (email, password_hash, first_name, last_name, company, job_title, phone, country, email_token, email_token_expires, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $email,
        $passwordHash,
        mb_substr($firstName, 0, 100),
        mb_substr($lastName, 0, 100),
        $company,
        $jobTitle,
        $phone,
        $country,
        $emailToken,
        $emailTokenExpires,
    ]);
} catch (\PDOException $e) {
    error_log('Register insert failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}

// Send verification email
$verifyUrl = SITE_URL . '/verify-email?token=' . $emailToken . '&email=' . urlencode($email);

$emailSubject = SITE_NAME . ' — ' . t('auth.verify_email');
$emailContent = '<p>' . t('auth.verify_pending', ['email' => e($email)]) . '</p>'
    . '<p>' . t('auth.verify_check_inbox') . '</p>';

$emailBody = email_template(
    t('auth.verify_email'),
    $emailContent,
    $verifyUrl,
    t('auth.verify_button')
);

$emailSent = send_email($email, $emailSubject, $emailBody);
if (!$emailSent) {
    error_log('Verification email failed for: ' . $email);
}

// Stocker l'email en session pour la page verify-email
$_SESSION['verify_email'] = $email;

json_response([
    'success'    => true,
    'message'    => t('auth.register_success'),
    'redirect'   => '/verify-email',
    'csrf_token' => csrf_token(),
]);
