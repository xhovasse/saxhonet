<?php
/**
 * Saxho.net — Page Blog (listing)
 */
$pageCss = 'blog.css';
$pageDescription = t('blog.description');

// --- Data ---
$db = getDB();

// Fetch categories
$categories = $db->query('SELECT * FROM blog_categories ORDER BY id')->fetchAll();

// Active category filter
$activeCat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$activeCatId = null;
if ($activeCat !== '') {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $activeCat) {
            $activeCatId = (int)$cat['id'];
            break;
        }
    }
}

// Pagination
$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));

$countSql = 'SELECT COUNT(*) FROM blog_posts WHERE status = "published"';
$countParams = [];
if ($activeCatId !== null) {
    $countSql .= ' AND category_id = ?';
    $countParams[] = $activeCatId;
}
$countStmt = $db->prepare($countSql);
$countStmt->execute($countParams);
$totalArticles = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalArticles / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Fetch articles
$sql = 'SELECT p.*, c.slug AS cat_slug, c.name_fr AS cat_name_fr, c.name_en AS cat_name_en,
               u.first_name AS author_first, u.last_name AS author_last
        FROM blog_posts p
        LEFT JOIN blog_categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.author_id = u.id
        WHERE p.status = "published"';
$params = [];
if ($activeCatId !== null) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $activeCatId;
}
$sql .= ' ORDER BY p.published_at DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
?>

<!-- HERO -->
<section class="page-hero page-hero--blog">
    <div class="container">
        <h1 class="page-hero__title reveal reveal-up"><?= e(t('blog.title')) ?></h1>
        <p class="page-hero__subtitle reveal reveal-up reveal-delay-1"><?= e(t('blog.subtitle')) ?></p>
    </div>
</section>

<!-- BLOG LISTING -->
<section class="section blog-section">
    <div class="container">

        <!-- Category filters -->
        <div class="blog-filters reveal reveal-up">
            <a href="<?= SITE_URL ?>/blog"
               class="badge blog-filter<?= $activeCat === '' ? ' blog-filter--active' : '' ?>">
                <?= e(t('blog.filter_all')) ?>
            </a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= SITE_URL ?>/blog?cat=<?= e($cat['slug']) ?>"
               class="badge blog-filter<?= $activeCat === $cat['slug'] ? ' blog-filter--active' : '' ?>">
                <?= e($lang === 'fr' ? $cat['name_fr'] : ($cat['name_en'] ?? $cat['name_fr'])) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Article grid -->
        <?php if (empty($articles)): ?>
            <p class="blog-empty text-muted text-center">
                <?= e($activeCat !== '' ? t('blog.no_articles_category') : t('blog.no_articles')) ?>
            </p>
        <?php else: ?>
        <div class="blog-grid">
            <?php foreach ($articles as $i => $article):
                $artTitle = $lang === 'fr' ? $article['title_fr'] : ($article['title_en'] ?? $article['title_fr']);
                $artExcerpt = $lang === 'fr' ? $article['excerpt_fr'] : ($article['excerpt_en'] ?? $article['excerpt_fr']);
                $catName = $lang === 'fr' ? $article['cat_name_fr'] : ($article['cat_name_en'] ?? $article['cat_name_fr']);
            ?>
            <article class="blog-card card reveal reveal-up reveal-delay-<?= min($i + 1, 3) ?>">
                <?php if (!empty($article['cover_image'])): ?>
                <img src="<?= e(SITE_URL . '/assets/img/uploads/' . $article['cover_image']) ?>"
                     alt="" class="card__image blog-card__image" loading="lazy">
                <?php else: ?>
                <div class="blog-card__placeholder"></div>
                <?php endif; ?>

                <div class="card__body">
                    <?php if ($article['cat_slug']): ?>
                    <a href="<?= SITE_URL ?>/blog?cat=<?= e($article['cat_slug']) ?>"
                       class="badge badge--primary blog-card__category">
                        <?= e($catName) ?>
                    </a>
                    <?php endif; ?>

                    <h2 class="blog-card__title">
                        <a href="<?= SITE_URL ?>/blog/<?= e($article['slug']) ?>">
                            <?= e($artTitle) ?>
                        </a>
                    </h2>

                    <?php if ($artExcerpt): ?>
                    <p class="blog-card__excerpt"><?= e($artExcerpt) ?></p>
                    <?php endif; ?>
                </div>

                <div class="card__footer blog-card__footer">
                    <span class="blog-card__date">
                        <?= format_date($article['published_at'], $lang) ?>
                    </span>
                    <span class="blog-card__reading-time">
                        <?= t('common.min_reading', ['minutes' => $article['reading_time'] ?? 1]) ?>
                    </span>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="blog-pagination" aria-label="Pagination">
            <?php if ($page > 1): ?>
            <a href="<?= SITE_URL ?>/blog?<?= $activeCat ? 'cat=' . e($activeCat) . '&' : '' ?>page=<?= $page - 1 ?>"
               class="btn btn--ghost btn--sm">&larr; <?= e(t('blog.prev_page')) ?></a>
            <?php endif; ?>

            <span class="blog-pagination__info">
                <?= t('blog.page_label', ['current' => $page, 'total' => $totalPages]) ?>
            </span>

            <?php if ($page < $totalPages): ?>
            <a href="<?= SITE_URL ?>/blog?<?= $activeCat ? 'cat=' . e($activeCat) . '&' : '' ?>page=<?= $page + 1 ?>"
               class="btn btn--ghost btn--sm"><?= e(t('blog.next_page')) ?> &rarr;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

    </div>
</section>
