/* ============================================
   Saxho.net — Portfolio interactions
   Interest modal (open/close, toggle type, AJAX submit)
   ============================================ */
(function () {
    'use strict';

    /* --- Interest Modal --- */
    function initInterestModal() {
        var modal = document.getElementById('interest-modal');
        if (!modal) return;

        var overlay  = modal.querySelector('.interest-modal__overlay');
        var closeBtn = modal.querySelector('.interest-modal__close');
        var cancelBtn = modal.querySelector('[data-interest-cancel]');
        var form     = document.getElementById('interest-form');
        var typeInput   = document.getElementById('interest-type');
        var typeLabel   = document.getElementById('interest-type-label');
        var skillsSection = document.getElementById('interest-skills-section');
        var investSection = document.getElementById('interest-invest-section');
        var msgEl        = document.getElementById('interest-form-message');
        var siteUrl      = modal.getAttribute('data-site-url') || '';

        // Open buttons (on project detail page)
        var openBtns = document.querySelectorAll('[data-interest-type]');
        for (var i = 0; i < openBtns.length; i++) {
            openBtns[i].addEventListener('click', function () {
                var type = this.getAttribute('data-interest-type');
                openModal(type);
            });
        }

        function openModal(type) {
            typeInput.value = type;

            // Toggle sections
            if (type === 'competence') {
                skillsSection.classList.add('is-visible');
                investSection.classList.remove('is-visible');
                typeLabel.textContent = skillsSection.querySelector('.interest-form__section-title').textContent;
            } else {
                investSection.classList.add('is-visible');
                skillsSection.classList.remove('is-visible');
                typeLabel.textContent = investSection.querySelector('.interest-form__section-title').textContent;
            }

            // Reset message
            msgEl.className = 'interest-form__message';
            msgEl.textContent = '';
            msgEl.style.display = 'none';

            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        if (overlay) overlay.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        // ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        // Form submit
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var submitBtn = form.querySelector('[type="submit"]');
                var originalText = submitBtn.getAttribute('data-text');
                var loadingText  = submitBtn.getAttribute('data-loading');

                submitBtn.disabled = true;
                submitBtn.textContent = loadingText;

                // Collect data
                var type = typeInput.value;
                var csrfInput = form.querySelector('[name="csrf_token"]');
                var payload = {
                    project_id: document.getElementById('interest-project-id').value,
                    type: type,
                    contact_company: document.getElementById('interest-company').value.trim(),
                    contact_job_title: document.getElementById('interest-job').value.trim(),
                    contact_phone: document.getElementById('interest-phone').value.trim(),
                    contact_address: document.getElementById('interest-address').value.trim(),
                    contact_country: document.getElementById('interest-country').value.trim(),
                    message: document.getElementById('interest-message').value.trim()
                };

                if (csrfInput) {
                    payload[csrfInput.name] = csrfInput.value;
                }

                // Type-specific fields
                if (type === 'competence') {
                    payload.expertise_domain = document.getElementById('interest-expertise').value.trim();
                    payload.availability = document.getElementById('interest-availability').value.trim();
                    payload.linkedin_cv_url = document.getElementById('interest-linkedin').value.trim();
                } else {
                    payload.investment_range = document.getElementById('interest-range').value;
                    payload.investment_experience = document.getElementById('interest-experience').value.trim();
                    payload.investment_structure = document.getElementById('interest-structure').value.trim();
                }

                fetch(siteUrl + '/api/interest', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    // Update CSRF token
                    if (data.csrf_token && csrfInput) {
                        csrfInput.value = data.csrf_token;
                    }

                    if (data.success) {
                        msgEl.className = 'interest-form__message interest-form__message--success';
                        msgEl.textContent = data.message || 'OK';
                        msgEl.style.display = 'block';

                        // Hide form fields, show success
                        var groups = form.querySelectorAll('.interest-form__row, .interest-form__group, .interest-form__section, .interest-form__footer');
                        for (var g = 0; g < groups.length; g++) {
                            groups[g].style.display = 'none';
                        }

                        // Auto-close after 3s
                        setTimeout(function () {
                            closeModal();
                            location.reload();
                        }, 3000);
                    } else {
                        msgEl.className = 'interest-form__message interest-form__message--error';
                        msgEl.textContent = data.error || 'Error';
                        msgEl.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(function () {
                    msgEl.className = 'interest-form__message interest-form__message--error';
                    msgEl.textContent = 'Network error';
                    msgEl.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        }
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initInterestModal);
    } else {
        initInterestModal();
    }
})();
