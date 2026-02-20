<?php
/**
 * Saxho.net — Page Profil utilisateur
 */

// Connexion requise
require_login();

$pageCss = 'auth.css';
$pageJs = 'auth.js';
$pageDescription = t('auth.profile');

$user = current_user();
?>

<!-- ==========================================
     PROFIL UTILISATEUR
     ========================================== -->
<section class="section auth-section">
    <div class="container">
        <div class="auth-container" style="max-width: 640px;">

            <!-- ==========================================
                 SECTION 1 — Informations personnelles
                 ========================================== -->
            <div class="profile-section reveal reveal-up">
                <h2 class="profile-section__title"><?= e(t('auth.personal_info')) ?></h2>

                <form id="profile-info-form"
                      class="auth-form"
                      action="<?= SITE_URL ?>/api/auth/profile"
                      method="POST"
                      novalidate
                      data-submitting="<?= e(t('common.loading')) ?>"
                      data-site-url="<?= SITE_URL ?>">

                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_info">

                    <!-- Prenom + Nom -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profile-first-name" class="form-label">
                                <?= e(t('auth.first_name')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="text"
                                   id="profile-first-name"
                                   name="first_name"
                                   class="form-input"
                                   required
                                   maxlength="100"
                                   value="<?= e($user['first_name'] ?? '') ?>"
                                   data-error-required="<?= e(t('auth.error_name_required')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="profile-last-name" class="form-label">
                                <?= e(t('auth.last_name')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="text"
                                   id="profile-last-name"
                                   name="last_name"
                                   class="form-input"
                                   required
                                   maxlength="100"
                                   value="<?= e($user['last_name'] ?? '') ?>"
                                   data-error-required="<?= e(t('auth.error_name_required')) ?>">
                        </div>
                    </div>

                    <!-- Email (lecture seule) -->
                    <div class="form-group">
                        <label for="profile-email" class="form-label">
                            <?= e(t('auth.email')) ?>
                        </label>
                        <input type="email"
                               id="profile-email"
                               name="email"
                               class="form-input"
                               value="<?= e($user['email'] ?? '') ?>"
                               disabled
                               readonly>
                    </div>

                    <!-- Entreprise + Fonction -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profile-company" class="form-label">
                                <?= e(t('auth.company')) ?>
                            </label>
                            <input type="text"
                                   id="profile-company"
                                   name="company"
                                   class="form-input"
                                   maxlength="255"
                                   value="<?= e($user['company'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="profile-job-title" class="form-label">
                                <?= e(t('auth.job_title')) ?>
                            </label>
                            <input type="text"
                                   id="profile-job-title"
                                   name="job_title"
                                   class="form-input"
                                   maxlength="255"
                                   value="<?= e($user['job_title'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Telephone -->
                    <div class="form-group">
                        <label for="profile-phone" class="form-label">
                            <?= e(t('auth.phone')) ?>
                        </label>
                        <input type="tel"
                               id="profile-phone"
                               name="phone"
                               class="form-input"
                               maxlength="20"
                               value="<?= e($user['phone'] ?? '') ?>">
                    </div>

                    <!-- Adresse -->
                    <div class="form-group">
                        <label for="profile-address" class="form-label">
                            <?= e(t('auth.address')) ?>
                        </label>
                        <textarea id="profile-address"
                                  name="address"
                                  class="form-input"
                                  rows="3"
                                  maxlength="1000"><?= e($user['address'] ?? '') ?></textarea>
                    </div>

                    <!-- Pays -->
                    <div class="form-group">
                        <label for="profile-country" class="form-label">
                            <?= e(t('auth.country')) ?>
                        </label>
                        <input type="text"
                               id="profile-country"
                               name="country"
                               class="form-input"
                               maxlength="100"
                               value="<?= e($user['country'] ?? '') ?>">
                    </div>

                    <!-- Submit -->
                    <div class="form-group">
                        <button type="submit" class="btn btn--primary btn--lg auth-submit">
                            <?= e(t('auth.save_changes')) ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ==========================================
                 SECTION 2 — Changement de mot de passe
                 ========================================== -->
            <div class="profile-section reveal reveal-up reveal-delay-1">
                <h2 class="profile-section__title"><?= e(t('auth.change_password')) ?></h2>

                <form id="profile-password-form"
                      class="auth-form"
                      action="<?= SITE_URL ?>/api/auth/profile"
                      method="POST"
                      novalidate
                      data-submitting="<?= e(t('common.loading')) ?>"
                      data-site-url="<?= SITE_URL ?>">

                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <!-- Mot de passe actuel -->
                    <div class="form-group">
                        <label for="profile-current-password" class="form-label">
                            <?= e(t('auth.current_password')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="profile-current-password"
                                   name="current_password"
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

                    <!-- Nouveau mot de passe -->
                    <div class="form-group">
                        <label for="profile-new-password" class="form-label">
                            <?= e(t('auth.new_password')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="profile-new-password"
                                   name="new_password"
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
                        <label for="profile-password-confirm" class="form-label">
                            <?= e(t('auth.password_confirm')) ?> <span class="form-required">*</span>
                        </label>
                        <div class="form-input-wrapper">
                            <input type="password"
                                   id="profile-password-confirm"
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
                            <?= e(t('auth.change_password')) ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ==========================================
                 SECTION 3 — Authentification a deux facteurs
                 ========================================== -->
            <div class="profile-section reveal reveal-up reveal-delay-2">
                <h2 class="profile-section__title"><?= e(t('auth.mfa_title')) ?></h2>

                <div class="profile-mfa-status">
                    <?php if (!empty($user['mfa_enabled']) && $user['mfa_enabled'] == 1): ?>
                        <span class="profile-mfa-status__badge profile-mfa-status__badge--enabled">
                            <?= e(t('auth.mfa_status_enabled')) ?>
                        </span>
                    <?php else: ?>
                        <span class="profile-mfa-status__badge profile-mfa-status__badge--disabled">
                            <?= e(t('auth.mfa_status_disabled')) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <a href="<?= SITE_URL ?>/mfa-setup" class="btn btn--ghost btn--lg auth-submit" style="display: inline-block; text-align: center; text-decoration: none;">
                        <?php if (!empty($user['mfa_enabled']) && $user['mfa_enabled'] == 1): ?>
                            <?= e(t('common.edit')) ?>
                        <?php else: ?>
                            <?= e(t('auth.mfa_enable')) ?>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>
