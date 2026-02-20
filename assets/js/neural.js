/**
 * Saxho.net — Neural Network Background Animation
 * Reseau de neurones avec impulsions electriques en cascade
 */

(function () {
    'use strict';

    var canvas = document.getElementById('neural-canvas');
    if (!canvas) return;

    // Respecter prefers-reduced-motion
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        canvas.style.display = 'none';
        return;
    }

    var ctx = canvas.getContext('2d');
    var nodes = [];
    var pulses = [];
    var mouse = { x: -1000, y: -1000 };
    var raf;
    var dpr = Math.min(window.devicePixelRatio || 1, 2); // Cap a 2x pour perf

    // --- Configuration ---
    var CONFIG = {
        nodeCount: 100,          // Plus de nodes pour un reseau dense
        connectionDist: 160,     // Distance de connexion
        mouseDist: 200,          // Rayon d'influence de la souris
        nodeSpeed: 0.15,         // Mouvement tres lent
        nodeRadius: 1.2,         // Nodes tout petits (jonctions, pas flocons)
        lineBaseOpacity: 0.15,   // Lignes bien visibles par defaut
        lineMouseOpacity: 0.4,   // Lignes pres de la souris
        lineWidth: 0.8,          // Epaisseur ligne de base
        pulseInterval: 800,      // Pulses plus frequents
        pulseSpeed: 1.8,         // Vitesse de propagation
        pulseCascadeChance: 0.6, // Chance qu'un pulse rebondisse a l'arrivee
        maxCascadeDepth: 4,      // Profondeur max de cascade
        pulseGlowRadius: 18,     // Taille du halo lumineux
        pulseTrailLength: 0.25,  // Longueur de la trainee
        pulseLineWidth: 2.5,     // Epaisseur du pulse
        pulseColors: [
            { r: 58, g: 125, b: 255 },   // Bleu electrique
            { r: 166, g: 61, b: 107 },    // Rose framboise (accent logo)
            { r: 245, g: 166, b: 35 },    // Or chaud
            { r: 140, g: 100, b: 255 },   // Violet
        ]
    };

    // --- Resize ---
    function resize() {
        var rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        canvas.style.width = rect.width + 'px';
        canvas.style.height = rect.height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    // --- Create Nodes ---
    function createNodes() {
        nodes = [];
        var w = canvas.width / dpr;
        var h = canvas.height / dpr;
        for (var i = 0; i < CONFIG.nodeCount; i++) {
            nodes.push({
                x: Math.random() * w,
                y: Math.random() * h,
                vx: (Math.random() - 0.5) * CONFIG.nodeSpeed * 2,
                vy: (Math.random() - 0.5) * CONFIG.nodeSpeed * 2,
                radius: CONFIG.nodeRadius
            });
        }
    }

    // --- Trouver les voisins connectes d'un node ---
    function getNeighbors(nodeIdx) {
        var source = nodes[nodeIdx];
        var neighbors = [];
        for (var i = 0; i < nodes.length; i++) {
            if (i === nodeIdx) continue;
            var dx = nodes[i].x - source.x;
            var dy = nodes[i].y - source.y;
            if (dx * dx + dy * dy < CONFIG.connectionDist * CONFIG.connectionDist) {
                neighbors.push(i);
            }
        }
        return neighbors;
    }

    // --- Create Pulse (avec cascade possible) ---
    function createPulse(fromIdx, excludeIdx, depth) {
        depth = depth || 0;
        if (depth > CONFIG.maxCascadeDepth) return;
        if (!nodes[fromIdx]) return;

        var neighbors = getNeighbors(fromIdx);
        if (neighbors.length === 0) return;

        // Filtrer le node d'origine (pas de retour en arriere)
        if (excludeIdx !== undefined) {
            neighbors = neighbors.filter(function (n) { return n !== excludeIdx; });
        }
        if (neighbors.length === 0) return;

        var targetIdx = neighbors[Math.floor(Math.random() * neighbors.length)];
        var colorObj = CONFIG.pulseColors[Math.floor(Math.random() * CONFIG.pulseColors.length)];

        pulses.push({
            fromIdx: fromIdx,
            toIdx: targetIdx,
            progress: 0,
            color: colorObj,
            speed: CONFIG.pulseSpeed + Math.random() * 0.8,
            depth: depth,
            cascaded: false
        });
    }

    // --- Lancer un pulse initial ---
    function launchPulse() {
        var sourceIdx = Math.floor(Math.random() * nodes.length);
        createPulse(sourceIdx, undefined, 0);
    }

    // --- Update ---
    function update() {
        var w = canvas.width / dpr;
        var h = canvas.height / dpr;

        // Move nodes (tres lentement)
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            n.x += n.vx;
            n.y += n.vy;

            if (n.x < -10) { n.x = -10; n.vx *= -1; }
            if (n.x > w + 10) { n.x = w + 10; n.vx *= -1; }
            if (n.y < -10) { n.y = -10; n.vy *= -1; }
            if (n.y > h + 10) { n.y = h + 10; n.vy *= -1; }
        }

        // Update pulses + cascade
        for (var p = pulses.length - 1; p >= 0; p--) {
            var pulse = pulses[p];
            pulse.progress += pulse.speed * 0.012;

            // Quand le pulse arrive a destination, cascade
            if (pulse.progress >= 1) {
                if (!pulse.cascaded && Math.random() < CONFIG.pulseCascadeChance) {
                    pulse.cascaded = true;
                    createPulse(pulse.toIdx, pulse.fromIdx, pulse.depth + 1);
                }
                pulses.splice(p, 1);
            }
        }
    }

    // --- Draw ---
    function draw() {
        var w = canvas.width / dpr;
        var h = canvas.height / dpr;

        ctx.clearRect(0, 0, w, h);

        // 1. Draw connections (le reseau visible)
        for (var i = 0; i < nodes.length; i++) {
            for (var j = i + 1; j < nodes.length; j++) {
                var dx = nodes[j].x - nodes[i].x;
                var dy = nodes[j].y - nodes[i].y;
                var distSq = dx * dx + dy * dy;
                var maxDistSq = CONFIG.connectionDist * CONFIG.connectionDist;

                if (distSq < maxDistSq) {
                    var dist = Math.sqrt(distSq);
                    var opacity = (1 - dist / CONFIG.connectionDist);

                    // Pres de la souris = plus lumineux
                    var midX = (nodes[i].x + nodes[j].x) * 0.5;
                    var midY = (nodes[i].y + nodes[j].y) * 0.5;
                    var mDx = midX - mouse.x;
                    var mDy = midY - mouse.y;
                    var mDist = Math.sqrt(mDx * mDx + mDy * mDy);

                    var alpha;
                    if (mDist < CONFIG.mouseDist) {
                        var mInf = 1 - mDist / CONFIG.mouseDist;
                        alpha = (CONFIG.lineBaseOpacity + mInf * (CONFIG.lineMouseOpacity - CONFIG.lineBaseOpacity)) * opacity;
                        ctx.lineWidth = CONFIG.lineWidth + mInf * 0.8;
                    } else {
                        alpha = CONFIG.lineBaseOpacity * opacity;
                        ctx.lineWidth = CONFIG.lineWidth;
                    }

                    ctx.beginPath();
                    ctx.moveTo(nodes[i].x, nodes[i].y);
                    ctx.lineTo(nodes[j].x, nodes[j].y);
                    ctx.strokeStyle = 'rgba(58, 125, 255, ' + alpha.toFixed(3) + ')';
                    ctx.stroke();
                }
            }
        }

        // 2. Draw pulses (impulsions electriques)
        for (var p = 0; p < pulses.length; p++) {
            var pulse = pulses[p];
            var from = nodes[pulse.fromIdx];
            var to = nodes[pulse.toIdx];
            if (!from || !to) continue;

            var c = pulse.color;
            var px = from.x + (to.x - from.x) * pulse.progress;
            var py = from.y + (to.y - from.y) * pulse.progress;

            // Intensite decroit avec la profondeur de cascade
            var intensity = Math.max(0.4, 1 - pulse.depth * 0.15);

            // Halo lumineux
            var glow = ctx.createRadialGradient(px, py, 0, px, py, CONFIG.pulseGlowRadius);
            glow.addColorStop(0, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.9 * intensity) + ')');
            glow.addColorStop(0.4, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.3 * intensity) + ')');
            glow.addColorStop(1, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0)');

            ctx.beginPath();
            ctx.arc(px, py, CONFIG.pulseGlowRadius, 0, Math.PI * 2);
            ctx.fillStyle = glow;
            ctx.fill();

            // Trainee lumineuse
            var trailStart = Math.max(0, pulse.progress - CONFIG.pulseTrailLength);
            var sx = from.x + (to.x - from.x) * trailStart;
            var sy = from.y + (to.y - from.y) * trailStart;

            var lineGrad = ctx.createLinearGradient(sx, sy, px, py);
            lineGrad.addColorStop(0, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0)');
            lineGrad.addColorStop(0.6, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.5 * intensity) + ')');
            lineGrad.addColorStop(1, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.9 * intensity) + ')');

            ctx.beginPath();
            ctx.moveTo(sx, sy);
            ctx.lineTo(px, py);
            ctx.strokeStyle = lineGrad;
            ctx.lineWidth = CONFIG.pulseLineWidth;
            ctx.stroke();

            // Point central brillant
            ctx.beginPath();
            ctx.arc(px, py, 2.5, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255, 255, 255, ' + (0.9 * intensity) + ')';
            ctx.fill();
        }

        // 3. Draw nodes (petits points discrets aux jonctions)
        for (var n = 0; n < nodes.length; n++) {
            var node = nodes[n];
            var ndx = node.x - mouse.x;
            var ndy = node.y - mouse.y;
            var nDist = Math.sqrt(ndx * ndx + ndy * ndy);

            if (nDist < CONFIG.mouseDist) {
                var inf = 1 - nDist / CONFIG.mouseDist;
                // Petit glow autour du node proche de la souris
                ctx.beginPath();
                ctx.arc(node.x, node.y, 4 + inf * 4, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(58, 125, 255, ' + (inf * 0.15) + ')';
                ctx.fill();

                ctx.beginPath();
                ctx.arc(node.x, node.y, node.radius + inf * 1.5, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(150, 180, 255, ' + (0.4 + inf * 0.5) + ')';
                ctx.fill();
            } else {
                ctx.beginPath();
                ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(100, 150, 255, 0.25)';
                ctx.fill();
            }
        }
    }

    // --- Animation Loop ---
    function animate() {
        update();
        draw();
        raf = requestAnimationFrame(animate);
    }

    // --- Mouse tracking ---
    canvas.parentElement.addEventListener('mousemove', function (e) {
        var rect = canvas.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    });

    canvas.parentElement.addEventListener('mouseleave', function () {
        mouse.x = -1000;
        mouse.y = -1000;
    });

    // --- Init ---
    resize();
    createNodes();
    animate();

    // Pulses periodiques
    setInterval(launchPulse, CONFIG.pulseInterval);
    // Salve initiale
    setTimeout(launchPulse, 300);
    setTimeout(launchPulse, 700);
    setTimeout(launchPulse, 1100);
    setTimeout(launchPulse, 1500);

    // Resize handler
    var resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            resize();
            createNodes();
        }, 250);
    });

    window.addEventListener('beforeunload', function () {
        cancelAnimationFrame(raf);
    });

})();
