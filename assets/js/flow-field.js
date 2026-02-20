/**
 * Saxho.net — Flow Field Background Animation
 * Champ de vecteurs / flux de vent pour la page Services
 * Les particules-traits suivent un champ vectoriel et s'orientent vers la souris
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
    var time = 0;

    // --- Configuration ---
    var CONFIG = {
        cellSize: 20,
        particleSpeed: 1.5,
        particleMaxAge: 100,
        noiseScale: 0.005,
        noiseSpeed: 0.0003,
        mouseInfluence: 0.35,
        mouseRadius: 350,
        fadeAlpha: 0.032,
        lineWidth: 1,
        // Couleurs avec poids
        colors: [
            { r: 27,  g: 58,  b: 158, a: 0.45, weight: 0.90 },  // Bleu primaire
            { r: 58,  g: 125, b: 255, a: 0.35, weight: 0.08 },  // Bleu clair
            { r: 166, g: 61,  b: 107, a: 0.30, weight: 0.02 }   // Rose accent
        ]
    };

    // --- State ---
    var cols, rows;
    var field = [];       // Grille d'angles
    var particles = [];
    var particleCount = 300;
    var mouse = { x: -1000, y: -1000 };
    var canvasRect = { left: 0, top: 0 };
    var w = 0, h = 0;

    // Couleur fond pour le fade (--c-light = #F8F7F4)
    var bgR = 248, bgG = 247, bgB = 244;

    // --- Bruit simplifie (sans librairie) ---
    function noise(x, y, t) {
        return Math.sin(x * 0.8 + t) * Math.cos(y * 0.6 + t * 0.7)
             + Math.sin((x + y) * 0.5 - t * 0.4) * 0.5
             + Math.cos(x * 0.3 - y * 0.4 + t * 0.6) * 0.3;
    }

    // --- Interpolation lineaire ---
    function lerp(a, b, t) {
        return a + (b - a) * t;
    }

    // --- Choisir une couleur selon les poids ---
    function pickColor() {
        var r = Math.random();
        var cumul = 0;
        for (var i = 0; i < CONFIG.colors.length; i++) {
            cumul += CONFIG.colors[i].weight;
            if (r <= cumul) return CONFIG.colors[i];
        }
        return CONFIG.colors[0];
    }

    // --- Resize ---
    function resize() {
        var rect = canvas.parentElement.getBoundingClientRect();
        w = rect.width;
        h = window.innerHeight; // Fixed canvas = viewport height
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        cols = Math.ceil(w / CONFIG.cellSize) + 1;
        rows = Math.ceil(h / CONFIG.cellSize) + 1;

        // Adjust particle count
        if (w < 480) {
            particleCount = 80;
        } else if (w < 768) {
            particleCount = 150;
        } else {
            particleCount = 300;
        }

        updateCanvasRect();
        initField();
        initParticles();

        // Clear canvas completely on resize
        ctx.fillStyle = 'rgb(' + bgR + ',' + bgG + ',' + bgB + ')';
        ctx.fillRect(0, 0, w, h);
    }

    function updateCanvasRect() {
        var rect = canvas.getBoundingClientRect();
        canvasRect.left = rect.left;
        canvasRect.top = rect.top;
    }

    // --- Init flow field grid ---
    function initField() {
        field = new Array(cols * rows);
        for (var i = 0; i < field.length; i++) {
            field[i] = 0;
        }
    }

    // --- Update flow field ---
    function updateField() {
        var TWO_PI = Math.PI * 2;
        var scale = CONFIG.noiseScale;
        var hasMouse = mouse.x > -500;
        var mr = CONFIG.mouseRadius;
        var mrSq = mr * mr;
        var influence = CONFIG.mouseInfluence;

        for (var row = 0; row < rows; row++) {
            for (var col = 0; col < cols; col++) {
                var idx = row * cols + col;
                var px = col * CONFIG.cellSize;
                var py = row * CONFIG.cellSize;

                // Angle de base via bruit
                var n = noise(col * scale * 100, row * scale * 100, time);
                var baseAngle = n * TWO_PI;

                // Influence de la souris
                if (hasMouse) {
                    var dx = mouse.x - px;
                    var dy = mouse.y - py;
                    var distSq = dx * dx + dy * dy;

                    if (distSq < mrSq) {
                        var dist = Math.sqrt(distSq);
                        var proximity = 1 - dist / mr;
                        // Easing smooth
                        proximity = proximity * proximity;
                        var mouseAngle = Math.atan2(dy, dx);
                        baseAngle = lerp(baseAngle, mouseAngle, influence * proximity);
                    }
                }

                field[idx] = baseAngle;
            }
        }
    }

    // --- Init particles ---
    function initParticles() {
        particles = [];
        for (var i = 0; i < particleCount; i++) {
            particles.push(createParticle());
        }
    }

    function createParticle() {
        var color = pickColor();
        return {
            x: Math.random() * w,
            y: Math.random() * h,
            prevX: 0,
            prevY: 0,
            age: Math.floor(Math.random() * CONFIG.particleMaxAge),
            maxAge: CONFIG.particleMaxAge + Math.floor(Math.random() * 40) - 20,
            speed: CONFIG.particleSpeed + (Math.random() - 0.5) * 0.6,
            color: 'rgba(' + color.r + ',' + color.g + ',' + color.b + ',' + color.a + ')'
        };
    }

    function resetParticle(p) {
        p.x = Math.random() * w;
        p.y = Math.random() * h;
        p.prevX = p.x;
        p.prevY = p.y;
        p.age = 0;
        p.maxAge = CONFIG.particleMaxAge + Math.floor(Math.random() * 40) - 20;
        var color = pickColor();
        p.color = 'rgba(' + color.r + ',' + color.g + ',' + color.b + ',' + color.a + ')';
    }

    // --- Update & Draw ---
    function update() {
        time += CONFIG.noiseSpeed;
        updateField();

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];

            // Sauvegarder position precedente
            p.prevX = p.x;
            p.prevY = p.y;

            // Trouver la cellule
            var col = Math.floor(p.x / CONFIG.cellSize);
            var row = Math.floor(p.y / CONFIG.cellSize);

            // Clamp
            if (col < 0) col = 0;
            if (col >= cols) col = cols - 1;
            if (row < 0) row = 0;
            if (row >= rows) row = rows - 1;

            var angle = field[row * cols + col];

            // Avancer
            p.x += Math.cos(angle) * p.speed;
            p.y += Math.sin(angle) * p.speed;
            p.age++;

            // Reset si hors canvas ou trop vieux
            if (p.x < -10 || p.x > w + 10 || p.y < -10 || p.y > h + 10 || p.age > p.maxAge) {
                resetParticle(p);
            }
        }
    }

    function draw() {
        // Fade partiel (effet de trainee)
        ctx.fillStyle = 'rgba(' + bgR + ',' + bgG + ',' + bgB + ',' + CONFIG.fadeAlpha + ')';
        ctx.fillRect(0, 0, w, h);

        // Dessiner les particules
        ctx.lineWidth = CONFIG.lineWidth;

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];

            // Opacite basee sur l'age (fade in / fade out)
            var lifeRatio = p.age / p.maxAge;
            var opacity;
            if (lifeRatio < 0.1) {
                opacity = lifeRatio / 0.1;
            } else if (lifeRatio > 0.8) {
                opacity = (1 - lifeRatio) / 0.2;
            } else {
                opacity = 1;
            }

            if (opacity <= 0.01) continue;

            // Distance parcourue (eviter les artefacts de reset)
            var dx = p.x - p.prevX;
            var dy = p.y - p.prevY;
            var dist = dx * dx + dy * dy;
            if (dist > 100) continue; // Skip si teleportation (reset)

            ctx.globalAlpha = opacity;
            ctx.strokeStyle = p.color;
            ctx.beginPath();
            ctx.moveTo(p.prevX, p.prevY);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        }

        ctx.globalAlpha = 1;
    }

    // --- Animation Loop ---
    function animate() {
        update();
        draw();
        raf = requestAnimationFrame(animate);
    }

    // --- Mouse tracking (sur document car canvas = pointer-events:none) ---
    document.addEventListener('mousemove', function (e) {
        // Convertir en coordonnees relatives au canvas (fixed)
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    document.addEventListener('mouseleave', function () {
        mouse.x = -1000;
        mouse.y = -1000;
    });

    // --- Scroll: mettre a jour la position du canvas ---
    // Pas necessaire car le canvas est fixed (0,0) et la souris utilise clientX/Y

    // --- Init ---
    resize();
    animate();

    // Resize handler (debounce)
    var resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            resize();
        }, 250);
    });

    window.addEventListener('beforeunload', function () {
        cancelAnimationFrame(raf);
    });

})();
