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
            <svg class="climber-scene__svg" viewBox="0 0 800 750" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <filter id="hold-glow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur stdDeviation="6" result="blur"/>
                        <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                    </filter>
                </defs>

                <!-- ====== CLIMBING HOLDS (5 organic blob shapes — clustered) ====== -->
                <!-- Holds clustered around the climber, mimicking reference image layout -->

                <!-- Hold 5: upper-left — Incubateur Saxho — Indigo -->
                <g class="climber__hold climber__hold--5" data-service="5">
                    <circle class="climber__hold-glow" cx="340" cy="130" r="38" fill="#6366F1" opacity="0"/>
                    <path class="climber__hold-shape" d="M318,118 C324,100 340,92 358,98 C372,104 380,120 374,136 C368,152 350,160 336,154 C322,148 314,134 318,118 Z" fill="#6366F1"/>
                </g>

                <!-- Hold 4: upper-right — Task Force Innovation — Sky Blue -->
                <g class="climber__hold climber__hold--4" data-service="4">
                    <circle class="climber__hold-glow" cx="510" cy="190" r="30" fill="#38BDF8" opacity="0"/>
                    <path class="climber__hold-shape" d="M494,180 C500,166 514,160 526,168 C536,176 540,192 532,204 C524,216 508,218 498,208 C488,198 488,188 494,180 Z" fill="#38BDF8"/>
                </g>

                <!-- Hold 3: center-left — Processus d'innovation — Green -->
                <g class="climber__hold climber__hold--3" data-service="3">
                    <circle class="climber__hold-glow" cx="310" cy="330" r="34" fill="#10B981" opacity="0"/>
                    <path class="climber__hold-shape" d="M290,316 C298,300 316,294 332,302 C344,310 350,328 342,344 C334,358 316,362 302,354 C288,346 284,330 290,316 Z" fill="#10B981"/>
                </g>

                <!-- Hold 2: right-center — Facilitation d'idéation — Amber -->
                <g class="climber__hold climber__hold--2" data-service="2">
                    <circle class="climber__hold-glow" cx="500" cy="400" r="28" fill="#F5A623" opacity="0"/>
                    <path class="climber__hold-shape" d="M484,390 C490,376 504,372 516,380 C526,388 530,404 522,416 C514,426 498,428 488,418 C478,408 478,398 484,390 Z" fill="#F5A623"/>
                </g>

                <!-- Hold 1: bottom — Apporteur d'idées — Orange -->
                <g class="climber__hold climber__hold--1" data-service="1">
                    <circle class="climber__hold-glow" cx="380" cy="530" r="30" fill="#FF6B4A" opacity="0"/>
                    <path class="climber__hold-shape" d="M364,518 C370,504 384,498 398,504 C410,510 416,526 408,540 C400,552 384,556 372,548 C360,540 358,528 364,518 Z" fill="#FF6B4A"/>
                </g>

                <!-- ====== CLIMBER — 6 poses as embedded <image> using data URIs ====== -->
                <!-- Using the reference image approach: each pose is a pre-rendered SVG group -->
                <!-- Climber positioned center-right, facing left, reaching toward holds -->

                <!-- Pose 0: Default — standing on one leg, right arm up (like pose 3 in reference) -->
                <g class="climber__pose climber__pose--0" style="opacity:1">
                    <g transform="translate(440,280) scale(1.1)">
                        <!-- Head -->
                        <ellipse cx="48" cy="12" rx="14" ry="16" fill="#9CA3AF"/>
                        <!-- Neck -->
                        <path d="M42,26 L54,26 L52,38 L44,38 Z" fill="#9CA3AF"/>
                        <!-- Right arm (reaching up to hold) -->
                        <path d="M54,38 C58,34 64,28 68,20 C72,14 78,8 84,4 C88,2 92,0 96,-4 L100,-6 C102,-8 104,-6 102,-2 L96,4 C92,8 86,14 82,20 C78,26 74,30 70,34 C66,36 62,38 58,40" fill="#9CA3AF"/>
                        <!-- Left arm (bent, hand near face) -->
                        <path d="M40,38 C36,34 30,30 26,28 C22,26 18,24 16,22 C14,20 12,18 14,16 C16,14 18,16 20,20 L24,24 C28,26 32,30 36,34 L40,38" fill="#9CA3AF"/>
                        <!-- Torso -->
                        <path d="M38,38 C36,44 34,52 32,62 C30,72 30,82 32,90 L34,98 L62,98 L64,90 C66,82 66,72 64,62 C62,52 60,44 58,38 Z" fill="#9CA3AF"/>
                        <!-- Left leg (bent up, knee high) -->
                        <path d="M34,98 C30,104 24,112 20,120 C16,128 14,136 16,140 L18,144 C20,148 24,148 26,144 L28,136 C30,128 34,120 38,112 C42,106 44,102 44,100" fill="#9CA3AF"/>
                        <!-- Right leg (straight down, standing on hold) -->
                        <path d="M58,98 C60,108 62,120 64,134 C66,148 66,162 64,176 L62,188 C60,194 58,200 56,206 L54,212 C52,216 48,218 46,216 L44,212 C42,208 44,204 48,200 L52,190 C54,180 56,168 56,156 C56,144 54,132 52,120 C50,110 48,104 48,100" fill="#9CA3AF"/>
                        <!-- Right foot (on hold) -->
                        <path d="M44,212 C40,216 36,220 34,224 C32,228 34,232 38,232 L48,230 C52,228 54,224 54,220 L52,216 Z" fill="#9CA3AF"/>
                        <!-- Left shoe -->
                        <path d="M18,144 C14,148 10,152 8,156 C6,160 8,164 12,164 L22,162 C26,160 28,156 28,152 L26,148 Z" fill="#9CA3AF"/>
                        <!-- White detail lines -->
                        <path d="M40,40 C38,48 36,58 34,68" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M56,40 C58,48 60,58 62,68" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M34,90 L62,90" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                        <path d="M36,96 C38,94 42,92 48,92 C54,92 58,94 60,96" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                        <path d="M54,38 C56,36 60,32 64,28" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                    </g>
                </g>

                <!-- Pose 1: Low crouch, reaching far left-down (like pose 1 in reference) -->
                <g class="climber__pose climber__pose--1" style="opacity:0">
                    <g transform="translate(420,320) scale(1.1)">
                        <ellipse cx="68" cy="8" rx="14" ry="16" fill="#9CA3AF"/>
                        <path d="M62,22 L74,22 L72,34 L64,34 Z" fill="#9CA3AF"/>
                        <!-- Right arm up-right (gripping) -->
                        <path d="M74,34 C78,28 84,20 90,14 C96,8 102,4 108,2 L112,0 C114,-2 116,0 114,4 L108,10 C102,16 96,22 90,28 C84,32 80,34 76,36" fill="#9CA3AF"/>
                        <!-- Left arm reaching far down-left -->
                        <path d="M60,34 C54,38 44,44 34,52 C24,60 14,70 6,82 C0,92 -4,100 -6,108 L-8,114 C-10,118 -8,120 -4,118 L0,112 C4,102 10,92 18,82 C26,72 36,62 46,54 C54,48 58,44 62,40" fill="#9CA3AF"/>
                        <!-- Torso (leaning back) -->
                        <path d="M58,34 C54,42 50,52 48,64 C46,76 46,86 48,94 L52,102 L78,96 L80,88 C82,78 82,68 78,58 C76,48 72,40 68,34 Z" fill="#9CA3AF"/>
                        <!-- Left leg (extended far left) -->
                        <path d="M52,102 C46,110 38,118 28,126 C18,134 10,140 4,146 L0,150 C-2,154 0,158 4,158 L10,154 C18,148 28,140 38,130 C48,120 54,112 58,106" fill="#9CA3AF"/>
                        <!-- Right leg (bent, knee up) -->
                        <path d="M72,96 C76,104 82,114 86,126 C90,138 88,148 84,156 L80,164 C78,168 74,170 72,168 L70,162 C72,154 74,144 72,134 C70,124 66,114 62,106" fill="#9CA3AF"/>
                        <!-- Feet -->
                        <path d="M0,150 C-4,154 -8,158 -10,162 C-12,166 -10,170 -6,170 L4,168 C8,166 10,162 10,158 L8,154 Z" fill="#9CA3AF"/>
                        <path d="M80,164 C76,168 72,172 70,176 C68,180 70,184 74,184 L84,182 C88,180 90,176 90,172 L88,168 Z" fill="#9CA3AF"/>
                        <!-- White details -->
                        <path d="M60,36 C58,46 56,56 54,66" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M52,94 L76,90" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                        <path d="M74,34 C78,28 84,20 90,14" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                    </g>
                </g>

                <!-- Pose 2: Standing tall, right arm high (like pose 2 in reference) -->
                <g class="climber__pose climber__pose--2" style="opacity:0">
                    <g transform="translate(440,260) scale(1.1)">
                        <ellipse cx="46" cy="12" rx="14" ry="16" fill="#9CA3AF"/>
                        <path d="M40,26 L52,26 L50,38 L42,38 Z" fill="#9CA3AF"/>
                        <!-- Right arm reaching high up -->
                        <path d="M52,38 C56,32 60,24 64,16 C68,8 72,0 76,-10 C80,-18 84,-26 86,-34 L88,-40 C90,-44 92,-42 90,-38 L86,-28 C82,-18 78,-8 74,2 C70,12 66,20 62,28 L58,34" fill="#9CA3AF"/>
                        <!-- Left arm (bent near chest) -->
                        <path d="M38,38 C34,36 28,34 24,34 C20,34 16,36 14,38 C12,40 12,44 14,46 L18,48 C22,48 26,46 30,42 L36,38" fill="#9CA3AF"/>
                        <!-- Torso -->
                        <path d="M36,38 C34,46 32,56 30,68 C28,80 28,92 30,102 L32,110 L60,110 L62,102 C64,92 64,80 62,68 C60,56 58,46 56,38 Z" fill="#9CA3AF"/>
                        <!-- Left leg (bent, knee lifted) -->
                        <path d="M32,110 C28,118 22,128 18,138 C14,148 12,156 14,162 L16,168 C18,172 22,172 24,168 L26,160 C28,150 32,140 36,130 C40,122 42,116 42,112" fill="#9CA3AF"/>
                        <!-- Right leg (straight, weight-bearing) -->
                        <path d="M56,110 C58,122 60,136 62,152 C64,168 64,182 62,196 L60,208 C58,214 56,220 54,226 L52,232 C50,236 46,238 44,236 L42,232 C40,228 42,224 46,220 L50,210 C52,200 54,188 54,176 C54,164 52,152 50,140 C48,130 46,122 46,114" fill="#9CA3AF"/>
                        <!-- Feet -->
                        <path d="M16,168 C12,172 8,176 6,180 C4,184 6,188 10,188 L20,186 C24,184 26,180 26,176 L24,172 Z" fill="#9CA3AF"/>
                        <path d="M42,232 C38,236 34,240 32,244 C30,248 32,252 36,252 L46,250 C50,248 52,244 52,240 L50,236 Z" fill="#9CA3AF"/>
                        <!-- White details -->
                        <path d="M38,40 C36,50 34,62 32,74" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M54,40 C56,50 58,62 60,74" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M32,102 L60,102" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                        <path d="M34,108 C36,106 40,104 46,104 C52,104 56,106 58,108" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                    </g>
                </g>

                <!-- Pose 3: Dynamic, left leg raised high (like pose 3 in reference) -->
                <g class="climber__pose climber__pose--3" style="opacity:0">
                    <g transform="translate(430,270) scale(1.1)">
                        <ellipse cx="50" cy="10" rx="14" ry="16" fill="#9CA3AF"/>
                        <path d="M44,24 L56,24 L54,36 L46,36 Z" fill="#9CA3AF"/>
                        <!-- Right arm high up (gripping hold) -->
                        <path d="M56,36 C60,28 66,18 70,8 C74,0 78,-10 82,-20 L86,-28 C88,-32 90,-30 88,-26 L84,-16 C80,-6 76,4 72,14 C68,22 64,28 60,32" fill="#9CA3AF"/>
                        <!-- Left arm (bent near head) -->
                        <path d="M42,36 C38,32 32,28 28,26 C24,24 20,24 18,26 C16,28 16,32 18,34 L22,38 C26,40 30,40 34,38 L40,36" fill="#9CA3AF"/>
                        <!-- Torso -->
                        <path d="M40,36 C38,44 36,54 34,66 C32,78 32,88 34,96 L36,104 L64,104 L66,96 C68,88 68,78 66,66 C64,54 62,44 60,36 Z" fill="#9CA3AF"/>
                        <!-- Left leg (raised high, knee bent, dynamic) -->
                        <path d="M36,104 C30,110 22,116 16,118 C10,120 6,118 4,114 C2,110 4,106 8,104 L14,102 C20,102 26,104 32,108" fill="#9CA3AF"/>
                        <!-- Right leg (standing, slightly bent) -->
                        <path d="M60,104 C62,114 64,128 66,144 C68,160 68,176 66,190 L64,202 C62,208 60,214 58,220 L56,226 C54,230 50,232 48,230 L46,226 C44,222 46,218 50,214 L54,204 C56,194 58,182 58,170 C58,158 56,146 54,134 C52,124 50,116 50,108" fill="#9CA3AF"/>
                        <!-- Feet -->
                        <path d="M4,114 C0,118 -4,122 -6,126 C-8,130 -6,134 -2,134 L8,132 C12,130 14,126 14,122 L12,118 Z" fill="#9CA3AF"/>
                        <path d="M46,226 C42,230 38,234 36,238 C34,242 36,246 40,246 L50,244 C54,242 56,238 56,234 L54,230 Z" fill="#9CA3AF"/>
                        <!-- White details -->
                        <path d="M42,38 C40,48 38,60 36,72" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M58,38 C60,48 62,60 64,72" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M36,96 L64,96" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                        <path d="M38,102 C40,100 44,98 50,98 C56,98 60,100 62,102" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                    </g>
                </g>

                <!-- Pose 4: Tall reach, left leg on side hold (like pose 4 in reference) -->
                <g class="climber__pose climber__pose--4" style="opacity:0">
                    <g transform="translate(440,250) scale(1.1)">
                        <ellipse cx="44" cy="12" rx="14" ry="16" fill="#9CA3AF"/>
                        <path d="M38,26 L50,26 L48,38 L40,38 Z" fill="#9CA3AF"/>
                        <!-- Right arm reaching very high -->
                        <path d="M50,38 C54,30 58,20 62,8 C66,-4 70,-16 74,-28 C78,-38 80,-46 82,-52 L84,-58 C86,-62 88,-60 86,-56 L82,-44 C78,-32 74,-20 70,-8 C66,4 62,14 58,24 L54,32" fill="#9CA3AF"/>
                        <!-- Left arm (bent, hand gripping near shoulder) -->
                        <path d="M36,38 C32,36 26,34 22,34 C18,34 14,36 12,40 C10,44 12,48 16,50 L22,50 C26,48 30,44 34,40" fill="#9CA3AF"/>
                        <!-- Torso -->
                        <path d="M34,38 C32,48 30,60 28,74 C26,88 26,100 28,110 L30,118 L58,118 L60,110 C62,100 62,88 60,74 C58,60 56,48 54,38 Z" fill="#9CA3AF"/>
                        <!-- Left leg (bent to the side, foot on hold) -->
                        <path d="M30,118 C24,124 16,130 10,134 C4,138 0,140 -2,144 L-4,148 C-6,152 -4,156 0,156 L6,154 C12,150 18,144 24,138 C30,132 34,126 36,122" fill="#9CA3AF"/>
                        <!-- Right leg (straight down) -->
                        <path d="M54,118 C56,130 58,146 60,164 C62,182 62,198 60,214 L58,226 C56,232 54,238 52,244 L50,250 C48,254 44,256 42,254 L40,250 C38,246 40,242 44,238 L48,228 C50,218 52,206 52,194 C52,182 50,168 48,156 C46,144 44,134 44,124" fill="#9CA3AF"/>
                        <!-- Feet -->
                        <path d="M-4,148 C-8,152 -12,156 -14,160 C-16,164 -14,168 -10,168 L0,166 C4,164 6,160 6,156 L4,152 Z" fill="#9CA3AF"/>
                        <path d="M40,250 C36,254 32,258 30,262 C28,266 30,270 34,270 L44,268 C48,266 50,262 50,258 L48,254 Z" fill="#9CA3AF"/>
                        <!-- White details -->
                        <path d="M36,40 C34,52 32,66 30,80" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M52,40 C54,52 56,66 58,80" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M30,110 L58,110" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                        <path d="M32,116 C34,114 38,112 44,112 C50,112 54,114 56,116" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                    </g>
                </g>

                <!-- Pose 5: Compact, crouching high (like pose 5 in reference) -->
                <g class="climber__pose climber__pose--5" style="opacity:0">
                    <g transform="translate(450,280) scale(1.1)">
                        <ellipse cx="42" cy="8" rx="14" ry="16" fill="#9CA3AF"/>
                        <path d="M36,22 L48,22 L46,34 L38,34 Z" fill="#9CA3AF"/>
                        <!-- Right arm (bent overhead, hand above head) -->
                        <path d="M48,34 C52,28 56,20 58,12 C60,6 62,0 62,-6 L62,-10 C62,-14 60,-14 60,-10 L58,-2 C56,6 54,14 50,22 L48,28" fill="#9CA3AF"/>
                        <!-- Left arm (bent, hand near shoulder) -->
                        <path d="M34,34 C30,32 24,30 20,30 C16,30 12,32 10,36 C8,40 10,44 14,46 L20,46 C24,44 28,40 32,36" fill="#9CA3AF"/>
                        <!-- Torso (shorter, crouching) -->
                        <path d="M32,34 C30,42 28,52 28,62 C28,72 28,80 30,86 L32,92 L58,92 L60,86 C62,80 62,72 60,62 C58,52 56,42 54,34 Z" fill="#9CA3AF"/>
                        <!-- Left leg (bent high, knee up) -->
                        <path d="M32,92 C26,98 18,106 12,114 C6,122 4,128 6,132 L8,136 C10,140 14,140 16,136 L18,128 C22,120 28,112 34,104 C38,98 40,96 40,94" fill="#9CA3AF"/>
                        <!-- Right leg (crouching, deep knee bend) -->
                        <path d="M54,92 C58,100 62,112 64,124 C66,136 64,146 60,154 L56,162 C54,166 50,168 48,166 L46,160 C48,154 50,144 50,134 C50,124 48,114 44,106 C42,100 40,96 40,94" fill="#9CA3AF"/>
                        <!-- Feet -->
                        <path d="M8,136 C4,140 0,144 -2,148 C-4,152 -2,156 2,156 L12,154 C16,152 18,148 18,144 L16,140 Z" fill="#9CA3AF"/>
                        <path d="M56,162 C52,166 48,170 46,174 C44,178 46,182 50,182 L60,180 C64,178 66,174 66,170 L64,166 Z" fill="#9CA3AF"/>
                        <!-- White details -->
                        <path d="M34,36 C32,46 30,56 28,66" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M52,36 C54,46 56,56 58,66" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" fill="none"/>
                        <path d="M32,86 L58,86" stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                    </g>
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
