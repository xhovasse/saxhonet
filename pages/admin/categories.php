<?php
/**
 * Saxho.net — Admin Categories
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';
$pageDescription = t('admin.categories');

// Fetch categories with article count
$db = getDB();
$categories = $db->query(
    'SELECT c.id, c.name_fr, c.name_en, c.slug,
            (SELECT COUNT(*) FROM blog_posts WHERE category_id = c.id) AS post_count
     FROM blog_categories c
     ORDER BY c.name_fr'
)->fetchAll();
?>

<!-- Admin Layout -->
<section class="admin-layout" data-site-url="<?= SITE_URL ?>">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <p class="admin-sidebar__title">Admin</p>
        <ul class="admin-sidebar__nav">
            <li>
                <a href="<?= SITE_URL ?>/admin" class="admin-sidebar__link">
                    <span class="admin-sidebar__icon">&#x1F4CA;</span>
                    <?= e(t('admin.dashboard')) ?>
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/blog" class="admin-sidebar__link">
                    <span class="admin-sidebar__icon">&#x1F4DD;</span>
                    <?= e(t('admin.blog_list')) ?>
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/categories" class="admin-sidebar__link admin-sidebar__link--active">
                    <span class="admin-sidebar__icon">&#x1F3F7;</span>
                    <?= e(t('admin.categories')) ?>
                </a>
            </li>
        </ul>
        <div class="admin-sidebar__sep"></div>
        <ul class="admin-sidebar__nav">
            <li>
                <a href="<?= SITE_URL ?>/" class="admin-sidebar__link">
                    <span class="admin-sidebar__icon">&#x1F310;</span>
                    Voir le site
                </a>
            </li>
        </ul>
    </aside>

    <!-- Content -->
    <div class="admin-content">
        <?= csrf_field() ?>

        <div class="admin-header">
            <h1 class="admin-header__title"><?= e(t('admin.categories')) ?></h1>
        </div>

        <!-- Inline Category Form -->
        <form id="category-form"
              class="admin-category-form"
              data-site-url="<?= SITE_URL ?>"
              data-error-name="<?= e(t('admin.error_category_name_required')) ?>"
              novalidate>

            <input type="hidden" id="category-id" value="">

            <h3 id="category-form-title"
                class="admin-category-form__title"
                data-add-label="<?= e(t('admin.add_category')) ?>"
                data-edit-label="<?= e(t('admin.edit_category')) ?>">
                <?= e(t('admin.add_category')) ?>
            </h3>

            <div class="admin-category-form__row">
                <div class="admin-form__group">
                    <label for="category-name-fr" class="admin-form__label">
                        <?= e(t('admin.name_fr')) ?> <span class="admin-form__required">*</span>
                    </label>
                    <input type="text"
                           id="category-name-fr"
                           class="admin-form__input"
                           maxlength="100"
                           required>
                </div>
                <div class="admin-form__group">
                    <label for="category-name-en" class="admin-form__label"><?= e(t('admin.name_en')) ?></label>
                    <input type="text"
                           id="category-name-en"
                           class="admin-form__input"
                           maxlength="100">
                </div>
                <div class="admin-category-form__actions">
                    <button type="submit" class="btn btn--primary btn--sm"><?= e(t('admin.save')) ?></button>
                    <button type="button"
                            id="category-cancel"
                            class="btn btn--outline btn--sm"
                            style="display: none;">
                        <?= e(t('admin.cancel_edit')) ?>
                    </button>
                </div>
            </div>
        </form>

        <!-- Categories Table -->
        <div class="admin-table-wrapper">
            <?php if (empty($categories)): ?>
                <p class="admin-table__empty"><?= $lang === 'fr' ? 'Aucune cat&eacute;gorie.' : 'No categories.' ?></p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= e(t('admin.name_fr')) ?></th>
                        <th><?= e(t('admin.name_en')) ?></th>
                        <th><?= e(t('admin.slug')) ?></th>
                        <th><?= e(t('admin.nb_articles')) ?></th>
                        <th><?= e(t('admin.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td data-label="<?= e(t('admin.name_fr')) ?>">
                            <strong><?= e($cat['name_fr']) ?></strong>
                        </td>
                        <td data-label="<?= e(t('admin.name_en')) ?>">
                            <?= $cat['name_en'] ? e($cat['name_en']) : '<span style="color:#999;">—</span>' ?>
                        </td>
                        <td data-label="<?= e(t('admin.slug')) ?>">
                            <code style="font-size: 12px; color: #666;"><?= e($cat['slug']) ?></code>
                        </td>
                        <td data-label="<?= e(t('admin.nb_articles')) ?>">
                            <?= $cat['post_count'] ?>
                        </td>
                        <td data-label="<?= e(t('admin.actions')) ?>">
                            <div class="admin-actions">
                                <button type="button"
                                        class="btn btn--sm btn--outline"
                                        data-edit-category="<?= $cat['id'] ?>"
                                        data-name-fr="<?= e($cat['name_fr']) ?>"
                                        data-name-en="<?= e($cat['name_en'] ?? '') ?>">
                                    <?= e(t('admin.edit')) ?>
                                </button>
                                <?php if ((int)$cat['post_count'] === 0): ?>
                                <button type="button"
                                        class="btn btn--sm btn--ghost"
                                        style="color: #dc3545;"
                                        data-delete-id="<?= $cat['id'] ?>"
                                        data-delete-url="/api/admin/category-delete"
                                        data-delete-message="<?= e(t('admin.confirm_delete_category')) ?>">
                                    <?= e(t('admin.delete')) ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- Delete Confirmation Modal -->
<div id="admin-delete-modal" class="admin-modal">
    <div class="admin-modal__overlay"></div>
    <div class="admin-modal__box">
        <h3 class="admin-modal__title"><?= e(t('admin.confirm_delete')) ?></h3>
        <p class="admin-modal__text"></p>
        <div class="admin-modal__actions">
            <button type="button" class="btn btn--sm btn--outline" data-modal-cancel><?= e(t('common.cancel')) ?></button>
            <button type="button" class="btn btn--sm btn--primary" style="background-color: #dc3545;" data-modal-confirm><?= e(t('admin.delete')) ?></button>
        </div>
    </div>
</div>
