<?php
/**
 * Saxho.net — Admin Sidebar (shared partial)
 * Inclus dans toutes les pages admin.
 * Variable attendue : $currentAdmin (string) — slug de la page active.
 *
 * Usage : <?php $currentAdmin = 'dashboard'; include __DIR__ . '/_sidebar.php'; ?>
 */
if (!isset($currentAdmin)) $currentAdmin = '';

// Badge messages non-lus
$_sidebarUnread = 0;
try {
    $_sidebarUnread = (int) getDB()->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0')->fetchColumn();
} catch (\Throwable $e) {}
?>
<aside class="admin-sidebar">
    <p class="admin-sidebar__title">Admin</p>
    <ul class="admin-sidebar__nav">
        <li>
            <a href="<?= SITE_URL ?>/admin" class="admin-sidebar__link<?= $currentAdmin === 'dashboard' ? ' admin-sidebar__link--active' : '' ?>">
                <span class="admin-sidebar__icon">&#x1F4CA;</span>
                Dashboard
            </a>
        </li>
        <li>
            <a href="<?= SITE_URL ?>/admin/blog" class="admin-sidebar__link<?= ($currentAdmin === 'blog' || $currentAdmin === 'blog-form') ? ' admin-sidebar__link--active' : '' ?>">
                <span class="admin-sidebar__icon">&#x1F4DD;</span>
                Articles
            </a>
        </li>
        <li>
            <a href="<?= SITE_URL ?>/admin/categories" class="admin-sidebar__link<?= $currentAdmin === 'categories' ? ' admin-sidebar__link--active' : '' ?>">
                <span class="admin-sidebar__icon">&#x1F3F7;</span>
                Categories
            </a>
        </li>
    </ul>
    <div class="admin-sidebar__sep"></div>
    <ul class="admin-sidebar__nav">
        <li>
            <a href="<?= SITE_URL ?>/admin/messages" class="admin-sidebar__link<?= $currentAdmin === 'messages' ? ' admin-sidebar__link--active' : '' ?>">
                <span class="admin-sidebar__icon">&#x2709;</span>
                Messages
                <?php if ($_sidebarUnread > 0): ?>
                <span class="admin-sidebar__badge"><?= $_sidebarUnread ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="<?= SITE_URL ?>/admin/users" class="admin-sidebar__link<?= $currentAdmin === 'users' ? ' admin-sidebar__link--active' : '' ?>">
                <span class="admin-sidebar__icon">&#x1F464;</span>
                Utilisateurs
            </a>
        </li>
        <li>
            <a href="<?= SITE_URL ?>/admin/projects" class="admin-sidebar__link<?= ($currentAdmin === 'projects' || $currentAdmin === 'project-form') ? ' admin-sidebar__link--active' : '' ?>">
                <span class="admin-sidebar__icon">&#x1F4C1;</span>
                Projets
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
