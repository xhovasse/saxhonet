#!/usr/bin/env php
<?php
/**
 * Saxho.net — Test SMTP
 * Usage en SSH : php _app/test-smtp.php
 *
 * Teste la connexion SMTP et l'envoi d'un email de test.
 * Affiche chaque etape en temps reel.
 */

// Charger la config
require_once __DIR__ . '/includes/config.php';

echo "\n";
echo "========================================\n";
echo "  SAXHO — Test SMTP\n";
echo "========================================\n\n";

echo "Configuration:\n";
echo "  SMTP_HOST:  " . SMTP_HOST . "\n";
echo "  SMTP_PORT:  " . SMTP_PORT . "\n";
echo "  SMTP_USER:  " . SMTP_USER . "\n";
echo "  SMTP_FROM:  " . SMTP_FROM . "\n";
echo "  ADMIN_EMAIL: " . ADMIN_EMAIL . "\n";
echo "  USE_SMTP:   " . (USE_SMTP ? 'true' : 'false') . "\n";
echo "\n";

// Tester la connexion
$useSSL = (SMTP_PORT == 465);
$host = ($useSSL ? 'ssl://' : '') . SMTP_HOST;

echo "[1/8] Connexion a $host:" . SMTP_PORT . " ...\n";

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
    echo "  ❌ ECHEC: $errstr ($errno)\n";
    echo "\n  Verifiez que le serveur SMTP est accessible.\n";
    echo "  Sur Hostinger, le host est generalement smtp.hostinger.com\n";
    exit(1);
}

stream_set_timeout($socket, 10);
echo "  ✅ Connecte\n\n";

// Fonctions locales
function s_send($socket, $cmd) {
    echo "  → $cmd\n";
    fputs($socket, $cmd . "\r\n");
}

function s_read($socket) {
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        if (isset($line[3]) && $line[3] !== '-') break;
    }
    $response = trim($response);
    echo "  ← $response\n";
    return $response;
}

function s_read_multi($socket) {
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    $response = trim($response);
    // Afficher que la premiere et derniere ligne (EHLO est verbeux)
    $lines = explode("\n", $response);
    echo "  ← " . $lines[0] . " (+" . (count($lines) - 1) . " lignes)\n";
    return $response;
}

function check($response, $code, $step) {
    if (!str_starts_with($response, $code)) {
        echo "  ❌ ECHEC a l'etape: $step\n";
        echo "  Reponse attendue: $code...\n";
        echo "  Reponse recue: $response\n";
        return false;
    }
    echo "  ✅ OK\n\n";
    return true;
}

// Banner
echo "[2/8] Lecture du banner SMTP ...\n";
$banner = s_read($socket);
if (!check($banner, '220', 'Banner')) exit(1);

// EHLO
$hostname = parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost';
echo "[3/8] EHLO $hostname ...\n";
s_send($socket, "EHLO $hostname");
$ehlo = s_read_multi($socket);
if (!check($ehlo, '250', 'EHLO')) exit(1);

// STARTTLS si necessaire
if (!$useSSL) {
    echo "[3b] STARTTLS ...\n";
    s_send($socket, "STARTTLS");
    $tls = s_read($socket);
    if (!check($tls, '220', 'STARTTLS')) exit(1);
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    echo "  ✅ Chiffrement TLS actif\n\n";

    s_send($socket, "EHLO $hostname");
    s_read_multi($socket);
}

// AUTH LOGIN
echo "[4/8] AUTH LOGIN ...\n";
s_send($socket, "AUTH LOGIN");
$a1 = s_read($socket);
if (!check($a1, '334', 'AUTH LOGIN')) exit(1);

echo "[5/8] Envoi username (base64) ...\n";
s_send($socket, base64_encode(SMTP_USER));
$a2 = s_read($socket);
if (!check($a2, '334', 'Username')) exit(1);

echo "[6/8] Envoi password (base64) ...\n";
fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
echo "  → [MASKED]\n";
$a3 = s_read($socket);
if (!check($a3, '235', 'Password / Auth')) {
    echo "  ⚠️  Le mot de passe est probablement incorrect.\n";
    echo "  Verifiez SMTP_PASS dans config.php.\n";
    echo "  Le compte SMTP est: " . SMTP_USER . "\n";
    exit(1);
}

// MAIL FROM
echo "[7/8] MAIL FROM + RCPT TO + DATA ...\n";
s_send($socket, "MAIL FROM:<" . SMTP_FROM . ">");
$mf = s_read($socket);
if (!check($mf, '250', 'MAIL FROM')) {
    echo "  ⚠️  L'adresse d'expediteur " . SMTP_FROM . " est rejetee.\n";
    exit(1);
}

s_send($socket, "RCPT TO:<" . ADMIN_EMAIL . ">");
$rcpt = s_read($socket);
if (!check($rcpt, '250', 'RCPT TO')) {
    echo "  ⚠️  L'adresse destinataire " . ADMIN_EMAIL . " est rejetee.\n";
    echo "  Verifiez que cette adresse existe et est accessible.\n";
    exit(1);
}

s_send($socket, "DATA");
$data = s_read($socket);
if (!check($data, '354', 'DATA')) exit(1);

// Envoyer le message
echo "[8/8] Envoi du message de test ...\n";
$msg = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
$msg .= "To: <" . ADMIN_EMAIL . ">\r\n";
$msg .= "Subject: =?UTF-8?B?" . base64_encode("[Saxho] Test SMTP — " . date('Y-m-d H:i:s')) . "?=\r\n";
$msg .= "Date: " . date('r') . "\r\n";
$msg .= "Message-ID: <test-" . uniqid('', true) . "@$hostname>\r\n";
$msg .= "MIME-Version: 1.0\r\n";
$msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
$msg .= "Content-Transfer-Encoding: base64\r\n";
$msg .= "\r\n";
$msg .= chunk_split(base64_encode("Ceci est un email de test envoye depuis le script test-smtp.php.\n\nDate: " . date('Y-m-d H:i:s') . "\nServeur: $hostname\nFrom: " . SMTP_FROM . "\nTo: " . ADMIN_EMAIL));
$msg .= "\r\n.\r\n";

fputs($socket, $msg);
$result = s_read($socket);

s_send($socket, "QUIT");
fclose($socket);

echo "\n";
if (str_starts_with($result, '250')) {
    echo "========================================\n";
    echo "  ✅ EMAIL ENVOYE AVEC SUCCES !\n";
    echo "========================================\n";
    echo "\n";
    echo "  Verifiez la boite de reception de: " . ADMIN_EMAIL . "\n";
    echo "  (et le dossier spam/junk)\n";
} else {
    echo "========================================\n";
    echo "  ❌ L'EMAIL N'A PAS ETE ACCEPTE\n";
    echo "========================================\n";
    echo "\n";
    echo "  Reponse du serveur: $result\n";
}
echo "\n";
