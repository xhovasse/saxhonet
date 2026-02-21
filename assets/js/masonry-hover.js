/**
 * Saxho.net — Masonry Collage Layout Engine
 * Positionnement absolu avec layouts pre-calcules.
 * Au survol, la carte active grandit et les autres se repositionnent.
 * Mobile : desactive, retour au flux normal.
 */

(function () {
    'use strict';

    var MOBILE_BP = 768;
    var BASE_W = 1100;
    var GAP = 12;

    /* =============================================
       LAYOUTS PRE-CALCULES
       Base 1100px, scale proportionnel au container
       ============================================= */

    /* --- Homepage : cartes avec description --- */
    var LAYOUTS_HOME = {
        restHeight: 440,
        rest: [
            { left: 0,   top: 0,   width: 240, height: 200 },
            { left: 252, top: 15,  width: 320, height: 215 },
            { left: 584, top: 0,   width: 250, height: 195 },
            { left: 846, top: 20,  width: 250, height: 210 },
            { left: 100, top: 228, width: 330, height: 200 }
        ],
        activeHeights: [300, 300, 300, 300, 448],
        active: [
            /* Card 0 active — ancre a gauche */
            [
                { left: 0,   top: 0,   width: 480, height: 290 },
                { left: 492, top: 0,   width: 200, height: 140 },
                { left: 704, top: 0,   width: 190, height: 140 },
                { left: 904, top: 0,   width: 192, height: 140 },
                { left: 492, top: 152, width: 290, height: 138 }
            ],
            /* Card 1 active — centre */
            [
                { left: 0,   top: 0,   width: 185, height: 145 },
                { left: 197, top: 0,   width: 480, height: 290 },
                { left: 689, top: 0,   width: 195, height: 140 },
                { left: 896, top: 0,   width: 200, height: 150 },
                { left: 0,   top: 157, width: 185, height: 133 }
            ],
            /* Card 2 active — droite du centre */
            [
                { left: 0,   top: 0,   width: 190, height: 140 },
                { left: 202, top: 0,   width: 200, height: 150 },
                { left: 414, top: 0,   width: 480, height: 290 },
                { left: 906, top: 0,   width: 190, height: 145 },
                { left: 0,   top: 152, width: 200, height: 138 }
            ],
            /* Card 3 active — a droite */
            [
                { left: 0,   top: 0,   width: 185, height: 140 },
                { left: 197, top: 0,   width: 200, height: 150 },
                { left: 409, top: 0,   width: 195, height: 140 },
                { left: 616, top: 0,   width: 480, height: 290 },
                { left: 0,   top: 152, width: 200, height: 138 }
            ],
            /* Card 4 active — bas centre */
            [
                { left: 0,   top: 0,   width: 180, height: 140 },
                { left: 192, top: 0,   width: 195, height: 145 },
                { left: 399, top: 0,   width: 190, height: 140 },
                { left: 601, top: 0,   width: 185, height: 145 },
                { left: 200, top: 157, width: 480, height: 280 }
            ]
        ]
    };

    /* --- Services page : cartes navigation (pas de description) --- */
    var LAYOUTS_NAV = {
        restHeight: 345,
        rest: [
            { left: 0,   top: 0,   width: 220, height: 160 },
            { left: 232, top: 10,  width: 300, height: 170 },
            { left: 544, top: 0,   width: 230, height: 155 },
            { left: 786, top: 12,  width: 240, height: 165 },
            { left: 80,  top: 180, width: 310, height: 155 }
        ],
        activeHeights: [260, 260, 260, 260, 355],
        active: [
            /* Card 0 active */
            [
                { left: 0,   top: 0,   width: 340, height: 220 },
                { left: 352, top: 0,   width: 180, height: 120 },
                { left: 544, top: 0,   width: 170, height: 115 },
                { left: 726, top: 0,   width: 175, height: 120 },
                { left: 352, top: 132, width: 240, height: 118 }
            ],
            /* Card 1 active */
            [
                { left: 0,   top: 0,   width: 170, height: 120 },
                { left: 182, top: 0,   width: 340, height: 220 },
                { left: 534, top: 0,   width: 175, height: 115 },
                { left: 721, top: 0,   width: 180, height: 120 },
                { left: 0,   top: 132, width: 170, height: 118 }
            ],
            /* Card 2 active */
            [
                { left: 0,   top: 0,   width: 170, height: 115 },
                { left: 182, top: 0,   width: 180, height: 120 },
                { left: 374, top: 0,   width: 340, height: 220 },
                { left: 726, top: 0,   width: 175, height: 120 },
                { left: 0,   top: 127, width: 180, height: 118 }
            ],
            /* Card 3 active */
            [
                { left: 0,   top: 0,   width: 165, height: 115 },
                { left: 177, top: 0,   width: 180, height: 120 },
                { left: 369, top: 0,   width: 170, height: 115 },
                { left: 551, top: 0,   width: 340, height: 220 },
                { left: 0,   top: 127, width: 175, height: 118 }
            ],
            /* Card 4 active */
            [
                { left: 0,   top: 0,   width: 165, height: 115 },
                { left: 177, top: 0,   width: 175, height: 120 },
                { left: 364, top: 0,   width: 170, height: 115 },
                { left: 546, top: 0,   width: 165, height: 120 },
                { left: 160, top: 132, width: 340, height: 210 }
            ]
        ]
    };

    /* =============================================
       MOTEUR DE LAYOUT
       ============================================= */

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function isMobile() {
        return window.innerWidth < MOBILE_BP;
    }

    function applyLayout(container, cards, layouts, state, activeIndex) {
        var containerWidth = container.offsetWidth;
        var scale = Math.min(containerWidth / BASE_W, 1);

        var layout, height;
        if (state === 'rest') {
            layout = layouts.rest;
            height = layouts.restHeight;
        } else {
            layout = layouts.active[activeIndex];
            height = layouts.activeHeights[activeIndex];
        }

        container.style.height = Math.round(height * scale) + 'px';

        for (var i = 0; i < cards.length; i++) {
            var pos = layout[i];
            var card = cards[i];
            card.style.position = 'absolute';
            card.style.left = Math.round(pos.left * scale) + 'px';
            card.style.top = Math.round(pos.top * scale) + 'px';
            card.style.width = Math.round(pos.width * scale) + 'px';
            card.style.height = Math.round(pos.height * scale) + 'px';
        }
    }

    function resetToFlow(container, cards) {
        container.style.height = '';
        container.classList.remove('masonry--has-active');
        for (var i = 0; i < cards.length; i++) {
            cards[i].style.position = '';
            cards[i].style.left = '';
            cards[i].style.top = '';
            cards[i].style.width = '';
            cards[i].style.height = '';
            cards[i].classList.remove('masonry__card--active');
        }
    }

    function setupContainer(container) {
        var cards = container.querySelectorAll('.masonry__card');
        if (cards.length !== 5) return;

        var isNav = container.classList.contains('masonry--nav');
        var layouts = isNav ? LAYOUTS_NAV : LAYOUTS_HOME;
        var leaveTimer = null;
        var activeIndex = -1;

        /* Appliquer le layout rest au chargement */
        if (!isMobile()) {
            applyLayout(container, cards, layouts, 'rest', -1);
        }

        /* Hover sur chaque carte */
        for (var j = 0; j < cards.length; j++) {
            (function (index) {
                cards[index].addEventListener('mouseenter', function () {
                    if (isMobile() || prefersReducedMotion) return;
                    clearTimeout(leaveTimer);
                    activeIndex = index;
                    container.classList.add('masonry--has-active');
                    applyLayout(container, cards, layouts, 'active', index);
                    for (var k = 0; k < cards.length; k++) {
                        cards[k].classList.toggle('masonry__card--active', k === index);
                    }
                });

                cards[index].addEventListener('mouseleave', function () {
                    if (isMobile() || prefersReducedMotion) return;
                    leaveTimer = setTimeout(function () {
                        activeIndex = -1;
                        container.classList.remove('masonry--has-active');
                        applyLayout(container, cards, layouts, 'rest', -1);
                        for (var k = 0; k < cards.length; k++) {
                            cards[k].classList.remove('masonry__card--active');
                        }
                    }, 100);
                });
            })(j);
        }

        /* Resize : recalculer ou reset */
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (isMobile()) {
                    resetToFlow(container, cards);
                } else {
                    applyLayout(
                        container, cards, layouts,
                        activeIndex >= 0 ? 'active' : 'rest',
                        activeIndex
                    );
                }
            }, 200);
        });
    }

    /* =============================================
       INITIALISATION
       ============================================= */

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
