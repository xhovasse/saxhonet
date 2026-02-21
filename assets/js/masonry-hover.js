/**
 * Saxho.net — Masonry Hover Highlight
 * Grille de 5 cartes egales. Au survol d'une carte, les autres
 * s'estompent legerement pour mettre en exergue la carte active.
 * Tout le visuel est en CSS — le JS ne fait que toggler des classes.
 * Mobile : desactive.
 */

(function () {
    'use strict';

    var MOBILE_BP = 768;
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function isMobile() {
        return window.innerWidth < MOBILE_BP;
    }

    function setupContainer(container) {
        var cards = container.querySelectorAll('.masonry__card');
        if (!cards.length) return;

        var leaveTimer = null;

        for (var j = 0; j < cards.length; j++) {
            (function (index) {
                cards[index].addEventListener('mouseenter', function () {
                    if (isMobile() || prefersReducedMotion) return;
                    clearTimeout(leaveTimer);
                    container.classList.add('masonry--has-hover');
                    for (var k = 0; k < cards.length; k++) {
                        cards[k].classList.toggle('masonry__card--active', k === index);
                    }
                });

                cards[index].addEventListener('mouseleave', function () {
                    if (isMobile() || prefersReducedMotion) return;
                    leaveTimer = setTimeout(function () {
                        container.classList.remove('masonry--has-hover');
                        for (var k = 0; k < cards.length; k++) {
                            cards[k].classList.remove('masonry__card--active');
                        }
                    }, 200);
                });
            })(j);
        }
    }

    function init() {
        var containers = document.querySelectorAll('.masonry');
        for (var i = 0; i < containers.length; i++) {
            setupContainer(containers[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
