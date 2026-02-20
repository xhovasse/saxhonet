<footer class="site-footer">
    <div class="container">
        <div class="footer__grid">
            <!-- Colonne 1 : Logo + tagline -->
            <div class="footer__brand">
                <span class="logo__text logo__text--light">saxh<svg class="logo__bulb" viewBox="0 0 28 34" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <line x1="14" y1="0" x2="14" y2="3.5" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="3" y1="4.5" x2="5.5" y2="7" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="25" y1="4.5" x2="22.5" y2="7" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="0" y1="14" x2="3" y2="14" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="25" y1="14" x2="28" y2="14" stroke="#C4547F" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M14 4C9.03 4 5 8.03 5 13c0 3.3 1.8 6.2 4.5 7.8.5.3.5.7.5 1.2v1h8v-1c0-.5 0-.9.5-1.2C21.2 19.2 23 16.3 23 13c0-4.97-4.03-9-9-9z" fill="#A63D6B"/>
                    <rect x="10" y="24" width="8" height="2.5" rx="1.25" fill="#8B3159"/>
                    <rect x="10.5" y="27.5" width="7" height="2" rx="1" fill="#8B3159"/>
                    <rect x="12" y="30.5" width="4" height="1.5" rx="0.75" fill="#8B3159"/>
                </svg></span>
                <p class="footer__tagline"><?= e(t('footer.tagline')) ?></p>
            </div>

            <!-- Colonne 2 : Liens rapides -->
            <div class="footer__links">
                <h4 class="footer__heading"><?= e(t('footer.links')) ?></h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/about"><?= e(t('nav.about')) ?></a></li>
                    <li><a href="<?= SITE_URL ?>/services"><?= e(t('nav.services')) ?></a></li>
                    <li><a href="<?= SITE_URL ?>/portfolio"><?= e(t('nav.portfolio')) ?></a></li>
                    <li><a href="<?= SITE_URL ?>/blog"><?= e(t('nav.blog')) ?></a></li>
                    <li><a href="<?= SITE_URL ?>/contact"><?= e(t('nav.contact')) ?></a></li>
                </ul>
            </div>

            <!-- Colonne 3 : Ecosysteme -->
            <div class="footer__ecosystem">
                <h4 class="footer__heading"><?= $lang === 'fr' ? 'Ecosysteme' : 'Ecosystem' ?></h4>
                <ul>
                    <li><a href="https://www.ixila.com" target="_blank" rel="noopener">IXILA</a></li>
                    <li><a href="https://www.pmside.com" target="_blank" rel="noopener">PM Side</a></li>
                </ul>
            </div>

            <!-- Colonne 4 : Contact -->
            <div class="footer__contact">
                <h4 class="footer__heading">Contact</h4>
                <address>
                    <p>47 Avenue de la Liberation</p>
                    <p>13850 Greasque, France</p>
                    <p><a href="mailto:contact@saxho.net">contact@saxho.net</a></p>
                </address>
            </div>
        </div>

        <div class="footer__bottom">
            <p><?= t('footer.copyright', ['year' => date('Y')]) ?></p>
            <div class="footer__legal">
                <a href="<?= SITE_URL ?>/legal"><?= e(t('footer.legal')) ?></a>
                <span class="footer__sep">|</span>
                <a href="<?= SITE_URL ?>/privacy"><?= e(t('footer.privacy')) ?></a>
            </div>
        </div>
    </div>
</footer>
