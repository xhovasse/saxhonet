<?php
/**
 * Saxho.net — Page Mot de passe oublie
 */

// Rediriger si deja connecte
if (is_logged_in()) {
    redirect(SITE_URL . '/');
}

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.forgot_password_title');
?>

<!-- ==========================================
     MOT DE PASSE OUBLIE
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card reveal reveal-up">

                <!-- Header -->
                <div class="auth-header">
                    <h1 class="auth-header__title"><?= e(t('auth.forgot_password_title')) ?></h1>
                    <p class="auth-header__subtitle"><?= e(t('auth.forgot_password_subtitle')) ?></p>
                </div>

                <!-- Formulaire -->
                <form id="forgot-form"
                      class="auth-form"
                      action="<?= SITE_URL ?>/api/auth/forgot-password"
                      method="POST"
                      novalidate
                      data-submitting="<?= e(t('common.loading')) ?>"
                      data-site-url="<?= SITE_URL ?>">

                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="forgot-email" class="form-label">
                            <?= e(t('auth.email')) ?> <span class="form-required">*</span>
                        </label>
                        <input type="email"
                               id="forgot-email"
                               name="email"
                               class="form-input"
                               required
                               autocomplete="email"
                               maxlength="255"
                               data-error-required="<?= e(t('auth.error_email_invalid')) ?>">
                    </div>

                    <!-- Submit -->
                    <div class="form-group">
                        <button type="submit" class="btn btn--primary btn--lg auth-submit">
                            <?= e(t('auth.send_reset_link')) ?>
                        </button>
                    </div>
                </form>

                <!-- Liens -->
                <div class="auth-links">
                    <a href="<?= SITE_URL ?>/login" class="auth-links__link">
                        &larr; <?= e(t('auth.login')) ?>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
