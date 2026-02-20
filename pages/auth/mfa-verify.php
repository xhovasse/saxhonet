<?php
/**
 * Saxho.net — Page Verification MFA
 * L'utilisateur a saisi ses identifiants corrects mais doit confirmer via TOTP
 */

// Rediriger si pas en etat MFA-pending
require_mfa_pending();

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.mfa_verify');
?>

<!-- ==========================================
     VERIFICATION MFA
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card reveal reveal-up">

                <!-- Header -->
                <div class="auth-header">
                    <h1 class="auth-header__title"><?= e(t('auth.mfa_verify')) ?></h1>
                    <p class="auth-header__subtitle"><?= e(t('auth.mfa_enter_code')) ?></p>
                </div>

                <!-- Section TOTP -->
                <div id="totp-code-section">
                    <form id="mfa-verify-form"
                          class="auth-form"
                          action="<?= SITE_URL ?>/api/auth/mfa-verify"
                          method="POST"
                          novalidate
                          data-submitting="<?= e(t('common.loading')) ?>"
                          data-site-url="<?= SITE_URL ?>">

                        <?= csrf_field() ?>

                        <input type="hidden" name="type" value="totp">

                        <!-- Code TOTP -->
                        <div class="form-group">
                            <label for="mfa-totp-code" class="form-label">
                                <?= e(t('auth.mfa_code_label')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="text"
                                   id="mfa-totp-code"
                                   name="code"
                                   class="form-input mfa-code-input"
                                   required
                                   inputmode="numeric"
                                   pattern="[0-9]{6}"
                                   maxlength="6"
                                   autocomplete="one-time-code"
                                   placeholder="000000"
                                   data-error-required="<?= e(t('auth.error_mfa_code')) ?>">
                        </div>

                        <!-- Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary btn--lg auth-submit">
                                <?= e(t('auth.verify_button')) ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lien vers code de secours -->
                <div class="auth-links">
                    <a href="#" id="use-backup-code" class="auth-links__link">
                        <?= e(t('auth.mfa_use_backup')) ?>
                    </a>
                </div>

                <!-- Section code de secours (cachee par defaut) -->
                <div id="backup-code-section" style="display: none;">
                    <form class="auth-form"
                          action="<?= SITE_URL ?>/api/auth/mfa-verify"
                          method="POST"
                          novalidate
                          data-submitting="<?= e(t('common.loading')) ?>"
                          data-site-url="<?= SITE_URL ?>">

                        <?= csrf_field() ?>

                        <input type="hidden" name="type" value="backup">

                        <!-- Code de secours -->
                        <div class="form-group">
                            <label for="mfa-backup-code" class="form-label">
                                <?= e(t('auth.mfa_backup_label')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="text"
                                   id="mfa-backup-code"
                                   name="code"
                                   class="form-input"
                                   required
                                   autocomplete="off"
                                   data-error-required="<?= e(t('auth.error_mfa_code')) ?>">
                        </div>

                        <!-- Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary btn--lg auth-submit">
                                <?= e(t('auth.verify_button')) ?>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>
