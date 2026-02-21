<?php
/**
 * Saxho.net — Project Detail (members only)
 * Displays full project info + expression of interest modal
 */

$pageCss = 'portfolio.css';
$pageJs  = 'portfolio.js';

// Require login — redirect to login if not connected
if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . SITE_URL . '/login');
    exit;
}

$db   = getDB();
$lang = $_SESSION['lang'] ?? 'fr';
$slug = $routeParams['project_slug'] ?? '';

// Fetch project
$stmt = $db->prepare('SELECT * FROM projects WHERE slug = ? AND is_visible = 1');
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    include ROOT_PATH . '/pages/404.php';
    return;
}

// Bilingual helper
$name     = $lang === 'fr' ? $project['name_fr'] : ($project['name_en'] ?? $project['name_fr']);
$pitch    = $lang === 'fr' ? $project['pitch_fr'] : ($project['pitch_en'] ?? $project['pitch_fr']);
$problem  = $lang === 'fr' ? $project['problem_fr'] : ($project['problem_en'] ?? $project['problem_fr']);
$solution = $lang === 'fr' ? $project['solution_fr'] : ($project['solution_en'] ?? $project['solution_fr']);
$skills   = $lang === 'fr' ? ($project['skills_sought_fr'] ?? '') : ($project['skills_sought_en'] ?? $project['skills_sought_fr'] ?? '');

$pageDescription = $pitch;

// Phase progress
$phases = ['ideation', 'study', 'prototype', 'development', 'pre_launch', 'transferred'];
$currentPhaseIndex = array_search($project['phase'], $phases);

// Current user for pre-filling interest form
$user = current_user();

// Check if user already submitted an interest
$stmtCheck = $db->prepare('SELECT id FROM interest_requests WHERE user_id = ? AND project_id = ?');
$stmtCheck->execute([current_user_id(), $project['id']]);
$hasExistingInterest = $stmtCheck->fetch() !== false;
?>

<!-- Header -->
<section class="project-detail__header">
    <div class="container">
        <a href="<?= SITE_URL ?>/portfolio" class="project-detail__back" style="color: rgba(255,255,255,0.7);"><?= e(t('portfolio.back_to_portfolio')) ?></a>

        <?php if (!empty($project['image'])): ?>
        <div class="project-detail__image-wrap reveal reveal-up">
            <img src="<?= SITE_URL ?>/assets/img/uploads/<?= e($project['image']) ?>" alt="<?= e($name) ?>">
        </div>
        <?php endif; ?>

        <h1 class="project-detail__name reveal reveal-up"><?= e($name) ?></h1>

        <div class="project-detail__badges reveal reveal-up">
            <span class="badge badge--primary"><?= e($project['domain']) ?></span>
            <span class="badge badge--<?= $project['status'] === 'open' ? 'success' : ($project['status'] === 'paused' ? 'warning' : 'muted') ?>">
                <?= e(t('portfolio.status_' . $project['status'])) ?>
            </span>
        </div>
    </div>
</section>

<section class="project-detail section">
    <div class="container">

        <!-- Phase Progress -->
        <div class="project-detail__phase reveal reveal-up">
            <div class="steps">
                <?php foreach ($phases as $idx => $ph): ?>
                <div class="step<?= $idx < $currentPhaseIndex ? ' step--done' : '' ?><?= $idx === $currentPhaseIndex ? ' step--active' : '' ?>">
                    <div class="step__dot"></div>
                    <span class="step__label"><?= e(t('portfolio.phase_' . $ph)) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Content -->
        <div class="project-detail__content">

            <!-- Pitch -->
            <div class="project-detail__section reveal reveal-up">
                <h2 class="project-detail__section-title"><?= e($name) ?></h2>
                <p class="project-detail__text"><?= nl2br(e($pitch)) ?></p>
            </div>

            <!-- Problem -->
            <?php if (!empty($problem)): ?>
            <div class="project-detail__section reveal reveal-up">
                <h2 class="project-detail__section-title"><?= e(t('portfolio.problem')) ?></h2>
                <p class="project-detail__text"><?= nl2br(e($problem)) ?></p>
            </div>
            <?php endif; ?>

            <!-- Solution -->
            <?php if (!empty($solution)): ?>
            <div class="project-detail__section reveal reveal-up">
                <h2 class="project-detail__section-title"><?= e(t('portfolio.solution')) ?></h2>
                <p class="project-detail__text"><?= nl2br(e($solution)) ?></p>
            </div>
            <?php endif; ?>

            <!-- Info -->
            <div class="project-detail__section reveal reveal-up">
                <div class="project-detail__info">
                    <div class="project-detail__info-item">
                        <span class="project-detail__info-label"><?= e(t('portfolio.domain')) ?></span>
                        <span class="project-detail__info-value"><?= e($project['domain']) ?></span>
                    </div>
                    <div class="project-detail__info-item">
                        <span class="project-detail__info-label"><?= e(t('portfolio.phase')) ?></span>
                        <span class="project-detail__info-value"><?= e(t('portfolio.phase_' . $project['phase'])) ?></span>
                    </div>
                    <?php if (!empty($project['investment_sought'])): ?>
                    <div class="project-detail__info-item">
                        <span class="project-detail__info-label"><?= e(t('portfolio.investment')) ?></span>
                        <span class="project-detail__info-value"><?= e($project['investment_sought']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['launch_date'])): ?>
                    <div class="project-detail__info-item">
                        <span class="project-detail__info-label"><?= e(t('portfolio.launch_date')) ?></span>
                        <span class="project-detail__info-value"><?= e(format_date($project['launch_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Skills Sought -->
            <?php if (!empty($skills)): ?>
            <div class="project-detail__section reveal reveal-up">
                <h2 class="project-detail__section-title"><?= e(t('portfolio.skills')) ?></h2>
                <p class="project-detail__text"><?= nl2br(e($skills)) ?></p>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <?php if ($project['status'] === 'open' && !$hasExistingInterest): ?>
            <div class="project-detail__actions reveal reveal-up">
                <button type="button" class="btn btn--primary" data-interest-type="competence" data-project-id="<?= $project['id'] ?>">
                    <?= e(t('portfolio.contribute_skills')) ?>
                </button>
                <button type="button" class="btn btn--secondary" data-interest-type="investment" data-project-id="<?= $project['id'] ?>">
                    <?= e(t('portfolio.contribute_invest')) ?>
                </button>
            </div>
            <?php elseif ($hasExistingInterest): ?>
            <div class="project-detail__actions reveal reveal-up">
                <p style="color: var(--c-muted); font-style: italic;"><?= e(t('portfolio.interest_already')) ?></p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<!-- Interest Modal -->
<div id="interest-modal" class="interest-modal" data-site-url="<?= SITE_URL ?>">
    <div class="interest-modal__overlay"></div>
    <div class="interest-modal__box">
        <button type="button" class="interest-modal__close">&times;</button>

        <h3 class="interest-modal__title"><?= e(t('portfolio.interest_title')) ?></h3>
        <p class="interest-modal__type" id="interest-type-label"></p>

        <div id="interest-form-message" class="interest-form__message"></div>

        <form id="interest-form">
            <?= csrf_field() ?>
            <input type="hidden" id="interest-project-id" value="<?= $project['id'] ?>">
            <input type="hidden" id="interest-type" value="">

            <!-- Common fields -->
            <div class="interest-form__row">
                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-company"><?= e(t('portfolio.interest_company')) ?> *</label>
                    <input type="text" id="interest-company" class="interest-form__input"
                           value="<?= e($user['company'] ?? '') ?>" required>
                </div>
                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-job"><?= e(t('portfolio.interest_job')) ?> *</label>
                    <input type="text" id="interest-job" class="interest-form__input"
                           value="<?= e($user['job_title'] ?? '') ?>" required>
                </div>
            </div>

            <div class="interest-form__row">
                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-phone"><?= e(t('portfolio.interest_phone')) ?> *</label>
                    <input type="tel" id="interest-phone" class="interest-form__input"
                           value="<?= e($user['phone'] ?? '') ?>" required>
                </div>
                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-country"><?= e(t('portfolio.interest_country')) ?> *</label>
                    <input type="text" id="interest-country" class="interest-form__input"
                           value="<?= e($user['country'] ?? '') ?>" required>
                </div>
            </div>

            <div class="interest-form__group">
                <label class="interest-form__label" for="interest-address"><?= e(t('portfolio.interest_address')) ?> *</label>
                <input type="text" id="interest-address" class="interest-form__input"
                       value="<?= e($user['address'] ?? '') ?>" required>
            </div>

            <div class="interest-form__group">
                <label class="interest-form__label" for="interest-message"><?= e(t('portfolio.interest_message')) ?></label>
                <textarea id="interest-message" class="interest-form__textarea" rows="3"></textarea>
            </div>

            <!-- Skills-specific fields -->
            <div id="interest-skills-section" class="interest-form__section">
                <p class="interest-form__section-title"><?= e(t('portfolio.interest_type_skills')) ?></p>

                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-expertise"><?= e(t('portfolio.interest_expertise')) ?> *</label>
                    <input type="text" id="interest-expertise" class="interest-form__input">
                </div>
                <div class="interest-form__row">
                    <div class="interest-form__group">
                        <label class="interest-form__label" for="interest-availability"><?= e(t('portfolio.interest_availability')) ?></label>
                        <input type="text" id="interest-availability" class="interest-form__input" placeholder="Ex: 2 jours/semaine">
                    </div>
                    <div class="interest-form__group">
                        <label class="interest-form__label" for="interest-linkedin"><?= e(t('portfolio.interest_linkedin')) ?></label>
                        <input type="url" id="interest-linkedin" class="interest-form__input" placeholder="https://">
                    </div>
                </div>
            </div>

            <!-- Investment-specific fields -->
            <div id="interest-invest-section" class="interest-form__section">
                <p class="interest-form__section-title"><?= e(t('portfolio.interest_type_invest')) ?></p>

                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-range"><?= e(t('portfolio.interest_range')) ?> *</label>
                    <select id="interest-range" class="interest-form__select">
                        <option value="">—</option>
                        <option value="less_10k"><?= e(t('portfolio.range_less_10k')) ?></option>
                        <option value="10k_50k"><?= e(t('portfolio.range_10k_50k')) ?></option>
                        <option value="50k_100k"><?= e(t('portfolio.range_50k_100k')) ?></option>
                        <option value="more_100k"><?= e(t('portfolio.range_more_100k')) ?></option>
                        <option value="to_discuss"><?= e(t('portfolio.range_to_discuss')) ?></option>
                    </select>
                </div>
                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-experience"><?= e(t('portfolio.interest_experience')) ?></label>
                    <textarea id="interest-experience" class="interest-form__textarea" rows="2"></textarea>
                </div>
                <div class="interest-form__group">
                    <label class="interest-form__label" for="interest-structure"><?= e(t('portfolio.interest_structure')) ?></label>
                    <input type="text" id="interest-structure" class="interest-form__input">
                </div>
            </div>

            <!-- Submit -->
            <div class="interest-form__footer">
                <button type="button" class="btn btn--outline" data-interest-cancel><?= e(t('common.cancel')) ?></button>
                <button type="submit" class="btn btn--primary" data-text="<?= e(t('portfolio.interest_submit')) ?>" data-loading="<?= e(t('portfolio.interest_submitting')) ?>">
                    <?= e(t('portfolio.interest_submit')) ?>
                </button>
            </div>
        </form>
    </div>
</div>
