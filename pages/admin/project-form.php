<?php
/**
 * Saxho.net — Admin Project Form (Create / Edit)
 */
require_admin();

$pageCss = 'admin.css';
$pageJs  = 'admin.js';
$pageDescription = 'Projet';

$db = getDB();

// Determine if editing
$isEdit = false;
$project = null;

if (!empty($_GET['id'])) {
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $project = $stmt->fetch();
    if ($project) {
        $isEdit = true;
    }
}

$phases = ['ideation', 'study', 'prototype', 'development', 'pre_launch', 'transferred'];
$statuses = ['open', 'complete', 'paused'];
?>

<!-- Admin Layout -->
<section class="admin-layout" data-site-url="<?= SITE_URL ?>">

    <?php $currentAdmin = 'project-form'; include __DIR__ . '/_sidebar.php'; ?>

    <!-- Content -->
    <div class="admin-content">
        <a href="<?= SITE_URL ?>/admin/projects" class="admin-back">&larr; Retour a la liste</a>

        <div class="admin-header">
            <h1 class="admin-header__title"><?= $isEdit ? 'Modifier le projet' : 'Nouveau projet' ?></h1>
        </div>

        <form id="project-form" class="admin-form"
              data-error-name="Le nom du projet est requis."
              data-error-pitch="Le pitch est requis.">

            <?php if ($isEdit): ?>
                <input type="hidden" id="project-id" value="<?= $project['id'] ?>">
            <?php endif; ?>

            <!-- Identite -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title">Identite</h2>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-name-fr">
                            Nom (FR) <span class="admin-form__required">*</span>
                        </label>
                        <input type="text" id="project-name-fr" class="admin-form__input"
                               value="<?= $isEdit ? e($project['name_fr']) : '' ?>" required>
                    </div>
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-name-en">Nom (EN)</label>
                        <input type="text" id="project-name-en" class="admin-form__input"
                               value="<?= $isEdit ? e($project['name_en'] ?? '') : '' ?>">
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-slug">Slug</label>
                        <input type="text" id="project-slug" class="admin-form__input"
                               value="<?= $isEdit ? e($project['slug']) : '' ?>"
                               placeholder="auto-genere depuis le nom">
                        <span class="admin-form__hint">Laissez vide pour generer automatiquement.</span>
                    </div>
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-domain">
                            Domaine <span class="admin-form__required">*</span>
                        </label>
                        <input type="text" id="project-domain" class="admin-form__input"
                               value="<?= $isEdit ? e($project['domain']) : '' ?>"
                               placeholder="Ex: SaaS, EdTech, FinTech..." required>
                    </div>
                </div>

                <!-- Image -->
                <div class="admin-form__group">
                    <label class="admin-form__label">Image du projet</label>
                    <input type="hidden" id="project-image" value="<?= $isEdit ? e($project['image'] ?? '') : '' ?>">
                    <div id="project-image-preview" style="<?= ($isEdit && !empty($project['image'])) ? '' : 'display:none;' ?>">
                        <?php if ($isEdit && !empty($project['image'])): ?>
                        <div class="admin-form__image-preview">
                            <img src="<?= SITE_URL ?>/assets/img/uploads/<?= e($project['image']) ?>" alt="Cover">
                            <button type="button" class="admin-form__image-remove" title="Supprimer">&times;</button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" id="upload-project-image-btn" class="admin-form__upload-btn" data-label="Choisir une image">
                        Choisir une image
                    </button>
                    <input type="file" id="project-image-input" class="admin-form__file-input" accept="image/*">
                </div>
            </div>

            <!-- Contenu bilingue -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title">Pitch</h2>

                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-pitch-fr">
                        Pitch (FR) <span class="admin-form__required">*</span>
                    </label>
                    <textarea id="project-pitch-fr" class="admin-form__textarea" rows="4"
                              required><?= $isEdit ? e($project['pitch_fr']) : '' ?></textarea>
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-pitch-en">Pitch (EN)</label>
                    <textarea id="project-pitch-en" class="admin-form__textarea" rows="4"><?= $isEdit ? e($project['pitch_en'] ?? '') : '' ?></textarea>
                </div>
            </div>

            <div class="admin-form__section">
                <h2 class="admin-form__section-title">Problematique</h2>

                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-problem-fr">
                        Probleme (FR) <span class="admin-form__required">*</span>
                    </label>
                    <textarea id="project-problem-fr" class="admin-form__textarea" rows="4"
                              required><?= $isEdit ? e($project['problem_fr']) : '' ?></textarea>
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-problem-en">Probleme (EN)</label>
                    <textarea id="project-problem-en" class="admin-form__textarea" rows="4"><?= $isEdit ? e($project['problem_en'] ?? '') : '' ?></textarea>
                </div>
            </div>

            <div class="admin-form__section">
                <h2 class="admin-form__section-title">Solution</h2>

                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-solution-fr">
                        Solution (FR) <span class="admin-form__required">*</span>
                    </label>
                    <textarea id="project-solution-fr" class="admin-form__textarea" rows="4"
                              required><?= $isEdit ? e($project['solution_fr']) : '' ?></textarea>
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-solution-en">Solution (EN)</label>
                    <textarea id="project-solution-en" class="admin-form__textarea" rows="4"><?= $isEdit ? e($project['solution_en'] ?? '') : '' ?></textarea>
                </div>
            </div>

            <!-- Parametres -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title">Parametres</h2>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-phase">Phase</label>
                        <select id="project-phase" class="admin-form__select">
                            <?php foreach ($phases as $ph): ?>
                            <option value="<?= $ph ?>"<?= ($isEdit && $project['phase'] === $ph) ? ' selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $ph)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-status">Statut</label>
                        <select id="project-status" class="admin-form__select">
                            <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st ?>"<?= ($isEdit && $project['status'] === $st) ? ' selected' : '' ?>>
                                <?= ucfirst($st) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-investment">Investissement recherche</label>
                        <input type="text" id="project-investment" class="admin-form__input"
                               value="<?= $isEdit ? e($project['investment_sought'] ?? '') : '' ?>"
                               placeholder="Ex: 50 000 - 100 000 EUR">
                    </div>
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-launch-date">Date de lancement</label>
                        <input type="date" id="project-launch-date" class="admin-form__input"
                               value="<?= $isEdit ? e($project['launch_date'] ?? '') : '' ?>">
                    </div>
                </div>

                <div class="admin-form__row">
                    <div class="admin-form__group">
                        <label class="admin-form__label" for="project-display-order">Ordre d'affichage</label>
                        <input type="number" id="project-display-order" class="admin-form__input"
                               value="<?= $isEdit ? (int)$project['display_order'] : 0 ?>" min="0">
                    </div>
                    <div class="admin-form__group">
                        <label class="admin-form__label">Visibilite</label>
                        <div style="padding-top: 10px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" id="project-visible"
                                       <?= (!$isEdit || $project['is_visible']) ? 'checked' : '' ?>>
                                Visible publiquement
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Competences recherchees -->
            <div class="admin-form__section">
                <h2 class="admin-form__section-title">Competences recherchees</h2>

                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-skills-fr">Competences (FR)</label>
                    <textarea id="project-skills-fr" class="admin-form__textarea" rows="3"><?= $isEdit ? e($project['skills_sought_fr'] ?? '') : '' ?></textarea>
                </div>
                <div class="admin-form__group">
                    <label class="admin-form__label" for="project-skills-en">Competences (EN)</label>
                    <textarea id="project-skills-en" class="admin-form__textarea" rows="3"><?= $isEdit ? e($project['skills_sought_en'] ?? '') : '' ?></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="admin-form__footer">
                <button type="submit" class="btn btn--primary" data-text="Enregistrer" data-loading="Enregistrement...">
                    Enregistrer
                </button>
                <a href="<?= SITE_URL ?>/admin/projects" class="btn btn--outline">Annuler</a>
            </div>

        </form>
    </div>
</section>
