<?php
/**
 * Saxho.net — Correcteur de config SMTP (TEMPORAIRE)
 * Securise par token — A SUPPRIMER immediatement apres usage
 *
 * Usage : https://saxho.net/api/fix-config.php?token=saxho-fix-2025-m3p8
 *
 * Ce script met a jour les valeurs SMTP dans config.php
 */

define('FIX_TOKEN', 'saxho-fix-2025-m3p8');

if (($_GET['token'] ?? '') !== FIX_TOKEN) {
    http_response_code(404);
    echo "Not found.";
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-cache, no-store');

$configFile = __DIR__ . '/../_app/includes/config.php';

if (!file_exists($configFile)) {
    echo "ERREUR: config.php introuvable a $configFile\n";
    exit;
}

echo "=== Lecture du config.php actuel ===\n\n";
$content = file_get_contents($configFile);

// Afficher les valeurs SMTP actuelles (masquer le password)
preg_match("/define\('USE_SMTP',\s*(.+?)\)/", $content, $m);
echo "USE_SMTP actuel:  " . ($m[1] ?? '?') . "\n";
preg_match("/define\('SMTP_HOST',\s*'(.+?)'\)/", $content, $m);
echo "SMTP_HOST actuel: " . ($m[1] ?? 'VIDE') . "\n";
preg_match("/define\('SMTP_PORT',\s*(\d+)\)/", $content, $m);
echo "SMTP_PORT actuel: " . ($m[1] ?? '?') . "\n";
preg_match("/define\('SMTP_USER',\s*'(.+?)'\)/", $content, $m);
echo "SMTP_USER actuel: " . ($m[1] ?? 'VIDE') . "\n";
preg_match("/define\('SMTP_FROM',\s*'(.+?)'\)/", $content, $m);
echo "SMTP_FROM actuel: " . ($m[1] ?? 'VIDE') . "\n";

echo "\n=== Application des corrections ===\n\n";

// Les valeurs correctes
$fixes = [
    ["define('USE_SMTP', false)",  "define('USE_SMTP', true)"],
    ["define('USE_SMTP',false)",   "define('USE_SMTP', true)"],
    ["define('SMTP_HOST', '')",    "define('SMTP_HOST', 'smtp.hostinger.com')"],
    ["define('SMTP_PORT', 587)",   "define('SMTP_PORT', 465)"],
    ["define('SMTP_USER', '')",    "define('SMTP_USER', 'contact@mail.saxho.net')"],
    ["define('SMTP_FROM', 'noreply@saxho.net')", "define('SMTP_FROM', 'contact@mail.saxho.net')"],
];

$modified = false;
foreach ($fixes as [$search, $replace]) {
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        echo "  FIX: $search\n    -> $replace\n\n";
        $modified = true;
    }
}

// Verifier si SMTP_HOST est toujours vide (format different possible)
if (strpos($content, "SMTP_HOST") !== false && strpos($content, "'smtp.hostinger.com'") === false) {
    // Remplacement regex
    $content = preg_replace(
        "/define\('SMTP_HOST',\s*'[^']*'\)/",
        "define('SMTP_HOST', 'smtp.hostinger.com')",
        $content
    );
    echo "  FIX (regex): SMTP_HOST -> smtp.hostinger.com\n";
    $modified = true;
}

// Verifier SMTP_PORT
if (strpos($content, "SMTP_PORT") !== false && strpos($content, "465") === false) {
    $content = preg_replace(
        "/define\('SMTP_PORT',\s*\d+\)/",
        "define('SMTP_PORT', 465)",
        $content
    );
    echo "  FIX (regex): SMTP_PORT -> 465\n";
    $modified = true;
}

// Verifier USE_SMTP
if (strpos($content, "USE_SMTP") !== false && strpos($content, "true") === false) {
    $content = preg_replace(
        "/define\('USE_SMTP',\s*\w+\)/",
        "define('USE_SMTP', true)",
        $content
    );
    echo "  FIX (regex): USE_SMTP -> true\n";
    $modified = true;
}

// Verifier SMTP_USER
if (preg_match("/define\('SMTP_USER',\s*''\)/", $content)) {
    $content = preg_replace(
        "/define\('SMTP_USER',\s*''\)/",
        "define('SMTP_USER', 'contact@mail.saxho.net')",
        $content
    );
    echo "  FIX (regex): SMTP_USER -> contact@mail.saxho.net\n";
    $modified = true;
}

// Verifier SMTP_PASS
if (preg_match("/define\('SMTP_PASS',\s*''\)/", $content)) {
    echo "\n  !! ATTENTION: SMTP_PASS est vide.\n";
    echo "  !! Vous devrez le definir manuellement via le File Manager Hostinger.\n";
    echo "  !! Le mot de passe est celui du compte email contact@mail.saxho.net\n";
}

if ($modified) {
    // Sauvegarder
    $backup = $configFile . '.bak.' . date('YmdHis');
    copy($configFile, $backup);
    echo "  Backup cree: $backup\n";

    file_put_contents($configFile, $content);
    echo "\n  [OK] config.php mis a jour !\n";
} else {
    echo "  Aucune modification necessaire (valeurs deja correctes ?)\n";
}

echo "\n=== Verification apres modification ===\n\n";

// Relire pour verifier
$content2 = file_get_contents($configFile);
preg_match("/define\('USE_SMTP',\s*(.+?)\)/", $content2, $m);
echo "USE_SMTP:  " . ($m[1] ?? '?') . "\n";
preg_match("/define\('SMTP_HOST',\s*'([^']*)'\)/", $content2, $m);
echo "SMTP_HOST: " . ($m[1] ?: 'VIDE') . "\n";
preg_match("/define\('SMTP_PORT',\s*(\d+)\)/", $content2, $m);
echo "SMTP_PORT: " . ($m[1] ?? '?') . "\n";
preg_match("/define\('SMTP_USER',\s*'([^']*)'\)/", $content2, $m);
echo "SMTP_USER: " . ($m[1] ?: 'VIDE') . "\n";
preg_match("/define\('SMTP_FROM',\s*'([^']*)'\)/", $content2, $m);
echo "SMTP_FROM: " . ($m[1] ?: 'VIDE') . "\n";
preg_match("/define\('SMTP_PASS',\s*'([^']*)'\)/", $content2, $m);
$pass = $m[1] ?? '';
echo "SMTP_PASS: " . ($pass ? str_repeat('*', strlen($pass)) : 'VIDE !!!') . "\n";

echo "\n=== Prochaine etape ===\n";
echo "1. Si SMTP_PASS est VIDE, le definir via File Manager Hostinger\n";
echo "2. Relancer le test: /api/test-smtp.php?token=saxho-diag-2025-x7k9\n";
echo "3. SUPPRIMER ce fichier fix-config.php immediatement !\n";
