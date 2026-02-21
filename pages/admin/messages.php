<?php
/**
 * Saxho.net — Admin Messages (Contact Form Submissions)
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';
$pageDescription = 'Messages';

// Fetch all messages
$db = getDB();
$messages = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();

// Count unread
$unreadCount = 0;
foreach ($messages as $m) {
    if (!$m['is_read']) $unreadCount++;
}
?>

<!-- Admin Layout -->
<section class="admin-layout" data-site-url="<?= SITE_URL ?>">

    <?php $currentAdmin = 'messages'; include __DIR__ . '/_sidebar.php'; ?>

    <!-- Content -->
    <div class="admin-content">
        <?= csrf_field() ?>

        <div class="admin-header">
            <h1 class="admin-header__title">
                Messages
                <?php if ($unreadCount > 0): ?>
                    <span class="admin-badge admin-badge--unread"><?= $unreadCount ?> non lu<?= $unreadCount > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </h1>
        </div>

        <div class="admin-table-wrapper">
            <?php if (empty($messages)): ?>
                <p class="admin-table__empty">Aucun message.</p>
            <?php else: ?>
            <table class="admin-table admin-table--messages">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Sujet</th>
                        <th>Extrait</th>
                        <th>Statut</th>
                        <th><?= e(t('admin.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                    <tr class="admin-table__row--clickable<?= !$msg['is_read'] ? ' admin-table__row--unread' : '' ?>"
                        data-toggle-detail="<?= $msg['id'] ?>">
                        <td data-label="Date">
                            <?= e(format_date($msg['created_at'])) ?>
                        </td>
                        <td data-label="Nom">
                            <?= e($msg['name']) ?>
                        </td>
                        <td data-label="Email">
                            <a href="mailto:<?= e($msg['email']) ?>" class="admin-table__email-link" onclick="event.stopPropagation();"><?= e($msg['email']) ?></a>
                        </td>
                        <td data-label="Sujet">
                            <?= e($msg['subject']) ?>
                        </td>
                        <td data-label="Extrait">
                            <?= e(mb_strimwidth($msg['message'], 0, 80, '...')) ?>
                        </td>
                        <td data-label="Statut">
                            <?php if ($msg['is_read']): ?>
                                <span class="admin-badge admin-badge--read">Lu</span>
                            <?php else: ?>
                                <span class="admin-badge admin-badge--unread">Non lu</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="<?= e(t('admin.actions')) ?>">
                            <div class="admin-actions">
                                <button type="button"
                                        class="btn btn--sm btn--ghost"
                                        data-message-read-id="<?= $msg['id'] ?>"
                                        onclick="event.stopPropagation();"
                                        title="<?= $msg['is_read'] ? 'Marquer non lu' : 'Marquer lu' ?>">
                                    <?= $msg['is_read'] ? 'Non lu' : 'Lu' ?>
                                </button>
                                <button type="button"
                                        class="btn btn--sm btn--ghost"
                                        style="color: #dc3545;"
                                        data-delete-id="<?= $msg['id'] ?>"
                                        data-delete-url="/api/admin/message-delete"
                                        data-delete-message="Etes-vous sur de vouloir supprimer ce message ? Cette action est irreversible."
                                        onclick="event.stopPropagation();">
                                    <?= e(t('admin.delete')) ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Detail row (hidden by default) -->
                    <tr class="admin-table__detail" data-detail-for="<?= $msg['id'] ?>" style="display: none;">
                        <td colspan="7">
                            <div class="admin-message-detail">
                                <div class="admin-message-detail__meta">
                                    <strong><?= e($msg['name']) ?></strong>
                                    &lt;<?= e($msg['email']) ?>&gt;
                                    <?php if (!empty($msg['company'])): ?>
                                        &mdash; <?= e($msg['company']) ?>
                                    <?php endif; ?>
                                    <span class="admin-message-detail__date"><?= e(format_date($msg['created_at'])) ?></span>
                                    <?php if (!empty($msg['ip_address'])): ?>
                                        <span class="admin-message-detail__ip">IP: <?= e($msg['ip_address']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="admin-message-detail__subject">
                                    <strong>Sujet :</strong> <?= e($msg['subject']) ?>
                                </div>
                                <div class="admin-message-detail__body">
                                    <?= nl2br(e($msg['message'])) ?>
                                </div>
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
