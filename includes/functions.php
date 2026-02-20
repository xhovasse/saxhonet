<?php
/**
 * Fonctions utilitaires
 */

/**
 * Generer un token CSRF et le stocker en session
 */
function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Champ hidden CSRF pour les formulaires
 */
function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

/**
 * Verifier un token CSRF
 */
function csrf_verify(string $token): bool
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    $valid = hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    // Regenerer apres verification
    unset($_SESSION[CSRF_TOKEN_NAME]);
    return $valid;
}

/**
 * Echapper pour le HTML (protection XSS)
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirect avec header Location
 */
function redirect(string $url, int $code = 302): never
{
    header('Location: ' . $url, true, $code);
    exit;
}

/**
 * Messages flash (stockes en session, affiches une fois)
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash_messages'][] = [
        'type'    => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message,
    ];
}

/**
 * Recuperer et vider les messages flash
 */
function get_flash(): array
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Rendre le HTML des messages flash
 */
function render_flash(): string
{
    $messages = get_flash();
    if (empty($messages)) {
        return '';
    }
    $html = '<div class="flash-container">';
    foreach ($messages as $msg) {
        $html .= sprintf(
            '<div class="flash flash--%s" role="alert">%s<button class="flash__close" aria-label="Fermer">&times;</button></div>',
            e($msg['type']),
            e($msg['message'])
        );
    }
    $html .= '</div>';
    return $html;
}

/**
 * Envoyer un email
 */
function send_email(string $to, string $subject, string $body, bool $isHtml = true): bool
{
    if (USE_SMTP) {
        return send_email_smtp($to, $subject, $body, $isHtml);
    }

    $headers = [
        'From'         => SMTP_FROM_NAME . ' <' . SMTP_FROM . '>',
        'Reply-To'     => ADMIN_EMAIL,
        'MIME-Version' => '1.0',
        'Content-Type' => $isHtml ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8',
        'X-Mailer'     => 'Saxho/1.0',
    ];

    $headerStr = '';
    foreach ($headers as $key => $value) {
        $headerStr .= "$key: $value\r\n";
    }

    return mail($to, $subject, $body, $headerStr);
}

/**
 * Envoi SMTP basique (sans dependance externe)
 */
function send_email_smtp(string $to, string $subject, string $body, bool $isHtml = true): bool
{
    try {
        $socket = fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
        if (!$socket) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return false;
        }

        $read = fgets($socket, 512);
        fputs($socket, "EHLO " . parse_url(SITE_URL, PHP_URL_HOST) . "\r\n");
        $read = fgets($socket, 512);

        fputs($socket, "STARTTLS\r\n");
        $read = fgets($socket, 512);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        fputs($socket, "EHLO " . parse_url(SITE_URL, PHP_URL_HOST) . "\r\n");
        $read = fgets($socket, 512);

        fputs($socket, "AUTH LOGIN\r\n");
        $read = fgets($socket, 512);
        fputs($socket, base64_encode(SMTP_USER) . "\r\n");
        $read = fgets($socket, 512);
        fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
        $read = fgets($socket, 512);

        fputs($socket, "MAIL FROM:<" . SMTP_FROM . ">\r\n");
        $read = fgets($socket, 512);
        fputs($socket, "RCPT TO:<$to>\r\n");
        $read = fgets($socket, 512);
        fputs($socket, "DATA\r\n");
        $read = fgets($socket, 512);

        $contentType = $isHtml ? 'text/html' : 'text/plain';
        $message = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
        $message .= "To: <$to>\r\n";
        $message .= "Subject: $subject\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: $contentType; charset=UTF-8\r\n";
        $message .= "\r\n";
        $message .= $body . "\r\n.\r\n";

        fputs($socket, $message);
        $read = fgets($socket, 512);

        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    } catch (\Exception $e) {
        error_log("SMTP error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generer un slug a partir d'un texte
 */
function slugify(string $text): string
{
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Formater une date selon la langue
 */
function format_date(string $date, string $lang = null): string
{
    $lang = $lang ?? ($_SESSION['lang'] ?? DEFAULT_LANG);
    $timestamp = strtotime($date);

    if ($lang === 'fr') {
        $months = ['janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin',
                   'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre'];
        return date('j', $timestamp) . ' ' . $months[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
    }

    return date('F j, Y', $timestamp);
}

/**
 * Calculer le temps de lecture
 */
function reading_time(string $text): int
{
    $wordCount = str_word_count(strip_tags($text));
    return max(1, (int) ceil($wordCount / 200));
}

/**
 * Verifier si la requete est AJAX
 */
function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Repondre en JSON
 */
function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtenir l'IP du visiteur
 */
function get_ip(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}
