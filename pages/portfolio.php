<?php
/**
 * Saxho.net — Portfolio (public)
 * Double display: blurred cards (visitor) / full cards (logged-in member)
 */

$pageCss = 'portfolio.css';
$pageJs  = 'portfolio.js';
$pageDescription = t('portfolio.description');

$db   = getDB();
$lang = $_SESSION['lang'] ?? 'fr';
$isLogged = is_logged_in();

// Filter by domain
$domainFilter = trim($_GET['domain'] ?? '');

// Fetch visible projects
if ($domainFilter !== '') {
    $stmt = $db->prepare(
        'SELECT * FROM projects WHERE is_visible = 1 AND domain = ?
         ORDER BY display_order ASC, created_at DESC'
    );
    $stmt->execute([$domainFilter]);
    $projects = $stmt->fetchAll();
} else {
    $projects = $db->query(
        'SELECT * FROM projects WHERE is_visible = 1
         ORDER BY display_order ASC, created_at DESC'
    )->fetchAll();
}

// Distinct domains for filters
$domains = $db->query(
    'SELECT DISTINCT domain FROM projects WHERE is_visible = 1 ORDER BY domain ASC'
)->fetchAll(PDO::FETCH_COLUMN);

$phases = ['ideation', 'study', 'prototype', 'development', 'pre_launch', 'transferred'];
?>

<!-- Hero -->
<section class="portfolio-hero">
    <div class="container">
        <h1 class="portfolio-hero__title reveal reveal-up"><?= e(t('portfolio.title')) ?></h1>
        <p class="portfolio-hero__subtitle reveal reveal-up"><?= e(t('portfolio.subtitle')) ?></p>
    </div>
</section>

<!-- Content -->
<section class="section">
    <div class="container">

        <!-- Filters -->
        <?php if (count($domains) > 1): ?>
        <div class="portfolio-filters reveal reveal-up">
            <a href="<?= SITE_URL ?>/portfolio"
               class="portfolio-filters__btn<?= $domainFilter === '' ? ' portfolio-filters__btn--active' : '' ?>">
                <?= e(t('portfolio.filter_all')) ?>
            </a>
            <?php foreach ($domains as $dom): ?>
            <a href="<?= SITE_URL ?>/portfolio?domain=<?= urlencode($dom) ?>"
               class="portfolio-filters__btn<?= $domainFilter === $dom ? ' portfolio-filters__btn--active' : '' ?>">
                <?= e($dom) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Grid -->
        <?php if (empty($projects)): ?>
            <p class="portfolio-empty"><?= e(t('portfolio.no_projects')) ?></p>
        <?php else: ?>
        <div class="portfolio-grid">
            <?php foreach ($projects as $i => $p):
                $name  = $lang === 'fr' ? $p['name_fr'] : ($p['name_en'] ?? $p['name_fr']);
                $pitch = $lang === 'fr' ? $p['pitch_fr'] : ($p['pitch_en'] ?? $p['pitch_fr']);
                $delay = ($i % 3) + 1;
            ?>

                <?php if ($isLogged): ?>
                <!-- Member: full card -->
                <a href="<?= SITE_URL ?>/project/<?= e($p['slug']) ?>" class="project-card reveal reveal-up reveal-delay-<?= $delay ?>">
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?= SITE_URL ?>/assets/img/uploads/<?= e($p['image']) ?>" alt="<?= e($name) ?>" class="project-card__image">
                    <?php else: ?>
                        <div class="project-card__image-placeholder"><span><?= e(mb_strtoupper(mb_substr($name, 0, 2))) ?></span></div>
                    <?php endif; ?>
                    <div class="project-card__body">
                        <div class="project-card__badges">
                            <span class="badge badge--primary"><?= e($p['domain']) ?></span>
                            <span class="badge badge--dark"><?= e(t('portfolio.phase_' . $p['phase'])) ?></span>
                        </div>
                        <h3 class="project-card__name"><?= e($name) ?></h3>
                        <p class="project-card__pitch"><?= e($pitch) ?></p>
                        <div class="project-card__footer">
                            <span class="project-card__status badge badge--<?= $p['status'] === 'open' ? 'success' : ($p['status'] === 'paused' ? 'warning' : 'muted') ?>"><?= e(t('portfolio.status_' . $p['status'])) ?></span>
                            <span class="project-card__link"><?= e(t('portfolio.discover')) ?> &rarr;</span>
                        </div>
                    </div>
                </a>

                <?php else: ?>
                <!-- Visitor: blurred card -->
                <div class="project-card--blurred reveal reveal-up reveal-delay-<?= $delay ?>">
                    <div class="project-card__visible">
                        <span class="badge badge--primary project-card__domain"><?= e($p['domain']) ?></span>
                        <span class="badge badge--dark project-card__phase"><?= e(t('portfolio.phase_' . $p['phase'])) ?></span>
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

            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
