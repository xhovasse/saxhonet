<header class="site-header<?= ($currentSlug !== '' && $currentSlug !== 'home') ? ' header--light' : '' ?>" id="site-header">
    <div class="container header__inner">
        <!-- Logo -->
        <a href="<?= SITE_URL ?>/" class="header__logo" aria-label="<?= e(SITE_NAME) ?> — <?= e(t('site.tagline')) ?>">
            <span class="logo__text">saxh<span class="logo__accent">o</span></span>
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
                <?php if (is_admin()): ?>
                <li class="nav__item">
                    <a href="<?= SITE_URL ?>/admin" class="nav__link<?= str_starts_with($currentSlug, 'admin') ? ' nav__link--active' : '' ?>">Admin</a>
                </li>
                <?php endif; ?>
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
            <div class="user-menu" id="user-menu">
                <button class="user-menu__trigger" id="user-menu-trigger" aria-expanded="false" aria-haspopup="true" aria-label="<?= e(t('nav.profile')) ?>">
                    <span class="user-menu__avatar"><?= strtoupper(mb_substr($_SESSION['user_name'] ?? '?', 0, 1)) ?></span>
                </button>
                <div class="user-menu__dropdown" id="user-menu-dropdown">
                    <div class="user-menu__info">
                        <span class="user-menu__name"><?= e($_SESSION['user_name'] ?? '') ?></span>
                        <span class="user-menu__email"><?= e($_SESSION['user_email'] ?? '') ?></span>
                    </div>
                    <hr class="user-menu__sep">
                    <a href="<?= SITE_URL ?>/profile" class="user-menu__item">
                        <?= e(t('nav.profile')) ?>
                    </a>
                    <hr class="user-menu__sep">
                    <form action="<?= SITE_URL ?>/api/auth/logout" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="user-menu__item user-menu__item--danger">
                            <?= e(t('nav.logout')) ?>
                        </button>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="header__auth-buttons">
                <a href="<?= SITE_URL ?>/login" class="btn btn--sm btn--outline"><?= e(t('nav.login')) ?></a>
                <a href="<?= SITE_URL ?>/register" class="btn btn--sm btn--primary"><?= e(t('nav.register')) ?></a>
            </div>
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
