<?php
/**
 * Saxho.net — API: Contact form submission
 * POST /api/contact
 */

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Parse JSON body (JS sends JSON) with fallback to POST form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
    $input = $_POST;
}

// CSRF verification
$csrfToken = $input[CSRF_TOKEN_NAME] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response(['error' => t('contact.error_csrf')], 403);
}

// Honeypot check — if the hidden field "website" has any value, it's a bot
$honeypot = trim($input['website'] ?? '');
if ($honeypot !== '') {
    // Silently succeed to not reveal detection to bots
    json_response(['success' => true, 'message' => t('contact.success')]);
}

// Rate limiting: 3 messages per hour per IP
$ip = get_ip();
$db = getDB();

$stmt = $db->prepare(
    'SELECT COUNT(*) FROM contact_messages
     WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'
);
$stmt->execute([$ip]);
$recentCount = (int) $stmt->fetchColumn();

if ($recentCount >= 3) {
    json_response(['error' => t('contact.error_rate_limit'), CSRF_TOKEN_NAME => csrf_token()], 429);
}

// Input validation
$errors = [];

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$company = trim($input['company'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

if ($name === '' || mb_strlen($name) > 200) {
    $errors['name'] = t('contact.error_name');
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 255) {
    $errors['email'] = t('contact.error_email');
}

// Subject whitelist
$validSubjects = ['general', 'ideas', 'ideation', 'process', 'taskforce', 'portfolio_contribute', 'portfolio_invest', 'other'];
if (!in_array($subject, $validSubjects, true)) {
    $errors['subject'] = t('contact.error_subject');
}

if ($message === '') {
    $errors['message'] = t('contact.error_message');
} elseif (mb_strlen($message) < 10) {
    $errors['message'] = t('contact.error_message_short');
}

if (!empty($errors)) {
    json_response(['error' => 'Validation failed', 'errors' => $errors, CSRF_TOKEN_NAME => csrf_token()], 422);
}

// Sanitize lengths
$name    = mb_substr($name, 0, 200);
$email   = mb_substr($email, 0, 255);
$company = $company !== '' ? mb_substr($company, 0, 255) : null;
$message = mb_substr($message, 0, 5000);

// Insert into database
try {
    $stmt = $db->prepare(
        'INSERT INTO contact_messages (name, email, company, subject, message, ip_address, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$name, $email, $company, $subject, $message, $ip]);
} catch (\PDOException $e) {
    error_log('Contact insert failed: ' . $e->getMessage());
    json_response(['error' => t('contact.error'), CSRF_TOKEN_NAME => csrf_token()], 500);
}

// Send notification email to admin
$subjectLabel = t('contact.subjects.' . $subject);
$emailSubject = "[Saxho.net] Nouveau message: $subjectLabel";

// Utiliser le template email Saxho
$emailContent = "<p><strong>Nom :</strong> " . e($name) . "</p>"
    . "<p><strong>Email :</strong> <a href=\"mailto:" . e($email) . "\">" . e($email) . "</a></p>"
    . ($company ? "<p><strong>Entreprise :</strong> " . e($company) . "</p>" : '')
    . "<p><strong>Sujet :</strong> " . e($subjectLabel) . "</p>"
    . "<hr style=\"border:none;border-top:1px solid #eee;margin:16px 0;\">"
    . "<p>" . nl2br(e($message)) . "</p>"
    . "<hr style=\"border:none;border-top:1px solid #eee;margin:16px 0;\">"
    . "<p style=\"font-size:12px;color:#888;\">IP: $ip &mdash; " . date('d/m/Y H:i:s') . "</p>";

$emailBody = email_template('Nouveau message de contact', $emailContent);

$emailSent = send_email(ADMIN_EMAIL, $emailSubject, $emailBody);
if (!$emailSent) {
    error_log('Contact notification email FAILED for message from: ' . $email . ' — check SMTP logs above');
} else {
    error_log('Contact notification email sent successfully to ' . ADMIN_EMAIL);
}

json_response([
    'success' => true,
    'message' => t('contact.success'),
    CSRF_TOKEN_NAME => csrf_token()
]);
