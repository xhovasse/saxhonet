<?php
/**
 * Saxho.net — Page Services
 */
$pageCss = 'services.css';
$pageJs = 'constellation-services.js';
$pageDescription = t('services.page_subtitle');
?>

<!-- ==========================================
     HERO — Services
     ========================================== -->
<div class="services-page" id="services-page">

<section class="page-hero page-hero--services">
    <div class="container">
        <h1 class="page-hero__title reveal reveal-up"><?= e(t('services.title')) ?></h1>
        <p class="page-hero__subtitle reveal reveal-up reveal-delay-1"><?= e(t('services.page_subtitle')) ?></p>
    </div>
</section>

<!-- ==========================================
     CONSTELLATION SERVICES (Diagonal Line)
     ========================================== -->
<?php
$services = [
    ['key' => 's1', 'icon' => '&#x1F4A1;', 'class' => '1'],
    ['key' => 's2', 'icon' => '&#x1F91D;', 'class' => '2'],
    ['key' => 's3', 'icon' => '&#x2699;',  'class' => '3'],
    ['key' => 's4', 'icon' => '&#x1F680;', 'class' => '4'],
    ['key' => 's5', 'icon' => '&#x1F331;', 'class' => '5'],
];
?>
<section class="section services-progression">
    <div class="container">
        <p class="services-progression__label reveal reveal-up"><?= e(t('services.progression_label')) ?></p>

        <div class="constellation-svc reveal reveal-up" id="constellation-svc"
             role="img" aria-label="<?= e(t('services.constellation_aria_label')) ?>">
            <canvas class="constellation-svc__canvas" id="constellation-svc-canvas" aria-hidden="true"></canvas>

            <?php foreach ($services as $i => $s): ?>
            <a href="#service-<?= $i + 1 ?>" class="constellation-svc__label constellation-svc__label--<?= $i + 1 ?>" data-service="<?= $i + 1 ?>">
                <span class="constellation-svc__num"><?= $i + 1 ?></span>
                <div>
                    <h3 class="constellation-svc__label-title constellation-svc__label-title--<?= $i + 1 ?>"><?= e(t('services.' . $s['key'] . '_title')) ?></h3>
                    <p class="constellation-svc__label-text"><?= e(t('services.' . $s['key'] . '_short')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Mobile : liste verticale -->
        <div class="services-list-mobile reveal reveal-up">
            <?php foreach ($services as $i => $s): ?>
            <a href="#service-<?= $i + 1 ?>" class="services-list-mobile__item">
                <div class="service-card__icon service-card__icon--<?= $s['class'] ?>" aria-hidden="true">
                    <?= $s['icon'] ?>
                </div>
                <span class="services-list-mobile__title"><?= e(t('services.' . $s['key'] . '_title')) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==========================================
     DETAIL DES 5 SERVICES
     ========================================== -->
<?php foreach ($services as $i => $s): ?>
<section class="section service-detail <?= $i % 2 === 1 ? 'section--surface' : '' ?>" id="service-<?= $i + 1 ?>">
    <div class="container">
        <div class="service-detail__header reveal reveal-up">
            <div class="service-detail__icon service-card__icon service-card__icon--<?= $s['class'] ?>" aria-hidden="true">
                <?= $s['icon'] ?>
            </div>
            <div class="service-detail__title-group">
                <h2 class="service-detail__title"><?= e(t('services.' . $s['key'] . '_title')) ?></h2>
                <?php if ($s['key'] === 's4' || $s['key'] === 's5'): ?>
                <span class="badge badge--<?= $s['key'] === 's4' ? 'secondary' : 'tertiary' ?>">
                    <?= e(t('services.' . $s['key'] . '_badge')) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="service-detail__grid">
            <div class="service-detail__block reveal reveal-up reveal-delay-1">
                <span class="service-detail__label"><?= e(t('services.detail_problem')) ?></span>
                <p class="service-detail__text"><?= e(t('services.' . $s['key'] . '_problem')) ?></p>
            </div>
            <div class="service-detail__block reveal reveal-up reveal-delay-2">
                <span class="service-detail__label"><?= e(t('services.detail_solution')) ?></span>
                <p class="service-detail__text"><?= e(t('services.' . $s['key'] . '_solution')) ?></p>
            </div>
            <div class="service-detail__block reveal reveal-up reveal-delay-3">
                <span class="service-detail__label"><?= e(t('services.detail_audience')) ?></span>
                <p class="service-detail__text"><?= e(t('services.' . $s['key'] . '_audience')) ?></p>
            </div>
        </div>

        <?php if ($s['key'] === 's5'): ?>
        <p class="service-detail__open reveal reveal-up"><?= e(t('services.s5_open')) ?></p>
        <?php endif; ?>

        <div class="service-detail__cta reveal reveal-up">
            <a href="<?= SITE_URL ?>/contact" class="btn btn--outline-dark btn--sm"><?= e(t('services.cta')) ?> &rarr;</a>
        </div>
    </div>
</section>
<?php endforeach; ?>

<!-- ==========================================
     CTA FINAL
     ========================================== -->
<section class="section section--gradient services-cta reveal reveal-scale">
    <div class="container text-center">
        <h2 class="services-cta__title"><?= e(t('services.cta_final_title')) ?></h2>
        <p class="services-cta__text"><?= e(t('services.cta_final_text')) ?></p>
        <a href="<?= SITE_URL ?>/contact" class="btn btn--primary btn--lg btn--pulse"><?= e(t('services.cta')) ?></a>
    </div>
</section>

</div><!-- /.services-page -->
