/**
 * Saxho.net — Ondes legeres (Services page background)
 * Des ondulations concentriques emanent de la souris
 * comme des ondes sur de l'eau calme
 */

(function () {
    'use strict';

    var canvas = document.getElementById('flow-canvas');
    if (!canvas) return;

    // Respecter prefers-reduced-motion
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        canvas.style.display = 'none';
        return;
    }

    var ctx = canvas.getContext('2d');
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var raf;
    var w = 0, h = 0;

    // --- Configuration ---
    var CONFIG = {
        maxWaves: 30,
        waveSpeed: 1.2,
        waveMaxRadius: 300,
        waveInterval: 180,
        waveLineWidth: 1.5,
        waveBaseOpacity: 0.20,

        // 2 couleurs en alternance (bien visibles sur fond clair)
        waveColors: [
            { r: 27,  g: 58,  b: 158 },  // Bleu profond (--c-primary)
            { r: 166, g: 61,  b: 107 }   // Rose berry   (--c-accent)
        ],

        // Ondes ambiantes
        ambientInterval: 2500,
        ambientMaxRadius: 220,
        ambientBaseOpacity: 0.12,
        ambientSpeed: 0.8
    };

    // --- State ---
    var waves = [];
    var mouse = { x: -1000, y: -1000 };
    var lastWaveTime = 0;
    var lastMouseX = -1000;
    var lastMouseY = -1000;
    var colorIndex = 0;

    // --- Resize ---
    function resize() {
        var rect = canvas.parentElement.getBoundingClientRect();
        w = rect.width;
        h = window.innerHeight;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    // --- Couleur suivante (alternance cyclique) ---
    function nextColor() {
        var c = CONFIG.waveColors[colorIndex];
        colorIndex = (colorIndex + 1) % CONFIG.waveColors.length;
        return c;
    }

    // --- Creer une onde ---
    function createWave(x, y, isAmbient) {
        if (waves.length >= CONFIG.maxWaves) return;

        waves.push({
            x: x,
            y: y,
            radius: 0,
            maxRadius: isAmbient ? CONFIG.ambientMaxRadius : CONFIG.waveMaxRadius,
            baseOpacity: isAmbient ? CONFIG.ambientBaseOpacity : CONFIG.waveBaseOpacity,
            speed: isAmbient ? CONFIG.ambientSpeed : CONFIG.waveSpeed,
            color: nextColor()
        });
    }

    // --- Onde ambiante (position aleatoire) ---
    function launchAmbientWave() {
        createWave(
            Math.random() * w,
            Math.random() * h,
            true
        );
    }

    // --- Animation ---
    function animate() {
        ctx.clearRect(0, 0, w, h);

        // Update & draw waves
        for (var i = waves.length - 1; i >= 0; i--) {
            var wave = waves[i];

            // Expand
            wave.radius += wave.speed;

            // Remove if done
            if (wave.radius >= wave.maxRadius) {
                waves.splice(i, 1);
                continue;
            }

            // Calculer opacite : smooth fade out
            var ratio = wave.radius / wave.maxRadius;
            // Ease out cubic pour un fade plus naturel
            var fade = 1 - ratio * ratio;
            var opacity = wave.baseOpacity * fade;

            if (opacity < 0.003) continue;

            // Dessiner le cercle
            var c = wave.color;
            ctx.beginPath();
            ctx.arc(wave.x, wave.y, wave.radius, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + opacity.toFixed(4) + ')';
            ctx.lineWidth = CONFIG.waveLineWidth;
            ctx.stroke();
        }

        raf = requestAnimationFrame(animate);
    }

    // --- Mouse tracking ---
    document.addEventListener('mousemove', function (e) {
        mouse.x = e.clientX;
        mouse.y = e.clientY;

        // Creer une onde si assez de temps ecoule et si la souris a bouge
        var now = Date.now();
        var dx = mouse.x - lastMouseX;
        var dy = mouse.y - lastMouseY;
        var moved = Math.sqrt(dx * dx + dy * dy);

        if (now - lastWaveTime > CONFIG.waveInterval && moved > 5) {
            createWave(mouse.x, mouse.y, false);
            lastWaveTime = now;
            lastMouseX = mouse.x;
            lastMouseY = mouse.y;
        }
    });

    document.addEventListener('mouseleave', function () {
        mouse.x = -1000;
        mouse.y = -1000;
    });

    // --- Init ---
    resize();
    animate();

    // Ondes ambiantes periodiques
    setInterval(launchAmbientWave, CONFIG.ambientInterval);
    // Quelques ondes au demarrage
    setTimeout(function () { launchAmbientWave(); }, 500);
    setTimeout(function () { launchAmbientWave(); }, 1200);
    setTimeout(function () { launchAmbientWave(); }, 2000);

    // Resize handler
    var resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(resize, 250);
    });

    window.addEventListener('beforeunload', function () {
        cancelAnimationFrame(raf);
    });

})();
