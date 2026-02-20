<?php
/**
 * Saxho.net — Page À propos
 */
$pageCss = 'about.css';
$pageDescription = t('about.description');
?>

<!-- ==========================================
     HERO — Page header
     ========================================== -->
<section class="page-hero page-hero--about">
    <div class="container">
        <h1 class="page-hero__title reveal reveal-up"><?= e(t('about.title')) ?></h1>
        <p class="page-hero__subtitle reveal reveal-up reveal-delay-1"><?= e(t('about.subtitle')) ?></p>
    </div>
</section>

<!-- ==========================================
     L'HISTOIRE
     ========================================== -->
<section class="section about-history" id="histoire">
    <div class="container container--narrow">
        <h2 class="section__title text-center reveal reveal-up"><?= e(t('about.history_title')) ?></h2>
        <div class="about-history__content">
            <p class="about-history__text reveal reveal-up reveal-delay-1"><?= e(t('about.history_p1')) ?></p>
            <p class="about-history__text reveal reveal-up reveal-delay-2"><?= e(t('about.history_p2')) ?></p>
            <p class="about-history__text reveal reveal-up reveal-delay-3"><?= e(t('about.history_p3')) ?></p>
        </div>
    </div>
</section>

<!-- ==========================================
     L'ÉCOSYSTÈME
     ========================================== -->
<section class="section section--surface about-ecosystem" id="ecosysteme">
    <div class="container">
        <div class="section__header reveal reveal-up">
            <h2 class="section__title"><?= e(t('about.ecosystem_title')) ?></h2>
            <p class="section__subtitle"><?= e(t('about.ecosystem_intro')) ?></p>
        </div>

        <div class="about-ecosystem__grid">
            <?php
            $entities = [
                ['key' => 'saxho',  'url' => null,                     'class' => 'primary'],
                ['key' => 'ixila',  'url' => 'https://www.ixila.com',  'class' => 'secondary'],
                ['key' => 'pmside', 'url' => 'https://www.pmside.com', 'class' => 'tertiary'],
            ];
            foreach ($entities as $i => $entity):
            ?>
            <div class="about-ecosystem__card reveal reveal-up reveal-delay-<?= $i + 1 ?>">
                <div class="about-ecosystem__card-header">
                    <h3 class="about-ecosystem__card-title"><?= e(t('about.eco_' . $entity['key'] . '_title')) ?></h3>
                    <span class="badge badge--<?= $entity['class'] ?>"><?= e(t('about.eco_' . $entity['key'] . '_location')) ?></span>
                </div>
                <p class="about-ecosystem__card-text"><?= e(t('about.eco_' . $entity['key'] . '_text')) ?></p>
                <?php if ($entity['url']): ?>
                <a href="<?= $entity['url'] ?>" target="_blank" rel="noopener" class="about-ecosystem__card-link">
                    <?= e(t('about.eco_' . $entity['key'] . '_title')) ?> &rarr;
                </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="about-ecosystem__synergy reveal reveal-up"><?= e(t('about.ecosystem_synergy')) ?></p>
    </div>
</section>

<!-- ==========================================
     L'APPROCHE
     ========================================== -->
<section class="section about-values" id="approche">
    <div class="container">
        <h2 class="section__title text-center reveal reveal-up"><?= e(t('about.values_title')) ?></h2>

        <div class="about-values__grid">
            <?php
            $values = [
                ['num' => 1, 'icon' => '&#x1F50D;'],
                ['num' => 2, 'icon' => '&#x2699;&#xFE0F;'],
                ['num' => 3, 'icon' => '&#x1F3AF;'],
                ['num' => 4, 'icon' => '&#x1F4C8;'],
            ];
            foreach ($values as $v):
            ?>
            <div class="about-values__card reveal reveal-up reveal-delay-<?= $v['num'] ?>">
                <div class="about-values__icon" aria-hidden="true"><?= $v['icon'] ?></div>
                <h3 class="about-values__card-title"><?= e(t('about.value' . $v['num'] . '_title')) ?></h3>
                <p class="about-values__card-text"><?= e(t('about.value' . $v['num'] . '_text')) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==========================================
     CTA FINAL
     ========================================== -->
<section class="section section--gradient about-cta reveal reveal-scale">
    <div class="container text-center">
        <h2 class="about-cta__title"><?= e(t('about.cta_title')) ?></h2>
        <a href="<?= SITE_URL ?>/contact" class="btn btn--primary btn--lg btn--pulse"><?= e(t('about.cta_button')) ?></a>
    </div>
</section>
