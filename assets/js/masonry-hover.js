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
    /* Imbrication serree, chevauchements legers aux bords */
    var LAYOUTS_HOME = {
        restHeight: 410,
        rest: [
            { left: 0,   top: 0,   width: 255, height: 195 },
            { left: 245, top: 20,  width: 310, height: 210 },
            { left: 548, top: 5,   width: 265, height: 188 },
            { left: 800, top: 25,  width: 260, height: 200 },
            { left: 70,  top: 200, width: 340, height: 195 }
        ],
        activeHeights: [310, 310, 310, 310, 440],
        active: [
            /* Card 0 active — grande a gauche, petites imbriquees a droite */
            [
                { left: 0,   top: 0,   width: 500, height: 300 },
                { left: 488, top: 5,   width: 195, height: 142 },
                { left: 672, top: 0,   width: 205, height: 148 },
                { left: 868, top: 8,   width: 195, height: 138 },
                { left: 500, top: 140, width: 280, height: 148 }
            ],
            /* Card 1 active — grande au centre, petites tassees autour */
            [
                { left: 0,   top: 5,   width: 190, height: 148 },
                { left: 178, top: 0,   width: 500, height: 300 },
                { left: 668, top: 8,   width: 200, height: 138 },
                { left: 858, top: 0,   width: 205, height: 148 },
                { left: 0,   top: 145, width: 188, height: 148 }
            ],
            /* Card 2 active — grande droite-centre, cluster a gauche */
            [
                { left: 0,   top: 0,   width: 195, height: 145 },
                { left: 185, top: 8,   width: 210, height: 148 },
                { left: 385, top: 0,   width: 500, height: 300 },
                { left: 875, top: 5,   width: 195, height: 145 },
                { left: 0,   top: 138, width: 210, height: 152 }
            ],
            /* Card 3 active — grande a droite, petites empilees a gauche */
            [
                { left: 0,   top: 0,   width: 195, height: 140 },
                { left: 185, top: 8,   width: 205, height: 148 },
                { left: 382, top: 0,   width: 198, height: 140 },
                { left: 570, top: 0,   width: 500, height: 300 },
                { left: 0,   top: 132, width: 210, height: 155 }
            ],
            /* Card 4 active — grande en bas, petites rangee du haut */
            [
                { left: 0,   top: 0,   width: 185, height: 140 },
                { left: 175, top: 8,   width: 205, height: 145 },
                { left: 370, top: 0,   width: 198, height: 140 },
                { left: 558, top: 6,   width: 195, height: 142 },
                { left: 155, top: 142, width: 500, height: 288 }
            ]
        ]
    };

    /* Toutes les mosaiques utilisent le meme layout (LAYOUTS_HOME) */

    /* =============================================
       MOTEUR DE LAYOUT
       ============================================= */

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function isMobile() {
        return window.innerWidth < MOBILE_BP;
    }

    /* =============================================
       PROFONDEUR DE CHAMP — niveaux gradues
       Distance geometrique → blur + scale + opacite
       ============================================= */

    function cardCenter(pos) {
        return { x: pos.left + pos.width / 2, y: pos.top + pos.height / 2 };
    }

    function dist(a, b) {
        var dx = a.x - b.x;
        var dy = a.y - b.y;
        return Math.sqrt(dx * dx + dy * dy);
    }

    /* Niveaux de profondeur par rang de distance.
       Plan 0 = carte active (nette, premier plan)
       Plan 1 = carte la plus proche (leger flou)
       Plan 2 = cartes intermediaires
       Plan 3 = carte la plus eloignee (flou maximum) */
    var DEPTH_LEVELS = [
        /* Plan 0 — Active : nette */
        { blur: 0,   scale: 1.03, opacity: 1,    brightness: 1,    saturate: 1   },
        /* Plan 1 — Proche : leger retrait */
        { blur: 1.0, scale: 0.97, opacity: 0.85, brightness: 0.92, saturate: 0.9 },
        /* Plan 2 — Moyen : en retrait */
        { blur: 2.5, scale: 0.93, opacity: 0.65, brightness: 0.80, saturate: 0.7 },
        /* Plan 3 — Loin : arriere-plan */
        { blur: 4.0, scale: 0.88, opacity: 0.45, brightness: 0.65, saturate: 0.5 }
    ];

    function applyDepth(cards, layout, activeIndex) {
        if (activeIndex < 0) {
            /* Repos : tout net, pas de profondeur */
            for (var i = 0; i < cards.length; i++) {
                cards[i].style.transform = '';
                cards[i].style.filter = '';
                cards[i].style.opacity = '';
            }
            return;
        }

        var activeC = cardCenter(layout[activeIndex]);

        /* Collecter les indices et distances des cartes inactives */
        var others = [];
        for (var j = 0; j < cards.length; j++) {
            if (j !== activeIndex) {
                others.push({
                    index: j,
                    dist: dist(cardCenter(layout[j]), activeC)
                });
            }
        }

        /* Trier par distance croissante → rang = plan de profondeur */
        others.sort(function (a, b) { return a.dist - b.dist; });

        /* Assigner les plans : rang 0 → plan 1, rang 1-2 → plan 2, rang 3 → plan 3 */
        var planMap = {};
        for (var r = 0; r < others.length; r++) {
            if (r === 0) {
                planMap[others[r].index] = 1; /* le plus proche */
            } else if (r === others.length - 1) {
                planMap[others[r].index] = 3; /* le plus loin */
            } else {
                planMap[others[r].index] = 2; /* intermediaire */
            }
        }

        /* Appliquer les styles par plan */
        for (var k = 0; k < cards.length; k++) {
            var plan = (k === activeIndex) ? 0 : planMap[k];
            var lvl = DEPTH_LEVELS[plan];

            cards[k].style.transform = 'scale(' + lvl.scale.toFixed(3) + ')';
            cards[k].style.filter =
                'blur(' + lvl.blur.toFixed(1) + 'px)' +
                ' brightness(' + lvl.brightness.toFixed(2) + ')' +
                ' saturate(' + lvl.saturate.toFixed(1) + ')';
            cards[k].style.opacity = lvl.opacity.toFixed(2);
        }
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

        /* Profondeur de champ graduee */
        applyDepth(cards, layout, activeIndex);
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
            cards[i].style.transform = '';
            cards[i].style.filter = '';
            cards[i].style.opacity = '';
            cards[i].classList.remove('masonry__card--active');
        }
    }

    var DEFAULT_ACTIVE = 1; /* Carte active par defaut au chargement (0-indexed) */

    function setupContainer(container) {
        var cards = container.querySelectorAll('.masonry__card');
        if (cards.length !== 5) return;

        var layouts = LAYOUTS_HOME;
        var leaveTimer = null;
        var activeIndex = DEFAULT_ACTIVE;

        /* Au chargement : demarrer avec une carte en focus (profondeur active) */
        if (!isMobile() && !prefersReducedMotion) {
            container.classList.add('masonry--has-active');
            applyLayout(container, cards, layouts, 'active', activeIndex);
            for (var m = 0; m < cards.length; m++) {
                cards[m].classList.toggle('masonry__card--active', m === activeIndex);
            }
        } else if (!isMobile()) {
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
                        /* Au lieu de revenir au repos, revenir a la carte par defaut */
                        activeIndex = DEFAULT_ACTIVE;
                        applyLayout(container, cards, layouts, 'active', activeIndex);
                        for (var k = 0; k < cards.length; k++) {
                            cards[k].classList.toggle('masonry__card--active', k === activeIndex);
                        }
                    }, 300);
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
                    /* Toujours en mode actif (carte par defaut ou survolee) */
                    applyLayout(container, cards, layouts, 'active', activeIndex);
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
