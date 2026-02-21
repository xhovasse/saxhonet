/**
 * Saxho.net — Masonry Card Stack Engine
 * Metaphore : jeu de cartes en eventail.
 * Les cartes se chevauchent physiquement (z-index), un overlay gradient
 * assombrit les cartes a l'arriere-plan. Au survol, la carte "monte"
 * progressivement vers l'avant en grandissant.
 * Mobile : desactive, retour au flux normal.
 */

(function () {
    'use strict';

    var MOBILE_BP = 768;
    var BASE_W = 1100;

    /* =============================================
       LAYOUTS PRE-CALCULES
       Base 1100px, scale proportionnel au container.
       Les cartes inactives se chevauchent — comme un
       jeu de cartes qu'on est en train de ranger.
       ============================================= */

    var LAYOUTS_HOME = {
        restHeight: 340,
        rest: [
            { left: 20,  top: 15,  width: 240, height: 190 },
            { left: 210, top: 0,   width: 290, height: 210 },
            { left: 460, top: 10,  width: 260, height: 195 },
            { left: 680, top: 5,   width: 250, height: 200 },
            { left: 100, top: 170, width: 280, height: 165 }
        ],
        activeHeights: [320, 320, 320, 320, 440],
        active: [
            /* Card 0 active — grande a gauche, empilage a droite */
            [
                { left: 0,   top: 0,   width: 520, height: 310 },
                { left: 460, top: 15,  width: 195, height: 150 },
                { left: 610, top: 5,   width: 210, height: 155 },
                { left: 820, top: 20,  width: 185, height: 145 },
                { left: 490, top: 155, width: 220, height: 150 }
            ],
            /* Card 1 active — grande centre-gauche, cartes empilees autour */
            [
                { left: 20,  top: 25,  width: 185, height: 150 },
                { left: 160, top: 0,   width: 520, height: 310 },
                { left: 630, top: 15,  width: 195, height: 145 },
                { left: 800, top: 5,   width: 200, height: 150 },
                { left: 30,  top: 160, width: 180, height: 150 }
            ],
            /* Card 2 active — grande au centre, cartes flanquantes */
            [
                { left: 10,  top: 10,  width: 190, height: 150 },
                { left: 155, top: 20,  width: 200, height: 145 },
                { left: 310, top: 0,   width: 520, height: 310 },
                { left: 790, top: 15,  width: 195, height: 148 },
                { left: 30,  top: 155, width: 195, height: 150 }
            ],
            /* Card 3 active — grande a droite, empilage a gauche */
            [
                { left: 5,   top: 10,  width: 185, height: 145 },
                { left: 150, top: 20,  width: 195, height: 150 },
                { left: 305, top: 5,   width: 200, height: 148 },
                { left: 460, top: 0,   width: 520, height: 310 },
                { left: 20,  top: 150, width: 190, height: 155 }
            ],
            /* Card 4 active — grande en bas-centre, petites rangee du haut */
            [
                { left: 10,  top: 5,   width: 185, height: 140 },
                { left: 160, top: 15,  width: 195, height: 138 },
                { left: 320, top: 5,   width: 190, height: 140 },
                { left: 475, top: 15,  width: 185, height: 138 },
                { left: 130, top: 145, width: 520, height: 285 }
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

    /* =============================================
       EMPILEMENT — 3 plans de profondeur
       Pas de blur. Z-index pour le masquage physique,
       overlay gradient sombre pour le recul visuel.
       ============================================= */

    function cardCenter(pos) {
        return { x: pos.left + pos.width / 2, y: pos.top + pos.height / 2 };
    }

    function dist(a, b) {
        var dx = a.x - b.x;
        var dy = a.y - b.y;
        return Math.sqrt(dx * dx + dy * dy);
    }

    /* 3 plans : avant (actif), milieu (2 proches), arriere (2 loin) */
    var DEPTH_LEVELS = [
        /* Plan 0 — Avant : carte active, premier plan */
        { zIndex: 10, overlay: 0,    scale: 1.02 },
        /* Plan 1 — Milieu : legerement assombri */
        { zIndex: 5,  overlay: 0.30, scale: 1    },
        /* Plan 2 — Arriere : nettement assombri */
        { zIndex: 2,  overlay: 0.55, scale: 1    }
    ];

    function applyDepth(cards, layout, activeIndex) {
        if (activeIndex < 0) {
            /* Repos : tout a plat, pas d'overlay */
            for (var i = 0; i < cards.length; i++) {
                cards[i].style.zIndex = '';
                cards[i].style.transform = '';
                cards[i].style.setProperty('--depth-overlay', '0');
            }
            return;
        }

        var activeC = cardCenter(layout[activeIndex]);

        /* Collecter distances des cartes inactives */
        var others = [];
        for (var j = 0; j < cards.length; j++) {
            if (j !== activeIndex) {
                others.push({
                    index: j,
                    dist: dist(cardCenter(layout[j]), activeC)
                });
            }
        }

        /* Trier par distance croissante */
        others.sort(function (a, b) { return a.dist - b.dist; });

        /* 2 plus proches → plan 1 (milieu), 2 plus loin → plan 2 (arriere) */
        var planMap = {};
        for (var r = 0; r < others.length; r++) {
            planMap[others[r].index] = (r < 2) ? 1 : 2;
        }

        /* Appliquer z-index, overlay et scale */
        for (var k = 0; k < cards.length; k++) {
            var plan = (k === activeIndex) ? 0 : planMap[k];
            var lvl = DEPTH_LEVELS[plan];

            cards[k].style.zIndex = lvl.zIndex;
            cards[k].style.setProperty('--depth-overlay', lvl.overlay.toFixed(2));

            if (plan === 0) {
                cards[k].style.transform = 'scale(' + lvl.scale.toFixed(3) + ')';
            } else {
                cards[k].style.transform = '';
            }
        }
    }

    /* Layout complet : positions + empilement dans le meme frame.
       La carte qui avance grandit (width/height) ET passe au-dessus
       (z-index) en meme temps — comme tirer une carte du jeu. */
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

        /* Empilement (z-index + overlay) */
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
            cards[i].style.zIndex = '';
            cards[i].style.setProperty('--depth-overlay', '0');
            /* Nettoyer les anciennes proprietes qui pourraient trainer */
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

        /* Au chargement : poser l'etat initial "a plat" (pas d'overlay),
           puis animer vers l'empilement pour une entree visuelle douce */
        if (!isMobile() && !prefersReducedMotion) {
            /* Etape 1 : poser positions SANS transitions, overlay a 0 */
            for (var m = 0; m < cards.length; m++) {
                cards[m].style.transition = 'none';
                cards[m].style.setProperty('--depth-overlay', '0');
            }
            container.classList.add('masonry--has-active');
            var initLayout = layouts.active[activeIndex];
            var initScale = Math.min(container.offsetWidth / BASE_W, 1);
            container.style.height = Math.round(layouts.activeHeights[activeIndex] * initScale) + 'px';
            for (var m2 = 0; m2 < cards.length; m2++) {
                var initPos = initLayout[m2];
                cards[m2].style.position = 'absolute';
                cards[m2].style.left = Math.round(initPos.left * initScale) + 'px';
                cards[m2].style.top = Math.round(initPos.top * initScale) + 'px';
                cards[m2].style.width = Math.round(initPos.width * initScale) + 'px';
                cards[m2].style.height = Math.round(initPos.height * initScale) + 'px';
                cards[m2].classList.toggle('masonry__card--active', m2 === activeIndex);
            }

            /* Forcer le repaint */
            container.offsetHeight; /* eslint-disable-line no-unused-expressions */

            /* Etape 2 : reactiver les transitions et appliquer l'empilement.
               Les overlays s'animent de 0 → valeur cible (600ms ease via CSS). */
            requestAnimationFrame(function () {
                for (var n = 0; n < cards.length; n++) {
                    cards[n].style.transition = '';
                }
                applyDepth(cards, initLayout, activeIndex);
            });

        } else if (!isMobile()) {
            applyLayout(container, cards, layouts, 'rest', -1);
        }

        /* Hover : positions + tailles + empilement changent ensemble.
           La carte survolee "monte" vers l'avant en grandissant. */
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
                        activeIndex = DEFAULT_ACTIVE;
                        applyLayout(container, cards, layouts, 'active', activeIndex);
                        for (var k = 0; k < cards.length; k++) {
                            cards[k].classList.toggle('masonry__card--active', k === activeIndex);
                        }
                    }, 300);
                });
            })(j);
        }

        /* Resize */
        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (isMobile()) {
                    resetToFlow(container, cards);
                } else {
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
