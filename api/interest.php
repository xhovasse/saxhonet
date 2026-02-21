<?php
/**
 * Saxho.net — API: Expression of interest
 * POST /api/interest
 */

// Must be logged in
if (!is_logged_in()) {
    json_response(['success' => false, 'error' => 'Unauthorized'], 401);
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

// CSRF verification
$csrfToken = $input[CSRF_TOKEN_NAME] ?? '';
if (!csrf_verify($csrfToken)) {
    json_response([
        'success'    => false,
        'error'      => t('contact.error_csrf'),
        'csrf_token' => csrf_token(),
    ], 403);
}

$db = getDB();
$userId = current_user_id();

// Validate project
$projectId = !empty($input['project_id']) ? (int)$input['project_id'] : 0;
if ($projectId <= 0) {
    json_response([
        'success'    => false,
        'error'      => 'Projet manquant.',
        'csrf_token' => csrf_token(),
    ], 422);
}

$stmt = $db->prepare('SELECT id, name_fr, is_visible FROM projects WHERE id = ? AND is_visible = 1');
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    json_response([
        'success'    => false,
        'error'      => 'Projet introuvable.',
        'csrf_token' => csrf_token(),
    ], 404);
}

// Validate type
$type = trim($input['type'] ?? '');
if (!in_array($type, ['competence', 'investment'], true)) {
    json_response([
        'success'    => false,
        'error'      => 'Type invalide.',
        'csrf_token' => csrf_token(),
    ], 422);
}

// Check for existing interest
$stmtCheck = $db->prepare('SELECT id FROM interest_requests WHERE user_id = ? AND project_id = ?');
$stmtCheck->execute([$userId, $projectId]);
if ($stmtCheck->fetch()) {
    json_response([
        'success'    => false,
        'error'      => t('portfolio.interest_already'),
        'csrf_token' => csrf_token(),
    ], 422);
}

// Common fields
$company  = trim($input['contact_company'] ?? '');
$jobTitle = trim($input['contact_job_title'] ?? '');
$phone    = trim($input['contact_phone'] ?? '');
$address  = trim($input['contact_address'] ?? '');
$country  = trim($input['contact_country'] ?? '');
$message  = trim($input['message'] ?? '');

// Validation
$errors = [];
if ($company === '') $errors[] = 'Entreprise requise.';
if ($jobTitle === '') $errors[] = 'Poste requis.';
if ($phone === '') $errors[] = 'Telephone requis.';
if ($address === '') $errors[] = 'Adresse requise.';
if ($country === '') $errors[] = 'Pays requis.';

// Type-specific fields
$expertiseDomain = null;
$availability = null;
$linkedinCvUrl = null;
$investmentRange = null;
$investmentExperience = null;
$investmentStructure = null;

if ($type === 'competence') {
    $expertiseDomain = trim($input['expertise_domain'] ?? '');
    $availability    = trim($input['availability'] ?? '');
    $linkedinCvUrl   = trim($input['linkedin_cv_url'] ?? '');
    if ($expertiseDomain === '') $errors[] = 'Domaine d\'expertise requis.';
} else {
    $investmentRange      = trim($input['investment_range'] ?? '');
    $investmentExperience = trim($input['investment_experience'] ?? '');
    $investmentStructure  = trim($input['investment_structure'] ?? '');
    $validRanges = ['less_10k', '10k_50k', '50k_100k', 'more_100k', 'to_discuss'];
    if (!in_array($investmentRange, $validRanges, true)) {
        $errors[] = 'Fourchette d\'investissement requise.';
    }
}

if (!empty($errors)) {
    json_response([
        'success'    => false,
        'error'      => implode(' ', $errors),
        'csrf_token' => csrf_token(),
    ], 422);
}

// Sanitize lengths
$company   = mb_substr($company, 0, 255);
$jobTitle  = mb_substr($jobTitle, 0, 255);
$phone     = mb_substr($phone, 0, 20);
$address   = mb_substr($address, 0, 1000);
$country   = mb_substr($country, 0, 100);
$message   = mb_substr($message, 0, 5000);

// Insert
try {
    $stmt = $db->prepare(
        'INSERT INTO interest_requests
            (user_id, project_id, type,
             contact_company, contact_job_title, contact_phone, contact_address, contact_country,
             message, expertise_domain, availability, linkedin_cv_url,
             investment_range, investment_experience, investment_structure,
             status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "submitted", NOW())'
    );
    $stmt->execute([
        $userId, $projectId, $type,
        $company, $jobTitle, $phone, $address, $country,
        $message ?: null,
        $expertiseDomain, $availability, $linkedinCvUrl,
        $investmentRange ?: null, $investmentExperience ?: null, $investmentStructure ?: null,
    ]);
} catch (\PDOException $e) {
    error_log('Interest insert failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('portfolio.interest_error'),
        'csrf_token' => csrf_token(),
    ], 500);
}

// Send admin notification email
$user = current_user();
$typeLabel = $type === 'competence' ? 'Competences' : 'Investissement';
$emailSubject = "[Saxho.net] Expression d'interet: {$typeLabel} — {$project['name_fr']}";

$emailContent = "<p><strong>Type :</strong> {$typeLabel}</p>"
    . "<p><strong>Projet :</strong> " . e($project['name_fr']) . "</p>"
    . "<hr style='border:none;border-top:1px solid #eee;margin:16px 0;'>"
    . "<p><strong>Utilisateur :</strong> " . e($user['first_name'] . ' ' . $user['last_name']) . "</p>"
    . "<p><strong>Email :</strong> <a href='mailto:" . e($user['email']) . "'>" . e($user['email']) . "</a></p>"
    . "<p><strong>Entreprise :</strong> " . e($company) . "</p>"
    . "<p><strong>Poste :</strong> " . e($jobTitle) . "</p>"
    . "<p><strong>Telephone :</strong> " . e($phone) . "</p>"
    . "<p><strong>Adresse :</strong> " . e($address) . "</p>"
    . "<p><strong>Pays :</strong> " . e($country) . "</p>";

if ($message) {
    $emailContent .= "<hr style='border:none;border-top:1px solid #eee;margin:16px 0;'>"
        . "<p><strong>Message :</strong></p>"
        . "<p>" . nl2br(e($message)) . "</p>";
}

if ($type === 'competence') {
    $emailContent .= "<hr style='border:none;border-top:1px solid #eee;margin:16px 0;'>"
        . "<p><strong>Expertise :</strong> " . e($expertiseDomain) . "</p>"
        . ($availability ? "<p><strong>Disponibilite :</strong> " . e($availability) . "</p>" : '')
        . ($linkedinCvUrl ? "<p><strong>LinkedIn/CV :</strong> <a href='" . e($linkedinCvUrl) . "'>" . e($linkedinCvUrl) . "</a></p>" : '');
} else {
    $emailContent .= "<hr style='border:none;border-top:1px solid #eee;margin:16px 0;'>"
        . "<p><strong>Fourchette :</strong> " . e($investmentRange) . "</p>"
        . ($investmentExperience ? "<p><strong>Experience :</strong> " . e($investmentExperience) . "</p>" : '')
        . ($investmentStructure ? "<p><strong>Structure :</strong> " . e($investmentStructure) . "</p>" : '');
}

$emailBody = email_template("Expression d'interet — " . $typeLabel, $emailContent);
$emailSent = send_email(ADMIN_EMAIL, $emailSubject, $emailBody);

if (!$emailSent) {
    error_log('Interest notification email FAILED for user: ' . $user['email']);
}

json_response([
    'success'    => true,
    'message'    => t('portfolio.interest_success'),
    'csrf_token' => csrf_token(),
]);
