/**
 * Saxho.net — Auth JS
 * Gestion des formulaires d'authentification (AJAX, validation, MFA, QR code)
 */
(function () {
    'use strict';

    // =============================================
    // Utilitaires
    // =============================================

    /**
     * Soumission AJAX d'un formulaire
     */
    function submitForm(form, callback) {
        var btn = form.querySelector('[type="submit"]');
        var originalText = btn ? btn.textContent : '';
        var submitting = form.dataset.submitting || 'Chargement...';

        if (btn) {
            btn.disabled = true;
            btn.classList.add('auth-submit--loading');
            btn.textContent = submitting;
        }

        // Effacer les erreurs precedentes
        clearErrors(form);

        var formData = new FormData(form);
        var data = {};
        formData.forEach(function (value, key) {
            data[key] = value;
        });

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(function (res) { return res.json(); })
        .then(function (json) {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('auth-submit--loading');
                btn.textContent = originalText;
            }

            // Mettre a jour le token CSRF
            if (json.csrf_token) {
                var csrfInputs = form.querySelectorAll('input[name="csrf_token"]');
                csrfInputs.forEach(function (input) {
                    input.value = json.csrf_token;
                });
                // MAJ aussi dans les autres formulaires de la page
                document.querySelectorAll('input[name="csrf_token"]').forEach(function (input) {
                    input.value = json.csrf_token;
                });
            }

            callback(json);
        })
        .catch(function (err) {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('auth-submit--loading');
                btn.textContent = originalText;
            }
            showAlert('error', 'Erreur de connexion. Veuillez reessayer.');
            console.error('Auth fetch error:', err);
        });
    }

    /**
     * Afficher une erreur sous un champ
     */
    function showFieldError(form, fieldName, message) {
        var field = form.querySelector('[name="' + fieldName + '"]');
        if (!field) return;

        field.classList.add('form-input--error');
        var group = field.closest('.form-group');
        if (group) {
            var errorEl = group.querySelector('.form-error');
            if (!errorEl) {
                errorEl = document.createElement('div');
                errorEl.className = 'form-error visible';
                group.appendChild(errorEl);
            } else {
                errorEl.classList.add('visible');
            }
            errorEl.textContent = message;
        }
    }

    /**
     * Effacer toutes les erreurs
     */
    function clearErrors(form) {
        form.querySelectorAll('.form-input--error').forEach(function (el) {
            el.classList.remove('form-input--error');
        });
        form.querySelectorAll('.form-error').forEach(function (el) {
            el.classList.remove('visible');
            el.textContent = '';
        });
        // Effacer les alertes
        var alert = form.closest('.auth-card');
        if (alert) {
            var alertEl = alert.querySelector('.auth-alert');
            if (alertEl) alertEl.remove();
        }
    }

    /**
     * Afficher un message d'alerte au-dessus du formulaire
     */
    function showAlert(type, message) {
        var card = document.querySelector('.auth-card');
        if (!card) return;

        // Supprimer l'alerte precedente
        var existing = card.querySelector('.auth-alert');
        if (existing) existing.remove();

        var alert = document.createElement('div');
        alert.className = 'auth-alert auth-alert--' + type;
        alert.textContent = message;
        card.insertBefore(alert, card.querySelector('.auth-form') || card.firstChild);

        // Scroll vers l'alerte
        alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Afficher les erreurs serveur (field-level ou global)
     */
    function handleErrors(form, json) {
        if (json.errors) {
            // Erreurs par champ
            for (var field in json.errors) {
                if (json.errors.hasOwnProperty(field)) {
                    showFieldError(form, field, json.errors[field]);
                }
            }
        }
        if (json.error) {
            showAlert('error', json.error);
        }
    }

    // =============================================
    // Validation mot de passe
    // =============================================

    function initPasswordStrength() {
        var passwordInputs = document.querySelectorAll('input[data-strength]');
        passwordInputs.forEach(function (input) {
            var group = input.closest('.form-group');
            if (!group) return;

            // Creer la barre de force
            var meter = document.createElement('div');
            meter.className = 'password-strength';
            meter.innerHTML = '<div class="password-strength__bar"><div class="password-strength__fill"></div></div>' +
                              '<span class="password-strength__label"></span>';
            group.appendChild(meter);

            input.addEventListener('input', function () {
                var val = input.value;
                var score = 0;
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;

                var fill = meter.querySelector('.password-strength__fill');
                var label = meter.querySelector('.password-strength__label');
                var levels = ['', 'weak', 'fair', 'good', 'strong'];
                var labels = {
                    'weak': input.dataset.labelWeak || 'Faible',
                    'fair': input.dataset.labelFair || 'Moyen',
                    'good': input.dataset.labelGood || 'Bon',
                    'strong': input.dataset.labelStrong || 'Fort'
                };

                // Reset
                fill.className = 'password-strength__fill';
                if (val.length === 0) {
                    label.textContent = '';
                    return;
                }

                var level = levels[score] || 'weak';
                fill.classList.add('password-strength__fill--' + level);
                label.textContent = labels[level] || '';
            });
        });
    }

    // =============================================
    // Toggle visibilite mot de passe
    // =============================================

    function initPasswordToggle() {
        document.querySelectorAll('.password-toggle').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var wrapper = btn.closest('.form-input-wrapper');
                var input = wrapper ? wrapper.querySelector('input') : null;
                if (!input) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    btn.textContent = btn.dataset.hideText || 'Masquer';
                } else {
                    input.type = 'password';
                    btn.textContent = btn.dataset.showText || 'Afficher';
                }
            });
        });
    }

    // =============================================
    // Formulaire Login
    // =============================================

    function initLoginForm() {
        var form = document.getElementById('login-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var email = form.querySelector('[name="email"]').value.trim();
            var password = form.querySelector('[name="password"]').value;

            if (!email) {
                showFieldError(form, 'email', form.querySelector('[name="email"]').dataset.errorRequired || 'Email obligatoire');
                return;
            }
            if (!password) {
                showFieldError(form, 'password', form.querySelector('[name="password"]').dataset.errorRequired || 'Mot de passe obligatoire');
                return;
            }

            submitForm(form, function (json) {
                if (json.success) {
                    window.location.href = json.redirect || (form.dataset.siteUrl + '/');
                } else {
                    handleErrors(form, json);
                }
            });
        });
    }

    // =============================================
    // Formulaire Register
    // =============================================

    function initRegisterForm() {
        var form = document.getElementById('register-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var firstName = form.querySelector('[name="first_name"]').value.trim();
            var lastName = form.querySelector('[name="last_name"]').value.trim();
            var email = form.querySelector('[name="email"]').value.trim();
            var password = form.querySelector('[name="password"]').value;
            var passwordConfirm = form.querySelector('[name="password_confirm"]').value;
            var terms = form.querySelector('[name="accept_terms"]');

            var hasError = false;

            if (!firstName || !lastName) {
                if (!firstName) showFieldError(form, 'first_name', form.dataset.errorNameRequired || 'Obligatoire');
                if (!lastName) showFieldError(form, 'last_name', form.dataset.errorNameRequired || 'Obligatoire');
                hasError = true;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showFieldError(form, 'email', form.dataset.errorEmailInvalid || 'Email invalide');
                hasError = true;
            }
            if (password.length < 8) {
                showFieldError(form, 'password', form.dataset.errorPasswordShort || '8 caracteres minimum');
                hasError = true;
            } else if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                showFieldError(form, 'password', form.dataset.errorPasswordWeak || 'Majuscule et chiffre requis');
                hasError = true;
            }
            if (password !== passwordConfirm) {
                showFieldError(form, 'password_confirm', form.dataset.errorPasswordMismatch || 'Non identiques');
                hasError = true;
            }
            if (terms && !terms.checked) {
                showFieldError(form, 'accept_terms', form.dataset.errorTerms || 'Obligatoire');
                hasError = true;
            }

            if (hasError) return;

            submitForm(form, function (json) {
                if (json.success) {
                    window.location.href = json.redirect || (form.dataset.siteUrl + '/verify-email');
                } else {
                    handleErrors(form, json);
                }
            });
        });
    }

    // =============================================
    // Formulaire Forgot Password
    // =============================================

    function initForgotForm() {
        var form = document.getElementById('forgot-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var email = form.querySelector('[name="email"]').value.trim();
            if (!email) {
                showFieldError(form, 'email', 'Email obligatoire');
                return;
            }

            submitForm(form, function (json) {
                if (json.success) {
                    showAlert('success', json.message || 'Email envoye.');
                    form.querySelector('[type="submit"]').disabled = true;
                } else {
                    handleErrors(form, json);
                }
            });
        });
    }

    // =============================================
    // Formulaire Reset Password
    // =============================================

    function initResetForm() {
        var form = document.getElementById('reset-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var password = form.querySelector('[name="password"]').value;
            var passwordConfirm = form.querySelector('[name="password_confirm"]').value;

            if (password.length < 8) {
                showFieldError(form, 'password', '8 caracteres minimum');
                return;
            }
            if (password !== passwordConfirm) {
                showFieldError(form, 'password_confirm', 'Non identiques');
                return;
            }

            submitForm(form, function (json) {
                if (json.success) {
                    showAlert('success', json.message || 'Mot de passe reinitialise.');
                    setTimeout(function () {
                        window.location.href = form.dataset.siteUrl + '/login';
                    }, 2000);
                } else {
                    handleErrors(form, json);
                }
            });
        });
    }

    // =============================================
    // MFA Verify (login step 2)
    // =============================================

    function initMfaVerifyForm() {
        var form = document.getElementById('mfa-verify-form');
        if (!form) return;

        // Auto-focus
        var codeInput = form.querySelector('[name="code"]');
        if (codeInput) codeInput.focus();

        // N'accepter que les chiffres
        if (codeInput) {
            codeInput.addEventListener('input', function () {
                codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
                // Auto-submit quand 6 chiffres
                if (codeInput.value.length === 6) {
                    form.dispatchEvent(new Event('submit'));
                }
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var code = form.querySelector('[name="code"]').value.trim();
            if (!code || code.length < 6) {
                showFieldError(form, 'code', 'Code a 6 chiffres requis');
                return;
            }

            submitForm(form, function (json) {
                if (json.success) {
                    window.location.href = json.redirect || (form.dataset.siteUrl + '/');
                } else {
                    handleErrors(form, json);
                    if (codeInput) {
                        codeInput.value = '';
                        codeInput.focus();
                    }
                }
            });
        });

        // Toggle backup code
        var backupLink = document.getElementById('use-backup-code');
        var backupForm = document.getElementById('backup-code-section');
        if (backupLink && backupForm) {
            backupLink.addEventListener('click', function (e) {
                e.preventDefault();
                backupForm.style.display = backupForm.style.display === 'none' ? 'block' : 'none';
                var mainSection = document.getElementById('totp-code-section');
                if (mainSection) mainSection.style.display = mainSection.style.display === 'none' ? 'block' : 'none';
            });
        }
    }

    // =============================================
    // MFA Setup
    // =============================================

    function initMfaSetup() {
        var container = document.getElementById('mfa-setup');
        if (!container) return;

        var generateBtn = document.getElementById('mfa-generate');
        var enableForm = document.getElementById('mfa-enable-form');
        var disableForm = document.getElementById('mfa-disable-form');

        // Generer le secret + QR
        if (generateBtn) {
            generateBtn.addEventListener('click', function () {
                var formData = new FormData();
                formData.set('action', 'generate');

                // Recuperer le CSRF token
                var csrf = container.dataset.csrfToken;

                fetch(container.dataset.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'generate', csrf_token: csrf })
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.csrf_token) container.dataset.csrfToken = json.csrf_token;

                    if (json.success && json.uri) {
                        // Afficher la section QR
                        document.getElementById('mfa-step-generate').style.display = 'none';
                        document.getElementById('mfa-step-verify').style.display = 'block';

                        // Generer le QR code
                        if (typeof qrcode !== 'undefined') {
                            var qr = qrcode(0, 'M');
                            qr.addData(json.uri);
                            qr.make();
                            document.getElementById('mfa-qr-target').innerHTML = qr.createSvgTag(4);
                        }

                        // Afficher le secret
                        var secretEl = document.getElementById('mfa-secret-text');
                        if (secretEl) secretEl.textContent = json.secret;
                    } else {
                        showAlert('error', json.error || 'Erreur');
                    }
                })
                .catch(function () {
                    showAlert('error', 'Erreur de connexion');
                });
            });
        }

        // Activer MFA
        if (enableForm) {
            enableForm.addEventListener('submit', function (e) {
                e.preventDefault();

                var code = enableForm.querySelector('[name="code"]').value.trim();
                if (!code || code.length !== 6) {
                    showFieldError(enableForm, 'code', 'Code a 6 chiffres requis');
                    return;
                }

                var csrf = container.dataset.csrfToken;

                fetch(container.dataset.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'enable', code: code, csrf_token: csrf })
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.csrf_token) container.dataset.csrfToken = json.csrf_token;

                    if (json.success && json.backup_codes) {
                        // Afficher les codes de secours
                        document.getElementById('mfa-step-verify').style.display = 'none';
                        document.getElementById('mfa-step-backup').style.display = 'block';

                        var list = document.getElementById('backup-codes-list');
                        if (list) {
                            list.innerHTML = '';
                            json.backup_codes.forEach(function (code) {
                                var el = document.createElement('div');
                                el.className = 'backup-codes__code';
                                el.textContent = code;
                                list.appendChild(el);
                            });
                        }
                    } else {
                        handleErrors(enableForm, json);
                    }
                })
                .catch(function () {
                    showAlert('error', 'Erreur de connexion');
                });
            });
        }

        // Desactiver MFA
        if (disableForm) {
            disableForm.addEventListener('submit', function (e) {
                e.preventDefault();

                var password = disableForm.querySelector('[name="password"]').value;
                if (!password) {
                    showFieldError(disableForm, 'password', 'Mot de passe requis');
                    return;
                }

                var csrf = container.dataset.csrfToken;

                fetch(container.dataset.apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'disable', password: password, csrf_token: csrf })
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.csrf_token) container.dataset.csrfToken = json.csrf_token;

                    if (json.success) {
                        window.location.reload();
                    } else {
                        handleErrors(disableForm, json);
                    }
                })
                .catch(function () {
                    showAlert('error', 'Erreur de connexion');
                });
            });
        }
    }

    // =============================================
    // Profile forms
    // =============================================

    function initProfileForms() {
        // Formulaire infos personnelles
        var infoForm = document.getElementById('profile-info-form');
        if (infoForm) {
            infoForm.addEventListener('submit', function (e) {
                e.preventDefault();
                submitForm(infoForm, function (json) {
                    if (json.success) {
                        showAlert('success', json.message || 'Profil mis a jour.');
                    } else {
                        handleErrors(infoForm, json);
                    }
                });
            });
        }

        // Formulaire changement mot de passe
        var pwForm = document.getElementById('profile-password-form');
        if (pwForm) {
            pwForm.addEventListener('submit', function (e) {
                e.preventDefault();

                var current = pwForm.querySelector('[name="current_password"]').value;
                var newPw = pwForm.querySelector('[name="new_password"]').value;
                var confirm = pwForm.querySelector('[name="password_confirm"]').value;

                if (!current) {
                    showFieldError(pwForm, 'current_password', 'Obligatoire');
                    return;
                }
                if (newPw.length < 8) {
                    showFieldError(pwForm, 'new_password', '8 caracteres minimum');
                    return;
                }
                if (newPw !== confirm) {
                    showFieldError(pwForm, 'password_confirm', 'Non identiques');
                    return;
                }

                submitForm(pwForm, function (json) {
                    if (json.success) {
                        showAlert('success', json.message || 'Mot de passe modifie.');
                        pwForm.reset();
                    } else {
                        handleErrors(pwForm, json);
                    }
                });
            });
        }
    }

    // =============================================
    // Verify email (auto-verify si token present)
    // =============================================

    function initVerifyEmail() {
        var container = document.getElementById('verify-email-container');
        if (!container) return;

        var token = container.dataset.token;
        if (!token) return; // Pas de token = mode "check your inbox"

        // Verification automatique
        fetch(container.dataset.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                token: token,
                csrf_token: container.dataset.csrfToken
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (json) {
            var statusEl = document.getElementById('verify-status');
            if (json.success) {
                if (statusEl) {
                    statusEl.className = 'auth-alert auth-alert--success';
                    statusEl.textContent = json.message || 'Email verifie !';
                }
                // Rediriger vers login apres 3s
                setTimeout(function () {
                    window.location.href = container.dataset.siteUrl + '/login';
                }, 3000);
            } else {
                if (statusEl) {
                    statusEl.className = 'auth-alert auth-alert--error';
                    statusEl.textContent = json.error || 'Lien invalide ou expire.';
                }
            }
        })
        .catch(function () {
            var statusEl = document.getElementById('verify-status');
            if (statusEl) {
                statusEl.className = 'auth-alert auth-alert--error';
                statusEl.textContent = 'Erreur de connexion.';
            }
        });

        // Bouton renvoyer
        var resendBtn = document.getElementById('resend-email-btn');
        if (resendBtn) {
            resendBtn.addEventListener('click', function () {
                resendBtn.disabled = true;
                resendBtn.textContent = 'Envoi...';

                fetch(container.dataset.resendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        email: container.dataset.email,
                        csrf_token: container.dataset.csrfToken
                    })
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.csrf_token) container.dataset.csrfToken = json.csrf_token;
                    showAlert('info', json.message || 'Email renvoye.');
                    // Cooldown 60s
                    var seconds = 60;
                    var interval = setInterval(function () {
                        seconds--;
                        resendBtn.textContent = 'Renvoyer (' + seconds + 's)';
                        if (seconds <= 0) {
                            clearInterval(interval);
                            resendBtn.disabled = false;
                            resendBtn.textContent = resendBtn.dataset.text || 'Renvoyer l\'email';
                        }
                    }, 1000);
                })
                .catch(function () {
                    resendBtn.disabled = false;
                    resendBtn.textContent = resendBtn.dataset.text || 'Renvoyer l\'email';
                });
            });
        }
    }

    // =============================================
    // Initialisation
    // =============================================

    document.addEventListener('DOMContentLoaded', function () {
        initPasswordStrength();
        initPasswordToggle();
        initLoginForm();
        initRegisterForm();
        initForgotForm();
        initResetForm();
        initMfaVerifyForm();
        initMfaSetup();
        initProfileForms();
        initVerifyEmail();
    });

})();
