/**
 * Saxho.net — Neural Network Background Animation
 * Reseau de neurones avec impulsions lumineuses
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
    var dpr = window.devicePixelRatio || 1;

    // --- Configuration ---
    var CONFIG = {
        nodeCount: 80,           // Nombre de neurones
        connectionDist: 180,     // Distance max pour connecter deux nodes
        mouseDist: 250,          // Rayon d'influence de la souris
        nodeSpeed: 0.3,          // Vitesse de deplacement des nodes
        nodeRadius: 2,           // Rayon des nodes
        nodeColor: 'rgba(58, 125, 255, 0.5)',          // Bleu doux
        nodeHighlight: 'rgba(58, 125, 255, 0.9)',      // Bleu plus vif au hover
        lineColor: 'rgba(58, 125, 255, 0.08)',         // Lignes tres subtiles
        lineHighlight: 'rgba(58, 125, 255, 0.25)',     // Lignes proches souris
        pulseInterval: 2000,     // Frequence des pulses (ms)
        pulseSpeed: 2.5,         // Vitesse des pulses
        pulseColors: [
            'rgba(245, 166, 35, 0.8)',    // Or chaud
            'rgba(58, 125, 255, 0.7)',    // Bleu
            'rgba(200, 140, 255, 0.6)',   // Violet doux
            'rgba(224, 168, 184, 0.7)',   // Rose poudre
        ]
    };

    // --- Resize ---
    function resize() {
        var rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        canvas.style.width = rect.width + 'px';
        canvas.style.height = rect.height + 'px';
        ctx.scale(dpr, dpr);
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
                radius: CONFIG.nodeRadius + Math.random() * 1.5
            });
        }
    }

    // --- Create Pulse ---
    function createPulse() {
        if (nodes.length < 2) return;

        // Choisir un node source au hasard
        var sourceIdx = Math.floor(Math.random() * nodes.length);
        var source = nodes[sourceIdx];

        // Trouver un node connecte
        var targets = [];
        for (var i = 0; i < nodes.length; i++) {
            if (i === sourceIdx) continue;
            var dx = nodes[i].x - source.x;
            var dy = nodes[i].y - source.y;
            var dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < CONFIG.connectionDist) {
                targets.push(i);
            }
        }

        if (targets.length === 0) return;

        var targetIdx = targets[Math.floor(Math.random() * targets.length)];
        var color = CONFIG.pulseColors[Math.floor(Math.random() * CONFIG.pulseColors.length)];

        pulses.push({
            fromIdx: sourceIdx,
            toIdx: targetIdx,
            progress: 0,
            color: color,
            speed: CONFIG.pulseSpeed + Math.random() * 1.5
        });
    }

    // --- Update ---
    function update() {
        var w = canvas.width / dpr;
        var h = canvas.height / dpr;

        // Move nodes
        for (var i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            n.x += n.vx;
            n.y += n.vy;

            // Rebondir sur les bords (avec marge)
            if (n.x < -20) { n.x = -20; n.vx *= -1; }
            if (n.x > w + 20) { n.x = w + 20; n.vx *= -1; }
            if (n.y < -20) { n.y = -20; n.vy *= -1; }
            if (n.y > h + 20) { n.y = h + 20; n.vy *= -1; }
        }

        // Update pulses
        for (var p = pulses.length - 1; p >= 0; p--) {
            pulses[p].progress += pulses[p].speed * 0.01;
            if (pulses[p].progress >= 1) {
                pulses.splice(p, 1);
            }
        }
    }

    // --- Draw ---
    function draw() {
        var w = canvas.width / dpr;
        var h = canvas.height / dpr;

        ctx.clearRect(0, 0, w, h);

        // Draw connections
        for (var i = 0; i < nodes.length; i++) {
            for (var j = i + 1; j < nodes.length; j++) {
                var dx = nodes[j].x - nodes[i].x;
                var dy = nodes[j].y - nodes[i].y;
                var dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < CONFIG.connectionDist) {
                    var opacity = 1 - (dist / CONFIG.connectionDist);

                    // Plus lumineux pres de la souris
                    var midX = (nodes[i].x + nodes[j].x) / 2;
                    var midY = (nodes[i].y + nodes[j].y) / 2;
                    var mouseDx = midX - mouse.x;
                    var mouseDy = midY - mouse.y;
                    var mouseDist = Math.sqrt(mouseDx * mouseDx + mouseDy * mouseDy);

                    if (mouseDist < CONFIG.mouseDist) {
                        var mouseInfluence = 1 - (mouseDist / CONFIG.mouseDist);
                        ctx.strokeStyle = 'rgba(58, 125, 255, ' + (0.05 + mouseInfluence * 0.25) * opacity + ')';
                        ctx.lineWidth = 1 + mouseInfluence * 0.5;
                    } else {
                        ctx.strokeStyle = 'rgba(58, 125, 255, ' + (0.06 * opacity) + ')';
                        ctx.lineWidth = 0.5;
                    }

                    ctx.beginPath();
                    ctx.moveTo(nodes[i].x, nodes[i].y);
                    ctx.lineTo(nodes[j].x, nodes[j].y);
                    ctx.stroke();
                }
            }
        }

        // Draw pulses
        for (var p = 0; p < pulses.length; p++) {
            var pulse = pulses[p];
            var from = nodes[pulse.fromIdx];
            var to = nodes[pulse.toIdx];
            if (!from || !to) continue;

            var px = from.x + (to.x - from.x) * pulse.progress;
            var py = from.y + (to.y - from.y) * pulse.progress;

            // Glow du pulse
            var gradient = ctx.createRadialGradient(px, py, 0, px, py, 12);
            gradient.addColorStop(0, pulse.color);
            gradient.addColorStop(1, 'transparent');

            ctx.beginPath();
            ctx.arc(px, py, 12, 0, Math.PI * 2);
            ctx.fillStyle = gradient;
            ctx.fill();

            // Ligne lumineuse le long du trajet
            var trailLength = 0.15;
            var trailStart = Math.max(0, pulse.progress - trailLength);
            var sx = from.x + (to.x - from.x) * trailStart;
            var sy = from.y + (to.y - from.y) * trailStart;

            var lineGrad = ctx.createLinearGradient(sx, sy, px, py);
            lineGrad.addColorStop(0, 'transparent');
            lineGrad.addColorStop(1, pulse.color);

            ctx.beginPath();
            ctx.moveTo(sx, sy);
            ctx.lineTo(px, py);
            ctx.strokeStyle = lineGrad;
            ctx.lineWidth = 2;
            ctx.stroke();
        }

        // Draw nodes
        for (var n = 0; n < nodes.length; n++) {
            var node = nodes[n];
            var ndx = node.x - mouse.x;
            var ndy = node.y - mouse.y;
            var nDist = Math.sqrt(ndx * ndx + ndy * ndy);

            if (nDist < CONFIG.mouseDist) {
                var inf = 1 - (nDist / CONFIG.mouseDist);
                // Node plus grand et brillant pres de la souris
                var r = node.radius + inf * 3;

                var glow = ctx.createRadialGradient(node.x, node.y, 0, node.x, node.y, r * 3);
                glow.addColorStop(0, 'rgba(58, 125, 255, ' + (0.6 + inf * 0.4) + ')');
                glow.addColorStop(0.5, 'rgba(58, 125, 255, ' + (inf * 0.15) + ')');
                glow.addColorStop(1, 'transparent');

                ctx.beginPath();
                ctx.arc(node.x, node.y, r * 3, 0, Math.PI * 2);
                ctx.fillStyle = glow;
                ctx.fill();

                ctx.beginPath();
                ctx.arc(node.x, node.y, r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(58, 125, 255, ' + (0.7 + inf * 0.3) + ')';
                ctx.fill();
            } else {
                ctx.beginPath();
                ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
                ctx.fillStyle = CONFIG.nodeColor;
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
    setInterval(createPulse, CONFIG.pulseInterval);
    // Quelques pulses au demarrage
    setTimeout(createPulse, 500);
    setTimeout(createPulse, 1200);
    setTimeout(createPulse, 1800);

    // Resize handler
    var resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function () {
            resize();
            createNodes();
        }, 250);
    });

    // Cleanup quand la page se dechare
    window.addEventListener('beforeunload', function () {
        cancelAnimationFrame(raf);
    });

})();
