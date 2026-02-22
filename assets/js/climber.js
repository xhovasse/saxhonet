/* ============================================
   Saxho.net — Climber Scene
   Interactive SVG: hover holds to animate climber pose
   ============================================ */
(function () {
    'use strict';

    var scene = document.getElementById('climber-scene');
    if (!scene) return;

    var MOBILE_BP = 768;
    var POSE_COUNT = 5;
    var LEAVE_DELAY = 350;
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var activeService = 0;
    var leaveTimer = null;

    var svg = scene.querySelector('.climber-scene__svg');
    if (!svg) return;

    // Cache poses (0 = default, 1-5 = services)
    var poses = [];
    var holds = [];
    var labels = [];

    for (var i = 0; i <= POSE_COUNT; i++) {
        var pose = svg.querySelector('.climber__pose--' + i);
        if (pose) poses.push(pose); else poses.push(null);
    }

    for (var j = 1; j <= POSE_COUNT; j++) {
        var hold = svg.querySelector('.climber__hold--' + j);
        var label = scene.querySelector('.climber-label--' + j);
        holds.push(hold);
        labels.push(label);
    }

    function isMobile() {
        return window.innerWidth < MOBILE_BP;
    }

    function activateService(index) {
        if (index === activeService) return;
        activeService = index;

        // Crossfade poses
        for (var p = 0; p < poses.length; p++) {
            if (poses[p]) {
                if (prefersReduced) {
                    poses[p].style.opacity = (p === index) ? '1' : '0';
                } else {
                    poses[p].style.opacity = (p === index) ? '1' : '0';
                }
            }
        }

        // Toggle scene active state
        if (index > 0) {
            scene.classList.add('climber-scene--has-active');
        } else {
            scene.classList.remove('climber-scene--has-active');
        }

        // Update holds
        for (var h = 0; h < holds.length; h++) {
            if (holds[h]) {
                if (h + 1 === index) {
                    holds[h].classList.add('climber__hold--active');
                } else {
                    holds[h].classList.remove('climber__hold--active');
                }
            }
        }

        // Update labels
        for (var l = 0; l < labels.length; l++) {
            if (labels[l]) {
                if (l + 1 === index) {
                    labels[l].classList.add('climber-label--active');
                } else {
                    labels[l].classList.remove('climber-label--active');
                }
            }
        }
    }

    function handleEnter(serviceIndex) {
        if (isMobile()) return;
        clearTimeout(leaveTimer);
        activateService(serviceIndex);
    }

    function handleLeave() {
        if (isMobile()) return;
        clearTimeout(leaveTimer);
        leaveTimer = setTimeout(function () {
            activateService(0);
        }, LEAVE_DELAY);
    }

    function handleClick() {
        var url = scene.getAttribute('data-services-url') || '/services';
        window.location.href = url;
    }

    // Bind events — holds (SVG)
    for (var hh = 0; hh < holds.length; hh++) {
        (function (idx) {
            if (!holds[idx]) return;
            holds[idx].addEventListener('mouseenter', function () { handleEnter(idx + 1); });
            holds[idx].addEventListener('mouseleave', handleLeave);
            holds[idx].addEventListener('click', handleClick);
        })(hh);
    }

    // Bind events — labels (HTML)
    for (var ll = 0; ll < labels.length; ll++) {
        (function (idx) {
            if (!labels[idx]) return;
            labels[idx].addEventListener('mouseenter', function () { handleEnter(idx + 1); });
            labels[idx].addEventListener('mouseleave', handleLeave);
            labels[idx].addEventListener('click', handleClick);
        })(ll);
    }

    // Touch support: tap to toggle
    scene.addEventListener('touchstart', function (e) {
        var target = e.target;
        var holdGroup = target.closest ? target.closest('.climber__hold') : null;
        var labelDiv = target.closest ? target.closest('.climber-label') : null;

        if (holdGroup) {
            var holdIdx = parseInt(holdGroup.getAttribute('data-service'), 10);
            if (activeService === holdIdx) {
                activateService(0);
            } else {
                activateService(holdIdx);
            }
            e.preventDefault();
        } else if (labelDiv) {
            var labelIdx = parseInt(labelDiv.getAttribute('data-service'), 10);
            if (activeService === labelIdx) {
                activateService(0);
            } else {
                activateService(labelIdx);
            }
            e.preventDefault();
        } else {
            activateService(0);
        }
    }, { passive: false });

})();
