<?php
/**
 * Saxho.net — Page Verification email
 *
 * Deux modes :
 * 1. Avec ?token= : verification automatique via JS (appel API)
 * 2. Sans token : message "consultez votre boite de reception"
 */

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.verify_email');

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? ($_SESSION['verify_email'] ?? '');
?>

<!-- ==========================================
     VERIFICATION EMAIL
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card reveal reveal-up">

                <?php if ($token): ?>
                <!-- ===== MODE VERIFICATION (avec token) ===== -->
                <div id="verify-email-container"
                     data-token="<?= e($token) ?>"
                     data-api-url="<?= SITE_URL ?>/api/auth/verify-email"
                     data-site-url="<?= SITE_URL ?>"
                     data-csrf-token="<?= csrf_token() ?>">

                    <div class="auth-header">
                        <h1 class="auth-header__title"><?= e(t('auth.verify_email')) ?></h1>
                    </div>

                    <!-- Statut de verification (mis a jour par JS) -->
                    <div id="verify-status" class="auth-alert auth-alert--info">
                        <?= e(t('common.loading')) ?>
                    </div>

                    <!-- Lien vers login (affiche apres succes par JS) -->
                    <div class="auth-links" id="verify-login-link" style="display: none;">
                        <a href="<?= SITE_URL ?>/login" class="btn btn--primary btn--lg auth-submit">
                            <?= e(t('auth.login_button')) ?>
                        </a>
                    </div>
                </div>

                <?php else: ?>
                <!-- ===== MODE ATTENTE (sans token) ===== -->
                <div id="verify-pending-container"
                     data-email="<?= e($email) ?>"
                     data-resend-url="<?= SITE_URL ?>/api/auth/verify-email"
                     data-csrf-token="<?= csrf_token() ?>">

                    <div class="auth-header">
                        <div class="auth-header__icon" aria-hidden="true">&#x2709;&#xFE0F;</div>
                        <h1 class="auth-header__title"><?= e(t('auth.verify_pending', ['email' => $email])) ?></h1>
                        <p class="auth-header__subtitle"><?= e(t('auth.verify_check_inbox')) ?></p>
                    </div>

                    <!-- Renvoyer l'email -->
                    <div class="form-group" style="text-align: center;">
                        <button type="button"
                                id="resend-email-btn"
                                class="btn btn--outline"
                                data-text="<?= e(t('auth.resend_email')) ?>">
                            <?= e(t('auth.resend_email')) ?>
                        </button>
                    </div>

                    <!-- Lien login -->
                    <div class="auth-links">
                        <a href="<?= SITE_URL ?>/login" class="auth-links__link">
                            <?= e(t('auth.login')) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
