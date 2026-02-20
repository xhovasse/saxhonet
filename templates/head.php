<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= !empty($pageTitle) ? e($pageTitle) . ' — ' . e(SITE_NAME) : e(SITE_NAME) . ' — ' . e(t('site.tagline')) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= !empty($pageTitle) ? e($pageTitle) . ' — ' . e(SITE_NAME) : e(SITE_NAME) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:image" content="<?= e($pageOgImage) ?>">
    <meta property="og:url" content="<?= e(SITE_URL . '/' . $currentSlug) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?= $lang === 'fr' ? 'fr_FR' : 'en_US' ?>">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= !empty($pageTitle) ? e($pageTitle) . ' — ' . e(SITE_NAME) : e(SITE_NAME) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($pageOgImage) ?>">

    <!-- Canonical -->
    <link rel="canonical" href="<?= e(SITE_URL . '/' . $currentSlug) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/img/favicon.ico">

    <!-- Fonts (self-hosted) -->
    <link rel="preload" href="<?= SITE_URL ?>/assets/fonts/Outfit-Variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= SITE_URL ?>/assets/fonts/SpaceGrotesk-Variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= SITE_URL ?>/assets/fonts/Inter-Variable.woff2" as="font" type="font/woff2" crossorigin>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/variables.css?v=1.6">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/reset.css?v=1.6">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/layout.css?v=1.6">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/components.css?v=1.6">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/animations.css?v=1.6">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/responsive.css?v=1.6">
    <?php if (!empty($pageCss)): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= $pageCss ?>?v=1.6">
    <?php endif; ?>

    <style>
        @font-face {
            font-family: 'Space Grotesk';
            src: url('<?= SITE_URL ?>/assets/fonts/SpaceGrotesk-Variable.woff2') format('woff2');
            font-weight: 300 700;
            font-display: swap;
        }
        @font-face {
            font-family: 'Inter';
            src: url('<?= SITE_URL ?>/assets/fonts/Inter-Variable.woff2') format('woff2');
            font-weight: 100 900;
            font-display: swap;
        }
        @font-face {
            font-family: 'JetBrains Mono';
            src: url('<?= SITE_URL ?>/assets/fonts/JetBrainsMono-Regular.woff2') format('woff2');
            font-weight: 400;
            font-display: swap;
        }
        @font-face {
            font-family: 'Outfit';
            src: url('<?= SITE_URL ?>/assets/fonts/Outfit-Variable.woff2') format('woff2');
            font-weight: 100 900;
            font-display: swap;
        }
    </style>

    <!-- Fallback: si le JS ne charge pas, tout reste visible -->
    <noscript>
    <style>
        .reveal, .reveal-fade, .reveal-up, .reveal-down, .reveal-left, .reveal-right, .reveal-scale {
            opacity: 1 !important;
            transform: none !important;
        }
    </style>
    </noscript>
</head>
