<header class="site-header<?= ($currentSlug !== '' && $currentSlug !== 'home') ? ' header--light' : '' ?>" id="site-header">
    <div class="container header__inner">
        <!-- Logo -->
        <a href="<?= SITE_URL ?>/" class="header__logo" aria-label="<?= e(SITE_NAME) ?> — <?= e(t('site.tagline')) ?>">
            <span class="logo__text">saxh<svg class="logo__bulb" viewBox="0 0 28 34" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Rayons -->
                <line x1="14" y1="0" x2="14" y2="3.5" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                <line x1="3" y1="4.5" x2="5.5" y2="7" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                <line x1="25" y1="4.5" x2="22.5" y2="7" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                <line x1="0" y1="14" x2="3" y2="14" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                <line x1="25" y1="14" x2="28" y2="14" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                <!-- Bulbe -->
                <path d="M14 4C9.03 4 5 8.03 5 13c0 3.3 1.8 6.2 4.5 7.8.5.3.5.7.5 1.2v1h8v-1c0-.5 0-.9.5-1.2C21.2 19.2 23 16.3 23 13c0-4.97-4.03-9-9-9z" fill="#A63D6B"/>
                <!-- Culot -->
                <rect x="10" y="24" width="8" height="2.5" rx="1.25" fill="#8B3159"/>
                <rect x="10.5" y="27.5" width="7" height="2" rx="1" fill="#8B3159"/>
                <rect x="12" y="30.5" width="4" height="1.5" rx="0.75" fill="#8B3159"/>
            </svg></span>
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
                <?php $supportedLangs = SUPPORTED_LANGS; if ($l !== end($supportedLangs)): ?>
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
