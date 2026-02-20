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
 * Avec logging detaille a chaque etape pour le debug
 */
function send_email_smtp(string $to, string $subject, string $body, bool $isHtml = true): bool
{
    try {
        // Port 465 = SSL direct, port 587 = STARTTLS
        $useSSL = (SMTP_PORT == 465);
        $host = ($useSSL ? 'ssl://' : '') . SMTP_HOST;

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ]);

        $socket = stream_socket_client(
            "$host:" . SMTP_PORT,
            $errno, $errstr, 15,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            error_log("SMTP: connection failed to $host:" . SMTP_PORT . " — $errstr ($errno)");
            return false;
        }

        // Timeout de lecture
        stream_set_timeout($socket, 10);

        $hostname = parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost';

        // Lire le banner
        $banner = smtp_read($socket);
        if (!smtp_ok($banner, '220')) {
            error_log("SMTP: bad banner: $banner");
            fclose($socket);
            return false;
        }

        // EHLO
        smtp_send($socket, "EHLO $hostname");
        $ehlo = smtp_read_multi($socket);

        // STARTTLS uniquement si pas SSL direct
        if (!$useSSL) {
            smtp_send($socket, "STARTTLS");
            $tls = smtp_read($socket);
            if (!smtp_ok($tls, '220')) {
                error_log("SMTP: STARTTLS failed: $tls");
                fclose($socket);
                return false;
            }
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);

            smtp_send($socket, "EHLO $hostname");
            smtp_read_multi($socket);
        }

        // AUTH LOGIN
        smtp_send($socket, "AUTH LOGIN");
        $auth1 = smtp_read($socket);
        if (!smtp_ok($auth1, '334')) {
            error_log("SMTP: AUTH LOGIN rejected: $auth1");
            fclose($socket);
            return false;
        }

        smtp_send($socket, base64_encode(SMTP_USER));
        $auth2 = smtp_read($socket);
        if (!smtp_ok($auth2, '334')) {
            error_log("SMTP: username rejected: $auth2");
            fclose($socket);
            return false;
        }

        smtp_send($socket, base64_encode(SMTP_PASS));
        $auth3 = smtp_read($socket);
        if (!smtp_ok($auth3, '235')) {
            error_log("SMTP: auth failed (wrong password?): $auth3");
            fclose($socket);
            return false;
        }

        // MAIL FROM
        smtp_send($socket, "MAIL FROM:<" . SMTP_FROM . ">");
        $from = smtp_read($socket);
        if (!smtp_ok($from, '250')) {
            error_log("SMTP: MAIL FROM rejected: $from");
            fclose($socket);
            return false;
        }

        // RCPT TO
        smtp_send($socket, "RCPT TO:<$to>");
        $rcpt = smtp_read($socket);
        if (!smtp_ok($rcpt, '250')) {
            error_log("SMTP: RCPT TO rejected: $rcpt");
            fclose($socket);
            return false;
        }

        // DATA
        smtp_send($socket, "DATA");
        $data = smtp_read($socket);
        if (!smtp_ok($data, '354')) {
            error_log("SMTP: DATA rejected: $data");
            fclose($socket);
            return false;
        }

        // Construire le message
        $contentType = $isHtml ? 'text/html' : 'text/plain';
        $message = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">\r\n";
        $message .= "To: <$to>\r\n";
        $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $message .= "Date: " . date('r') . "\r\n";
        $message .= "Message-ID: <" . uniqid('saxho-', true) . "@$hostname>\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: $contentType; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "\r\n";
        $message .= chunk_split(base64_encode($body));
        $message .= "\r\n.\r\n";

        fputs($socket, $message);
        $result = smtp_read($socket);

        smtp_send($socket, "QUIT");
        fclose($socket);

        $success = smtp_ok($result, '250');
        if (!$success) {
            error_log("SMTP: message not accepted: $result");
        }
        return $success;

    } catch (\Throwable $e) {
        error_log("SMTP error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        return false;
    }
}

/**
 * Envoyer une commande SMTP
 */
function smtp_send($socket, string $command): void
{
    fputs($socket, $command . "\r\n");
}

/**
 * Lire une ligne de reponse SMTP
 */
function smtp_read($socket): string
{
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        // Derniere ligne : code + espace (pas de tiret)
        if (isset($line[3]) && $line[3] !== '-') break;
    }
    return trim($response);
}

/**
 * Lire une reponse multi-lignes SMTP (EHLO)
 */
function smtp_read_multi($socket): string
{
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') break;
    }
    return trim($response);
}

/**
 * Verifier si une reponse SMTP commence par le code attendu
 */
function smtp_ok(string $response, string $code): bool
{
    return str_starts_with($response, $code);
}

/**
 * Generer le HTML d'un email transactionnel Saxho
 */
function email_template(string $title, string $content, string $buttonUrl = '', string $buttonText = ''): string
{
    $button = '';
    if ($buttonUrl && $buttonText) {
        $button = '<div style="text-align:center;margin:32px 0;">
            <a href="' . e($buttonUrl) . '" style="display:inline-block;padding:14px 32px;background:#1B3A9E;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:600;font-size:16px;">' . e($buttonText) . '</a>
        </div>';
    }

    return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head>
    <body style="margin:0;padding:0;background:#F8F7F4;font-family:Arial,sans-serif;">
        <div style="max-width:560px;margin:40px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
            <div style="background:#0D0D1A;padding:24px 32px;text-align:center;">
                <span style="font-size:24px;font-weight:600;color:#ffffff;letter-spacing:1px;">saxh<span style="color:#A63D6B;">o</span></span>
            </div>
            <div style="padding:40px 32px;">
                <h1 style="margin:0 0 24px;font-size:22px;color:#0D0D1A;">' . $title . '</h1>
                <div style="font-size:15px;line-height:1.7;color:#333333;">' . $content . '</div>
                ' . $button . '
            </div>
            <div style="background:#F8F7F4;padding:20px 32px;text-align:center;font-size:12px;color:#888888;">
                SAXHO &mdash; De l\'id&eacute;e au succ&egrave;s<br>
                <a href="' . SITE_URL . '" style="color:#1B3A9E;text-decoration:none;">' . SITE_URL . '</a>
            </div>
        </div>
    </body></html>';
}

/**
 * Generer un slug a partir d'un texte
 */
function slugify(string $text): string
{
    // Utiliser intl si disponible, sinon fallback manuel
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    } else {
        $text = mb_strtolower($text, 'UTF-8');
        // Remplacements courants pour le francais
        $accents = [
            'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a',
            'è'=>'e','ê'=>'e','ë'=>'e','é'=>'e',
            'ì'=>'i','î'=>'i','ï'=>'i','í'=>'i',
            'ò'=>'o','ô'=>'o','ö'=>'o','ó'=>'o','õ'=>'o',
            'ù'=>'u','û'=>'u','ü'=>'u','ú'=>'u',
            'ÿ'=>'y','ý'=>'y','ñ'=>'n','ç'=>'c',
            'œ'=>'oe','æ'=>'ae',
        ];
        $text = strtr($text, $accents);
    }
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
