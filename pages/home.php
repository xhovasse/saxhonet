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
                <!-- Layout: holds grouped in a tight cluster, upper-left quadrant -->
                <!-- Climber is bottom-right, reaching up toward the holds -->

                <!-- Hold 5: top-left — Incubateur Saxho — Dark Blue/Indigo blob -->
                <g class="climber__hold climber__hold--5" data-service="5">
                    <circle class="climber__hold-glow" cx="310" cy="115" r="40" fill="#6366F1" opacity="0"/>
                    <path class="climber__hold-shape" d="M290,100 C295,82 312,75 328,80 C340,84 348,96 346,110 C344,126 334,138 318,140 C302,142 290,132 286,118 C284,110 286,104 290,100 Z" fill="#6366F1"/>
                </g>

                <!-- Hold 4: top-right — Task Force Innovation — Sky Blue blob -->
                <g class="climber__hold climber__hold--4" data-service="4">
                    <circle class="climber__hold-glow" cx="470" cy="175" r="32" fill="#38BDF8" opacity="0"/>
                    <path class="climber__hold-shape" d="M452,168 C456,152 470,148 482,156 C492,162 496,178 488,192 C480,204 464,206 454,196 C446,188 448,176 452,168 Z" fill="#38BDF8"/>
                </g>

                <!-- Hold 3: center-left — Processus d'innovation — Green blob -->
                <g class="climber__hold climber__hold--3" data-service="3">
                    <circle class="climber__hold-glow" cx="280" cy="310" r="36" fill="#10B981" opacity="0"/>
                    <path class="climber__hold-shape" d="M260,296 C268,280 286,276 300,284 C312,292 318,310 312,326 C306,340 290,346 276,338 C262,330 254,312 260,296 Z" fill="#10B981"/>
                </g>

                <!-- Hold 2: right-center — Facilitation d'idéation — Amber/Yellow blob -->
                <g class="climber__hold climber__hold--2" data-service="2">
                    <circle class="climber__hold-glow" cx="480" cy="370" r="30" fill="#F5A623" opacity="0"/>
                    <path class="climber__hold-shape" d="M464,358 C470,344 484,340 496,348 C506,356 510,372 502,384 C494,396 478,398 468,388 C458,378 458,368 464,358 Z" fill="#F5A623"/>
                </g>

                <!-- Hold 1: bottom-center — Apporteur d'idées — Orange blob -->
                <g class="climber__hold climber__hold--1" data-service="1">
                    <circle class="climber__hold-glow" cx="350" cy="490" r="32" fill="#FF6B4A" opacity="0"/>
                    <path class="climber__hold-shape" d="M334,478 C340,464 354,458 368,464 C380,470 386,486 380,500 C374,512 358,518 346,510 C334,502 328,490 334,478 Z" fill="#FF6B4A"/>
                </g>

                <!-- ====== CLIMBER SILHOUETTE (bottom-right, outline style) ====== -->
                <!-- Realistic climber: filled grey body with white internal lines for muscle/clothing detail -->
                <!-- Positioned bottom-right, dynamic crouching pose, right arm reaching up toward hold 2 -->

                <!-- All 6 poses share the same bottom-right position -->
                <!-- Pose 0: Default — dynamic crouch, right arm reaching up-right -->
                <g class="climber__pose climber__pose--0" style="opacity:1">
                    <!-- Body silhouette (single unified shape) -->
                    <path d="
                        M510,395 C516,389 520,382 518,376 C516,370 510,366 504,368
                        L494,374 C488,378 482,384 478,390
                        L468,408 C464,414 458,418 452,420
                        L440,424 C436,426 434,430 436,434
                        L440,438 C444,442 450,440 454,436
                        L472,422 C478,418 484,412 488,406
                        L496,394
                        L498,400 L496,416
                        C494,428 490,440 484,450
                        L474,466 C470,472 468,480 468,488
                        L470,520 C470,530 466,540 460,548
                        L440,572 C434,578 430,586 430,596
                        L430,624 C430,630 432,636 436,640
                        L444,650 C448,654 454,654 456,650
                        L458,644 C460,638 458,632 454,628
                        L446,620 C442,616 440,610 440,604
                        L442,590 C442,584 446,578 450,574
                        L472,548 C480,540 486,530 488,518
                        L490,500 C490,494 492,488 496,484
                        L500,478
                        L504,486 L506,498
                        C508,510 512,522 520,532
                        L542,558 C548,564 552,572 554,582
                        L558,616 C558,624 562,632 568,638
                        L580,650 C584,654 590,654 592,650
                        L594,644 C596,638 592,632 588,628
                        L578,618 C574,614 572,608 572,602
                        L568,572 C566,562 562,552 556,544
                        L534,518 C528,510 524,500 522,490
                        L518,460 L516,440
                        L518,420 L522,404
                        L528,412 L538,428
                        C544,436 552,442 562,446
                        L586,452 C592,454 596,452 598,448
                        L600,442 C602,436 598,432 592,430
                        L570,424 C562,422 554,416 548,408
                        L532,386 C528,380 524,376 520,374
                        L514,378 L510,382
                        Z
                    " fill="#9CA3AF"/>
                    <!-- Head -->
                    <circle cx="512" cy="362" r="18" fill="#9CA3AF"/>
                    <!-- White outline details (climbing harness, arm contour, leg contour) -->
                    <path d="M498,400 L496,416 C494,428 490,440 484,450" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <path d="M504,486 L506,498 C508,510 512,522 520,532" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <path d="M490,500 C490,494 492,488 496,484" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <ellipse cx="500" cy="462" rx="14" ry="6" stroke="rgba(255,255,255,0.2)" stroke-width="1" fill="none" transform="rotate(-10,500,462)"/>
                </g>

                <!-- Pose 1: Reaching down-left toward hold 1 (orange, bottom) -->
                <g class="climber__pose climber__pose--1" style="opacity:0">
                    <path d="
                        M510,395 C516,389 520,382 518,376 C516,370 510,366 504,368
                        L494,374 C488,378 482,384 478,390
                        L460,416 C454,424 446,432 436,438
                        L410,456 C402,462 394,470 388,480
                        L374,500 C370,506 368,510 370,514
                        L374,518 C378,520 382,518 386,514
                        L400,492 C408,480 418,470 428,462
                        L456,444 C462,440 468,434 472,428
                        L484,412
                        L486,418 L484,434
                        C482,446 478,458 472,468
                        L462,484 C458,490 456,498 456,506
                        L458,538 C458,548 454,558 448,566
                        L428,590 C422,596 418,604 418,614
                        L418,642 C418,648 420,654 424,658
                        L432,668 C436,672 442,672 444,668
                        L446,662 C448,656 446,650 442,646
                        L434,638 C430,634 428,628 428,622
                        L430,608 C430,602 434,596 438,592
                        L460,566 C468,558 474,548 476,536
                        L478,518 C478,512 480,506 484,502
                        L488,496
                        L492,504 L494,516
                        C496,528 500,540 508,550
                        L530,576 C536,582 540,590 542,600
                        L546,634 C546,642 550,650 556,656
                        L568,668 C572,672 578,672 580,668
                        L582,662 C584,656 580,650 576,646
                        L566,636 C562,632 560,626 560,620
                        L556,590 C554,580 550,570 544,562
                        L522,536 C516,528 512,518 510,508
                        L506,478 L504,458
                        L506,438 L510,422
                        L516,430 L526,446
                        C532,454 540,460 550,464
                        L574,470 C580,472 584,470 586,466
                        L588,460 C590,454 586,450 580,448
                        L558,442 C550,440 542,434 536,426
                        L520,404 C516,398 512,394 508,392
                        L510,395 Z
                    " fill="#9CA3AF"/>
                    <circle cx="512" cy="362" r="18" fill="#9CA3AF"/>
                    <path d="M486,418 L484,434 C482,446 478,458 472,468" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <path d="M492,504 L494,516 C496,528 500,540 508,550" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <ellipse cx="488" cy="480" rx="14" ry="6" stroke="rgba(255,255,255,0.2)" stroke-width="1" fill="none" transform="rotate(-10,488,480)"/>
                </g>

                <!-- Pose 2: Reaching right toward hold 2 (amber/yellow, right-center) -->
                <g class="climber__pose climber__pose--2" style="opacity:0">
                    <path d="
                        M510,395 C516,389 520,382 518,376 C516,370 510,366 504,368
                        L494,374 C488,378 482,384 478,390
                        L468,408 C464,414 458,418 452,420
                        L440,424 C436,426 434,430 436,434
                        L440,438 C444,442 450,440 454,436
                        L472,422 C478,418 484,412 488,406
                        L496,394
                        L498,400 L496,416
                        C494,428 490,440 484,450
                        L474,466 C470,472 468,480 468,488
                        L470,520 C470,530 466,540 460,548
                        L440,572 C434,578 430,586 430,596
                        L430,624 C430,630 432,636 436,640
                        L444,650 C448,654 454,654 456,650
                        L458,644 C460,638 458,632 454,628
                        L446,620 C442,616 440,610 440,604
                        L442,590 C442,584 446,578 450,574
                        L472,548 C480,540 486,530 488,518
                        L490,500 C490,494 492,488 496,484
                        L500,478
                        L504,486 L506,498
                        C508,510 512,522 520,532
                        L542,558 C548,564 552,572 554,582
                        L558,616 C558,624 562,632 568,638
                        L580,650 C584,654 590,654 592,650
                        L594,644 C596,638 592,632 588,628
                        L578,618 C574,614 572,608 572,602
                        L568,572 C566,562 562,552 556,544
                        L534,518 C528,510 524,500 522,490
                        L518,460 L516,440
                        L518,420 L522,404
                        L530,410 L542,420
                        C550,426 560,430 570,432
                        L598,434 C606,434 614,430 620,424
                        L634,410 C640,404 648,396 656,390
                        L672,378 C678,374 680,370 678,366
                        L674,362 C670,360 666,362 662,366
                        L644,380 C636,386 628,392 620,398
                        L604,412 C598,416 592,418 586,418
                        L560,416 C552,414 544,410 538,404
                        L524,390 C520,384 516,380 512,378
                        L510,382 L510,395 Z
                    " fill="#9CA3AF"/>
                    <circle cx="512" cy="362" r="18" fill="#9CA3AF"/>
                    <path d="M498,400 L496,416 C494,428 490,440 484,450" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <path d="M504,486 L506,498 C508,510 512,522 520,532" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <ellipse cx="500" cy="462" rx="14" ry="6" stroke="rgba(255,255,255,0.2)" stroke-width="1" fill="none" transform="rotate(-10,500,462)"/>
                </g>

                <!-- Pose 3: Reaching up-left toward hold 3 (green, center-left) -->
                <g class="climber__pose climber__pose--3" style="opacity:0">
                    <path d="
                        M510,395 C516,389 520,382 518,376 C516,370 510,366 504,368
                        L490,372 C484,376 476,382 470,390
                        L450,418 C442,428 432,436 420,440
                        L388,452 C378,454 370,450 364,444
                        L340,420 C332,412 324,406 316,402
                        L294,394 C288,392 282,394 280,398
                        L278,404 C278,410 282,414 288,414
                        L310,418 C318,420 326,426 332,432
                        L358,458 C366,466 376,470 388,468
                        L424,462 C432,460 440,454 446,448
                        L468,424 C472,420 476,414 478,410
                        L482,402
                        L484,410 L482,426
                        C480,438 476,450 470,460
                        L460,476 C456,482 454,490 454,498
                        L456,530 C456,540 452,550 446,558
                        L426,582 C420,588 416,596 416,606
                        L416,634 C416,640 418,646 422,650
                        L430,660 C434,664 440,664 442,660
                        L444,654 C446,648 444,642 440,638
                        L432,630 C428,626 426,620 426,614
                        L428,600 C428,594 432,588 436,584
                        L458,558 C466,550 472,540 474,528
                        L476,510 C476,504 478,498 482,494
                        L486,488
                        L490,496 L492,508
                        C494,520 498,532 506,542
                        L528,568 C534,574 538,582 540,592
                        L544,626 C544,634 548,642 554,648
                        L566,660 C570,664 576,664 578,660
                        L580,654 C582,648 578,642 574,638
                        L564,628 C560,624 558,618 558,612
                        L554,582 C552,572 548,562 542,554
                        L520,528 C514,520 510,510 508,500
                        L504,470 L502,450
                        L504,430 L508,414
                        L514,422 L524,438
                        C530,446 538,452 548,456
                        L572,462 C578,464 582,462 584,458
                        L586,452 C588,446 584,442 578,440
                        L556,434 C548,432 540,426 534,418
                        L518,396 C514,390 510,386 506,384
                        L510,395 Z
                    " fill="#9CA3AF"/>
                    <circle cx="512" cy="362" r="18" fill="#9CA3AF"/>
                    <path d="M484,410 L482,426 C480,438 476,450 470,460" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <path d="M490,496 L492,508 C494,520 498,532 506,542" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <ellipse cx="488" cy="474" rx="14" ry="6" stroke="rgba(255,255,255,0.2)" stroke-width="1" fill="none" transform="rotate(-10,488,474)"/>
                </g>

                <!-- Pose 4: Reaching up toward hold 4 (sky blue, top-right) -->
                <g class="climber__pose climber__pose--4" style="opacity:0">
                    <path d="
                        M510,395 C516,389 520,382 518,376 C516,370 510,366 504,368
                        L494,374 C488,378 482,384 478,390
                        L468,408 C464,414 458,418 452,420
                        L440,424 C436,426 434,430 436,434
                        L440,438 C444,442 450,440 454,436
                        L472,422 C478,418 484,412 488,406
                        L496,394
                        L498,400 L496,416
                        C494,428 490,440 484,450
                        L474,466 C470,472 468,480 468,488
                        L470,520 C470,530 466,540 460,548
                        L440,572 C434,578 430,586 430,596
                        L430,624 C430,630 432,636 436,640
                        L444,650 C448,654 454,654 456,650
                        L458,644 C460,638 458,632 454,628
                        L446,620 C442,616 440,610 440,604
                        L442,590 C442,584 446,578 450,574
                        L472,548 C480,540 486,530 488,518
                        L490,500 C490,494 492,488 496,484
                        L500,478
                        L504,486 L506,498
                        C508,510 512,522 520,532
                        L542,558 C548,564 552,572 554,582
                        L558,616 C558,624 562,632 568,638
                        L580,650 C584,654 590,654 592,650
                        L594,644 C596,638 592,632 588,628
                        L578,618 C574,614 572,608 572,602
                        L568,572 C566,562 562,552 556,544
                        L534,518 C528,510 524,500 522,490
                        L518,460 L516,440
                        L518,420 L522,404
                        L528,410 L540,416
                        C548,420 556,420 564,416
                        L588,402 C596,396 602,388 606,380
                        L618,352 C622,344 628,336 634,330
                        L654,310 C660,304 664,296 666,288
                        L670,270 C672,264 676,258 680,254
                        L690,244 C694,240 694,236 690,234
                        L686,234 C680,234 676,238 672,244
                        L660,260 C656,266 652,274 650,282
                        L646,302 C644,310 638,318 632,324
                        L610,346 C602,352 598,362 594,372
                        L582,398 C578,404 572,408 566,410
                        L542,414 C536,414 530,412 526,408
                        L516,396 L510,395 Z
                    " fill="#9CA3AF"/>
                    <circle cx="512" cy="362" r="18" fill="#9CA3AF"/>
                    <path d="M498,400 L496,416 C494,428 490,440 484,450" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <path d="M504,486 L506,498 C508,510 512,522 520,532" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <ellipse cx="500" cy="462" rx="14" ry="6" stroke="rgba(255,255,255,0.2)" stroke-width="1" fill="none" transform="rotate(-10,500,462)"/>
                </g>

                <!-- Pose 5: Reaching high up-left toward hold 5 (indigo, top-left) — summit pose -->
                <g class="climber__pose climber__pose--5" style="opacity:0">
                    <path d="
                        M510,395 C516,389 520,382 518,376 C516,370 510,366 504,368
                        L494,374 C488,378 482,384 478,390
                        L468,408 C464,414 458,418 452,420
                        L440,424 C436,426 434,430 436,434
                        L440,438 C444,442 450,440 454,436
                        L472,422 C478,418 484,412 488,406
                        L496,394
                        L498,400 L496,416
                        C494,428 490,440 484,450
                        L474,466 C470,472 468,480 468,488
                        L470,520 C470,530 466,540 460,548
                        L440,572 C434,578 430,586 430,596
                        L430,624 C430,630 432,636 436,640
                        L444,650 C448,654 454,654 456,650
                        L458,644 C460,638 458,632 454,628
                        L446,620 C442,616 440,610 440,604
                        L442,590 C442,584 446,578 450,574
                        L472,548 C480,540 486,530 488,518
                        L490,500 C490,494 492,488 496,484
                        L500,478
                        L504,486 L506,498
                        C508,510 512,522 520,532
                        L542,558 C548,564 552,572 554,582
                        L558,616 C558,624 562,632 568,638
                        L580,650 C584,654 590,654 592,650
                        L594,644 C596,638 592,632 588,628
                        L578,618 C574,614 572,608 572,602
                        L568,572 C566,562 562,552 556,544
                        L534,518 C528,510 524,500 522,490
                        L518,460 L516,440
                        L518,420 L522,404
                        L528,408 L538,406
                        C546,404 554,398 560,390
                        L578,366 C584,358 588,348 590,338
                        L596,306 C598,296 602,286 608,278
                        L628,254 C634,248 638,240 640,232
                        L644,214 C646,208 650,202 656,198
                        L670,186 C676,182 680,176 682,170
                        L686,156 C688,150 686,146 682,146
                        L678,148 C672,152 668,158 666,164
                        L660,182 C656,188 650,194 644,198
                        L624,216 C616,222 610,230 606,240
                        L600,264 C598,274 594,284 588,292
                        L568,320 C562,328 558,338 556,348
                        L550,376 C548,384 542,390 536,394
                        L526,400 C522,402 518,400 514,396
                        L510,395 Z
                    " fill="#9CA3AF"/>
                    <circle cx="512" cy="362" r="18" fill="#9CA3AF"/>
                    <path d="M498,400 L496,416 C494,428 490,440 484,450" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <path d="M504,486 L506,498 C508,510 512,522 520,532" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                    <ellipse cx="500" cy="462" rx="14" ry="6" stroke="rgba(255,255,255,0.2)" stroke-width="1" fill="none" transform="rotate(-10,500,462)"/>
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
