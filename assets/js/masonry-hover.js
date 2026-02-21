/**
 * Saxho.net — Masonry Hover Expansion
 * Adds/removes classes on hover for the masonry service card layout.
 * Desktop/tablet: scale expansion with spring animation.
 * Mobile: disabled (no hover on touch).
 */

(function () {
    'use strict';

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var mobileBreakpoint = 768;

    var masonryContainers = document.querySelectorAll('.masonry');
    if (!masonryContainers.length) return;

    function isMobile() {
        return window.innerWidth < mobileBreakpoint;
    }

    for (var i = 0; i < masonryContainers.length; i++) {
        (function (container) {
            var cards = container.querySelectorAll('.masonry__card');
            var leaveTimer = null;

            for (var j = 0; j < cards.length; j++) {
                (function (card) {
                    card.addEventListener('mouseenter', function () {
                        if (isMobile() || prefersReducedMotion) return;
                        clearTimeout(leaveTimer);
                        container.classList.add('masonry--has-active');
                        for (var k = 0; k < cards.length; k++) {
                            cards[k].classList.remove('masonry__card--active');
                        }
                        card.classList.add('masonry__card--active');
                    });

                    card.addEventListener('mouseleave', function () {
                        if (isMobile() || prefersReducedMotion) return;
                        leaveTimer = setTimeout(function () {
                            container.classList.remove('masonry--has-active');
                            for (var k = 0; k < cards.length; k++) {
                                cards[k].classList.remove('masonry__card--active');
                            }
                        }, 80);
                    });
                })(cards[j]);
            }
        })(masonryContainers[i]);
    }

    /* Cleanup on resize if crossing mobile breakpoint */
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (isMobile()) {
                for (var i = 0; i < masonryContainers.length; i++) {
                    masonryContainers[i].classList.remove('masonry--has-active');
                    var active = masonryContainers[i].querySelectorAll('.masonry__card--active');
                    for (var j = 0; j < active.length; j++) {
                        active[j].classList.remove('masonry__card--active');
                    }
                }
            }
        }, 200);
    });

})();
