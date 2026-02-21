<?php
/**
 * Saxho.net — Admin Users
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';
$pageDescription = 'Utilisateurs';

// Fetch all users
$db = getDB();
$users = $db->query(
    'SELECT id, email, first_name, last_name, role, is_active,
            email_verified, mfa_enabled, last_login, created_at
     FROM users
     ORDER BY created_at DESC'
)->fetchAll();

$currentUserId = current_user_id();
?>

<!-- Admin Layout -->
<section class="admin-layout" data-site-url="<?= SITE_URL ?>">

    <?php $currentAdmin = 'users'; include __DIR__ . '/_sidebar.php'; ?>

    <!-- Content -->
    <div class="admin-content">
        <?= csrf_field() ?>

        <div class="admin-header">
            <h1 class="admin-header__title">Utilisateurs</h1>
        </div>

        <div class="admin-table-wrapper">
            <?php if (empty($users)): ?>
                <p class="admin-table__empty">Aucun utilisateur.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actif</th>
                        <th>Email verifie</th>
                        <th>MFA</th>
                        <th>Derniere connexion</th>
                        <th><?= e(t('admin.actions')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td data-label="Nom">
                            <strong><?= e($u['first_name'] . ' ' . $u['last_name']) ?></strong>
                        </td>
                        <td data-label="Email">
                            <a href="mailto:<?= e($u['email']) ?>" class="admin-table__email-link"><?= e($u['email']) ?></a>
                        </td>
                        <td data-label="Role">
                            <span class="admin-badge admin-badge--<?= $u['role'] === 'admin' ? 'admin-role' : 'member-role' ?>">
                                <?= e($u['role']) ?>
                            </span>
                        </td>
                        <td data-label="Actif">
                            <?php if ($u['is_active']): ?>
                                <span class="admin-badge admin-badge--published">Actif</span>
                            <?php else: ?>
                                <span class="admin-badge admin-badge--draft">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Email verifie">
                            <?= $u['email_verified'] ? '&#x2705;' : '&#x274C;' ?>
                        </td>
                        <td data-label="MFA">
                            <?= $u['mfa_enabled'] ? '&#x1F512;' : '—' ?>
                        </td>
                        <td data-label="Derniere connexion">
                            <?= $u['last_login'] ? e(format_date($u['last_login'])) : '—' ?>
                        </td>
                        <td data-label="<?= e(t('admin.actions')) ?>">
                            <div class="admin-actions">
                                <?php if ((int)$u['id'] !== $currentUserId): ?>
                                <button type="button"
                                        class="btn btn--sm btn--ghost"
                                        data-user-toggle-id="<?= $u['id'] ?>"
                                        title="<?= $u['is_active'] ? 'Desactiver' : 'Activer' ?>">
                                    <?= $u['is_active'] ? 'Desactiver' : 'Activer' ?>
                                </button>
                                <button type="button"
                                        class="btn btn--sm btn--outline"
                                        data-user-role-id="<?= $u['id'] ?>"
                                        data-user-role-current="<?= e($u['role']) ?>"
                                        title="Changer le role">
                                    &rarr; <?= $u['role'] === 'admin' ? 'member' : 'admin' ?>
                                </button>
                                <?php else: ?>
                                <span style="color: #999; font-size: 12px;">Vous</span>
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
