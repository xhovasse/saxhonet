<?php
/**
 * Systeme d'internationalisation (i18n)
 */

/**
 * Charger les traductions pour une langue
 */
function load_translations(string $lang): array
{
    static $cache = [];

    if (isset($cache[$lang])) {
        return $cache[$lang];
    }

    $file = LANG_PATH . '/' . $lang . '.json';
    if (!file_exists($file)) {
        $file = LANG_PATH . '/' . DEFAULT_LANG . '.json';
    }

    $content = file_get_contents($file);
    $cache[$lang] = json_decode($content, true) ?? [];

    return $cache[$lang];
}

/**
 * Traduire une cle (notation pointee)
 * Ex: t('nav.home') retourne la valeur de $translations['nav']['home']
 */
function t(string $key, array $replacements = []): string
{
    global $translations;

    $parts = explode('.', $key);
    $value = $translations;

    foreach ($parts as $part) {
        if (!is_array($value) || !isset($value[$part])) {
            // Cle introuvable, retourner la cle elle-meme
            return $key;
        }
        $value = $value[$part];
    }

    if (!is_string($value)) {
        return $key;
    }

    // Remplacements : {name} => valeur
    foreach ($replacements as $placeholder => $replacement) {
        $value = str_replace('{' . $placeholder . '}', $replacement, $value);
    }

    return $value;
}

/**
 * Detecter et definir la langue
 */
function detect_language(): string
{
    // 1. Parametre URL explicite
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $_GET['lang'];
        setcookie('saxho_lang', $_GET['lang'], time() + 2592000, '/', '', true, true);
        return $_GET['lang'];
    }

    // 2. Session
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], SUPPORTED_LANGS)) {
        return $_SESSION['lang'];
    }

    // 3. Cookie
    if (isset($_COOKIE['saxho_lang']) && in_array($_COOKIE['saxho_lang'], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $_COOKIE['saxho_lang'];
        return $_COOKIE['saxho_lang'];
    }

    // 4. Header Accept-Language du navigateur
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if (in_array($browserLang, SUPPORTED_LANGS)) {
            $_SESSION['lang'] = $browserLang;
            return $browserLang;
        }
    }

    // 5. Langue par defaut
    $_SESSION['lang'] = DEFAULT_LANG;
    return DEFAULT_LANG;
}
