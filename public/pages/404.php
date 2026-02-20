<?php
/**
 * Saxho.net — Page 404
 */
?>
<section class="section" style="min-height: 60vh; display: flex; align-items: center;">
    <div class="container text-center">
        <h1 style="font-size: var(--fs-hero); color: var(--c-surface-hover); margin-bottom: var(--sp-lg);">404</h1>
        <h2 style="margin-bottom: var(--sp-md);"><?= $lang === 'fr' ? 'Page introuvable' : 'Page not found' ?></h2>
        <p class="text-muted" style="margin-bottom: var(--sp-xl);">
            <?= $lang === 'fr'
                ? 'La page que vous cherchez n\'existe pas ou a ete deplacee.'
                : 'The page you are looking for does not exist or has been moved.' ?>
        </p>
        <a href="<?= SITE_URL ?>/" class="btn btn--secondary"><?= e(t('nav.home')) ?></a>
    </div>
</section>
