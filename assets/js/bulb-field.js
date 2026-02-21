/**
 * Saxho.net — Bulb Field (Homepage resolution section)
 * Des ampoules-contours naissent, grandissent comme des bulles de savon,
 * puis eclosent en particules — symbolisant l'emergence des idees.
 */

(function () {
    'use strict';

    var canvas = document.getElementById('bulb-canvas');
    if (!canvas) return;

    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        canvas.style.display = 'none';
        return;
    }

    var section = canvas.closest('.resolution') || canvas.parentElement;
    var ctx = canvas.getContext('2d');
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var raf = null;
    var w = 0, h = 0;
    var animTime = 0;

    var CONFIG = {
        maxBulbs: 18,
        bulbInterval: 1500,
        growSpeed: 0.003,        // scale increment par frame (0 -> 1 en ~333 frames = ~5.5s)
        bulbLineWidth: 1.5,
        bulbOpacity: 0.4,
        bulbMinSize: 80,         // taille a scale=1
        bulbMaxSize: 180,

        wobbleAmount: 0.03,
        wobbleSpeed: 1.5,

        burstParticles: 10,
        burstSpeed: 2.0,
        burstLife: 50,

        mouseRadius: 160,
        mouseSpawnInterval: 700,
        floatSpeed: 0.3,

        colors: [
            { r: 27,  g: 58,  b: 158 },  // Bleu profond
            { r: 166, g: 61,  b: 107 },   // Rose berry
            { r: 185, g: 130, b: 50  }    // Or doux
        ]
    };

    // --- State ---
    var bulbs = [];
    var particles = [];
    var mouse = { x: -1000, y: -1000 };
    var lastAmbientTime = 0;
    var lastMouseSpawnTime = 0;
    var colorIndex = 0;

    // --- Resize ---
    function resize() {
        var rect = section.getBoundingClientRect();
        w = Math.floor(rect.width);
        h = Math.floor(rect.height);
        if (w < 1 || h < 1) return;
        canvas.width = w * dpr;
        canvas.height = h * dpr;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function nextColor() {
        var c = CONFIG.colors[colorIndex];
        colorIndex = (colorIndex + 1) % CONFIG.colors.length;
        return c;
    }

    function createBulb(x, y) {
        if (bulbs.length >= CONFIG.maxBulbs) return;
        bulbs.push({
            x: x,
            y: y,
            size: CONFIG.bulbMinSize + Math.random() * (CONFIG.bulbMaxSize - CONFIG.bulbMinSize),
            scale: 0.01,
            color: nextColor(),
            wobbleOff: Math.random() * Math.PI * 2,
            wobbleFreq: 0.8 + Math.random() * 0.6,
            rot: (Math.random() - 0.5) * 0.15,
            driftX: (Math.random() - 0.5) * 0.3
        });
    }

    function burstBulb(b) {
        var baseSize = b.size * b.scale;
        for (var i = 0; i < CONFIG.burstParticles; i++) {
            var a = Math.PI * 2 * i / CONFIG.burstParticles + (Math.random() - 0.5) * 0.5;
            var spd = CONFIG.burstSpeed * (0.5 + Math.random() * 0.8);
            particles.push({
                x: b.x, y: b.y,
                vx: Math.cos(a) * spd,
                vy: Math.sin(a) * spd - 0.5,
                life: CONFIG.burstLife,
                maxLife: CONFIG.burstLife,
                size: baseSize * 0.08,
                color: b.color,
                rot: Math.random() * Math.PI * 2
            });
        }
    }

    // --- Dessiner une ampoule ---
    // Forme en poire : globe arrondi en haut, retrecissement vers le col, culot strie
    function drawBulb(b) {
        var s = b.scale;
        var sz = b.size * s;
        if (sz < 4) return;

        // Opacite avec fade in/out
        var progress = s; // 0 -> 1
        var alpha;
        if (progress < 0.1) {
            alpha = CONFIG.bulbOpacity * (progress / 0.1);
        } else if (progress > 0.85) {
            alpha = CONFIG.bulbOpacity * ((1 - progress) / 0.15);
        } else {
            alpha = CONFIG.bulbOpacity;
        }
        if (alpha < 0.01) return;

        // Wobble bulle de savon
        var wt = animTime * CONFIG.wobbleSpeed + b.wobbleOff;
        var wx = 1 + Math.sin(wt * b.wobbleFreq) * CONFIG.wobbleAmount * s;
        var wy = 1 + Math.cos(wt * b.wobbleFreq * 1.3) * CONFIG.wobbleAmount * s;

        var c = b.color;
        var rgba = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',';

        ctx.save();
        ctx.translate(b.x, b.y);
        ctx.rotate(b.rot);
        ctx.scale(wx, wy);

        // --- Dimensions de reference ---
        var globeR = sz * 0.35;       // rayon du globe
        var globeCY = -sz * 0.12;     // centre Y du globe (haut de l'ampoule)
        var colW = globeR * 0.35;     // largeur du culot
        var colTop = globeCY + globeR * 0.92;  // debut du col
        var colBot = colTop + globeR * 0.55;   // bas du col / haut culot

        // --- Contour de l'ampoule (forme poire) ---
        // Un seul path continu : demi-cercle haut + courbes laterales + col
        ctx.beginPath();

        // Sommet du globe (arc du haut, de gauche a droite)
        ctx.arc(0, globeCY, globeR, -Math.PI * 0.85, -Math.PI * 0.15, false);

        // Cote droit : courbe du globe vers le col (retrecissement)
        ctx.bezierCurveTo(
            globeR * 0.85, globeCY + globeR * 0.75,  // cp1 : encore large
            colW * 1.6, colTop * 0.85,                // cp2 : commence a retrecir
            colW, colTop                               // end : haut du col
        );

        // Col droit vers bas
        ctx.lineTo(colW, colBot);

        // Bas du col (horizontal)
        ctx.lineTo(-colW, colBot);

        // Col gauche remonte
        ctx.lineTo(-colW, colTop);

        // Cote gauche : courbe du col vers le globe
        ctx.bezierCurveTo(
            -colW * 1.6, colTop * 0.85,
            -globeR * 0.85, globeCY + globeR * 0.75,
            -globeR * Math.cos(Math.PI * 0.15), globeCY - globeR * Math.sin(Math.PI * 0.15)
        );

        ctx.strokeStyle = rgba + alpha.toFixed(3) + ')';
        ctx.lineWidth = CONFIG.bulbLineWidth;
        ctx.stroke();

        // --- Stries du culot (3 lignes horizontales dans le col) ---
        var stripeAlpha = alpha * 0.55;
        ctx.strokeStyle = rgba + stripeAlpha.toFixed(3) + ')';
        ctx.lineWidth = CONFIG.bulbLineWidth * 0.5;
        for (var i = 0; i < 3; i++) {
            var sy = colTop + (colBot - colTop) * ((i + 1) / 4);
            ctx.beginPath();
            ctx.moveTo(-colW, sy);
            ctx.lineTo(colW, sy);
            ctx.stroke();
        }

        // --- Pointe du culot (petit V en bas) ---
        var tipH = globeR * 0.15;
        ctx.beginPath();
        ctx.moveTo(-colW * 0.5, colBot);
        ctx.lineTo(0, colBot + tipH);
        ctx.lineTo(colW * 0.5, colBot);
        ctx.strokeStyle = rgba + (alpha * 0.45).toFixed(3) + ')';
        ctx.lineWidth = CONFIG.bulbLineWidth * 0.5;
        ctx.stroke();

        // --- Filament interieur (quand assez grand) ---
        if (s > 0.2 && sz > 20) {
            var fAlpha = alpha * 0.55 * Math.min(1, (s - 0.2) * 3);
            ctx.strokeStyle = rgba + fAlpha.toFixed(3) + ')';
            ctx.lineWidth = CONFIG.bulbLineWidth * 0.6;

            // Support : 2 tiges verticales partant du col
            var stemW = colW * 0.5;
            var stemTop = globeCY + globeR * 0.1;
            var stemBot = colTop;

            ctx.beginPath();
            ctx.moveTo(-stemW, stemBot);
            ctx.lineTo(-stemW, stemTop);
            ctx.moveTo(stemW, stemBot);
            ctx.lineTo(stemW, stemTop);
            ctx.stroke();

            // Filament : zigzag / M entre les deux tiges
            var filY = stemTop;
            var filH = globeR * 0.3;
            ctx.beginPath();
            ctx.moveTo(-stemW, filY);
            ctx.quadraticCurveTo(-stemW * 0.5, filY - filH, 0, filY);
            ctx.quadraticCurveTo(stemW * 0.5, filY + filH * 0.5, stemW, filY);
            ctx.stroke();
        }

        // --- Rayons lumineux (quand mature) ---
        if (s > 0.4 && sz > 35) {
            var rayAlpha = alpha * 0.35 * Math.min(1, (s - 0.4) * 2);
            ctx.strokeStyle = rgba + rayAlpha.toFixed(3) + ')';
            ctx.lineWidth = CONFIG.bulbLineWidth * 0.7;

            var rayCount = 8;
            var rayInner = globeR * 1.15;
            var rayOuter = globeR * 1.4;

            for (var ri = 0; ri < rayCount; ri++) {
                // Repartir les rayons autour du haut du globe (arc de ~240 degres)
                var rayAngle = -Math.PI * 0.9 + (Math.PI * 1.8) * ri / (rayCount - 1);
                var rx1 = Math.cos(rayAngle) * rayInner;
                var ry1 = globeCY + Math.sin(rayAngle) * rayInner;
                var rx2 = Math.cos(rayAngle) * rayOuter;
                var ry2 = globeCY + Math.sin(rayAngle) * rayOuter;

                // Ne dessiner que les rayons au-dessus du col
                if (ry1 < colTop - 2) {
                    ctx.beginPath();
                    ctx.moveTo(rx1, ry1);
                    ctx.lineTo(rx2, ry2);
                    ctx.stroke();
                }
            }
        }

        // --- Reflet irise (bulle de savon) quand mature ---
        if (s > 0.3 && sz > 30) {
            var iAlpha = alpha * 0.15 * Math.min(1, (s - 0.3) * 2);
            var cIdx = 0;
            for (var ci = 0; ci < CONFIG.colors.length; ci++) {
                if (CONFIG.colors[ci] === c) { cIdx = ci; break; }
            }
            var nc = CONFIG.colors[(cIdx + 1) % CONFIG.colors.length];

            ctx.beginPath();
            ctx.arc(0, globeCY, globeR * 0.75, -Math.PI * 0.65, -Math.PI * 0.15);
            ctx.strokeStyle = 'rgba(' + nc.r + ',' + nc.g + ',' + nc.b + ',' + iAlpha.toFixed(3) + ')';
            ctx.lineWidth = CONFIG.bulbLineWidth * 2;
            ctx.stroke();
        }

        ctx.restore();
    }

    function drawParticle(p) {
        var ratio = p.life / p.maxLife;
        var alpha = CONFIG.bulbOpacity * ratio;
        if (alpha < 0.01) return;

        var c = p.color;
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate(p.rot + (1 - ratio) * 2);
        ctx.beginPath();
        ctx.arc(0, 0, p.size, 0, Math.PI * (0.3 + ratio * 0.7));
        ctx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + alpha.toFixed(3) + ')';
        ctx.lineWidth = 1;
        ctx.stroke();
        ctx.restore();
    }

    // --- Boucle d'animation ---
    function animate() {
        if (w < 1 || h < 1) {
            resize();
            raf = requestAnimationFrame(animate);
            return;
        }

        ctx.clearRect(0, 0, w, h);
        animTime += 0.016;

        var now = Date.now();
        var i, b, p;

        // Naissance ambiante
        if (now - lastAmbientTime > CONFIG.bulbInterval) {
            createBulb(
                w * 0.08 + Math.random() * w * 0.84,
                h * 0.1 + Math.random() * h * 0.8
            );
            lastAmbientTime = now;
        }

        // Naissance souris
        if (mouse.x > 0 && mouse.y > 0 && now - lastMouseSpawnTime > CONFIG.mouseSpawnInterval) {
            createBulb(
                mouse.x + (Math.random() - 0.5) * 50,
                mouse.y + (Math.random() - 0.5) * 50
            );
            lastMouseSpawnTime = now;
        }

        // Update & draw bulbes
        for (i = bulbs.length - 1; i >= 0; i--) {
            b = bulbs[i];

            b.scale += CONFIG.growSpeed;
            b.y -= CONFIG.floatSpeed;
            b.x += b.driftX * 0.5;

            // Repulsion souris
            if (mouse.x > 0 && mouse.y > 0) {
                var dx = b.x - mouse.x;
                var dy = b.y - mouse.y;
                var dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < CONFIG.mouseRadius && dist > 1) {
                    var force = (CONFIG.mouseRadius - dist) / CONFIG.mouseRadius * 0.4;
                    b.x += (dx / dist) * force;
                    b.y += (dy / dist) * force;
                }
            }

            // Eclosion
            if (b.scale >= 1) {
                burstBulb(b);
                bulbs.splice(i, 1);
                continue;
            }

            // Sortie de zone
            if (b.y < -200 || b.x < -200 || b.x > w + 200) {
                bulbs.splice(i, 1);
                continue;
            }

            drawBulb(b);
        }

        // Update & draw particules
        for (i = particles.length - 1; i >= 0; i--) {
            p = particles[i];
            p.x += p.vx;
            p.y += p.vy;
            p.vy += 0.025;
            p.vx *= 0.97;
            p.vy *= 0.97;
            p.life--;
            if (p.life <= 0) { particles.splice(i, 1); continue; }
            drawParticle(p);
        }

        raf = requestAnimationFrame(animate);
    }

    // --- Mouse ---
    section.addEventListener('mousemove', function (e) {
        var rect = section.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    });
    section.addEventListener('mouseleave', function () {
        mouse.x = -1000; mouse.y = -1000;
    });
    section.addEventListener('touchmove', function (e) {
        if (e.touches.length > 0) {
            var rect = section.getBoundingClientRect();
            mouse.x = e.touches[0].clientX - rect.left;
            mouse.y = e.touches[0].clientY - rect.top;
        }
    }, { passive: true });
    section.addEventListener('touchend', function () {
        mouse.x = -1000; mouse.y = -1000;
    });

    // --- Visibility ---
    if (window.IntersectionObserver) {
        var observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting && !raf) {
                raf = requestAnimationFrame(animate);
            }
        }, { threshold: 0.05 });
        observer.observe(section);
    }

    // --- Init ---
    resize();

    // Bulbes initiaux decales
    setTimeout(function () { createBulb(w * 0.15, h * 0.6); }, 100);
    setTimeout(function () { createBulb(w * 0.75, h * 0.35); }, 400);
    setTimeout(function () { createBulb(w * 0.45, h * 0.5); }, 800);
    setTimeout(function () { createBulb(w * 0.85, h * 0.7); }, 1200);
    setTimeout(function () { createBulb(w * 0.30, h * 0.25); }, 1600);

    lastAmbientTime = Date.now();
    raf = requestAnimationFrame(animate);

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(resize, 200);
    });

})();
