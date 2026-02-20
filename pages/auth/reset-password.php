<?php
/**
 * Saxho.net — Page Reinitialisation du mot de passe
 */

// Rediriger si deja connecte
if (is_logged_in()) {
    redirect(SITE_URL . '/');
}

// Recuperer le token et l'email depuis l'URL
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

// Rediriger si pas de token/email
if (empty($token) || empty($email)) {
    redirect(SITE_URL . '/forgot-password');
}

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.reset_password');
?>

<!-- ==========================================
     REINITIALISATION DU MOT DE PASSE
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card reveal reveal-up">

                <!-- Header -->
                <div class="auth-header">
                    <h1 class="auth-header__title"><?= e(t('auth.reset_password')) ?></h1>
                </div>

                <!-- Formulaire -->
                <form id="reset-form"
                      class="auth-form"
                      action="<?= SITE_URL ?>/api/auth/reset-password"
                      method="POST"
                      novalidate
                      data-submitting="<?= e(t('common.loading')) ?>"
                      data-site-url="<?= SITE_URL ?>">

                    <?= csrf_field() ?>

                    <!-- Token et email caches -->
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <input type="hidden" name="email" value="<?= e($email) ?>">

                    <!-- Nouveau mot de passe -->
                    <div class="form-group">
                        <label for="reset-password" class="form-label">
                            <?= e(t('auth.new_password')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="reset-password"
                                   name="password"
                                   class="form-input"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   data-strength
                                   data-error-required="<?= e(t('auth.error_password_short')) ?>">
                            <button type="button"
                                    class="password-toggle"
                                    aria-label="<?= e(t('auth.show_password')) ?>"
                                    data-label-show="<?= e(t('auth.show_password')) ?>"
                                    data-label-hide="<?= e(t('auth.hide_password')) ?>">
                                <span class="password-toggle__icon" aria-hidden="true">&#x1F441;</span>
                            </button>
                        </div>
                    </div>

                    <!-- Confirmer le mot de passe -->
                    <div class="form-group">
                        <label for="reset-password-confirm" class="form-label">
                            <?= e(t('auth.password_confirm')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="reset-password-confirm"
                                   name="password_confirm"
                                   class="form-input"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   data-error-required="<?= e(t('auth.error_password_mismatch')) ?>">
                            <button type="button"
                                    class="password-toggle"
                                    aria-label="<?= e(t('auth.show_password')) ?>"
                                    data-label-show="<?= e(t('auth.show_password')) ?>"
                                    data-label-hide="<?= e(t('auth.hide_password')) ?>">
                                <span class="password-toggle__icon" aria-hidden="true">&#x1F441;</span>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="form-group">
                        <button type="submit" class="btn btn--primary btn--lg auth-submit">
                            <?= e(t('auth.reset_password')) ?>
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
