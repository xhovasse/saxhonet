<?php
/**
 * Saxho.net — Page d'accueil
 */
$pageCss = 'home.css';
$pageDescription = t('site.description');

// Portfolio preview — dynamic projects
$db = getDB();
$previewProjects = $db->query(
    'SELECT name_fr, name_en, slug, domain, phase, pitch_fr, pitch_en, image
     FROM projects WHERE is_visible = 1
     ORDER BY display_order ASC, created_at DESC
     LIMIT 3'
)->fetchAll();
$lang = $_SESSION['lang'] ?? 'fr';
$isLogged = is_logged_in();
?>

<!-- ==========================================
     MOUVEMENT 1 — Le constat universel (Hero)
     ========================================== -->
<section class="hero" id="hero">
    <!-- Reseau neuronal anime -->
    <canvas class="hero__neural" id="neural-canvas" aria-hidden="true"></canvas>

    <div class="hero__content">
        <h1 class="hero__title" id="hero-typed" data-typed-text="<?= e(t('home.hero_title')) ?>">
            <?= e(t('home.hero_title')) ?>
        </h1>
        <p class="hero__subtitle">
            <?= e(t('home.hero_subtitle')) ?>
        </p>
    </div>

    <!-- Scroll indicator -->
    <div class="scroll-indicator" aria-hidden="true">
        <div class="scroll-indicator__arrow"></div>
    </div>
</section>

<!-- ==========================================
     MOUVEMENT 2 — Le paradoxe
     ========================================== -->
<section class="paradox section" id="paradox">
    <div class="container">
        <h2 class="paradox__title reveal reveal-up">
            <?= e(t('home.paradox_title')) ?>
        </h2>

        <div class="paradox__grid">
            <div class="paradox__card reveal reveal-up reveal-delay-1">
                <h3 class="paradox__card-title"><?= e(t('home.paradox_dilemma_title')) ?></h3>
                <p class="paradox__card-text"><?= e(t('home.paradox_dilemma')) ?></p>
            </div>
            <div class="paradox__card reveal reveal-up reveal-delay-2">
                <h3 class="paradox__card-title"><?= e(t('home.paradox_tension_title')) ?></h3>
                <p class="paradox__card-text"><?= e(t('home.paradox_tension')) ?></p>
            </div>
            <div class="paradox__card reveal reveal-up reveal-delay-3">
                <h3 class="paradox__card-title"><?= e(t('home.paradox_inertia_title')) ?></h3>
                <p class="paradox__card-text"><?= e(t('home.paradox_inertia')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     MOUVEMENT 3 — L'ouverture (Resolution)
     ========================================== -->
<section class="resolution section" id="services-preview">
    <!-- Fond anime : ampoules qui eclosent -->
    <canvas class="resolution__canvas" id="bulb-canvas" aria-hidden="true"></canvas>

    <div class="container">
        <div class="resolution__header reveal reveal-up">
            <h2 class="resolution__title"><?= e(t('home.resolution_title')) ?></h2>
            <p class="resolution__subtitle"><?= e(t('home.resolution_subtitle')) ?></p>
        </div>

        <div class="masonry" id="services-masonry">
            <?php
            $services = [
                ['key' => 's1', 'icon' => '&#x1F4A1;', 'class' => '1'],
                ['key' => 's2', 'icon' => '&#x1F91D;', 'class' => '2'],
                ['key' => 's3', 'icon' => '&#x2699;',  'class' => '3'],
                ['key' => 's4', 'icon' => '&#x1F680;', 'class' => '4'],
                ['key' => 's5', 'icon' => '&#x1F331;', 'class' => '5'],
            ];
            foreach ($services as $i => $s):
            ?>
            <div class="masonry__card service-card service-card--color-<?= $s['class'] ?> reveal reveal-up reveal-delay-<?= $i + 1 ?>">
                <div class="service-card__icon service-card__icon--<?= $s['class'] ?>" aria-hidden="true">
                    <?= $s['icon'] ?>
                </div>
                <h3 class="service-card__title"><?= e(t('services.' . $s['key'] . '_title')) ?></h3>
                <p class="service-card__text"><?= e(t('services.' . $s['key'] . '_short')) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="resolution__cta reveal reveal-up">
            <a href="<?= SITE_URL ?>/services" class="btn btn--secondary btn--lg"><?= e(t('common.read_more')) ?></a>
        </div>
    </div>
</section>

<!-- ==========================================
     PORTFOLIO — Apercu
     ========================================== -->
<section class="portfolio-preview section section--surface" id="portfolio-preview">
    <div class="container">
        <div class="portfolio-preview__header reveal reveal-up">
            <h2 class="section__title"><?= e(t('home.portfolio_title')) ?></h2>
        </div>

        <div class="portfolio-preview__grid">
            <?php if (!empty($previewProjects)):
                foreach ($previewProjects as $j => $pv):
                    $pvName  = $lang === 'fr' ? $pv['name_fr'] : ($pv['name_en'] ?? $pv['name_fr']);
                    $pvPitch = $lang === 'fr' ? $pv['pitch_fr'] : ($pv['pitch_en'] ?? $pv['pitch_fr']);
            ?>

                <?php if ($isLogged): ?>
                <!-- Member: preview card with real content -->
                <a href="<?= SITE_URL ?>/project/<?= e($pv['slug']) ?>" class="project-card--blurred reveal reveal-up reveal-delay-<?= $j + 1 ?>" style="text-decoration:none;color:inherit;">
                    <div class="project-card__visible">
                        <span class="badge badge--primary project-card__domain"><?= e($pv['domain']) ?></span>
                        <span class="badge badge--dark project-card__phase"><?= e(t('portfolio.phase_' . $pv['phase'])) ?></span>
                    </div>
                    <div style="filter:none;pointer-events:none;">
                        <h3 style="font-family:var(--ff-display);font-size:var(--fs-lg);font-weight:var(--fw-semibold);margin-bottom:var(--sp-sm);"><?= e($pvName) ?></h3>
                        <p style="font-size:var(--fs-small);line-height:var(--lh-relaxed);color:var(--c-text);display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;"><?= e($pvPitch) ?></p>
                    </div>
                </a>

                <?php else: ?>
                <!-- Visitor: blurred card -->
                <div class="project-card--blurred reveal reveal-up reveal-delay-<?= $j + 1 ?>">
                    <div class="project-card__visible">
                        <span class="badge badge--primary project-card__domain"><?= e($pv['domain']) ?></span>
                        <span class="badge badge--dark project-card__phase"><?= e(t('portfolio.phase_' . $pv['phase'])) ?></span>
                    </div>
                    <div class="project-card__blurred-content" aria-hidden="true">
                        <div class="placeholder-line"></div>
                        <div class="placeholder-line"></div>
                        <div class="placeholder-line"></div>
                        <div class="placeholder-line"></div>
                    </div>
                    <div class="project-card__overlay">
                        <p class="project-card__overlay-text"><?= e(t('portfolio.teaser_message')) ?></p>
                        <a href="<?= SITE_URL ?>/register" class="btn btn--primary btn--sm"><?= e(t('portfolio.teaser_cta')) ?></a>
                    </div>
                </div>
                <?php endif; ?>

            <?php endforeach;
            else:
                // Fallback if no projects in DB yet
                for ($j = 0; $j < 3; $j++): ?>
                <div class="project-card--blurred reveal reveal-up reveal-delay-<?= $j + 1 ?>">
                    <div class="project-card__blurred-content" aria-hidden="true">
                        <div class="placeholder-line"></div>
                        <div class="placeholder-line"></div>
                        <div class="placeholder-line"></div>
                        <div class="placeholder-line"></div>
                    </div>
                </div>
            <?php endfor; endif; ?>
        </div>

        <div class="portfolio-preview__link reveal reveal-up">
            <a href="<?= SITE_URL ?>/portfolio" class="btn btn--ghost">
                <?= e(t('home.portfolio_link')) ?> &rarr;
            </a>
        </div>
    </div>
</section>

<!-- ==========================================
     ECOSYSTEME — Mention subtile
     ========================================== -->
<section class="ecosystem section reveal reveal-fade">
    <div class="container">
        <div class="ecosystem__inner">
            <p class="ecosystem__text"><?= e(t('home.ecosystem_text')) ?></p>
            <div class="ecosystem__logos">
                <a href="https://www.ixila.com" target="_blank" rel="noopener" class="ecosystem__logo-name">IXILA</a>
                <a href="https://www.pmside.com" target="_blank" rel="noopener" class="ecosystem__logo-name">PM Side</a>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     CTA FINAL
     ========================================== -->
<section class="final-cta section reveal reveal-scale">
    <div class="container">
        <h2 class="final-cta__title"><?= e(t('home.cta_title')) ?></h2>
        <a href="<?= SITE_URL ?>/contact" class="btn btn--primary btn--lg btn--pulse"><?= e(t('home.cta_button')) ?></a>
    </div>
</section>
