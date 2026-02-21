<?php
/**
 * Saxho.net — API: Save project (create or update)
 * POST /api/admin/project-save
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

// Gather fields
$id              = !empty($input['id']) ? (int)$input['id'] : 0;
$name_fr         = trim($input['name_fr'] ?? '');
$name_en         = trim($input['name_en'] ?? '');
$slug            = trim($input['slug'] ?? '');
$domain          = trim($input['domain'] ?? '');
$image           = trim($input['image'] ?? '');
$pitch_fr        = trim($input['pitch_fr'] ?? '');
$pitch_en        = trim($input['pitch_en'] ?? '');
$problem_fr      = trim($input['problem_fr'] ?? '');
$problem_en      = trim($input['problem_en'] ?? '');
$solution_fr     = trim($input['solution_fr'] ?? '');
$solution_en     = trim($input['solution_en'] ?? '');
$phase           = trim($input['phase'] ?? 'ideation');
$investment      = trim($input['investment_sought'] ?? '');
$skills_fr       = trim($input['skills_sought_fr'] ?? '');
$skills_en       = trim($input['skills_sought_en'] ?? '');
$launch_date     = trim($input['launch_date'] ?? '');
$status          = trim($input['status'] ?? 'open');
$is_visible      = !empty($input['is_visible']) ? 1 : 0;
$display_order   = isset($input['display_order']) ? (int)$input['display_order'] : 0;

// Validate
$errors = [];
if ($name_fr === '') $errors['name_fr'] = 'Le nom du projet (FR) est requis.';
if ($pitch_fr === '') $errors['pitch_fr'] = 'Le pitch (FR) est requis.';
if ($domain === '') $errors['domain'] = 'Le domaine est requis.';
if ($problem_fr === '') $errors['problem_fr'] = 'La problematique (FR) est requise.';
if ($solution_fr === '') $errors['solution_fr'] = 'La solution (FR) est requise.';

$validPhases = ['ideation', 'study', 'prototype', 'development', 'pre_launch', 'transferred'];
if (!in_array($phase, $validPhases, true)) $phase = 'ideation';

$validStatuses = ['open', 'complete', 'paused'];
if (!in_array($status, $validStatuses, true)) $status = 'open';

if (!empty($errors)) {
    json_response([
        'success'    => false,
        'errors'     => $errors,
        'csrf_token' => csrf_token(),
    ], 422);
}

// Generate slug if empty
if ($slug === '') {
    $slug = slugify($name_fr);
}

// Validate launch_date
if ($launch_date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $launch_date)) {
    $launch_date = null;
} elseif ($launch_date === '') {
    $launch_date = null;
}

$db = getDB();

try {
    if ($id > 0) {
        // UPDATE
        // Check slug uniqueness (excluding self)
        $stmt = $db->prepare('SELECT id FROM projects WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . $id;
        }

        $stmt = $db->prepare(
            'UPDATE projects SET
                name_fr = ?, name_en = ?, slug = ?, image = ?, domain = ?,
                pitch_fr = ?, pitch_en = ?,
                problem_fr = ?, problem_en = ?,
                solution_fr = ?, solution_en = ?,
                phase = ?, investment_sought = ?,
                skills_sought_fr = ?, skills_sought_en = ?,
                launch_date = ?, status = ?, is_visible = ?, display_order = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $name_fr, $name_en ?: null, $slug, $image ?: null, $domain,
            $pitch_fr, $pitch_en ?: null,
            $problem_fr, $problem_en ?: null,
            $solution_fr, $solution_en ?: null,
            $phase, $investment ?: null,
            $skills_fr ?: null, $skills_en ?: null,
            $launch_date, $status, $is_visible, $display_order,
            $id
        ]);

        json_response([
            'success'    => true,
            'message'    => 'Projet mis a jour.',
            'id'         => $id,
            'slug'       => $slug,
            'csrf_token' => csrf_token(),
        ]);
    } else {
        // INSERT
        // Check slug uniqueness
        $stmt = $db->prepare('SELECT id FROM projects WHERE slug = ?');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug = $slug . '-' . time();
        }

        $stmt = $db->prepare(
            'INSERT INTO projects
                (name_fr, name_en, slug, image, domain,
                 pitch_fr, pitch_en,
                 problem_fr, problem_en,
                 solution_fr, solution_en,
                 phase, investment_sought,
                 skills_sought_fr, skills_sought_en,
                 launch_date, status, is_visible, display_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $name_fr, $name_en ?: null, $slug, $image ?: null, $domain,
            $pitch_fr, $pitch_en ?: null,
            $problem_fr, $problem_en ?: null,
            $solution_fr, $solution_en ?: null,
            $phase, $investment ?: null,
            $skills_fr ?: null, $skills_en ?: null,
            $launch_date, $status, $is_visible, $display_order
        ]);

        $newId = (int)$db->lastInsertId();

        json_response([
            'success'    => true,
            'message'    => 'Projet cree.',
            'id'         => $newId,
            'slug'       => $slug,
            'csrf_token' => csrf_token(),
        ]);
    }
} catch (\PDOException $e) {
    error_log('Project save failed: ' . $e->getMessage());
    json_response([
        'success'    => false,
        'error'      => t('auth.error_generic'),
        'csrf_token' => csrf_token(),
    ], 500);
}
