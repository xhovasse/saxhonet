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
    // Path simple : globe (arc) + col + culot
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

        // --- Globe de l'ampoule (cercle) ---
        var globeR = sz * 0.38;
        var globeY = -sz * 0.08;

        ctx.beginPath();
        ctx.arc(0, globeY, globeR, 0, Math.PI * 2);
        ctx.strokeStyle = rgba + alpha.toFixed(3) + ')';
        ctx.lineWidth = CONFIG.bulbLineWidth;
        ctx.stroke();

        // --- Col (trapeze) ---
        var colTop = globeY + globeR * 0.85;
        var colBot = globeY + globeR * 1.4;
        var colWTop = globeR * 0.55;
        var colWBot = globeR * 0.38;

        ctx.beginPath();
        ctx.moveTo(-colWTop, colTop);
        ctx.lineTo(-colWBot, colBot);
        ctx.strokeStyle = rgba + alpha.toFixed(3) + ')';
        ctx.lineWidth = CONFIG.bulbLineWidth;
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(colWTop, colTop);
        ctx.lineTo(colWBot, colBot);
        ctx.stroke();

        // --- Culot (3 lignes horizontales) ---
        var culotH = globeR * 0.3;
        for (var i = 0; i < 3; i++) {
            var cy = colBot + culotH * (i / 3);
            var cw = colWBot * (1 - i * 0.1);
            ctx.beginPath();
            ctx.moveTo(-cw, cy);
            ctx.lineTo(cw, cy);
            ctx.strokeStyle = rgba + (alpha * 0.6).toFixed(3) + ')';
            ctx.lineWidth = CONFIG.bulbLineWidth * 0.6;
            ctx.stroke();
        }

        // --- Pointe du culot ---
        var tipY = colBot + culotH;
        ctx.beginPath();
        ctx.moveTo(-colWBot * 0.4, colBot + culotH * 0.8);
        ctx.lineTo(0, tipY);
        ctx.lineTo(colWBot * 0.4, colBot + culotH * 0.8);
        ctx.strokeStyle = rgba + (alpha * 0.5).toFixed(3) + ')';
        ctx.lineWidth = CONFIG.bulbLineWidth * 0.6;
        ctx.stroke();

        // --- Filament (quand assez grand) ---
        if (s > 0.25 && sz > 25) {
            var fAlpha = alpha * 0.5 * Math.min(1, (s - 0.25) * 3);
            var fW = globeR * 0.3;
            var fH = globeR * 0.35;

            ctx.beginPath();
            ctx.moveTo(-fW * 0.5, globeY + globeR * 0.1);
            ctx.quadraticCurveTo(-fW * 0.25, globeY - fH, 0, globeY);
            ctx.quadraticCurveTo(fW * 0.25, globeY - fH, fW * 0.5, globeY + globeR * 0.1);
            ctx.strokeStyle = rgba + fAlpha.toFixed(3) + ')';
            ctx.lineWidth = CONFIG.bulbLineWidth * 0.6;
            ctx.stroke();

            // Tige
            ctx.beginPath();
            ctx.moveTo(0, globeY);
            ctx.lineTo(0, colTop);
            ctx.stroke();
        }

        // --- Reflet irise (bulle de savon) quand mature ---
        if (s > 0.3 && sz > 30) {
            var iAlpha = alpha * 0.15 * Math.min(1, (s - 0.3) * 2);
            var cIdx = 0;
            for (var ci = 0; ci < CONFIG.colors.length; ci++) {
                if (CONFIG.colors[ci] === c) { cIdx = ci; break; }
            }
            var nc = CONFIG.colors[(cIdx + 1) % CONFIG.colors.length];

            // Arc de reflet sur le globe
            ctx.beginPath();
            ctx.arc(0, globeY, globeR * 0.85, -Math.PI * 0.6, -Math.PI * 0.1);
            ctx.strokeStyle = 'rgba(' + nc.r + ',' + nc.g + ',' + nc.b + ',' + iAlpha.toFixed(3) + ')';
            ctx.lineWidth = CONFIG.bulbLineWidth * 2.5;
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
