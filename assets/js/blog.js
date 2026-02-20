/**
 * Saxho.net — Blog (comment form handler)
 */
(function () {
    'use strict';

    // --- Comment form handler ---
    var form = document.getElementById('comment-form');
    if (!form) return;

    var submitBtn = form.querySelector('[type="submit"]');
    var submitText = submitBtn ? submitBtn.textContent : '';
    var textarea = form.querySelector('[name="content"]');
    var commentsList = document.getElementById('comments-list');
    var csrfField = form.querySelector('[name="csrf_token"]');

    // Clear error on input
    if (textarea) {
        textarea.addEventListener('input', function () {
            this.classList.remove('is-error');
            var errorEl = this.parentNode.querySelector('.form-error');
            if (errorEl) errorEl.remove();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var content = (textarea.value || '').trim();

        // Client-side validation
        if (!content) {
            showFieldError(textarea, textarea.getAttribute('data-error-empty') || 'Required');
            return;
        }
        if (content.length > 2000) {
            showFieldError(textarea, textarea.getAttribute('data-error-long') || 'Too long');
            return;
        }

        // Disable button, show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = form.getAttribute('data-submitting') || 'Posting...';

        // Gather form data as JSON
        var formData = new FormData(form);
        var data = {};
        formData.forEach(function (value, key) {
            data[key] = value;
        });

        // Send request
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
            // Update CSRF token (consumed after verify)
            if (result.body.csrf_token && csrfField) {
                csrfField.value = result.body.csrf_token;
            }

            if (result.body.success) {
                // Clear textarea
                textarea.value = '';

                // Remove "no comments" empty message
                var emptyMsg = commentsList.querySelector('.blog-comments__empty');
                if (emptyMsg) emptyMsg.remove();

                // Build and prepend new comment
                var comment = result.body.comment;
                var commentEl = document.createElement('div');
                commentEl.className = 'blog-comment';
                commentEl.innerHTML =
                    '<div class="blog-comment__header">' +
                        '<strong class="blog-comment__author">' + escapeHtml(comment.author) + '</strong>' +
                        '<time class="blog-comment__date">' + escapeHtml(comment.date) + '</time>' +
                    '</div>' +
                    '<p class="blog-comment__text">' + escapeHtml(comment.content).replace(/\n/g, '<br>') + '</p>';

                commentsList.insertBefore(commentEl, commentsList.firstChild);

                // Update comment count
                updateCommentCount(1);

            } else if (result.body.errors) {
                if (result.body.errors.content) {
                    showFieldError(textarea, result.body.errors.content);
                }
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

    /**
     * Show a field-level error
     */
    function showFieldError(field, message) {
        field.classList.add('is-error');
        var existing = field.parentNode.querySelector('.form-error');
        if (existing) existing.remove();
        var el = document.createElement('div');
        el.className = 'form-error';
        el.textContent = message;
        field.parentNode.appendChild(el);
        field.focus();
    }

    /**
     * Show a general error above the form
     */
    function showGeneralError(message) {
        var existing = form.querySelector('.blog-comments__general-error');
        if (existing) existing.remove();
        var el = document.createElement('div');
        el.className = 'blog-comments__general-error flash flash--error';
        el.setAttribute('role', 'alert');
        el.textContent = message;
        form.insertBefore(el, form.firstChild);
        setTimeout(function () {
            if (el.parentNode) el.remove();
        }, 8000);
    }

    /**
     * Update the comment count in the title
     */
    function updateCommentCount(delta) {
        var countEl = document.querySelector('.blog-comments__count');
        if (!countEl) return;
        var match = countEl.textContent.match(/\d+/);
        if (match) {
            var newCount = parseInt(match[0], 10) + delta;
            countEl.textContent = countEl.textContent.replace(/\d+/, newCount);
        }
    }

    /**
     * Escape HTML to prevent XSS when injecting into DOM
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

})();
