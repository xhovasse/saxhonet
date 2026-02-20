<footer class="site-footer">
    <div class="container">
        <div class="footer__grid">
            <!-- Colonne 1 : Logo + tagline -->
            <div class="footer__brand">
                <span class="logo__text logo__text--light">S<span class="logo__accent">axho</span></span>
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
