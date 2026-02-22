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

        <div class="climber-scene reveal reveal-up" id="climber-scene" data-services-url="<?= SITE_URL ?>/services" role="img" aria-label="<?= e(t('home.climber_aria_label')) ?>">
            <svg class="climber-scene__svg" viewBox="0 0 800 700" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <filter id="hold-glow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur stdDeviation="8" result="blur"/>
                        <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                    </filter>
                </defs>

                <!-- ====== CLIMBER POSES (6 silhouettes, crossfade) ====== -->

                <!-- Pose 0: Neutral mid-climb -->
                <g class="climber__pose climber__pose--0" style="opacity:1">
                    <g transform="translate(380, 340)">
                        <!-- Head -->
                        <circle cx="0" cy="-95" r="18" fill="#9CA3AF"/>
                        <!-- Torso -->
                        <path d="M-16,-78 C-18,-55 -20,-30 -15,5 L15,5 C20,-30 18,-55 16,-78 Z" fill="#9CA3AF"/>
                        <!-- Left arm: reaching up-left -->
                        <path d="M-16,-70 C-35,-80 -55,-95 -68,-110 C-72,-115 -70,-118 -65,-115 L-50,-100 C-40,-88 -25,-75 -16,-70" fill="#9CA3AF"/>
                        <!-- Right arm: reaching up-right -->
                        <path d="M16,-70 C35,-80 55,-90 70,-100 C75,-104 73,-108 68,-105 L52,-92 C42,-83 28,-75 16,-70" fill="#9CA3AF"/>
                        <!-- Left leg: down-left, bent -->
                        <path d="M-12,5 C-18,30 -30,60 -45,90 C-48,96 -55,100 -60,95 L-50,85 C-38,62 -22,35 -12,5" fill="#9CA3AF"/>
                        <!-- Right leg: down-right, straight -->
                        <path d="M12,5 C20,35 32,70 40,105 C42,112 48,115 52,110 L45,100 C38,72 25,40 12,5" fill="#9CA3AF"/>
                        <!-- Left foot -->
                        <ellipse cx="-62" cy="98" rx="10" ry="5" fill="#9CA3AF" transform="rotate(-15,-62,98)"/>
                        <!-- Right foot -->
                        <ellipse cx="54" cy="113" rx="10" ry="5" fill="#9CA3AF" transform="rotate(10,54,113)"/>
                    </g>
                </g>

                <!-- Pose 1: Reaching bottom-left (Service 1) -->
                <g class="climber__pose climber__pose--1" style="opacity:0">
                    <g transform="translate(380, 360)">
                        <circle cx="5" cy="-90" r="18" fill="#9CA3AF"/>
                        <path d="M-11,-73 C-14,-50 -16,-25 -10,10 L20,8 C24,-25 22,-50 19,-73 Z" fill="#9CA3AF"/>
                        <!-- Left arm reaching far down-left -->
                        <path d="M-11,-65 C-40,-50 -80,-20 -120,30 C-130,42 -135,48 -128,50 L-115,40 C-82,-5 -45,-42 -11,-65" fill="#9CA3AF"/>
                        <!-- Right arm: gripping up-right -->
                        <path d="M19,-65 C38,-78 58,-92 72,-108 C76,-113 74,-117 69,-114 L55,-100 C42,-86 30,-75 19,-65" fill="#9CA3AF"/>
                        <!-- Left leg: extended down -->
                        <path d="M-8,10 C-15,40 -25,75 -30,110 C-31,118 -36,122 -40,118 L-34,108 C-28,78 -18,45 -8,10" fill="#9CA3AF"/>
                        <!-- Right leg: bent, foot on wall -->
                        <path d="M16,8 C28,28 35,55 30,80 C29,86 34,90 38,86 L35,78 C38,56 32,32 16,8" fill="#9CA3AF"/>
                        <ellipse cx="-42" cy="121" rx="10" ry="5" fill="#9CA3AF" transform="rotate(-5,-42,121)"/>
                        <ellipse cx="40" cy="88" rx="9" ry="5" fill="#9CA3AF" transform="rotate(15,40,88)"/>
                    </g>
                </g>

                <!-- Pose 2: Reaching left-center (Service 2) -->
                <g class="climber__pose climber__pose--2" style="opacity:0">
                    <g transform="translate(390, 350)">
                        <circle cx="-5" cy="-98" r="18" fill="#9CA3AF"/>
                        <path d="M-18,-80 C-22,-55 -20,-30 -14,5 L14,5 C18,-30 17,-55 14,-80 Z" fill="#9CA3AF"/>
                        <!-- Left arm reaching up-left high -->
                        <path d="M-18,-72 C-45,-90 -75,-115 -100,-140 C-106,-146 -108,-142 -104,-138 L-88,-122 C-68,-102 -42,-82 -18,-72" fill="#9CA3AF"/>
                        <!-- Right arm: holding mid-right -->
                        <path d="M14,-72 C30,-60 48,-50 62,-42 C67,-39 66,-34 61,-37 L48,-45 C36,-52 24,-62 14,-72" fill="#9CA3AF"/>
                        <!-- Left leg: flagging right -->
                        <path d="M-10,5 C5,30 25,55 40,75 C44,80 48,78 45,73 L30,55 C15,35 -2,15 -10,5" fill="#9CA3AF"/>
                        <!-- Right leg: standing on hold -->
                        <path d="M12,5 C15,35 18,68 20,100 C21,108 26,112 30,108 L24,98 C22,70 18,40 12,5" fill="#9CA3AF"/>
                        <ellipse cx="47" cy="77" rx="9" ry="5" fill="#9CA3AF" transform="rotate(30,47,77)"/>
                        <ellipse cx="32" cy="111" rx="10" ry="5" fill="#9CA3AF" transform="rotate(5,32,111)"/>
                    </g>
                </g>

                <!-- Pose 3: Reaching center-up (Service 3) -->
                <g class="climber__pose climber__pose--3" style="opacity:0">
                    <g transform="translate(385, 345)">
                        <circle cx="0" cy="-105" r="18" fill="#9CA3AF"/>
                        <path d="M-15,-88 C-17,-62 -18,-35 -12,0 L12,0 C18,-35 17,-62 15,-88 Z" fill="#9CA3AF"/>
                        <!-- Both arms reaching up high -->
                        <path d="M-15,-80 C-30,-105 -40,-135 -35,-170 C-34,-178 -28,-180 -28,-172 L-30,-148 C-32,-120 -22,-95 -15,-80" fill="#9CA3AF"/>
                        <path d="M15,-80 C28,-102 38,-130 42,-165 C43,-173 38,-176 37,-168 L36,-145 C34,-118 25,-95 15,-80" fill="#9CA3AF"/>
                        <!-- Left leg: wide stance left -->
                        <path d="M-10,0 C-25,25 -42,55 -55,85 C-58,92 -54,96 -50,91 L-42,75 C-32,50 -18,25 -10,0" fill="#9CA3AF"/>
                        <!-- Right leg: wide stance right -->
                        <path d="M10,0 C22,28 38,58 48,88 C50,95 55,97 56,91 L50,78 C42,52 26,28 10,0" fill="#9CA3AF"/>
                        <ellipse cx="-53" cy="94" rx="10" ry="5" fill="#9CA3AF" transform="rotate(-20,-53,94)"/>
                        <ellipse cx="58" cy="94" rx="10" ry="5" fill="#9CA3AF" transform="rotate(20,58,94)"/>
                    </g>
                </g>

                <!-- Pose 4: Reaching right (Service 4) -->
                <g class="climber__pose climber__pose--4" style="opacity:0">
                    <g transform="translate(375, 340)">
                        <circle cx="8" cy="-92" r="18" fill="#9CA3AF"/>
                        <path d="M-10,-75 C-14,-50 -15,-25 -10,8 L18,5 C22,-28 20,-52 17,-75 Z" fill="#9CA3AF"/>
                        <!-- Left arm: gripping left-down -->
                        <path d="M-10,-68 C-30,-58 -50,-45 -62,-35 C-67,-31 -65,-27 -60,-30 L-48,-40 C-35,-50 -20,-60 -10,-68" fill="#9CA3AF"/>
                        <!-- Right arm: reaching far right high -->
                        <path d="M17,-68 C50,-80 85,-95 125,-110 C133,-113 136,-108 130,-105 L100,-92 C72,-80 40,-70 17,-68" fill="#9CA3AF"/>
                        <!-- Left leg: bracing left -->
                        <path d="M-8,8 C-22,30 -38,58 -48,85 C-50,92 -46,96 -42,91 L-38,78 C-30,55 -16,30 -8,8" fill="#9CA3AF"/>
                        <!-- Right leg: tucked -->
                        <path d="M14,5 C20,25 22,48 18,68 C17,74 22,78 25,73 L22,62 C24,45 22,28 14,5" fill="#9CA3AF"/>
                        <ellipse cx="-45" cy="94" rx="10" ry="5" fill="#9CA3AF" transform="rotate(-10,-45,94)"/>
                        <ellipse cx="27" cy="75" rx="9" ry="5" fill="#9CA3AF" transform="rotate(20,27,75)"/>
                    </g>
                </g>

                <!-- Pose 5: Summit — reaching top-right (Service 5) -->
                <g class="climber__pose climber__pose--5" style="opacity:0">
                    <g transform="translate(380, 330)">
                        <circle cx="5" cy="-108" r="18" fill="#9CA3AF"/>
                        <path d="M-12,-90 C-15,-65 -16,-38 -10,0 L16,-2 C20,-38 19,-65 16,-90 Z" fill="#9CA3AF"/>
                        <!-- Left arm: down for balance -->
                        <path d="M-12,-82 C-28,-70 -42,-55 -50,-40 C-53,-35 -50,-32 -46,-36 L-40,-45 C-32,-58 -20,-72 -12,-82" fill="#9CA3AF"/>
                        <!-- Right arm: reaching high up-right (summit!) -->
                        <path d="M16,-82 C42,-110 70,-140 100,-175 C106,-182 110,-178 106,-172 L82,-145 C58,-118 35,-95 16,-82" fill="#9CA3AF"/>
                        <!-- Left leg: extended down-left -->
                        <path d="M-8,0 C-18,30 -32,65 -42,100 C-44,108 -40,112 -36,107 L-32,92 C-24,62 -14,30 -8,0" fill="#9CA3AF"/>
                        <!-- Right leg: bent, foot on hold -->
                        <path d="M12,-2 C22,18 28,42 25,65 C24,72 28,76 32,71 L28,60 C30,42 25,22 12,-2" fill="#9CA3AF"/>
                        <ellipse cx="-39" cy="110" rx="10" ry="5" fill="#9CA3AF" transform="rotate(-10,-39,110)"/>
                        <ellipse cx="34" cy="73" rx="9" ry="5" fill="#9CA3AF" transform="rotate(15,34,73)"/>
                    </g>
                </g>

                <!-- ====== CLIMBING HOLDS (5 organic blobs) ====== -->

                <!-- Hold 1: bottom-left — Indigo -->
                <g class="climber__hold climber__hold--1" data-service="1">
                    <circle class="climber__hold-glow" cx="165" cy="520" r="35" fill="#6366F1" opacity="0"/>
                    <path class="climber__hold-shape" d="M148,505 C152,495 162,490 172,492 C182,494 190,502 192,512 C194,522 190,532 180,538 C170,544 158,542 150,534 C142,526 144,515 148,505 Z" fill="#6366F1"/>
                </g>

                <!-- Hold 2: left-center — Sky Blue -->
                <g class="climber__hold climber__hold--2" data-service="2">
                    <circle class="climber__hold-glow" cx="240" cy="390" r="32" fill="#38BDF8" opacity="0"/>
                    <path class="climber__hold-shape" d="M222,378 C228,370 238,368 248,372 C258,376 264,386 262,396 C260,406 252,414 242,414 C232,414 224,408 222,398 C220,388 220,382 222,378 Z" fill="#38BDF8"/>
                </g>

                <!-- Hold 3: center-high — Green -->
                <g class="climber__hold climber__hold--3" data-service="3">
                    <circle class="climber__hold-glow" cx="400" cy="260" r="34" fill="#10B981" opacity="0"/>
                    <path class="climber__hold-shape" d="M382,248 C388,238 398,234 410,237 C422,240 428,250 427,262 C426,274 418,282 407,284 C396,286 386,280 382,270 C378,260 380,252 382,248 Z" fill="#10B981"/>
                </g>

                <!-- Hold 4: right — Orange -->
                <g class="climber__hold climber__hold--4" data-service="4">
                    <circle class="climber__hold-glow" cx="570" cy="175" r="32" fill="#FF6B4A" opacity="0"/>
                    <path class="climber__hold-shape" d="M554,162 C560,154 570,150 580,153 C590,156 596,165 594,176 C592,187 584,194 574,194 C564,194 556,188 554,178 C552,168 552,164 554,162 Z" fill="#FF6B4A"/>
                </g>

                <!-- Hold 5: top-right — Amber -->
                <g class="climber__hold climber__hold--5" data-service="5">
                    <circle class="climber__hold-glow" cx="680" cy="95" r="30" fill="#F5A623" opacity="0"/>
                    <path class="climber__hold-shape" d="M664,84 C670,76 680,73 690,76 C700,79 706,88 704,98 C702,108 695,114 685,115 C675,116 668,110 665,100 C662,90 662,86 664,84 Z" fill="#F5A623"/>
                </g>

            </svg>

            <!-- Labels HTML (i18n + rendu texte propre) -->
            <?php
            $climbLabels = [
                ['key' => 's1', 'pos' => '1'],
                ['key' => 's2', 'pos' => '2'],
                ['key' => 's3', 'pos' => '3'],
                ['key' => 's4', 'pos' => '4'],
                ['key' => 's5', 'pos' => '5'],
            ];
            foreach ($climbLabels as $cl):
            ?>
            <div class="climber-label climber-label--<?= $cl['pos'] ?>" data-service="<?= $cl['pos'] ?>">
                <h3 class="climber-label__title climber-label__title--<?= $cl['pos'] ?>"><?= e(t('services.' . $cl['key'] . '_title')) ?></h3>
                <p class="climber-label__text"><?= e(t('services.' . $cl['key'] . '_short')) ?></p>
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
