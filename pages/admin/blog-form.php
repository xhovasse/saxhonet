<?php
/**
 * Saxho.net — Admin Blog Form (Create / Edit)
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';

// Detect mode: create or edit
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $editId > 0;
$post   = null;

if ($isEdit) {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM blog_posts WHERE id = ?');
    $stmt->execute([$editId]);
    $post = $stmt->fetch();
    if (!$post) {
        flash('error', t('admin.error_not_found'));
        redirect(SITE_URL . '/admin/blog');
    }
    $pageDescription = t('admin.blog_edit');
} else {
    $pageDescription = t('admin.blog_create');
}

// Fetch categories for select
$db = $db ?? getDB();
$categories = $db->query('SELECT id, name_fr FROM blog_categories ORDER BY name_fr')->fetchAll();
?>

<!-- Admin Layout -->
<section class="admin-layout" data-site-url="<?= SITE_URL ?>">

    <?php $currentAdmin = 'blog-form'; include __DIR__ . '/_sidebar.php'; ?>

    <!-- Content -->
    <div class="admin-content">
        <a href="<?= SITE_URL ?>/admin/blog" class="admin-back">&larr; <?= e(t('admin.back_to_list')) ?></a>

        <div class="admin-header">
            <h1 class="admin-header__title"><?= e(t($isEdit ? 'admin.blog_edit' : 'admin.blog_create')) ?></h1>
            <?php if ($isEdit && $post['status'] === 'published'): ?>
            <div class="admin-header__actions">
                <a href="<?= SITE_URL ?>/blog/<?= e($post['slug']) ?>" target="_blank" class="btn btn--sm btn--outline"><?= e(t('admin.preview')) ?> &#x2197;</a>
            </div>
            <?php endif; ?>
        </div>

        <form id="blog-form"
              class="admin-form"
              data-site-url="<?= SITE_URL ?>"
              data-error-title="<?= e(t('admin.error_title_required')) ?>"
              data-error-content="<?= e(t('admin.error_content_required')) ?>"
              novalidate>

            <?= csrf_field() ?>
            <?php if ($isEdit): ?>
            <input type="hidden" id="blog-id" value="<?= $post['id'] ?>">
            <?php endif; ?>
            <input type="hidden" id="blog-cover-image" name="cover_image" value="<?= e($post['cover_image'] ?? '') ?>">

            <!-- Section: Titres + Slug -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title"><?= e(t('admin.title_fr')) ?></h2>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="blog-title-fr" class="admin-form__label">
                            <?= e(t('admin.title_fr')) ?> <span class="admin-form__required">*</span>
                        </label>
                        <input type="text"
                               id="blog-title-fr"
                               class="admin-form__input"
                               value="<?= e($post['title_fr'] ?? '') ?>"
                               maxlength="255"
                               required>
                    </div>
                    <div class="admin-form__group">
                        <label for="blog-title-en" class="admin-form__label"><?= e(t('admin.title_en')) ?></label>
                        <input type="text"
                               id="blog-title-en"
                               class="admin-form__input"
                               value="<?= e($post['title_en'] ?? '') ?>"
                               maxlength="255">
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="blog-slug" class="admin-form__label"><?= e(t('admin.slug')) ?></label>
                        <input type="text"
                               id="blog-slug"
                               class="admin-form__input"
                               value="<?= e($post['slug'] ?? '') ?>"
                               maxlength="255">
                        <p class="admin-form__hint"><?= $lang === 'fr' ? 'Auto-g&eacute;n&eacute;r&eacute; depuis le titre si vide.' : 'Auto-generated from title if empty.' ?></p>
                    </div>
                    <div class="admin-form__group">
                        <label for="blog-category" class="admin-form__label"><?= e(t('admin.category')) ?></label>
                        <select id="blog-category" class="admin-form__select">
                            <option value=""><?= e(t('admin.no_category')) ?></option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"<?= ($post['category_id'] ?? '') == $cat['id'] ? ' selected' : '' ?>>
                                <?= e($cat['name_fr']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section: Contenu -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title"><?= e(t('admin.content_fr')) ?></h2>

                <div class="admin-form__group">
                    <label for="blog-content-fr" class="admin-form__label">
                        <?= e(t('admin.content_fr')) ?> <span class="admin-form__required">*</span>
                    </label>
                    <textarea id="blog-content-fr"
                              class="admin-form__textarea admin-form__textarea--code"
                              required><?= e($post['content_fr'] ?? '') ?></textarea>
                </div>

                <div class="admin-form__group">
                    <label for="blog-content-en" class="admin-form__label"><?= e(t('admin.content_en')) ?></label>
                    <textarea id="blog-content-en"
                              class="admin-form__textarea admin-form__textarea--code"><?= e($post['content_en'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Section: Extraits -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title"><?= e(t('admin.excerpt_fr')) ?></h2>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label for="blog-excerpt-fr" class="admin-form__label"><?= e(t('admin.excerpt_fr')) ?></label>
                        <textarea id="blog-excerpt-fr"
                                  class="admin-form__textarea admin-form__textarea--excerpt"><?= e($post['excerpt_fr'] ?? '') ?></textarea>
                        <p class="admin-form__hint"><?= $lang === 'fr' ? 'Auto-g&eacute;n&eacute;r&eacute; depuis le contenu si vide.' : 'Auto-generated from content if empty.' ?></p>
                    </div>
                    <div class="admin-form__group">
                        <label for="blog-excerpt-en" class="admin-form__label"><?= e(t('admin.excerpt_en')) ?></label>
                        <textarea id="blog-excerpt-en"
                                  class="admin-form__textarea admin-form__textarea--excerpt"><?= e($post['excerpt_en'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section: Image de couverture -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title"><?= e(t('admin.cover_image')) ?></h2>

                <div id="image-preview" style="<?= empty($post['cover_image']) ? 'display:none;' : '' ?>">
                    <?php if (!empty($post['cover_image'])): ?>
                    <div class="admin-form__image-preview">
                        <img src="<?= UPLOAD_URL ?>/<?= e($post['cover_image']) ?>" alt="Cover">
                        <button type="button" class="admin-form__image-remove" title="<?= e(t('admin.remove_image')) ?>">&times;</button>
                    </div>
                    <?php endif; ?>
                </div>

                <input type="file" id="cover-image-input" class="admin-form__file-input" accept="image/jpeg,image/png,image/webp">
                <button type="button"
                        id="upload-image-btn"
                        class="admin-form__upload-btn"
                        data-label="<?= e(t('admin.upload_image')) ?>">
                    &#x1F4F7; <?= e(t('admin.upload_image')) ?>
                </button>
            </div>

            <!-- Section: Statut & Publication -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title"><?= e(t('admin.status')) ?></h2>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label class="admin-form__label"><?= e(t('admin.status')) ?></label>
                        <div style="display: flex; gap: 20px; padding: 8px 0;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="radio" name="status" value="draft"<?= ($post['status'] ?? 'draft') === 'draft' ? ' checked' : '' ?>>
                                <?= e(t('admin.draft')) ?>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="radio" name="status" value="published"<?= ($post['status'] ?? '') === 'published' ? ' checked' : '' ?>>
                                <?= e(t('admin.published')) ?>
                            </label>
                        </div>
                    </div>
                    <div class="admin-form__group">
                        <label for="blog-published-at" class="admin-form__label"><?= e(t('admin.published_at')) ?></label>
                        <input type="datetime-local"
                               id="blog-published-at"
                               class="admin-form__input"
                               value="<?= !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>">
                        <p class="admin-form__hint"><?= $lang === 'fr' ? 'Rempli automatiquement si publi&eacute; sans date.' : 'Auto-filled if published without date.' ?></p>
                    </div>
                </div>
            </div>

            <!-- Footer: Buttons -->
            <div class="admin-form__footer">
                <button type="submit"
                        class="btn btn--primary"
                        data-text="<?= e(t('admin.save')) ?>"
                        data-loading="<?= e(t('common.loading')) ?>">
                    <?= e(t('admin.save')) ?>
                </button>
                <a href="<?= SITE_URL ?>/admin/blog" class="btn btn--outline"><?= e(t('common.cancel')) ?></a>
            </div>

        </form>
    </div>
</section>
