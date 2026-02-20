<?php
/**
 * Saxho.net — Page Article de blog
 * Route dynamique : /blog/{slug}
 * Parametre : $routeParams['article_slug']
 */
$pageCss = 'blog.css';
$pageJs  = 'blog.js';

// --- Fetch article ---
$articleSlug = $routeParams['article_slug'] ?? '';
if ($articleSlug === '') {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

$db = getDB();
$stmt = $db->prepare(
    'SELECT p.*, c.slug AS cat_slug, c.name_fr AS cat_name_fr, c.name_en AS cat_name_en,
            u.first_name AS author_first, u.last_name AS author_last
     FROM blog_posts p
     LEFT JOIN blog_categories c ON p.category_id = c.id
     LEFT JOIN users u ON p.author_id = u.id
     WHERE p.slug = ? AND p.status = "published"
     LIMIT 1'
);
$stmt->execute([$articleSlug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    return;
}

// --- Derived values ---
$title      = $lang === 'fr' ? $article['title_fr'] : ($article['title_en'] ?? $article['title_fr']);
$content    = $lang === 'fr' ? $article['content_fr'] : ($article['content_en'] ?? $article['content_fr']);
$catName    = $lang === 'fr' ? ($article['cat_name_fr'] ?? '') : ($article['cat_name_en'] ?? $article['cat_name_fr'] ?? '');
$authorName = trim(($article['author_first'] ?? '') . ' ' . ($article['author_last'] ?? ''));
$excerpt    = $lang === 'fr' ? ($article['excerpt_fr'] ?? '') : ($article['excerpt_en'] ?? $article['excerpt_fr'] ?? '');

// Override page meta
$pageTitle       = $title;
$pageDescription = $excerpt;

// --- Share URLs ---
$articleUrl   = SITE_URL . '/blog/' . $article['slug'];
$shareLinkedIn = 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($articleUrl);
$shareTwitter  = 'https://twitter.com/intent/tweet?url=' . urlencode($articleUrl) . '&text=' . urlencode($title);
$shareEmail    = 'mailto:?subject=' . rawurlencode($title) . '&body=' . rawurlencode($articleUrl);

// --- Fetch comments ---
$commentsStmt = $db->prepare(
    'SELECT bc.*, u.first_name, u.last_name
     FROM blog_comments bc
     JOIN users u ON bc.user_id = u.id
     WHERE bc.post_id = ?
     ORDER BY bc.created_at DESC'
);
$commentsStmt->execute([$article['id']]);
$comments = $commentsStmt->fetchAll();
$commentCount = count($comments);
?>

<!-- ARTICLE HERO -->
<section class="page-hero page-hero--blog-article">
    <div class="container">
        <a href="<?= SITE_URL ?>/blog" class="blog-article__back reveal reveal-up">
            &larr; <?= e(t('blog.back_to_blog')) ?>
        </a>

        <?php if ($catName): ?>
        <div class="reveal reveal-up reveal-delay-1">
            <span class="badge badge--primary blog-article__category"><?= e($catName) ?></span>
        </div>
        <?php endif; ?>

        <h1 class="page-hero__title blog-article__title reveal reveal-up reveal-delay-1">
            <?= e($title) ?>
        </h1>

        <div class="blog-article__meta reveal reveal-up reveal-delay-2">
            <span><?= format_date($article['published_at'], $lang) ?></span>
            <span class="blog-article__meta-sep">&bull;</span>
            <span><?= t('common.min_reading', ['minutes' => $article['reading_time'] ?? 1]) ?></span>
            <?php if ($authorName): ?>
            <span class="blog-article__meta-sep">&bull;</span>
            <span><?= e($authorName) ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ARTICLE CONTENT -->
<section class="section blog-article-section">
    <div class="container container--narrow">

        <!-- Article body (HTML from DB — admin trusted content) -->
        <div class="blog-article__content reveal reveal-up">
            <?= $content ?>
        </div>

        <!-- Share buttons -->
        <div class="blog-article__share reveal reveal-up">
            <span class="blog-article__share-label"><?= e(t('blog.share')) ?></span>
            <a href="<?= e($shareLinkedIn) ?>" target="_blank" rel="noopener"
               class="btn btn--sm btn--ghost blog-share-btn"
               aria-label="<?= e(t('blog.share_linkedin')) ?>">
                LinkedIn
            </a>
            <a href="<?= e($shareTwitter) ?>" target="_blank" rel="noopener"
               class="btn btn--sm btn--ghost blog-share-btn"
               aria-label="<?= e(t('blog.share_twitter')) ?>">
                X
            </a>
            <a href="<?= e($shareEmail) ?>"
               class="btn btn--sm btn--ghost blog-share-btn"
               aria-label="<?= e(t('blog.share_email')) ?>">
                Email
            </a>
        </div>

        <hr class="blog-article__divider">

        <!-- COMMENTS SECTION -->
        <section class="blog-comments" id="comments">
            <h2 class="blog-comments__title">
                <?= e(t('blog.comments_title')) ?>
                <span class="blog-comments__count">
                    (<?= t('blog.comments_count', ['count' => $commentCount]) ?>)
                </span>
            </h2>

            <?php if (is_logged_in()): ?>
            <!-- Comment form -->
            <form id="comment-form" class="blog-comments__form"
                  action="<?= SITE_URL ?>/api/blog-comment"
                  method="POST"
                  data-post-id="<?= (int)$article['id'] ?>"
                  data-submitting="<?= e(t('blog.comment_submitting')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="post_id" value="<?= (int)$article['id'] ?>">
                <div class="form-group">
                    <textarea name="content" id="comment-content"
                              class="form-textarea blog-comments__textarea"
                              rows="3"
                              placeholder="<?= e(t('blog.comment_placeholder')) ?>"
                              maxlength="2000"
                              required
                              data-error-empty="<?= e(t('blog.comment_error_empty')) ?>"
                              data-error-long="<?= e(t('blog.comment_error_long')) ?>"></textarea>
                </div>
                <button type="submit" class="btn btn--primary btn--sm">
                    <?= e(t('blog.comment_submit')) ?>
                </button>
            </form>
            <?php else: ?>
            <!-- Login prompt -->
            <div class="blog-comments__login-prompt">
                <p>
                    <?= e(t('blog.comments_login')) ?>
                    <a href="<?= SITE_URL ?>/login?redirect=<?= urlencode('/blog/' . $article['slug'] . '#comments') ?>">
                        <?= e(t('blog.comments_login_link')) ?>
                    </a>
                </p>
            </div>
            <?php endif; ?>

            <!-- Comments list -->
            <div class="blog-comments__list" id="comments-list">
                <?php if (empty($comments)): ?>
                <p class="blog-comments__empty text-muted">
                    <?= e(t('blog.comments_none')) ?>
                </p>
                <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                <div class="blog-comment">
                    <div class="blog-comment__header">
                        <strong class="blog-comment__author">
                            <?= e($comment['first_name'] . ' ' . $comment['last_name']) ?>
                        </strong>
                        <time class="blog-comment__date" datetime="<?= e($comment['created_at']) ?>">
                            <?= format_date($comment['created_at'], $lang) ?>
                        </time>
                    </div>
                    <p class="blog-comment__text"><?= nl2br(e($comment['content'])) ?></p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>
</section>
