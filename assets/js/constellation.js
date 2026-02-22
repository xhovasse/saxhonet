/**
 * Saxho.net — Constellation Path
 * Riviere de particules lumineuses le long d'une courbe ascendante,
 * 5 noeuds orbitaux representant les 5 niveaux de service.
 * "De l'idee au succes" — les idees se structurent a travers le chemin.
 */
(function () {
    'use strict';

    var scene = document.getElementById('constellation');
    if (!scene) return;

    var canvas = document.getElementById('constellation-canvas');
    if (!canvas) return;

    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) {
        canvas.style.display = 'none';
        return;
    }

    var MOBILE_BP = 768;
    var ctx = canvas.getContext('2d');
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var raf = null;
    var w = 0, h = 0;
    var animTime = 0;

    // --- Configuration ---
    var CONFIG = {
        particleCount: 180,
        particleSpeed: 0.0012,
        particleMinSize: 0.8,
        particleMaxSize: 2.8,
        riverWidthStart: 45,
        riverWidthEnd: 12,

        nodeBaseRadius: 6,
        nodeHoverScale: 1.5,
        nodeScaleSpeed: 0.08,
        orbitBaseSpeed: 0.4,
        orbitHoverSpeed: 1.8,
        haloRadius: 50,

        pathPadding: 0.08,

        mouseGravity: 0.12,
        mouseRadius: 140,

        leaveDelay: 350,

        colors: [
            { r: 255, g: 107, b: 74  },  // #FF6B4A — orange
            { r: 212, g: 148, b: 10  },  // #D4940A — amber
            { r: 16,  g: 185, b: 129 },  // #10B981 — green
            { r: 14,  g: 165, b: 233 },  // #0EA5E9 — sky
            { r: 99,  g: 102, b: 241 }   // #6366F1 — indigo
        ]
    };

    // Node definitions: t = position on curve, rings = orbital ring count
    var NODES = [
        { t: 0.08, rings: 1, sizeBonus: 0 },
        { t: 0.28, rings: 1, sizeBonus: 2 },
        { t: 0.50, rings: 2, sizeBonus: 4 },
        { t: 0.72, rings: 2, sizeBonus: 6 },
        { t: 0.92, rings: 3, sizeBonus: 8 }
    ];

    // --- State ---
    var particles = [];
    var mouse = { x: -1000, y: -1000 };
    var activeNode = -1;
    var targetActive = -1;
    var leaveTimer = null;
    var nodePositions = []; // {x, y} for each node in px
    var nodeScales = [1, 1, 1, 1, 1];  // current animated scale

    // Bezier control points (set on resize)
    var bp0 = { x: 0, y: 0 };
    var bp1 = { x: 0, y: 0 };
    var bp2 = { x: 0, y: 0 };
    var bp3 = { x: 0, y: 0 };

    // Label elements
    var labels = [];
    for (var li = 0; li < NODES.length; li++) {
        var lbl = scene.querySelector('.constellation__label--' + (li + 1));
        labels.push(lbl);
    }

    // --- Bezier math ---
    function bezierPoint(t) {
        var u = 1 - t;
        var tt = t * t;
        var uu = u * u;
        var uuu = uu * u;
        var ttt = tt * t;
        return {
            x: uuu * bp0.x + 3 * uu * t * bp1.x + 3 * u * tt * bp2.x + ttt * bp3.x,
            y: uuu * bp0.y + 3 * uu * t * bp1.y + 3 * u * tt * bp2.y + ttt * bp3.y
        };
    }

    function bezierTangent(t) {
        var u = 1 - t;
        var tx = 3 * u * u * (bp1.x - bp0.x) + 6 * u * t * (bp2.x - bp1.x) + 3 * t * t * (bp3.x - bp2.x);
        var ty = 3 * u * u * (bp1.y - bp0.y) + 6 * u * t * (bp2.y - bp1.y) + 3 * t * t * (bp3.y - bp2.y);
        var len = Math.sqrt(tx * tx + ty * ty) || 1;
        return { x: tx / len, y: ty / len };
    }

    function perpendicular(tangent) {
        return { x: -tangent.y, y: tangent.x };
    }

    // --- Color interpolation ---
    function lerpColor(t) {
        // Map t (0-1) across the 5 colors
        var idx = t * (CONFIG.colors.length - 1);
        var i0 = Math.floor(idx);
        var i1 = Math.min(i0 + 1, CONFIG.colors.length - 1);
        var f = idx - i0;
        var c0 = CONFIG.colors[i0];
        var c1 = CONFIG.colors[i1];
        return {
            r: Math.round(c0.r + (c1.r - c0.r) * f),
            g: Math.round(c0.g + (c1.g - c0.g) * f),
            b: Math.round(c0.b + (c1.b - c0.b) * f)
        };
    }

    // --- Resize ---
    function resize() {
        if (window.innerWidth < MOBILE_BP) return;

        var rect = scene.getBoundingClientRect();
        w = Math.floor(rect.width);
        h = Math.floor(rect.height);
        if (w < 1 || h < 1) return;

        canvas.width = w * dpr;
        canvas.height = h * dpr;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        // Bezier: bottom-left to top-right, gentle S-curve
        var pad = w * CONFIG.pathPadding;
        bp0.x = pad;
        bp0.y = h - pad * 0.5;
        bp1.x = w * 0.25;
        bp1.y = h * 0.75;
        bp2.x = w * 0.75;
        bp2.y = h * 0.25;
        bp3.x = w - pad;
        bp3.y = pad * 0.8;

        // Compute node positions
        nodePositions = [];
        for (var n = 0; n < NODES.length; n++) {
            nodePositions.push(bezierPoint(NODES[n].t));
        }

        positionLabels();
    }

    // --- Position HTML labels ---
    function positionLabels() {
        if (window.innerWidth < MOBILE_BP) return;

        for (var i = 0; i < NODES.length; i++) {
            if (!labels[i] || !nodePositions[i]) continue;
            var np = nodePositions[i];
            var labelW = labels[i].offsetWidth || 180;
            var labelH = labels[i].offsetHeight || 60;
            var offsetX, textAlign;

            // Alternate sides: odd (0,2,4) left, even (1,3) right
            if (i % 2 === 0) {
                // Label to the left of the node
                offsetX = np.x - labelW - 30;
                textAlign = 'right';
            } else {
                // Label to the right of the node
                offsetX = np.x + 30;
                textAlign = 'left';
            }

            var offsetY = np.y - labelH * 0.5;

            // Clamp within bounds
            offsetX = Math.max(8, Math.min(w - labelW - 8, offsetX));
            offsetY = Math.max(8, Math.min(h - labelH - 8, offsetY));

            labels[i].style.left = offsetX + 'px';
            labels[i].style.top = offsetY + 'px';
            labels[i].style.textAlign = textAlign;
        }
    }

    // --- Create particles ---
    function createParticles() {
        particles = [];
        for (var i = 0; i < CONFIG.particleCount; i++) {
            particles.push({
                t: Math.random(),
                speed: CONFIG.particleSpeed * (0.6 + Math.random() * 0.8),
                offset: (Math.random() - 0.5) * 2, // -1 to 1
                size: CONFIG.particleMinSize + Math.random() * (CONFIG.particleMaxSize - CONFIG.particleMinSize),
                brightness: 0.3 + Math.random() * 0.7,
                wobbleOff: Math.random() * Math.PI * 2,
                wobbleFreq: 0.5 + Math.random() * 1.0
            });
        }
    }

    // --- Activate / deactivate node ---
    function activateNode(index) {
        if (index === targetActive) return;
        targetActive = index;

        if (index >= 0) {
            scene.classList.add('constellation--has-active');
        } else {
            scene.classList.remove('constellation--has-active');
        }

        for (var i = 0; i < labels.length; i++) {
            if (labels[i]) {
                if (i === index) {
                    labels[i].classList.add('constellation__label--active');
                } else {
                    labels[i].classList.remove('constellation__label--active');
                }
            }
        }
    }

    function handleEnter(index) {
        if (window.innerWidth < MOBILE_BP) return;
        clearTimeout(leaveTimer);
        activateNode(index);
    }

    function handleLeave() {
        if (window.innerWidth < MOBILE_BP) return;
        clearTimeout(leaveTimer);
        leaveTimer = setTimeout(function () {
            activateNode(-1);
        }, CONFIG.leaveDelay);
    }

    function handleClick() {
        var url = scene.getAttribute('data-services-url') || '/services';
        window.location.href = url;
    }

    // --- Drawing ---
    function drawPath() {
        // Draw connecting gradient line along the Bezier
        var steps = 80;
        for (var i = 0; i < steps; i++) {
            var t0 = i / steps;
            var t1 = (i + 1) / steps;
            var p0 = bezierPoint(t0);
            var p1 = bezierPoint(t1);
            var c = lerpColor((t0 + t1) * 0.5);

            var dimFactor = 1;
            if (targetActive >= 0) {
                // Dim segments far from active node
                var nodeT = NODES[targetActive].t;
                var dist = Math.abs((t0 + t1) * 0.5 - nodeT);
                dimFactor = Math.max(0.15, 1 - dist * 1.8);
            }

            ctx.beginPath();
            ctx.moveTo(p0.x, p0.y);
            ctx.lineTo(p1.x, p1.y);
            ctx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.15 * dimFactor).toFixed(3) + ')';
            ctx.lineWidth = 1.5;
            ctx.stroke();
        }
    }

    function drawParticles() {
        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];

            // Progress along path — acceleration near the end
            var speedMul = 1 + p.t * 0.5;
            p.t += p.speed * speedMul;
            if (p.t > 1) {
                p.t -= 1;
                p.offset = (Math.random() - 0.5) * 2;
            }

            // River width narrows along the path
            var riverWidth = CONFIG.riverWidthStart + (CONFIG.riverWidthEnd - CONFIG.riverWidthStart) * p.t;

            // Wobble
            var wobble = Math.sin(animTime * p.wobbleFreq + p.wobbleOff) * 0.3;
            var totalOffset = (p.offset + wobble) * riverWidth;

            var pos = bezierPoint(p.t);
            var tang = bezierTangent(p.t);
            var perp = perpendicular(tang);

            var px = pos.x + perp.x * totalOffset;
            var py = pos.y + perp.y * totalOffset;

            // Mouse gravity — subtle attraction
            if (mouse.x > 0 && mouse.y > 0) {
                var mdx = mouse.x - px;
                var mdy = mouse.y - py;
                var mDist = Math.sqrt(mdx * mdx + mdy * mdy);
                if (mDist < CONFIG.mouseRadius && mDist > 1) {
                    var force = (CONFIG.mouseRadius - mDist) / CONFIG.mouseRadius * CONFIG.mouseGravity;
                    px += mdx / mDist * force * 3;
                    py += mdy / mDist * force * 3;
                }
            }

            // Node gravity (vortex when active)
            if (targetActive >= 0) {
                var np = nodePositions[targetActive];
                var ndx = np.x - px;
                var ndy = np.y - py;
                var nDist = Math.sqrt(ndx * ndx + ndy * ndy);
                if (nDist < CONFIG.haloRadius * 2 && nDist > 1) {
                    var nForce = (CONFIG.haloRadius * 2 - nDist) / (CONFIG.haloRadius * 2) * 0.35;
                    px += ndx / nDist * nForce * 5;
                    py += ndy / nDist * nForce * 5;
                }
            }

            // Color and opacity — brighter along the path
            var c = lerpColor(p.t);
            var alpha = (0.2 + p.t * 0.6) * p.brightness;

            // Dim particles far from active node
            if (targetActive >= 0) {
                var nodeT = NODES[targetActive].t;
                var distT = Math.abs(p.t - nodeT);
                alpha *= Math.max(0.15, 1 - distT * 2);
            }

            // Size increases along path
            var size = p.size * (0.6 + p.t * 0.6);

            ctx.beginPath();
            ctx.arc(px, py, size, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + alpha.toFixed(3) + ')';
            ctx.fill();
        }
    }

    function drawNodes() {
        for (var i = 0; i < NODES.length; i++) {
            var node = NODES[i];
            var np = nodePositions[i];
            var c = CONFIG.colors[i];
            var isActive = (i === targetActive);

            // Animate scale
            var targetScale = isActive ? CONFIG.nodeHoverScale : 1;
            nodeScales[i] += (targetScale - nodeScales[i]) * CONFIG.nodeScaleSpeed;
            var scale = nodeScales[i];

            // Pulse idle animation
            var pulse = 1 + Math.sin(animTime * 1.5 + i * 1.2) * 0.04;
            var finalScale = scale * pulse;

            var baseR = CONFIG.nodeBaseRadius + node.sizeBonus;
            var r = baseR * finalScale;

            // Dim factor
            var dimFactor = 1;
            if (targetActive >= 0 && !isActive) {
                dimFactor = 0.25;
            }

            // Halo (radial gradient)
            if (scale > 1.05 || dimFactor === 1) {
                var haloR = r * 3;
                var halo = ctx.createRadialGradient(np.x, np.y, r * 0.5, np.x, np.y, haloR);
                halo.addColorStop(0, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.15 * dimFactor).toFixed(3) + ')');
                halo.addColorStop(1, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0)');
                ctx.beginPath();
                ctx.arc(np.x, np.y, haloR, 0, Math.PI * 2);
                ctx.fillStyle = halo;
                ctx.fill();
            }

            // Orbital rings
            var orbitSpeed = isActive ? CONFIG.orbitHoverSpeed : CONFIG.orbitBaseSpeed;
            for (var ring = 0; ring < node.rings; ring++) {
                var orbitR = r * (2 + ring * 1.2);
                var angle = animTime * orbitSpeed * (ring % 2 === 0 ? 1 : -0.7) + ring * 1.1;

                ctx.save();
                ctx.translate(np.x, np.y);
                ctx.rotate(angle);

                ctx.beginPath();
                // Draw partial arc (not full circle — gives spinning feel)
                var arcLen = Math.PI * (0.5 + ring * 0.3);
                ctx.arc(0, 0, orbitR, 0, arcLen);
                ctx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.3 * dimFactor * finalScale).toFixed(3) + ')';
                ctx.lineWidth = 1.2;

                // Dashed ring for extra complexity on higher nodes
                if (ring >= 1 && node.rings >= 2) {
                    ctx.setLineDash([4, 6]);
                } else {
                    ctx.setLineDash([]);
                }
                ctx.stroke();
                ctx.setLineDash([]);

                // Small orbiting dot
                var dotAngle = angle + arcLen * 0.5;
                var dotX = Math.cos(dotAngle) * orbitR;
                var dotY = Math.sin(dotAngle) * orbitR;
                ctx.beginPath();
                ctx.arc(dotX, dotY, 2 * finalScale, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.6 * dimFactor).toFixed(3) + ')';
                ctx.fill();

                ctx.restore();
            }

            // Core circle
            ctx.beginPath();
            ctx.arc(np.x, np.y, r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.85 * dimFactor).toFixed(3) + ')';
            ctx.fill();

            // Inner white highlight
            ctx.beginPath();
            ctx.arc(np.x - r * 0.2, np.y - r * 0.2, r * 0.4, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255, 255, 255, ' + (0.3 * dimFactor).toFixed(3) + ')';
            ctx.fill();
        }
    }

    // --- Hit testing ---
    function getHoveredNode(mx, my) {
        for (var i = 0; i < nodePositions.length; i++) {
            var np = nodePositions[i];
            var dx = mx - np.x;
            var dy = my - np.y;
            var r = (CONFIG.nodeBaseRadius + NODES[i].sizeBonus) * 3; // generous hit area
            if (dx * dx + dy * dy < r * r) {
                return i;
            }
        }
        return -1;
    }

    // --- Animation loop ---
    function animate() {
        if (w < 1 || h < 1 || window.innerWidth < MOBILE_BP) {
            raf = requestAnimationFrame(animate);
            return;
        }

        ctx.clearRect(0, 0, w, h);
        animTime += 0.016;

        drawPath();
        drawParticles();
        drawNodes();

        raf = requestAnimationFrame(animate);
    }

    // --- Events: mouse on canvas ---
    scene.addEventListener('mousemove', function (e) {
        if (window.innerWidth < MOBILE_BP) return;
        var rect = scene.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;

        var hovered = getHoveredNode(mouse.x, mouse.y);
        if (hovered >= 0) {
            handleEnter(hovered);
            scene.style.cursor = 'pointer';
        } else {
            if (targetActive >= 0) {
                handleLeave();
            }
            scene.style.cursor = 'default';
        }
    });

    scene.addEventListener('mouseleave', function () {
        mouse.x = -1000;
        mouse.y = -1000;
        handleLeave();
    });

    scene.addEventListener('click', function (e) {
        if (window.innerWidth < MOBILE_BP) return;
        var rect = scene.getBoundingClientRect();
        var mx = e.clientX - rect.left;
        var my = e.clientY - rect.top;
        if (getHoveredNode(mx, my) >= 0) {
            handleClick();
        }
    });

    // --- Events: labels ---
    for (var ll = 0; ll < labels.length; ll++) {
        (function (idx) {
            if (!labels[idx]) return;
            labels[idx].addEventListener('mouseenter', function () { handleEnter(idx); });
            labels[idx].addEventListener('mouseleave', handleLeave);
            labels[idx].addEventListener('click', handleClick);
        })(ll);
    }

    // --- Touch support ---
    scene.addEventListener('touchstart', function (e) {
        if (window.innerWidth < MOBILE_BP) return;
        var touch = e.touches[0];
        var rect = scene.getBoundingClientRect();
        var mx = touch.clientX - rect.left;
        var my = touch.clientY - rect.top;

        var target = e.target;
        var labelDiv = target.closest ? target.closest('.constellation__label') : null;

        if (labelDiv) {
            var svc = parseInt(labelDiv.getAttribute('data-service'), 10);
            if (targetActive === svc - 1) {
                activateNode(-1);
            } else {
                activateNode(svc - 1);
            }
            e.preventDefault();
            return;
        }

        var hovered = getHoveredNode(mx, my);
        if (hovered >= 0) {
            if (targetActive === hovered) {
                activateNode(-1);
            } else {
                activateNode(hovered);
            }
            e.preventDefault();
        } else {
            activateNode(-1);
        }
    }, { passive: false });

    // --- IntersectionObserver to pause when off-screen ---
    var isVisible = false;
    if (window.IntersectionObserver) {
        var observer = new IntersectionObserver(function (entries) {
            isVisible = entries[0].isIntersecting;
            if (isVisible && !raf) {
                raf = requestAnimationFrame(animate);
            }
        }, { threshold: 0.05 });
        observer.observe(scene);
    } else {
        isVisible = true;
    }

    // --- Init ---
    function init() {
        if (window.innerWidth < MOBILE_BP) return;
        resize();
        createParticles();
        if (!raf) {
            raf = requestAnimationFrame(animate);
        }
    }

    init();

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            resize();
            if (particles.length === 0) {
                createParticles();
            }
        }, 250);
    });

})();
