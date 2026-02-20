<?php
/**
 * Saxho.net — Page Configuration MFA
 * Activer/desactiver l'authentification a deux facteurs
 */

// Connexion requise
require_login();

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.mfa_setup');

$user = current_user();
?>

<!-- ==========================================
     CONFIGURATION MFA
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container" style="max-width: 560px;">

            <div id="mfa-setup"
                 data-api-url="<?= SITE_URL ?>/api/auth/mfa-setup"
                 data-csrf-token="<?= csrf_token() ?>">

                <div class="auth-card reveal reveal-up">

                    <?php if (empty($user['mfa_enabled']) || $user['mfa_enabled'] == 0): ?>
                    <!-- ========================================
                         MFA NON ACTIVE — Processus d'activation
                         ======================================== -->

                    <!-- Etape 1 : Generation -->
                    <div id="mfa-step-generate">
                        <div class="auth-header">
                            <h1 class="auth-header__title"><?= e(t('auth.mfa_title')) ?></h1>
                            <p class="auth-header__subtitle"><?= e(t('auth.mfa_scan_qr')) ?></p>
                        </div>

                        <div class="form-group">
                            <button type="button"
                                    id="mfa-generate"
                                    class="btn btn--primary btn--lg auth-submit">
                                <?= e(t('auth.mfa_enable')) ?>
                            </button>
                        </div>
                    </div>

                    <!-- Etape 2 : Scanner le QR et verifier (cache par defaut) -->
                    <div id="mfa-step-verify" style="display: none;">
                        <div class="auth-header">
                            <h1 class="auth-header__title"><?= e(t('auth.mfa_title')) ?></h1>
                            <p class="auth-header__subtitle"><?= e(t('auth.mfa_scan_qr')) ?></p>
                        </div>

                        <!-- QR Code -->
                        <div id="mfa-qr-target" class="mfa-qrcode"></div>

                        <!-- Secret en texte -->
                        <div id="mfa-secret-text" class="mfa-secret"></div>

                        <!-- Formulaire de verification -->
                        <form id="mfa-enable-form" class="auth-form" novalidate>
                            <?= csrf_field() ?>

                            <div class="form-group">
                                <label for="mfa-enable-code" class="form-label">
                                    <?= e(t('auth.mfa_code_label')) ?> <span class="form-required">*</span>
                                </label>
                                <input type="text"
                                       id="mfa-enable-code"
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

                            <div class="form-group">
                                <button type="submit" class="btn btn--primary btn--lg auth-submit">
                                    <?= e(t('auth.verify_button')) ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Etape 3 : Codes de secours (cache par defaut) -->
                    <div id="mfa-step-backup" style="display: none;">
                        <div class="auth-header">
                            <h1 class="auth-header__title"><?= e(t('auth.mfa_backup_title')) ?></h1>
                        </div>

                        <div class="backup-codes__warning">
                            <?= e(t('auth.mfa_backup_warning')) ?>
                        </div>

                        <div id="backup-codes-list" class="backup-codes"></div>

                        <!-- Confirmation -->
                        <div class="form-group">
                            <label class="form-checkbox">
                                <input type="checkbox" id="mfa-backup-confirm">
                                <span class="form-checkbox__label"><?= e(t('auth.mfa_backup_saved')) ?></span>
                            </label>
                        </div>

                        <div class="form-group">
                            <a href="<?= SITE_URL ?>/profile"
                               id="mfa-done-btn"
                               class="btn btn--primary btn--lg auth-submit"
                               style="display: inline-block; text-align: center; text-decoration: none; pointer-events: none; opacity: 0.5;">
                                <?= e(t('common.save')) ?>
                            </a>
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- ========================================
                         MFA ACTIVE — Option de desactivation
                         ======================================== -->

                    <div class="auth-header">
                        <h1 class="auth-header__title"><?= e(t('auth.mfa_title')) ?></h1>
                    </div>

                    <div class="profile-mfa-status">
                        <span class="profile-mfa-status__badge profile-mfa-status__badge--enabled">
                            <?= e(t('auth.mfa_status_enabled')) ?>
                        </span>
                    </div>

                    <form id="mfa-disable-form"
                          class="auth-form"
                          novalidate
                          data-submitting="<?= e(t('common.loading')) ?>"
                          data-site-url="<?= SITE_URL ?>">

                        <?= csrf_field() ?>

                        <p class="form-hint"><?= e(t('auth.mfa_confirm_disable')) ?></p>

                        <!-- Mot de passe pour confirmer -->
                        <div class="form-group">
                            <label for="mfa-disable-password" class="form-label">
                                <?= e(t('auth.password')) ?> <span class="form-required">*</span>
                            </label>
                            <div class="form-input-wrapper">
                                <input type="password"
                                       id="mfa-disable-password"
                                       name="password"
                                       class="form-input"
                                       required
                                       autocomplete="current-password"
                                       data-error-required="<?= e(t('auth.error_current_password')) ?>">
                                <button type="button"
                                        class="password-toggle"
                                        aria-label="<?= e(t('auth.show_password')) ?>"
                                        data-label-show="<?= e(t('auth.show_password')) ?>"
                                        data-label-hide="<?= e(t('auth.hide_password')) ?>">
                                    <span class="password-toggle__icon" aria-hidden="true">&#x1F441;</span>
                                </button>
                            </div>
                        </div>

                        <!-- Submit (style danger) -->
                        <div class="form-group">
                            <button type="submit" class="btn btn--ghost btn--lg auth-submit">
                                <?= e(t('auth.mfa_disable')) ?>
                            </button>
                        </div>
                    </form>

                    <?php endif; ?>

                </div>

                <!-- Lien retour profil -->
                <div class="auth-links">
                    <a href="<?= SITE_URL ?>/profile" class="auth-links__link">
                        <?= e(t('common.back')) ?> <?= e(t('auth.profile')) ?>
                    </a>
                </div>

            </div>

        </div>
    </div>
</section>
