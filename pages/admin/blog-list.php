<?php
/**
 * Saxho.net — Admin Blog List
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';
$pageDescription = t('admin.blog_list');

// Fetch all articles
$db = getDB();
$articles = $db->query(
    'SELECT p.id, p.title_fr, p.slug, p.status, p.published_at, p.created_at,
            c.name_fr AS category_name
     FROM blog_posts p
     LEFT JOIN blog_categories c ON p.category_id = c.id
     ORDER BY p.created_at DESC'
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
                <a href="<?= SITE_URL ?>/admin/blog" class="admin-sidebar__link admin-sidebar__link--active">
                    <span class="admin-sidebar__icon">&#x1F4DD;</span>
                    <?= e(t('admin.blog_list')) ?>
                </a>
            </li>
            <li>
                <a href="<?= SITE_URL ?>/admin/categories" class="admin-sidebar__link">
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
            <h1 class="admin-header__title"><?= e(t('admin.blog_list')) ?></h1>
            <div class="admin-header__actions">
                <a href="<?= SITE_URL ?>/admin/blog/create" class="btn btn--primary btn--sm">+ <?= e(t('admin.new_article')) ?></a>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <?php if (empty($articles)): ?>
                <p class="admin-table__empty"><?= e(t('admin.no_articles')) ?></p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= e(t('admin.title_fr')) ?></th>
                        <th><?= e(t('admin.category')) ?></th>
                        <th><?= e(t('admin.status')) ?></th>
                        <th><?= e(t('admin.published_at')) ?></th>
                        <th><?= e(t('admin.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td data-label="<?= e(t('admin.title_fr')) ?>">
                            <a href="<?= SITE_URL ?>/admin/blog/edit?id=<?= $article['id'] ?>" class="admin-table__title-link">
                                <?= e($article['title_fr']) ?>
                            </a>
                        </td>
                        <td data-label="<?= e(t('admin.category')) ?>">
                            <?php if ($article['category_name']): ?>
                                <span class="admin-badge admin-badge--category"><?= e($article['category_name']) ?></span>
                            <?php else: ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="<?= e(t('admin.status')) ?>">
                            <span class="admin-badge admin-badge--<?= $article['status'] ?>">
                                <?= e(t('admin.' . $article['status'])) ?>
                            </span>
                        </td>
                        <td data-label="<?= e(t('admin.published_at')) ?>">
                            <?= $article['published_at'] ? e(format_date($article['published_at'])) : '—' ?>
                        </td>
                        <td data-label="<?= e(t('admin.actions')) ?>">
                            <div class="admin-actions">
                                <a href="<?= SITE_URL ?>/admin/blog/edit?id=<?= $article['id'] ?>" class="btn btn--sm btn--outline"><?= e(t('admin.edit')) ?></a>
                                <?php if ($article['status'] === 'published'): ?>
                                    <a href="<?= SITE_URL ?>/blog/<?= e($article['slug']) ?>" target="_blank" class="btn btn--sm btn--ghost"><?= e(t('admin.view')) ?></a>
                                    <button type="button"
                                            class="btn btn--sm btn--ghost"
                                            data-publish-id="<?= $article['id'] ?>"
                                            data-publish-status="draft"
                                            title="<?= e(t('admin.unpublish')) ?>">
                                        <?= e(t('admin.unpublish')) ?>
                                    </button>
                                <?php else: ?>
                                    <button type="button"
                                            class="btn btn--sm btn--ghost"
                                            data-publish-id="<?= $article['id'] ?>"
                                            data-publish-status="published"
                                            title="<?= e(t('admin.publish')) ?>">
                                        <?= e(t('admin.publish')) ?>
                                    </button>
                                <?php endif; ?>
                                <button type="button"
                                        class="btn btn--sm btn--ghost"
                                        style="color: #dc3545;"
                                        data-delete-id="<?= $article['id'] ?>"
                                        data-delete-url="/api/admin/blog-delete"
                                        data-delete-message="<?= e(t('admin.confirm_delete_article')) ?>">
                                    <?= e(t('admin.delete')) ?>
                                </button>
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
