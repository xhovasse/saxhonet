<header class="site-header" id="site-header">
    <div class="container header__inner">
        <!-- Logo -->
        <a href="<?= SITE_URL ?>/" class="header__logo" aria-label="<?= e(SITE_NAME) ?> — <?= e(t('site.tagline')) ?>">
            <span class="logo__text">S<span class="logo__accent">axho</span></span>
        </a>

        <!-- Navigation principale -->
        <nav class="header__nav" id="main-nav" aria-label="Navigation principale">
            <ul class="nav__list">
                <li class="nav__item">
                    <a href="<?= SITE_URL ?>/" class="nav__link<?= $currentSlug === '' ? ' nav__link--active' : '' ?>"><?= e(t('nav.home')) ?></a>
                </li>
                <li class="nav__item">
                    <a href="<?= SITE_URL ?>/about" class="nav__link<?= $currentSlug === 'about' ? ' nav__link--active' : '' ?>"><?= e(t('nav.about')) ?></a>
                </li>
                <li class="nav__item">
                    <a href="<?= SITE_URL ?>/services" class="nav__link<?= $currentSlug === 'services' ? ' nav__link--active' : '' ?>"><?= e(t('nav.services')) ?></a>
                </li>
                <li class="nav__item">
                    <a href="<?= SITE_URL ?>/portfolio" class="nav__link<?= $currentSlug === 'portfolio' ? ' nav__link--active' : '' ?>"><?= e(t('nav.portfolio')) ?></a>
                </li>
                <li class="nav__item">
                    <a href="<?= SITE_URL ?>/blog" class="nav__link<?= str_starts_with($currentSlug, 'blog') ? ' nav__link--active' : '' ?>"><?= e(t('nav.blog')) ?></a>
                </li>
                <li class="nav__item">
                    <a href="<?= SITE_URL ?>/contact" class="nav__link<?= $currentSlug === 'contact' ? ' nav__link--active' : '' ?>"><?= e(t('nav.contact')) ?></a>
                </li>
            </ul>
        </nav>

        <!-- Actions (langue + auth) -->
        <div class="header__actions">
            <!-- Selecteur de langue -->
            <div class="lang-switcher">
                <?php foreach (SUPPORTED_LANGS as $l): ?>
                <a href="?lang=<?= $l ?>" class="lang-switcher__link<?= $l === $lang ? ' lang-switcher__link--active' : '' ?>" aria-label="<?= $l === 'fr' ? 'Francais' : 'English' ?>">
                    <?= strtoupper($l) ?>
                </a>
                <?php if ($l !== end(SUPPORTED_LANGS)): ?>
                <span class="lang-switcher__sep">|</span>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Auth -->
            <?php if (is_logged_in()): ?>
            <div class="header__user">
                <a href="<?= SITE_URL ?>/profile" class="btn btn--sm btn--outline"><?= e(t('nav.profile')) ?></a>
            </div>
            <?php else: ?>
            <a href="<?= SITE_URL ?>/login" class="btn btn--sm btn--primary"><?= e(t('nav.login')) ?></a>
            <?php endif; ?>

            <!-- Burger mobile -->
            <button class="header__burger" id="burger-btn" aria-label="Menu" aria-expanded="false" aria-controls="main-nav">
                <span class="burger__line"></span>
                <span class="burger__line"></span>
                <span class="burger__line"></span>
            </button>
        </div>
    </div>
</header>
