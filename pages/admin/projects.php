<?php
/**
 * Saxho.net — Admin Projects (Portfolio)
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';
$pageDescription = 'Projets';

// Fetch all projects
$db = getDB();
$projects = $db->query(
    'SELECT id, name_fr, slug, domain, phase, status, is_visible, display_order, created_at
     FROM projects
     ORDER BY display_order ASC, created_at DESC'
)->fetchAll();
?>

<!-- Admin Layout -->
<section class="admin-layout" data-site-url="<?= SITE_URL ?>">

    <?php $currentAdmin = 'projects'; include __DIR__ . '/_sidebar.php'; ?>

    <!-- Content -->
    <div class="admin-content">
        <?= csrf_field() ?>

        <div class="admin-header">
            <h1 class="admin-header__title">Projets</h1>
            <div class="admin-header__actions">
                <a href="<?= SITE_URL ?>/admin/projects/create" class="btn btn--primary btn--sm">+ Nouveau projet</a>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <?php if (empty($projects)): ?>
                <p class="admin-table__empty">Aucun projet.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Domaine</th>
                        <th>Phase</th>
                        <th>Statut</th>
                        <th>Visible</th>
                        <th><?= e(t('admin.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td data-label="#"><?= $p['display_order'] ?></td>
                        <td data-label="Nom">
                            <a href="<?= SITE_URL ?>/admin/projects/edit?id=<?= $p['id'] ?>" class="admin-table__title-link">
                                <?= e($p['name_fr']) ?>
                            </a>
                        </td>
                        <td data-label="Domaine">
                            <span class="admin-badge admin-badge--category"><?= e($p['domain']) ?></span>
                        </td>
                        <td data-label="Phase">
                            <span class="admin-badge admin-badge--phase-<?= e($p['phase']) ?>">
                                <?= e(ucfirst(str_replace('_', ' ', $p['phase']))) ?>
                            </span>
                        </td>
                        <td data-label="Statut">
                            <span class="admin-badge admin-badge--<?= $p['status'] ?>">
                                <?= e(ucfirst($p['status'])) ?>
                            </span>
                        </td>
                        <td data-label="Visible">
                            <button type="button"
                                    class="btn btn--sm btn--ghost"
                                    data-project-visibility-id="<?= $p['id'] ?>"
                                    title="<?= $p['is_visible'] ? 'Masquer' : 'Rendre visible' ?>">
                                <?= $p['is_visible'] ? '&#x1F441;' : '&#x1F648;' ?>
                            </button>
                        </td>
                        <td data-label="<?= e(t('admin.actions')) ?>">
                            <div class="admin-actions">
                                <a href="<?= SITE_URL ?>/admin/projects/edit?id=<?= $p['id'] ?>" class="btn btn--sm btn--outline">
                                    <?= e(t('admin.edit')) ?>
                                </a>
                                <button type="button"
                                        class="btn btn--sm btn--ghost"
                                        style="color: #dc3545;"
                                        data-delete-id="<?= $p['id'] ?>"
                                        data-delete-url="/api/admin/project-delete"
                                        data-delete-message="Etes-vous sur de vouloir supprimer ce projet ? Cette action est irreversible.">
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
