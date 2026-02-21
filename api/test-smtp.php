<?php
/**
 * Saxho.net — Test SMTP via navigateur
 * Securise par token secret — A SUPPRIMER apres diagnostic
 *
 * Usage : https://saxho.net/api/test-smtp.php?token=saxho-diag-2025-x7k9
 */

// Token secret (a usage unique, supprimer ce fichier apres test)
define('DIAG_TOKEN', 'saxho-diag-2025-x7k9');

// Verifier le token
if (($_GET['token'] ?? '') !== DIAG_TOKEN) {
    http_response_code(404);
    echo "Not found.";
    exit;
}

// Charger la config
require_once __DIR__ . '/../_app/includes/config.php';
require_once __DIR__ . '/../_app/includes/functions.php';

// Output texte brut
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-cache, no-store');

echo "========================================\n";
echo "  SAXHO — Diagnostic SMTP\n";
echo "  " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

echo "Configuration:\n";
echo "  SMTP_HOST:   " . SMTP_HOST . "\n";
echo "  SMTP_PORT:   " . SMTP_PORT . "\n";
echo "  SMTP_USER:   " . SMTP_USER . "\n";
echo "  SMTP_FROM:   " . SMTP_FROM . "\n";
echo "  ADMIN_EMAIL: " . ADMIN_EMAIL . "\n";
echo "  USE_SMTP:    " . (USE_SMTP ? 'true' : 'false') . "\n\n";

// Fonctions locales d'affichage
function out($msg) { echo $msg . "\n"; flush(); }

function s_send_test($socket, $cmd) {
    out("  -> $cmd");
    fputs($socket, $cmd . "\r\n");
}

function s_read_test($socket) {
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        if (isset($line[3]) && $line[3] !== '-') break;
    }
    $response = trim($response);
    out("  <- $response");
    return $response;
}

function s_read_multi_test($socket) {
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    $response = trim($response);
    $lines = explode("\n", $response);
    out("  <- " . $lines[0] . " (+" . (count($lines) - 1) . " lignes)");
    return $response;
}

function check_test($response, $code, $step) {
    if (!str_starts_with($response, $code)) {
        out("  [ECHEC] $step");
        out("  Attendu: $code... | Recu: $response");
        return false;
    }
    out("  [OK] $step\n");
    return true;
}

// === Debut du test ===

$useSSL = (SMTP_PORT == 465);
$host = ($useSSL ? 'ssl://' : '') . SMTP_HOST;

out("[1/8] Connexion a $host:" . SMTP_PORT . " ...");

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ]
]);

$socket = @stream_socket_client(
    "$host:" . SMTP_PORT,
    $errno, $errstr, 15,
    STREAM_CLIENT_CONNECT,
    $context
);

if (!$socket) {
    out("  [ECHEC] Connexion impossible: $errstr ($errno)");
    out("\n  -> Verifiez SMTP_HOST et SMTP_PORT dans config.php");
    exit;
}

stream_set_timeout($socket, 10);
out("  [OK] Connecte\n");

// Banner
out("[2/8] Lecture du banner ...");
$banner = s_read_test($socket);
if (!check_test($banner, '220', 'Banner SMTP')) { fclose($socket); exit; }

// EHLO
$hostname = parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost';
out("[3/8] EHLO $hostname ...");
s_send_test($socket, "EHLO $hostname");
$ehlo = s_read_multi_test($socket);
if (!check_test($ehlo, '250', 'EHLO')) { fclose($socket); exit; }

// STARTTLS si necessaire
if (!$useSSL) {
    out("[3b] STARTTLS ...");
    s_send_test($socket, "STARTTLS");
    $tls = s_read_test($socket);
    if (!check_test($tls, '220', 'STARTTLS')) { fclose($socket); exit; }
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    out("  [OK] Chiffrement TLS actif\n");
    s_send_test($socket, "EHLO $hostname");
    s_read_multi_test($socket);
}

// AUTH LOGIN
out("[4/8] AUTH LOGIN ...");
s_send_test($socket, "AUTH LOGIN");
$a1 = s_read_test($socket);
if (!check_test($a1, '334', 'AUTH LOGIN')) { fclose($socket); exit; }

out("[5/8] Envoi username ...");
s_send_test($socket, base64_encode(SMTP_USER));
$a2 = s_read_test($socket);
if (!check_test($a2, '334', 'Username accepte')) { fclose($socket); exit; }

out("[6/8] Envoi password ...");
fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
out("  -> [MOT DE PASSE MASQUE]");
$a3 = s_read_test($socket);
if (!check_test($a3, '235', 'Authentification')) {
    out("  !! Le mot de passe est probablement incorrect.");
    out("  !! Compte: " . SMTP_USER);
    out("  !! Verifiez SMTP_PASS dans config.php");
    fclose($socket); exit;
}

// MAIL FROM
out("[7/8] MAIL FROM + RCPT TO ...");
s_send_test($socket, "MAIL FROM:<" . SMTP_FROM . ">");
$mf = s_read_test($socket);
if (!check_test($mf, '250', 'MAIL FROM')) {
    out("  !! L'adresse expediteur " . SMTP_FROM . " est rejetee.");
    fclose($socket); exit;
}

s_send_test($socket, "RCPT TO:<" . ADMIN_EMAIL . ">");
$rcpt = s_read_test($socket);
if (!check_test($rcpt, '250', 'RCPT TO')) {
    out("  !! L'adresse destinataire " . ADMIN_EMAIL . " est rejetee.");
    out("  !! Verifiez que cette boite mail existe bien.");
    fclose($socket); exit;
}

s_send_test($socket, "DATA");
$data = s_read_test($socket);
if (!check_test($data, '354', 'DATA')) { fclose($socket); exit; }

// Message
out("[8/8] Envoi du message de test ...");
$testSubject = "[Saxho] Test SMTP — " . date('Y-m-d H:i:s');
$testBody = "Ceci est un email de test.\n\nDate: " . date('Y-m-d H:i:s') . "\nFrom: " . SMTP_FROM . "\nTo: " . ADMIN_EMAIL;

$msg = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
$msg .= "To: <" . ADMIN_EMAIL . ">\r\n";
$msg .= "Subject: =?UTF-8?B?" . base64_encode($testSubject) . "?=\r\n";
$msg .= "Date: " . date('r') . "\r\n";
$msg .= "Message-ID: <test-" . uniqid('', true) . "@$hostname>\r\n";
$msg .= "MIME-Version: 1.0\r\n";
$msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
$msg .= "Content-Transfer-Encoding: base64\r\n";
$msg .= "\r\n";
$msg .= chunk_split(base64_encode($testBody));
$msg .= "\r\n.\r\n";

fputs($socket, $msg);
$result = s_read_test($socket);

s_send_test($socket, "QUIT");
fclose($socket);

echo "\n";
if (str_starts_with($result, '250')) {
    echo "========================================\n";
    echo "  [SUCCES] EMAIL ENVOYE !\n";
    echo "========================================\n";
    echo "\n  Verifiez la boite: " . ADMIN_EMAIL . "\n";
    echo "  (verifiez aussi le dossier spam)\n";
} else {
    echo "========================================\n";
    echo "  [ECHEC] EMAIL NON ACCEPTE\n";
    echo "========================================\n";
    echo "\n  Reponse serveur: $result\n";
}

echo "\n--- Aussi: consultez _app/logs/smtp.log pour les logs du formulaire contact ---\n";

// Aussi afficher le contenu du log s'il existe
$logFile = ROOT_PATH . '/_app/logs/smtp.log';
if (file_exists($logFile)) {
    echo "\n=== Contenu de smtp.log ===\n";
    echo file_get_contents($logFile);
} else {
    echo "\n(smtp.log n'existe pas encore — soumettez le formulaire contact d'abord)\n";
}
