<?php
/**
 * Saxho.net — Page Inscription
 */

// Rediriger si deja connecte
if (is_logged_in()) {
    redirect(SITE_URL . '/');
}

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.register');
?>

<!-- ==========================================
     INSCRIPTION
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card auth-card--wide reveal reveal-up">

                <!-- Header -->
                <div class="auth-header">
                    <h1 class="auth-header__title"><?= e(t('auth.register')) ?></h1>
                    <p class="auth-header__subtitle"><?= e(t('auth.register_subtitle')) ?></p>
                </div>

                <!-- Formulaire -->
                <form id="register-form"
                      class="auth-form"
                      action="<?= SITE_URL ?>/api/auth/register"
                      method="POST"
                      novalidate
                      data-submitting="<?= e(t('common.loading')) ?>"
                      data-site-url="<?= SITE_URL ?>"
                      data-error-name-required="<?= e(t('auth.error_name_required')) ?>"
                      data-error-email-invalid="<?= e(t('auth.error_email_invalid')) ?>"
                      data-error-password-short="<?= e(t('auth.error_password_short')) ?>"
                      data-error-password-weak="<?= e(t('auth.error_password_weak')) ?>"
                      data-error-password-mismatch="<?= e(t('auth.error_password_mismatch')) ?>"
                      data-error-terms="<?= e(t('auth.error_terms')) ?>">

                    <?= csrf_field() ?>

                    <!-- Prenom + Nom (cote a cote) -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="register-first-name" class="form-label">
                                <?= e(t('auth.first_name')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="text"
                                   id="register-first-name"
                                   name="first_name"
                                   class="form-input"
                                   required
                                   autocomplete="given-name"
                                   maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="register-last-name" class="form-label">
                                <?= e(t('auth.last_name')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="text"
                                   id="register-last-name"
                                   name="last_name"
                                   class="form-input"
                                   required
                                   autocomplete="family-name"
                                   maxlength="100">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="register-email" class="form-label">
                            <?= e(t('auth.email')) ?> <span class="form-required">*</span>
                        </label>
                        <input type="email"
                               id="register-email"
                               name="email"
                               class="form-input"
                               required
                               autocomplete="email"
                               maxlength="255">
                    </div>

                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="register-password" class="form-label">
                            <?= e(t('auth.password')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="register-password"
                                   name="password"
                                   class="form-input"
                                   required
                                   autocomplete="new-password"
                                   minlength="8"
                                   data-strength
                                   data-error-required="<?= e(t('auth.error_password_short')) ?>"
                                   data-label-weak="<?= e(t('auth.password_strength_weak')) ?>"
                                   data-label-fair="<?= e(t('auth.password_strength_fair')) ?>"
                                   data-label-good="<?= e(t('auth.password_strength_good')) ?>"
                                   data-label-strong="<?= e(t('auth.password_strength_strong')) ?>">
                            <button type="button"
                                    class="password-toggle"
                                    aria-label="<?= e(t('auth.show_password')) ?>"
                                    data-label-show="<?= e(t('auth.show_password')) ?>"
                                    data-label-hide="<?= e(t('auth.hide_password')) ?>">
                                <span class="password-toggle__icon" aria-hidden="true">&#x1F441;</span>
                            </button>
                        </div>
                        <div class="password-strength" aria-live="polite">
                            <div class="password-strength__bar"></div>
                            <span class="password-strength__label"><?= e(t('auth.password_strength')) ?></span>
                        </div>
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div class="form-group">
                        <label for="register-password-confirm" class="form-label">
                            <?= e(t('auth.password_confirm')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="register-password-confirm"
                                   name="password_confirm"
                                   class="form-input"
                                   required
                                   autocomplete="new-password">
                            <button type="button"
                                    class="password-toggle"
                                    aria-label="<?= e(t('auth.show_password')) ?>"
                                    data-label-show="<?= e(t('auth.show_password')) ?>"
                                    data-label-hide="<?= e(t('auth.hide_password')) ?>">
                                <span class="password-toggle__icon" aria-hidden="true">&#x1F441;</span>
                            </button>
                        </div>
                    </div>

                    <!-- Champs optionnels -->
                    <div class="auth-form__optional">
                        <p class="auth-form__optional-title"><?= e(t('auth.personal_info')) ?> <span class="form-hint">(<?= e(t('contact.company_optional')) ?>)</span></p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="register-company" class="form-label"><?= e(t('auth.company')) ?></label>
                                <input type="text"
                                       id="register-company"
                                       name="company"
                                       class="form-input"
                                       autocomplete="organization"
                                       maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="register-job-title" class="form-label"><?= e(t('auth.job_title')) ?></label>
                                <input type="text"
                                       id="register-job-title"
                                       name="job_title"
                                       class="form-input"
                                       autocomplete="organization-title"
                                       maxlength="255">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="register-phone" class="form-label"><?= e(t('auth.phone')) ?></label>
                                <input type="tel"
                                       id="register-phone"
                                       name="phone"
                                       class="form-input"
                                       autocomplete="tel"
                                       maxlength="20">
                            </div>
                            <div class="form-group">
                                <label for="register-country" class="form-label"><?= e(t('auth.country')) ?></label>
                                <input type="text"
                                       id="register-country"
                                       name="country"
                                       class="form-input"
                                       autocomplete="country-name"
                                       maxlength="100">
                            </div>
                        </div>
                    </div>

                    <!-- CGU -->
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="accept_terms" value="1" required>
                            <span class="form-checkbox__label">
                                <?= e(t('auth.accept_terms')) ?>
                                <a href="<?= SITE_URL ?>/legal" target="_blank" rel="noopener"><?= e(t('footer.legal')) ?></a>
                            </span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="form-group">
                        <button type="submit" class="btn btn--primary btn--lg auth-submit">
                            <?= e(t('auth.register_button')) ?>
                        </button>
                    </div>
                </form>

                <!-- Liens -->
                <div class="auth-links">
                    <a href="<?= SITE_URL ?>/login" class="auth-links__link">
                        <?= e(t('auth.has_account')) ?> <strong><?= e(t('auth.login')) ?></strong>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
