/**
 * Saxho.net — Contact Form (validation + AJAX submission)
 */
(function () {
    'use strict';

    var form = document.getElementById('contact-form');
    if (!form) return;

    var submitBtn = form.querySelector('[type="submit"]');
    var submitText = submitBtn ? submitBtn.textContent : '';
    var successEl = document.getElementById('contact-success');
    var formWrapper = document.getElementById('contact-form-wrapper');

    // Field references
    var fields = {
        name:    form.querySelector('[name="name"]'),
        email:   form.querySelector('[name="email"]'),
        subject: form.querySelector('[name="subject"]'),
        message: form.querySelector('[name="message"]')
    };

    // Clear error on input
    Object.keys(fields).forEach(function (key) {
        if (fields[key]) {
            fields[key].addEventListener('input', function () {
                clearError(this);
            });
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Client-side validation
        var errors = validate();
        if (Object.keys(errors).length > 0) {
            showErrors(errors);
            return;
        }

        // Disable button, show loading
        submitBtn.disabled = true;
        submitBtn.textContent = form.getAttribute('data-sending') || 'Sending...';

        // Gather form data
        var formData = new FormData(form);
        var data = {};
        formData.forEach(function (value, key) {
            data[key] = value;
        });

        // Send via fetch
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(function (response) {
            return response.json().then(function (json) {
                return { status: response.status, body: json };
            });
        })
        .then(function (result) {
            // Toujours mettre a jour le CSRF token si present
            if (result.body.csrf_token) {
                var csrfInput = form.querySelector('[name="csrf_token"]');
                if (csrfInput) csrfInput.value = result.body.csrf_token;
            }

            if (result.body.success) {
                // Show success message, hide form
                if (formWrapper) formWrapper.style.display = 'none';
                if (successEl) {
                    successEl.style.display = 'block';
                    successEl.classList.add('is-visible');
                }
            } else if (result.body.errors) {
                showErrors(result.body.errors);
            } else {
                showGeneralError(result.body.error || 'An error occurred.');
            }
        })
        .catch(function () {
            showGeneralError('Network error. Please try again.');
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = submitText;
        });
    });

    function validate() {
        var errors = {};
        var name = (fields.name.value || '').trim();
        var email = (fields.email.value || '').trim();
        var subject = (fields.subject.value || '').trim();
        var message = (fields.message.value || '').trim();

        if (!name) errors.name = fields.name.getAttribute('data-error') || 'Required';
        if (!email || !isValidEmail(email)) errors.email = fields.email.getAttribute('data-error') || 'Invalid email';
        if (!subject) errors.subject = fields.subject.getAttribute('data-error') || 'Required';
        if (!message) errors.message = fields.message.getAttribute('data-error') || 'Required';
        else if (message.length < 10) errors.message = fields.message.getAttribute('data-error-short') || 'Too short';

        return errors;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showErrors(errors) {
        // Clear all first
        form.querySelectorAll('.form-error').forEach(function (el) { el.remove(); });
        form.querySelectorAll('.is-error').forEach(function (el) { el.classList.remove('is-error'); });

        Object.keys(errors).forEach(function (key) {
            var field = fields[key] || form.querySelector('[name="' + key + '"]');
            if (field) {
                field.classList.add('is-error');
                var errorEl = document.createElement('div');
                errorEl.className = 'form-error';
                errorEl.textContent = errors[key];
                field.parentNode.appendChild(errorEl);
            }
        });

        // Focus first error field
        var firstErrorKey = Object.keys(errors)[0];
        if (fields[firstErrorKey]) fields[firstErrorKey].focus();
    }

    function clearError(field) {
        field.classList.remove('is-error');
        var errorEl = field.parentNode.querySelector('.form-error');
        if (errorEl) errorEl.remove();
    }

    function showGeneralError(message) {
        var existing = form.querySelector('.contact-form__general-error');
        if (existing) existing.remove();

        var el = document.createElement('div');
        el.className = 'contact-form__general-error flash flash--error';
        el.setAttribute('role', 'alert');
        el.textContent = message;
        form.insertBefore(el, form.firstChild);

        setTimeout(function () {
            if (el.parentNode) el.remove();
        }, 8000);
    }

})();
