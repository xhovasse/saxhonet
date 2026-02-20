/**
 * Saxho.net — Animations (Scroll Reveal, Parallax, Tilt)
 */

(function () {
    'use strict';

    // Check for reduced motion preference
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        // Force visibility of all reveal elements even with reduced motion
        document.querySelectorAll('.reveal, .reveal-fade, .reveal-up, .reveal-down, .reveal-left, .reveal-right, .reveal-scale').forEach(function (el) {
            el.classList.add('is-visible');
        });
        return;
    }

    // --- Scroll Reveal (IntersectionObserver) ---
    var revealElements = document.querySelectorAll('.reveal, .reveal-fade, .reveal-up, .reveal-down, .reveal-left, .reveal-right, .reveal-scale');

    if (revealElements.length > 0 && 'IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(function (el) {
            revealObserver.observe(el);
        });
    } else {
        // Fallback: show everything
        revealElements.forEach(function (el) {
            el.classList.add('is-visible');
        });
    }

    // --- Parallax on scroll ---
    var parallaxElements = document.querySelectorAll('[data-parallax]');

    if (parallaxElements.length > 0) {
        var ticking = false;

        function updateParallax() {
            var scrollY = window.scrollY;
            parallaxElements.forEach(function (el) {
                var speed = parseFloat(el.getAttribute('data-parallax')) || 0.3;
                var rect = el.getBoundingClientRect();
                var centerY = rect.top + rect.height / 2;
                var offset = (centerY - window.innerHeight / 2) * speed;
                el.style.transform = 'translateY(' + offset + 'px)';
            });
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });
    }

    // --- 3D Tilt cards ---
    var tiltCards = document.querySelectorAll('.tilt-card');

    tiltCards.forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var centerX = rect.width / 2;
            var centerY = rect.height / 2;

            var rotateX = ((y - centerY) / centerY) * -8;
            var rotateY = ((x - centerX) / centerX) * 8;

            card.style.transform = 'perspective(1000px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)';
        });

        card.addEventListener('mouseleave', function () {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
            card.style.transition = 'transform 0.5s ease';
        });

        card.addEventListener('mouseenter', function () {
            card.style.transition = 'transform 0.1s ease';
        });
    });

    // --- Counter animation (scroll-triggered) ---
    var counters = document.querySelectorAll('[data-count-to]');

    if (counters.length > 0 && 'IntersectionObserver' in window) {
        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function (counter) {
            counterObserver.observe(counter);
        });
    }

    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-count-to'), 10);
        var duration = parseInt(el.getAttribute('data-count-duration'), 10) || 2000;
        var start = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
            el.textContent = Math.floor(eased * target);
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target;
            }
        }

        requestAnimationFrame(step);
    }

})();
