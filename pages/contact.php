<?php
/**
 * Saxho.net — Page Contact
 */
$pageCss = 'contact.css';
$pageJs = 'contact-form.js';
$pageDescription = t('contact.description');
?>

<!-- ==========================================
     HERO — Contact
     ========================================== -->
<section class="page-hero page-hero--contact">
    <div class="container">
        <h1 class="page-hero__title reveal reveal-up"><?= e(t('contact.title')) ?></h1>
        <p class="page-hero__subtitle reveal reveal-up reveal-delay-1"><?= e(t('contact.subtitle')) ?></p>
    </div>
</section>

<!-- ==========================================
     FORMULAIRE + SIDEBAR
     ========================================== -->
<section class="section contact-section">
    <div class="container">
        <div class="contact-grid">

            <!-- Colonne gauche : Formulaire -->
            <div class="contact-form-col">
                <div id="contact-form-wrapper">
                    <form id="contact-form"
                          action="<?= SITE_URL ?>/api/contact"
                          method="POST"
                          novalidate
                          data-sending="<?= e(t('contact.sending')) ?>">

                        <?= csrf_field() ?>

                        <!-- Honeypot -->
                        <div class="contact-honeypot" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                        </div>

                        <!-- Nom -->
                        <div class="form-group">
                            <label for="contact-name" class="form-label">
                                <?= e(t('contact.name')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="text"
                                   id="contact-name"
                                   name="name"
                                   class="form-input"
                                   required
                                   maxlength="200"
                                   data-error="<?= e(t('contact.error_name')) ?>">
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="contact-email" class="form-label">
                                <?= e(t('contact.email')) ?> <span class="form-required">*</span>
                            </label>
                            <input type="email"
                                   id="contact-email"
                                   name="email"
                                   class="form-input"
                                   required
                                   maxlength="255"
                                   data-error="<?= e(t('contact.error_email')) ?>">
                        </div>

                        <!-- Entreprise -->
                        <div class="form-group">
                            <label for="contact-company" class="form-label">
                                <?= e(t('contact.company')) ?> <span class="form-optional"><?= e(t('contact.company_optional')) ?></span>
                            </label>
                            <input type="text"
                                   id="contact-company"
                                   name="company"
                                   class="form-input"
                                   maxlength="255">
                        </div>

                        <!-- Sujet -->
                        <div class="form-group">
                            <label for="contact-subject" class="form-label">
                                <?= e(t('contact.subject')) ?> <span class="form-required">*</span>
                            </label>
                            <select id="contact-subject"
                                    name="subject"
                                    class="form-select"
                                    required
                                    data-error="<?= e(t('contact.error_subject')) ?>">
                                <option value=""><?= e(t('contact.select_subject')) ?></option>
                                <?php
                                $subjects = [
                                    'general', 'ideas', 'ideation', 'process',
                                    'taskforce', 'portfolio_contribute', 'portfolio_invest', 'other'
                                ];
                                foreach ($subjects as $subj):
                                ?>
                                <option value="<?= $subj ?>"><?= e(t('contact.subjects.' . $subj)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Message -->
                        <div class="form-group">
                            <label for="contact-message" class="form-label">
                                <?= e(t('contact.message')) ?> <span class="form-required">*</span>
                            </label>
                            <textarea id="contact-message"
                                      name="message"
                                      class="form-textarea"
                                      required
                                      rows="6"
                                      maxlength="5000"
                                      data-error="<?= e(t('contact.error_message')) ?>"
                                      data-error-short="<?= e(t('contact.error_message_short')) ?>"></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary btn--lg contact-form__submit">
                                <?= e(t('contact.send')) ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Message de succes (cache par defaut) -->
                <div id="contact-success" class="contact-success" style="display: none;">
                    <div class="contact-success__icon" aria-hidden="true">&#x2705;</div>
                    <p class="contact-success__text"><?= e(t('contact.success')) ?></p>
                </div>
            </div>

            <!-- Colonne droite : Sidebar -->
            <aside class="contact-sidebar">
                <div class="contact-info">
                    <h3 class="contact-info__title"><?= e(t('contact.info_title')) ?></h3>

                    <div class="contact-info__item">
                        <span class="contact-info__icon" aria-hidden="true">&#x1F4CD;</span>
                        <div>
                            <p class="contact-info__text"><?= e(t('contact.info_address')) ?></p>
                            <p class="contact-info__text"><?= e(t('contact.info_city')) ?></p>
                        </div>
                    </div>

                    <div class="contact-info__item">
                        <span class="contact-info__icon" aria-hidden="true">&#x2709;&#xFE0F;</span>
                        <div>
                            <a href="mailto:<?= e(t('contact.info_email')) ?>" class="contact-info__link">
                                <?= e(t('contact.info_email')) ?>
                            </a>
                        </div>
                    </div>

                    <div class="contact-info__item">
                        <span class="contact-info__icon" aria-hidden="true">&#x1F30D;</span>
                        <div>
                            <p class="contact-info__text"><?= e(t('contact.info_ecosystem')) ?></p>
                        </div>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</section>
