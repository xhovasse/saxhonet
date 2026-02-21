<?php
/**
 * Saxho.net — Admin Dashboard
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';
$pageDescription = t('admin.dashboard');

// Fetch stats
$db = getDB();

$totalArticles = (int)$db->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn();
$publishedArticles = (int)$db->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn();
$draftArticles = $totalArticles - $publishedArticles;
$totalCategories = (int)$db->query('SELECT COUNT(*) FROM blog_categories')->fetchColumn();
$totalComments = (int)$db->query('SELECT COUNT(*) FROM blog_comments')->fetchColumn();
$totalUsers = (int)$db->query('SELECT COUNT(*) FROM users WHERE is_active = 1')->fetchColumn();
$unreadMessages = (int)$db->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
$totalProjects = (int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$visibleProjects = (int)$db->query('SELECT COUNT(*) FROM projects WHERE is_visible = 1')->fetchColumn();

// Recent articles
$recentArticles = $db->query(
    'SELECT p.id, p.title_fr, p.slug, p.status, p.published_at, p.created_at,
            c.name_fr AS category_name
     FROM blog_posts p
     LEFT JOIN blog_categories c ON p.category_id = c.id
     ORDER BY p.created_at DESC
     LIMIT 5'
)->fetchAll();
?>

<!-- Admin Layout -->
<section class="admin-layout" data-site-url="<?= SITE_URL ?>">

    <?php $currentAdmin = 'dashboard'; include __DIR__ . '/_sidebar.php'; ?>

    <!-- Content -->
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-header__title"><?= e(t('admin.dashboard')) ?></h1>
        </div>

        <!-- Stats -->
        <div class="admin-stats">
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $totalArticles ?></div>
                <div class="admin-stat-card__label"><?= e(t('admin.stats_articles')) ?></div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $publishedArticles ?></div>
                <div class="admin-stat-card__label"><?= e(t('admin.stats_published')) ?></div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $draftArticles ?></div>
                <div class="admin-stat-card__label"><?= e(t('admin.stats_drafts')) ?></div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $totalCategories ?></div>
                <div class="admin-stat-card__label"><?= e(t('admin.stats_categories')) ?></div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $totalComments ?></div>
                <div class="admin-stat-card__label"><?= e(t('admin.stats_comments')) ?></div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $totalUsers ?></div>
                <div class="admin-stat-card__label"><?= e(t('admin.stats_users')) ?></div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $unreadMessages ?></div>
                <div class="admin-stat-card__label"><?= e(t('admin.stats_messages')) ?></div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $totalProjects ?></div>
                <div class="admin-stat-card__label">Projets</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-card__value"><?= $visibleProjects ?></div>
                <div class="admin-stat-card__label">Projets visibles</div>
            </div>
        </div>

        <!-- Recent Articles -->
        <div class="admin-header" style="margin-bottom: 16px;">
            <h2 class="admin-header__title" style="font-size: 20px;"><?= e(t('admin.recent_articles')) ?></h2>
            <div class="admin-header__actions">
                <a href="<?= SITE_URL ?>/admin/blog/create" class="btn btn--primary btn--sm">+ <?= e(t('admin.new_article')) ?></a>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <?php if (empty($recentArticles)): ?>
                <p class="admin-table__empty"><?= e(t('admin.no_articles')) ?></p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><?= e(t('admin.title_fr')) ?></th>
                        <th><?= e(t('admin.category')) ?></th>
                        <th><?= e(t('admin.status')) ?></th>
                        <th><?= e(t('admin.published_at')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentArticles as $article): ?>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>
</section>
