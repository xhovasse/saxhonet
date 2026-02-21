/**
 * Saxho.net — Admin Back-Office JS
 * IIFE + var (convention projet)
 */
(function () {
    'use strict';

    var siteUrl = '';
    var csrfToken = '';

    /* ==========================================
       UTILS
       ========================================== */

    function getSiteUrl() {
        var meta = document.querySelector('meta[name="site-url"]');
        if (meta) return meta.getAttribute('content');
        // Fallback : depuis un formulaire
        var form = document.querySelector('[data-site-url]');
        if (form) return form.getAttribute('data-site-url');
        return '';
    }

    function getCsrfToken() {
        var input = document.querySelector('input[name="csrf_token"]');
        return input ? input.value : '';
    }

    function updateCsrfToken(token) {
        if (!token) return;
        csrfToken = token;
        var inputs = document.querySelectorAll('input[name="csrf_token"]');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value = token;
        }
    }

    function showAlert(container, type, message) {
        // Remove existing alerts
        var old = container.querySelectorAll('.admin-alert');
        for (var i = 0; i < old.length; i++) {
            old[i].remove();
        }
        var div = document.createElement('div');
        div.className = 'admin-alert admin-alert--' + type;
        div.textContent = message;
        container.insertBefore(div, container.firstChild);
        // Auto-remove after 5s
        setTimeout(function () {
            if (div.parentNode) div.remove();
        }, 5000);
    }

    /* ==========================================
       SLUG AUTO-GENERATION
       ========================================== */

    function initAutoSlug() {
        var titleInput = document.getElementById('blog-title-fr');
        var slugInput = document.getElementById('blog-slug');
        if (!titleInput || !slugInput) return;

        var slugManuallyEdited = false;

        // If slug already has a value (edit mode), consider it manually set
        if (slugInput.value.trim() !== '') {
            slugManuallyEdited = true;
        }

        slugInput.addEventListener('input', function () {
            slugManuallyEdited = true;
        });

        titleInput.addEventListener('input', function () {
            if (slugManuallyEdited) return;
            slugInput.value = generateSlug(titleInput.value);
        });
    }

    function generateSlug(text) {
        var accents = {
            '\u00e0':'a','\u00e2':'a','\u00e4':'a','\u00e1':'a','\u00e3':'a',
            '\u00e8':'e','\u00ea':'e','\u00eb':'e','\u00e9':'e',
            '\u00ec':'i','\u00ee':'i','\u00ef':'i','\u00ed':'i',
            '\u00f2':'o','\u00f4':'o','\u00f6':'o','\u00f3':'o','\u00f5':'o',
            '\u00f9':'u','\u00fb':'u','\u00fc':'u','\u00fa':'u',
            '\u00ff':'y','\u00fd':'y','\u00f1':'n','\u00e7':'c',
            '\u0153':'oe','\u00e6':'ae'
        };
        var result = text.toLowerCase();
        for (var key in accents) {
            if (accents.hasOwnProperty(key)) {
                result = result.split(key).join(accents[key]);
            }
        }
        result = result.replace(/[^a-z0-9]+/g, '-');
        result = result.replace(/^-+|-+$/g, '');
        return result;
    }

    /* ==========================================
       IMAGE UPLOAD
       ========================================== */

    function initImageUpload() {
        var uploadBtn = document.getElementById('upload-image-btn');
        var fileInput = document.getElementById('cover-image-input');
        var previewContainer = document.getElementById('image-preview');
        var hiddenInput = document.getElementById('blog-cover-image');
        if (!uploadBtn || !fileInput) return;

        uploadBtn.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            if (!fileInput.files || !fileInput.files[0]) return;
            var file = fileInput.files[0];

            var formData = new FormData();
            formData.append('image', file);
            formData.append('csrf_token', csrfToken);

            uploadBtn.disabled = true;
            uploadBtn.textContent = '...';

            fetch(siteUrl + '/api/admin/upload', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.csrf_token) updateCsrfToken(data.csrf_token);
                if (data.success) {
                    hiddenInput.value = data.filename;
                    showImagePreview(data.url);
                } else {
                    var errMsg = data.error || data.errors && Object.values(data.errors)[0] || 'Upload error';
                    alert(errMsg);
                }
            })
            .catch(function () {
                alert('Upload error');
            })
            .finally(function () {
                uploadBtn.disabled = false;
                uploadBtn.textContent = uploadBtn.getAttribute('data-label') || 'Upload';
                fileInput.value = '';
            });
        });
    }

    function showImagePreview(url) {
        var previewContainer = document.getElementById('image-preview');
        if (!previewContainer) return;
        previewContainer.innerHTML = '<div class="admin-form__image-preview">'
            + '<img src="' + url + '" alt="Cover">'
            + '<button type="button" class="admin-form__image-remove" title="Supprimer">&times;</button>'
            + '</div>';
        previewContainer.style.display = 'block';

        // Bind remove
        var removeBtn = previewContainer.querySelector('.admin-form__image-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                var hiddenInput = document.getElementById('blog-cover-image');
                if (hiddenInput) hiddenInput.value = '';
                previewContainer.innerHTML = '';
                previewContainer.style.display = 'none';
            });
        }
    }

    function initExistingImagePreview() {
        var previewContainer = document.getElementById('image-preview');
        var hiddenInput = document.getElementById('blog-cover-image');
        if (!previewContainer || !hiddenInput || !hiddenInput.value) return;

        var removeBtn = previewContainer.querySelector('.admin-form__image-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                hiddenInput.value = '';
                previewContainer.innerHTML = '';
                previewContainer.style.display = 'none';
            });
        }
    }

    /* ==========================================
       BLOG FORM (CREATE / EDIT)
       ========================================== */

    function initBlogForm() {
        var form = document.getElementById('blog-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Gather data
            var data = {
                csrf_token: csrfToken,
                title_fr: (document.getElementById('blog-title-fr') || {}).value || '',
                title_en: (document.getElementById('blog-title-en') || {}).value || '',
                slug: (document.getElementById('blog-slug') || {}).value || '',
                category_id: (document.getElementById('blog-category') || {}).value || '',
                content_fr: (document.getElementById('blog-content-fr') || {}).value || '',
                content_en: (document.getElementById('blog-content-en') || {}).value || '',
                excerpt_fr: (document.getElementById('blog-excerpt-fr') || {}).value || '',
                excerpt_en: (document.getElementById('blog-excerpt-en') || {}).value || '',
                cover_image: (document.getElementById('blog-cover-image') || {}).value || '',
                status: (document.querySelector('input[name="status"]:checked') || {}).value || 'draft',
                published_at: (document.getElementById('blog-published-at') || {}).value || ''
            };

            // Include ID if editing
            var idInput = document.getElementById('blog-id');
            if (idInput && idInput.value) {
                data.id = idInput.value;
            }

            // Validate
            if (!data.title_fr.trim()) {
                alert(form.getAttribute('data-error-title') || 'Title is required');
                return;
            }
            if (!data.content_fr.trim()) {
                alert(form.getAttribute('data-error-content') || 'Content is required');
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = submitBtn.getAttribute('data-loading') || '...';
            }

            fetch(siteUrl + '/api/admin/blog-save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(function (res) { return res.json(); })
            .then(function (result) {
                if (result.csrf_token) updateCsrfToken(result.csrf_token);
                if (result.success) {
                    // If was creating, redirect to edit
                    if (!data.id && result.id) {
                        window.location.href = siteUrl + '/admin/blog/edit?id=' + result.id;
                    } else {
                        showAlert(form.parentNode, 'success', result.message || 'Saved');
                        // Update slug field if returned
                        if (result.slug) {
                            var slugField = document.getElementById('blog-slug');
                            if (slugField) slugField.value = result.slug;
                        }
                    }
                } else {
                    var msg = result.error || (result.errors ? Object.values(result.errors)[0] : 'Error');
                    showAlert(form.parentNode, 'error', msg);
                }
            })
            .catch(function () {
                showAlert(form.parentNode, 'error', 'Network error');
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.getAttribute('data-text') || 'Save';
                }
            });
        });
    }

    /* ==========================================
       PUBLISH / UNPUBLISH TOGGLE
       ========================================== */

    function initPublishToggle() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-publish-id]');
            if (!btn) return;
            e.preventDefault();

            var id = btn.getAttribute('data-publish-id');
            var newStatus = btn.getAttribute('data-publish-status');

            fetch(siteUrl + '/api/admin/blog-publish', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    id: id,
                    status: newStatus
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.csrf_token) updateCsrfToken(data.csrf_token);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Error');
                }
            })
            .catch(function () {
                alert('Network error');
            });
        });
    }

    /* ==========================================
       DELETE CONFIRMATION (MODAL)
       ========================================== */

    function initDeleteModal() {
        var modal = document.getElementById('admin-delete-modal');
        if (!modal) return;

        var overlay = modal.querySelector('.admin-modal__overlay');
        var cancelBtn = modal.querySelector('[data-modal-cancel]');
        var confirmBtn = modal.querySelector('[data-modal-confirm]');
        var deleteUrl = '';
        var deleteId = '';

        // Open modal on delete click
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-delete-id]');
            if (!btn) return;
            e.preventDefault();
            deleteId = btn.getAttribute('data-delete-id');
            deleteUrl = btn.getAttribute('data-delete-url');
            var msgEl = modal.querySelector('.admin-modal__text');
            if (msgEl) {
                msgEl.textContent = btn.getAttribute('data-delete-message') || 'Are you sure?';
            }
            modal.classList.add('visible');
        });

        // Close modal
        function closeModal() {
            modal.classList.remove('visible');
            deleteUrl = '';
            deleteId = '';
        }

        if (overlay) overlay.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // Confirm delete
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (!deleteUrl || !deleteId) return;
                confirmBtn.disabled = true;

                fetch(siteUrl + deleteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        csrf_token: csrfToken,
                        id: deleteId
                    })
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.csrf_token) updateCsrfToken(data.csrf_token);
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error || 'Error');
                        closeModal();
                    }
                })
                .catch(function () {
                    alert('Network error');
                    closeModal();
                })
                .finally(function () {
                    confirmBtn.disabled = false;
                });
            });
        }
    }

    /* ==========================================
       CATEGORIES (INLINE FORM)
       ========================================== */

    function initCategories() {
        var form = document.getElementById('category-form');
        if (!form) return;

        var idInput = document.getElementById('category-id');
        var nameFrInput = document.getElementById('category-name-fr');
        var nameEnInput = document.getElementById('category-name-en');
        var cancelBtn = document.getElementById('category-cancel');
        var formTitle = document.getElementById('category-form-title');

        // Submit category
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var data = {
                csrf_token: csrfToken,
                name_fr: nameFrInput.value.trim(),
                name_en: nameEnInput.value.trim()
            };

            if (idInput.value) {
                data.id = idInput.value;
            }

            if (!data.name_fr) {
                alert(form.getAttribute('data-error-name') || 'Name is required');
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(siteUrl + '/api/admin/category-save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(function (res) { return res.json(); })
            .then(function (result) {
                if (result.csrf_token) updateCsrfToken(result.csrf_token);
                if (result.success) {
                    window.location.reload();
                } else {
                    var msg = result.error || (result.errors ? Object.values(result.errors)[0] : 'Error');
                    alert(msg);
                }
            })
            .catch(function () {
                alert('Network error');
            })
            .finally(function () {
                if (submitBtn) submitBtn.disabled = false;
            });
        });

        // Edit category (click on edit button in table)
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-edit-category]');
            if (!btn) return;
            e.preventDefault();
            idInput.value = btn.getAttribute('data-edit-category');
            nameFrInput.value = btn.getAttribute('data-name-fr') || '';
            nameEnInput.value = btn.getAttribute('data-name-en') || '';
            if (formTitle) formTitle.textContent = formTitle.getAttribute('data-edit-label') || 'Edit';
            if (cancelBtn) cancelBtn.style.display = 'inline-flex';
            nameFrInput.focus();
        });

        // Cancel edit
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                idInput.value = '';
                nameFrInput.value = '';
                nameEnInput.value = '';
                if (formTitle) formTitle.textContent = formTitle.getAttribute('data-add-label') || 'Add';
                cancelBtn.style.display = 'none';
            });
        }
    }

    /* ==========================================
       AUTO EXCERPT
       ========================================== */

    function initAutoExcerpt() {
        var contentFr = document.getElementById('blog-content-fr');
        var excerptFr = document.getElementById('blog-excerpt-fr');
        if (!contentFr || !excerptFr) return;

        contentFr.addEventListener('blur', function () {
            if (excerptFr.value.trim() !== '') return;
            // Strip HTML tags and take first 200 chars
            var text = contentFr.value.replace(/<[^>]*>/g, '').trim();
            if (text.length > 200) {
                text = text.substring(0, 200) + '...';
            }
            if (text) {
                excerptFr.value = text;
            }
        });
    }

    /* ==========================================
       MESSAGE DETAIL TOGGLE (expandable rows)
       ========================================== */

    function initMessageDetailToggle() {
        document.addEventListener('click', function (e) {
            var row = e.target.closest('[data-toggle-detail]');
            if (!row) return;

            var id = row.getAttribute('data-toggle-detail');
            var detailRow = document.querySelector('[data-detail-for="' + id + '"]');
            if (!detailRow) return;

            var isVisible = detailRow.style.display !== 'none';
            detailRow.style.display = isVisible ? 'none' : 'table-row';
        });
    }

    /* ==========================================
       MESSAGE READ TOGGLE
       ========================================== */

    function initMessageReadToggle() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-message-read-id]');
            if (!btn) return;
            e.preventDefault();

            var id = btn.getAttribute('data-message-read-id');
            btn.disabled = true;

            fetch(siteUrl + '/api/admin/message-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    id: id
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.csrf_token) updateCsrfToken(data.csrf_token);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Error');
                }
            })
            .catch(function () {
                alert('Network error');
            })
            .finally(function () {
                btn.disabled = false;
            });
        });
    }

    /* ==========================================
       USER TOGGLE ACTIVE
       ========================================== */

    function initUserToggleActive() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-user-toggle-id]');
            if (!btn) return;
            e.preventDefault();

            var id = btn.getAttribute('data-user-toggle-id');
            btn.disabled = true;

            fetch(siteUrl + '/api/admin/user-toggle-active', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    id: id
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.csrf_token) updateCsrfToken(data.csrf_token);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Error');
                }
            })
            .catch(function () {
                alert('Network error');
            })
            .finally(function () {
                btn.disabled = false;
            });
        });
    }

    /* ==========================================
       USER ROLE CHANGE
       ========================================== */

    function initUserRoleChange() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-user-role-id]');
            if (!btn) return;
            e.preventDefault();

            var id = btn.getAttribute('data-user-role-id');
            btn.disabled = true;

            fetch(siteUrl + '/api/admin/user-role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    id: id
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.csrf_token) updateCsrfToken(data.csrf_token);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Error');
                }
            })
            .catch(function () {
                alert('Network error');
            })
            .finally(function () {
                btn.disabled = false;
            });
        });
    }

    /* ==========================================
       PROJECT VISIBILITY TOGGLE
       ========================================== */

    function initProjectVisibility() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-project-visibility-id]');
            if (!btn) return;
            e.preventDefault();

            var id = btn.getAttribute('data-project-visibility-id');
            btn.disabled = true;

            fetch(siteUrl + '/api/admin/project-visibility', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    id: id
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.csrf_token) updateCsrfToken(data.csrf_token);
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.error || 'Error');
                }
            })
            .catch(function () {
                alert('Network error');
            })
            .finally(function () {
                btn.disabled = false;
            });
        });
    }

    /* ==========================================
       PROJECT FORM (CREATE / EDIT)
       ========================================== */

    function initProjectForm() {
        var form = document.getElementById('project-form');
        if (!form) return;

        // Auto-slug from name
        var nameInput = document.getElementById('project-name-fr');
        var slugInput = document.getElementById('project-slug');
        if (nameInput && slugInput) {
            var slugEdited = slugInput.value.trim() !== '';

            slugInput.addEventListener('input', function () {
                slugEdited = true;
            });

            nameInput.addEventListener('input', function () {
                if (slugEdited) return;
                slugInput.value = generateSlug(nameInput.value);
            });
        }

        // Image upload for project
        var uploadBtn = document.getElementById('upload-project-image-btn');
        var fileInput = document.getElementById('project-image-input');
        var previewContainer = document.getElementById('project-image-preview');
        var hiddenInput = document.getElementById('project-image');

        if (uploadBtn && fileInput) {
            uploadBtn.addEventListener('click', function () {
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
                if (!fileInput.files || !fileInput.files[0]) return;
                var file = fileInput.files[0];

                var formData = new FormData();
                formData.append('image', file);
                formData.append('csrf_token', csrfToken);

                uploadBtn.disabled = true;
                uploadBtn.textContent = '...';

                fetch(siteUrl + '/api/admin/upload', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.csrf_token) updateCsrfToken(data.csrf_token);
                    if (data.success) {
                        hiddenInput.value = data.filename;
                        previewContainer.innerHTML = '<div class="admin-form__image-preview">'
                            + '<img src="' + data.url + '" alt="Cover">'
                            + '<button type="button" class="admin-form__image-remove" title="Supprimer">&times;</button>'
                            + '</div>';
                        previewContainer.style.display = 'block';
                        var removeBtn = previewContainer.querySelector('.admin-form__image-remove');
                        if (removeBtn) {
                            removeBtn.addEventListener('click', function () {
                                hiddenInput.value = '';
                                previewContainer.innerHTML = '';
                                previewContainer.style.display = 'none';
                            });
                        }
                    } else {
                        var errMsg = data.error || (data.errors && Object.values(data.errors)[0]) || 'Upload error';
                        alert(errMsg);
                    }
                })
                .catch(function () {
                    alert('Upload error');
                })
                .finally(function () {
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = uploadBtn.getAttribute('data-label') || 'Upload';
                    fileInput.value = '';
                });
            });

            // Existing image remove
            if (previewContainer) {
                var existingRemove = previewContainer.querySelector('.admin-form__image-remove');
                if (existingRemove) {
                    existingRemove.addEventListener('click', function () {
                        hiddenInput.value = '';
                        previewContainer.innerHTML = '';
                        previewContainer.style.display = 'none';
                    });
                }
            }
        }

        // Submit
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var data = {
                csrf_token: csrfToken,
                name_fr: (document.getElementById('project-name-fr') || {}).value || '',
                name_en: (document.getElementById('project-name-en') || {}).value || '',
                slug: (document.getElementById('project-slug') || {}).value || '',
                domain: (document.getElementById('project-domain') || {}).value || '',
                image: (document.getElementById('project-image') || {}).value || '',
                pitch_fr: (document.getElementById('project-pitch-fr') || {}).value || '',
                pitch_en: (document.getElementById('project-pitch-en') || {}).value || '',
                problem_fr: (document.getElementById('project-problem-fr') || {}).value || '',
                problem_en: (document.getElementById('project-problem-en') || {}).value || '',
                solution_fr: (document.getElementById('project-solution-fr') || {}).value || '',
                solution_en: (document.getElementById('project-solution-en') || {}).value || '',
                phase: (document.getElementById('project-phase') || {}).value || 'ideation',
                status: (document.getElementById('project-status') || {}).value || 'open',
                investment_sought: (document.getElementById('project-investment') || {}).value || '',
                launch_date: (document.getElementById('project-launch-date') || {}).value || '',
                display_order: (document.getElementById('project-display-order') || {}).value || '0',
                is_visible: document.getElementById('project-visible') ? document.getElementById('project-visible').checked : true,
                skills_sought_fr: (document.getElementById('project-skills-fr') || {}).value || '',
                skills_sought_en: (document.getElementById('project-skills-en') || {}).value || ''
            };

            var idInput = document.getElementById('project-id');
            if (idInput && idInput.value) {
                data.id = idInput.value;
            }

            if (!data.name_fr.trim()) {
                alert(form.getAttribute('data-error-name') || 'Name is required');
                return;
            }
            if (!data.pitch_fr.trim()) {
                alert(form.getAttribute('data-error-pitch') || 'Pitch is required');
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = submitBtn.getAttribute('data-loading') || '...';
            }

            fetch(siteUrl + '/api/admin/project-save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(function (res) { return res.json(); })
            .then(function (result) {
                if (result.csrf_token) updateCsrfToken(result.csrf_token);
                if (result.success) {
                    if (!data.id && result.id) {
                        window.location.href = siteUrl + '/admin/projects/edit?id=' + result.id;
                    } else {
                        showAlert(form.parentNode, 'success', result.message || 'Saved');
                        if (result.slug) {
                            var slugField = document.getElementById('project-slug');
                            if (slugField) slugField.value = result.slug;
                        }
                    }
                } else {
                    var msg = result.error || (result.errors ? Object.values(result.errors)[0] : 'Error');
                    showAlert(form.parentNode, 'error', msg);
                }
            })
            .catch(function () {
                showAlert(form.parentNode, 'error', 'Network error');
            })
            .finally(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.getAttribute('data-text') || 'Save';
                }
            });
        });
    }

    /* ==========================================
       INIT
       ========================================== */

    function init() {
        siteUrl = getSiteUrl();
        csrfToken = getCsrfToken();

        initAutoSlug();
        initImageUpload();
        initExistingImagePreview();
        initBlogForm();
        initPublishToggle();
        initDeleteModal();
        initCategories();
        initAutoExcerpt();
        initMessageDetailToggle();
        initMessageReadToggle();
        initUserToggleActive();
        initUserRoleChange();
        initProjectVisibility();
        initProjectForm();
    }

    // Run
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
