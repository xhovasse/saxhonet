<?php
/**
 * Saxho.net — Page Connexion
 */

// Rediriger si deja connecte
if (is_logged_in()) {
    redirect(SITE_URL . '/');
}

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.login');
?>

<!-- ==========================================
     CONNEXION
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card reveal reveal-up">

                <!-- Header -->
                <div class="auth-header">
                    <h1 class="auth-header__title"><?= e(t('auth.login')) ?></h1>
                    <p class="auth-header__subtitle"><?= e(t('auth.login_subtitle')) ?></p>
                </div>

                <!-- Formulaire -->
                <form id="login-form"
                      class="auth-form"
                      action="<?= SITE_URL ?>/api/auth/login"
                      method="POST"
                      novalidate
                      data-submitting="<?= e(t('common.loading')) ?>"
                      data-site-url="<?= SITE_URL ?>">

                    <?= csrf_field() ?>

                    <!-- Redirect (si retour apres redirection login requis) -->
                    <input type="hidden" name="redirect" value="<?= e($_GET['redirect'] ?? '') ?>">

                    <!-- Email -->
                    <div class="form-group">
                        <label for="login-email" class="form-label">
                            <?= e(t('auth.email')) ?> <span class="form-required">*</span>
                        </label>
                        <input type="email"
                               id="login-email"
                               name="email"
                               class="form-input"
                               required
                               autocomplete="email"
                               maxlength="255"
                               data-error-required="<?= e(t('auth.error_email_invalid')) ?>">
                    </div>

                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="login-password" class="form-label">
                            <?= e(t('auth.password')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="login-password"
                                   name="password"
                                   class="form-input"
                                   required
                                   autocomplete="current-password"
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

                    <!-- Se souvenir de moi -->
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="remember_me" value="1">
                            <span class="form-checkbox__label"><?= e(t('auth.remember_me')) ?></span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="form-group">
                        <button type="submit" class="btn btn--primary btn--lg auth-submit">
                            <?= e(t('auth.login_button')) ?>
                        </button>
                    </div>
                </form>

                <!-- Liens -->
                <div class="auth-links">
                    <a href="<?= SITE_URL ?>/forgot-password" class="auth-links__link">
                        <?= e(t('auth.forgot_password')) ?>
                    </a>
                    <span class="auth-divider"><?= e(t('auth.or')) ?></span>
                    <a href="<?= SITE_URL ?>/register" class="auth-links__link">
                        <?= e(t('auth.no_account')) ?> <strong><?= e(t('auth.register')) ?></strong>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
