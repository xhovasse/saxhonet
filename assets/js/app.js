/**
 * Saxho.net — Main Application JS
 */

(function () {
    'use strict';

    // --- Header scroll behavior ---
    const header = document.getElementById('site-header');
    let lastScrollY = 0;

    function handleHeaderScroll() {
        const scrollY = window.scrollY;
        if (scrollY > 50) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
        lastScrollY = scrollY;
    }

    window.addEventListener('scroll', handleHeaderScroll, { passive: true });
    handleHeaderScroll();

    // --- Mobile menu ---
    const burgerBtn = document.getElementById('burger-btn');
    const mainNav = document.getElementById('main-nav');

    if (burgerBtn && mainNav) {
        function openMenu() {
            mainNav.classList.add('is-open');
            burgerBtn.classList.add('is-open');
            header.classList.add('nav-is-open');
            burgerBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            mainNav.classList.remove('is-open');
            burgerBtn.classList.remove('is-open');
            header.classList.remove('nav-is-open');
            burgerBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        burgerBtn.addEventListener('click', function () {
            if (mainNav.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        // Close on link click
        mainNav.querySelectorAll('.nav__link').forEach(function (link) {
            link.addEventListener('click', function () {
                closeMenu();
            });
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && mainNav.classList.contains('is-open')) {
                closeMenu();
            }
        });
    }

    // --- Flash message auto-dismiss ---
    document.querySelectorAll('.flash__close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var flash = this.closest('.flash');
            flash.style.opacity = '0';
            flash.style.transform = 'translateX(50px)';
            setTimeout(function () { flash.remove(); }, 300);
        });
    });

    // Auto-dismiss after 5s
    document.querySelectorAll('.flash').forEach(function (flash) {
        setTimeout(function () {
            if (flash.parentNode) {
                flash.style.opacity = '0';
                flash.style.transform = 'translateX(50px)';
                setTimeout(function () { flash.remove(); }, 300);
            }
        }, 5000);
    });

    // --- User menu dropdown ---
    const userMenuTrigger = document.getElementById('user-menu-trigger');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');

    if (userMenuTrigger && userMenuDropdown) {
        userMenuTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = userMenuDropdown.classList.toggle('is-open');
            userMenuTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Click dehors → fermer
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#user-menu')) {
                userMenuDropdown.classList.remove('is-open');
                userMenuTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        // Echap → fermer
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && userMenuDropdown.classList.contains('is-open')) {
                userMenuDropdown.classList.remove('is-open');
                userMenuTrigger.setAttribute('aria-expanded', 'false');
                userMenuTrigger.focus();
            }
        });
    }

    // --- Smooth scroll for anchor links ---
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var targetId = this.getAttribute('href');
            if (targetId === '#') return;
            var target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                var offset = header ? header.offsetHeight : 0;
                var top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });

})();
