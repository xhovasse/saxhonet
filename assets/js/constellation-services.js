/**
 * Saxho.net — Constellation Services (Diagonal Line Variant)
 * Riviere de particules lumineuses le long d'une diagonale ascendante,
 * 5 noeuds orbitaux + objets celestes decoratifs (planetes, satellites).
 * Click/tap sur un noeud → smooth-scroll vers la section detail.
 *
 * Mobile : riviere verticale avec labels alternes gauche/droite.
 */
(function () {
    'use strict';

    var scene = document.getElementById('constellation-svc');
    if (!scene) return;

    var canvas = document.getElementById('constellation-svc-canvas');
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
    var isMobile = false;

    // --- Configuration ---
    var CONFIG = {
        particleCount: 180,
        particleCountMobile: 80,
        particleSpeed: 0.0012,
        particleMinSize: 0.8,
        particleMaxSize: 2.8,
        riverWidthStart: 45,
        riverWidthEnd: 12,
        riverWidthMobile: 20,

        nodeBaseRadius: 6,
        nodeBaseRadiusMobile: 5,
        nodeHoverScale: 1.5,
        nodeScaleSpeed: 0.08,
        orbitBaseSpeed: 0.4,
        orbitHoverSpeed: 1.8,
        haloRadius: 50,

        pathPadding: 0.10,
        pathPaddingMobile: 0.06,

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

    // Node definitions
    var NODES = [
        { t: 0.10, rings: 1, sizeBonus: 0 },
        { t: 0.30, rings: 1, sizeBonus: 2 },
        { t: 0.50, rings: 2, sizeBonus: 4 },
        { t: 0.70, rings: 2, sizeBonus: 6 },
        { t: 0.90, rings: 3, sizeBonus: 8 }
    ];

    // --- Celestial objects (decorative, desktop only) ---
    // Positioned in normalized coords (0-1), placed in empty zones around the path
    var CELESTIALS = [
        { nx: 0.12, ny: 0.22, r: 4, ringTilt: 0.35, hasMoon: true,  colorIdx: 4, speed: 0.3  },
        { nx: 0.85, ny: 0.75, r: 5, ringTilt: 0.50, hasMoon: true,  colorIdx: 0, speed: 0.25 },
        { nx: 0.30, ny: 0.08, r: 3, ringTilt: 0.20, hasMoon: false, colorIdx: 2, speed: 0.4  },
        { nx: 0.75, ny: 0.15, r: 3, ringTilt: 0.45, hasMoon: true,  colorIdx: 3, speed: 0.35 },
        { nx: 0.08, ny: 0.60, r: 3, ringTilt: 0.30, hasMoon: false, colorIdx: 1, speed: 0.45 },
        { nx: 0.92, ny: 0.45, r: 4, ringTilt: 0.40, hasMoon: true,  colorIdx: 2, speed: 0.28 }
    ];
    var celestialPositions = []; // {x, y} in px

    // --- State ---
    var particles = [];
    var mouse = { x: -1000, y: -1000 };
    var activeNode = -1;
    var targetActive = -1;
    var leaveTimer = null;
    var nodePositions = [];
    var nodeScales = [1, 1, 1, 1, 1];

    // Line endpoints
    var lineStart = { x: 0, y: 0 };
    var lineEnd = { x: 0, y: 0 };
    var cachedTangent = { x: 0, y: 0 };
    var cachedPerp = { x: 0, y: 0 };

    // Label elements
    var labels = [];
    for (var li = 0; li < NODES.length; li++) {
        var lbl = scene.querySelector('.constellation-svc__label--' + (li + 1));
        labels.push(lbl);
    }

    // --- Line math ---
    function linePoint(t) {
        return {
            x: lineStart.x + (lineEnd.x - lineStart.x) * t,
            y: lineStart.y + (lineEnd.y - lineStart.y) * t
        };
    }

    // --- Color interpolation ---
    function lerpColor(t) {
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
        isMobile = window.innerWidth < MOBILE_BP;

        var rect = scene.getBoundingClientRect();
        w = Math.floor(rect.width);
        h = Math.floor(rect.height);
        if (w < 1 || h < 1) return;

        canvas.width = w * dpr;
        canvas.height = h * dpr;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        if (isMobile) {
            // Vertical line: top-center → bottom-center
            var padY = h * CONFIG.pathPaddingMobile;
            var cx = w * 0.5;
            lineStart.x = cx;
            lineStart.y = padY;
            lineEnd.x = cx;
            lineEnd.y = h - padY;
        } else {
            // Diagonal: bottom-left → top-right
            var pad = w * CONFIG.pathPadding;
            lineStart.x = pad;
            lineStart.y = h - pad;
            lineEnd.x = w - pad;
            lineEnd.y = pad;
        }

        // Tangent and perpendicular
        var dx = lineEnd.x - lineStart.x;
        var dy = lineEnd.y - lineStart.y;
        var len = Math.sqrt(dx * dx + dy * dy) || 1;
        cachedTangent.x = dx / len;
        cachedTangent.y = dy / len;
        cachedPerp.x = -cachedTangent.y;
        cachedPerp.y = cachedTangent.x;

        // Node positions
        nodePositions = [];
        for (var n = 0; n < NODES.length; n++) {
            nodePositions.push(linePoint(NODES[n].t));
        }

        // Celestial positions (desktop only)
        if (!isMobile) {
            celestialPositions = [];
            for (var ci = 0; ci < CELESTIALS.length; ci++) {
                celestialPositions.push({
                    x: CELESTIALS[ci].nx * w,
                    y: CELESTIALS[ci].ny * h
                });
            }
        }

        positionLabels();
    }

    // --- Position HTML labels ---
    function positionLabels() {
        if (isMobile) {
            positionLabelsMobile();
            return;
        }

        for (var i = 0; i < NODES.length; i++) {
            if (!labels[i] || !nodePositions[i]) continue;
            var np = nodePositions[i];
            var labelW = labels[i].offsetWidth || 180;
            var labelH = labels[i].offsetHeight || 60;
            var nodeR = (CONFIG.nodeBaseRadius + NODES[i].sizeBonus) * 2.5;
            var gap = nodeR + 14;
            var offsetX, dir;

            if (i % 2 === 0) {
                offsetX = np.x - labelW - gap;
                dir = 'right';
            } else {
                offsetX = np.x + gap;
                dir = 'left';
            }

            var vShift = (i % 2 === 0) ? -labelH * 0.15 : labelH * 0.15;
            var offsetY = np.y - labelH * 0.4 + vShift;

            offsetX = Math.max(8, Math.min(w - labelW - 8, offsetX));
            offsetY = Math.max(8, Math.min(h - labelH - 8, offsetY));

            labels[i].style.left = offsetX + 'px';
            labels[i].style.top = offsetY + 'px';
            labels[i].style.textAlign = dir;
        }
    }

    function positionLabelsMobile() {
        var centerX = w * 0.5;
        var labelMargin = 28; // gap from center line

        for (var i = 0; i < NODES.length; i++) {
            if (!labels[i] || !nodePositions[i]) continue;
            var np = nodePositions[i];
            var labelH = labels[i].offsetHeight || 50;

            // Alternate: even → left, odd → right
            if (i % 2 === 0) {
                // Label on the left
                labels[i].style.left = '8px';
                labels[i].style.right = '';
                labels[i].style.width = (centerX - labelMargin - 8) + 'px';
                labels[i].style.textAlign = 'right';
            } else {
                // Label on the right
                labels[i].style.left = (centerX + labelMargin) + 'px';
                labels[i].style.right = '';
                labels[i].style.width = (centerX - labelMargin - 8) + 'px';
                labels[i].style.textAlign = 'left';
            }

            labels[i].style.top = (np.y - labelH * 0.5) + 'px';
        }
    }

    // --- Create particles ---
    function createParticles() {
        particles = [];
        var count = isMobile ? CONFIG.particleCountMobile : CONFIG.particleCount;
        for (var i = 0; i < count; i++) {
            particles.push({
                t: Math.random(),
                speed: CONFIG.particleSpeed * (0.6 + Math.random() * 0.8),
                offset: (Math.random() - 0.5) * 2,
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
            scene.classList.add('constellation-svc--has-active');
        } else {
            scene.classList.remove('constellation-svc--has-active');
        }

        for (var i = 0; i < labels.length; i++) {
            if (labels[i]) {
                if (i === index) {
                    labels[i].classList.add('constellation-svc__label--active');
                } else {
                    labels[i].classList.remove('constellation-svc__label--active');
                }
            }
        }
    }

    function handleEnter(index) {
        clearTimeout(leaveTimer);
        activateNode(index);
    }

    function handleLeave() {
        clearTimeout(leaveTimer);
        leaveTimer = setTimeout(function () {
            activateNode(-1);
        }, CONFIG.leaveDelay);
    }

    function handleClick(nodeIndex) {
        var target = document.getElementById('service-' + (nodeIndex + 1));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // --- Drawing ---
    function drawPath() {
        var steps = 80;
        for (var i = 0; i < steps; i++) {
            var t0 = i / steps;
            var t1 = (i + 1) / steps;
            var p0 = linePoint(t0);
            var p1 = linePoint(t1);
            var c = lerpColor((t0 + t1) * 0.5);

            var dimFactor = 1;
            if (targetActive >= 0) {
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
        var riverW = isMobile ? CONFIG.riverWidthMobile : 0;

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];

            var speedMul = 1 + p.t * 0.5;
            p.t += p.speed * speedMul;
            if (p.t > 1) {
                p.t -= 1;
                p.offset = (Math.random() - 0.5) * 2;
            }

            var riverWidth = isMobile
                ? CONFIG.riverWidthMobile
                : CONFIG.riverWidthStart + (CONFIG.riverWidthEnd - CONFIG.riverWidthStart) * p.t;

            var wobble = Math.sin(animTime * p.wobbleFreq + p.wobbleOff) * 0.3;
            var totalOffset = (p.offset + wobble) * riverWidth;

            var pos = linePoint(p.t);
            var px = pos.x + cachedPerp.x * totalOffset;
            var py = pos.y + cachedPerp.y * totalOffset;

            // Mouse gravity (desktop only)
            if (!isMobile && mouse.x > 0 && mouse.y > 0) {
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

            var c = lerpColor(p.t);
            var alpha = (0.2 + p.t * 0.6) * p.brightness;

            if (targetActive >= 0) {
                var nodeT = NODES[targetActive].t;
                var distT = Math.abs(p.t - nodeT);
                alpha *= Math.max(0.15, 1 - distT * 2);
            }

            var size = p.size * (0.6 + p.t * 0.6);
            if (isMobile) size *= 0.7;

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

            var targetScale = isActive ? CONFIG.nodeHoverScale : 1;
            nodeScales[i] += (targetScale - nodeScales[i]) * CONFIG.nodeScaleSpeed;
            var scale = nodeScales[i];

            var pulse = 1 + Math.sin(animTime * 1.5 + i * 1.2) * 0.04;
            var finalScale = scale * pulse;

            var baseR = isMobile ? CONFIG.nodeBaseRadiusMobile : CONFIG.nodeBaseRadius;
            baseR += node.sizeBonus * (isMobile ? 0.5 : 1);
            var r = baseR * finalScale;

            var dimFactor = 1;
            if (targetActive >= 0 && !isActive) {
                dimFactor = 0.25;
            }

            // Halo
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
            var ringCount = isMobile ? Math.min(node.rings, 1) : node.rings;
            for (var ring = 0; ring < ringCount; ring++) {
                var orbitR = r * (2 + ring * 1.2);
                var angle = animTime * orbitSpeed * (ring % 2 === 0 ? 1 : -0.7) + ring * 1.1;

                ctx.save();
                ctx.translate(np.x, np.y);
                ctx.rotate(angle);

                ctx.beginPath();
                var arcLen = Math.PI * (0.5 + ring * 0.3);
                ctx.arc(0, 0, orbitR, 0, arcLen);
                ctx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.3 * dimFactor * finalScale).toFixed(3) + ')';
                ctx.lineWidth = 1.2;

                if (ring >= 1 && node.rings >= 2) {
                    ctx.setLineDash([4, 6]);
                } else {
                    ctx.setLineDash([]);
                }
                ctx.stroke();
                ctx.setLineDash([]);

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

    // --- Celestial objects (desktop only) ---
    function drawCelestials() {
        if (isMobile) return;

        for (var i = 0; i < CELESTIALS.length; i++) {
            var cel = CELESTIALS[i];
            var cp = celestialPositions[i];
            if (!cp) continue;

            var c = CONFIG.colors[cel.colorIdx];
            var angle = animTime * cel.speed;
            var pulse = 1 + Math.sin(animTime * 0.8 + i * 2.0) * 0.06;
            var r = cel.r * pulse;

            // Dim if a node is active (they're background decoration)
            var dimFactor = targetActive >= 0 ? 0.15 : 0.35;

            // Core body
            ctx.beginPath();
            ctx.arc(cp.x, cp.y, r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + dimFactor.toFixed(3) + ')';
            ctx.fill();

            // Tilted ring (ellipse via save/scale)
            ctx.save();
            ctx.translate(cp.x, cp.y);
            ctx.rotate(angle * 0.3 + i);
            ctx.scale(1, cel.ringTilt);
            ctx.beginPath();
            ctx.arc(0, 0, r * 2.5, 0, Math.PI * 2);
            ctx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (dimFactor * 0.6).toFixed(3) + ')';
            ctx.lineWidth = 0.8;
            ctx.stroke();
            ctx.restore();

            // Moon / satellite
            if (cel.hasMoon) {
                var moonDist = r * 4;
                var moonAngle = angle * 1.5 + i * 1.7;
                var moonX = cp.x + Math.cos(moonAngle) * moonDist;
                var moonY = cp.y + Math.sin(moonAngle) * moonDist * 0.6; // flattened orbit
                ctx.beginPath();
                ctx.arc(moonX, moonY, 1.2, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (dimFactor * 0.8).toFixed(3) + ')';
                ctx.fill();
            }
        }
    }

    // --- Hit testing ---
    function getHoveredNode(mx, my) {
        for (var i = 0; i < nodePositions.length; i++) {
            var np = nodePositions[i];
            var dx = mx - np.x;
            var dy = my - np.y;
            var r = (CONFIG.nodeBaseRadius + NODES[i].sizeBonus) * 3;
            if (dx * dx + dy * dy < r * r) {
                return i;
            }
        }
        return -1;
    }

    // --- Animation loop ---
    function animate() {
        if (w < 1 || h < 1) {
            raf = requestAnimationFrame(animate);
            return;
        }

        ctx.clearRect(0, 0, w, h);
        animTime += 0.016;

        drawCelestials(); // Background layer (behind path)
        drawPath();
        drawParticles();
        drawNodes();

        raf = requestAnimationFrame(animate);
    }

    // --- Events: mouse on canvas (desktop) ---
    scene.addEventListener('mousemove', function (e) {
        if (isMobile) return;
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
        if (isMobile) return;
        var rect = scene.getBoundingClientRect();
        var mx = e.clientX - rect.left;
        var my = e.clientY - rect.top;
        var hovered = getHoveredNode(mx, my);
        if (hovered >= 0) {
            handleClick(hovered);
        }
    });

    // --- Events: labels ---
    for (var ll = 0; ll < labels.length; ll++) {
        (function (idx) {
            if (!labels[idx]) return;
            labels[idx].addEventListener('mouseenter', function () { handleEnter(idx); });
            labels[idx].addEventListener('mouseleave', handleLeave);
        })(ll);
    }

    // --- Touch support ---
    scene.addEventListener('touchstart', function (e) {
        var touch = e.touches[0];
        var rect = scene.getBoundingClientRect();
        var mx = touch.clientX - rect.left;
        var my = touch.clientY - rect.top;

        var target = e.target;
        var labelEl = target.closest ? target.closest('.constellation-svc__label') : null;

        if (labelEl) {
            var svc = parseInt(labelEl.getAttribute('data-service'), 10);
            if (targetActive === svc - 1) {
                handleClick(svc - 1);
            } else {
                activateNode(svc - 1);
            }
            e.preventDefault();
            return;
        }

        var hovered = getHoveredNode(mx, my);
        if (hovered >= 0) {
            if (targetActive === hovered) {
                handleClick(hovered);
            } else {
                activateNode(hovered);
            }
            e.preventDefault();
        } else {
            activateNode(-1);
        }
    }, { passive: false });

    // --- IntersectionObserver ---
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
            var wasMobile = isMobile;
            resize();
            // Recreate particles if switching between mobile/desktop
            if (wasMobile !== isMobile || particles.length === 0) {
                createParticles();
            }
        }, 250);
    });

    // =============================================
    // Detail Planet Icons + Background Orbital Fields
    // =============================================
    (function initDetailPlanets() {
        var iconCanvases = document.querySelectorAll('canvas.service-detail__planet');
        var bgCanvases = document.querySelectorAll('canvas.service-detail__bg');
        if (!iconCanvases.length) return;

        // Config per service (progression: simple → complex)
        var PLANET_CONFIGS = [
            { rings: 1, ringTilt: 0.35, speed: 0.4,  moonCount: 1, bgRings: 3, bgParticles: 25 },
            { rings: 1, ringTilt: 0.40, speed: 0.35, moonCount: 1, bgRings: 4, bgParticles: 30 },
            { rings: 2, ringTilt: 0.30, speed: 0.3,  moonCount: 1, bgRings: 5, bgParticles: 35 },
            { rings: 2, ringTilt: 0.45, speed: 0.25, moonCount: 2, bgRings: 6, bgParticles: 40 },
            { rings: 3, ringTilt: 0.50, speed: 0.2,  moonCount: 2, bgRings: 8, bgParticles: 50 }
        ];

        var planets = [];
        var backgrounds = [];
        var detailRaf = null;
        var pDpr = Math.min(window.devicePixelRatio || 1, 2);

        // --- Collect icon canvases ---
        for (var pi = 0; pi < iconCanvases.length; pi++) {
            var cvs = iconCanvases[pi];
            var idx = parseInt(cvs.getAttribute('data-service-idx'), 10);
            if (isNaN(idx) || idx < 0 || idx >= CONFIG.colors.length) continue;

            var pCtx = cvs.getContext('2d');
            var displayW = cvs.offsetWidth || 80;
            var displayH = cvs.offsetHeight || 80;
            cvs.width = displayW * pDpr;
            cvs.height = displayH * pDpr;
            pCtx.setTransform(pDpr, 0, 0, pDpr, 0, 0);

            planets.push({
                canvas: cvs,
                ctx: pCtx,
                w: displayW,
                h: displayH,
                idx: idx,
                cfg: PLANET_CONFIGS[idx] || PLANET_CONFIGS[0],
                color: CONFIG.colors[idx]
            });
        }

        // --- Collect background canvases + create orbital particles ---
        for (var bi = 0; bi < bgCanvases.length; bi++) {
            var bgCvs = bgCanvases[bi];
            var bgIdx = parseInt(bgCvs.getAttribute('data-service-bg'), 10);
            if (isNaN(bgIdx) || bgIdx < 0 || bgIdx >= CONFIG.colors.length) continue;

            var bgCtx = bgCvs.getContext('2d');
            var section = bgCvs.parentElement;
            var sW = section.offsetWidth;
            var sH = section.offsetHeight;
            bgCvs.width = sW * pDpr;
            bgCvs.height = sH * pDpr;
            bgCtx.setTransform(pDpr, 0, 0, pDpr, 0, 0);

            var cfg = PLANET_CONFIGS[bgIdx] || PLANET_CONFIGS[0];
            var c = CONFIG.colors[bgIdx];

            // Find the planet icon position relative to section
            var planetIcon = section.querySelector('.service-detail__planet');
            var originX = 80; // default
            var originY = 80;
            if (planetIcon) {
                var secRect = section.getBoundingClientRect();
                var iconRect = planetIcon.getBoundingClientRect();
                originX = iconRect.left - secRect.left + iconRect.width * 0.5;
                originY = iconRect.top - secRect.top + iconRect.height * 0.5;
            }

            // Create floating particles for this background
            var bgParticles = [];
            for (var pp = 0; pp < cfg.bgParticles; pp++) {
                bgParticles.push({
                    angle: Math.random() * Math.PI * 2,
                    radius: 60 + Math.random() * Math.min(sW, sH) * 0.45,
                    speed: 0.0003 + Math.random() * 0.0008,
                    size: 1 + Math.random() * 2.5,
                    brightness: 0.15 + Math.random() * 0.25,
                    tiltFactor: 0.3 + Math.random() * 0.5,
                    phaseOff: Math.random() * Math.PI * 2
                });
            }

            backgrounds.push({
                canvas: bgCvs,
                ctx: bgCtx,
                w: sW,
                h: sH,
                idx: bgIdx,
                cfg: cfg,
                color: c,
                originX: originX,
                originY: originY,
                particles: bgParticles,
                visible: false,
                section: section
            });
        }

        if (!planets.length && !backgrounds.length) return;

        // Reduced motion: draw static icon only, no background animation
        if (prefersReduced) {
            for (var si = 0; si < planets.length; si++) {
                drawPlanetStatic(planets[si]);
            }
            return;
        }

        // IntersectionObserver on background canvases (they represent the section)
        if (window.IntersectionObserver) {
            var detailObs = new IntersectionObserver(function (entries) {
                for (var ei = 0; ei < entries.length; ei++) {
                    for (var bj = 0; bj < backgrounds.length; bj++) {
                        if (backgrounds[bj].canvas === entries[ei].target) {
                            backgrounds[bj].visible = entries[ei].isIntersecting;
                            break;
                        }
                    }
                }
                var anyVis = false;
                for (var av = 0; av < backgrounds.length; av++) {
                    if (backgrounds[av].visible) { anyVis = true; break; }
                }
                if (anyVis && !detailRaf) {
                    detailRaf = requestAnimationFrame(animateDetails);
                }
            }, { threshold: 0.05 });

            for (var oi = 0; oi < backgrounds.length; oi++) {
                detailObs.observe(backgrounds[oi].canvas);
            }
        } else {
            for (var fi = 0; fi < backgrounds.length; fi++) {
                backgrounds[fi].visible = true;
            }
        }

        if (!detailRaf) {
            detailRaf = requestAnimationFrame(animateDetails);
        }

        // --- Animation loop for both icon + background ---
        function animateDetails() {
            var anyVis = false;
            for (var i = 0; i < backgrounds.length; i++) {
                if (backgrounds[i].visible) {
                    anyVis = true;
                    drawBackground(backgrounds[i]);
                    // Draw the matching icon planet
                    for (var j = 0; j < planets.length; j++) {
                        if (planets[j].idx === backgrounds[i].idx) {
                            drawPlanet(planets[j]);
                            break;
                        }
                    }
                }
            }
            if (anyVis) {
                detailRaf = requestAnimationFrame(animateDetails);
            } else {
                detailRaf = null;
            }
        }

        // --- Draw background: wide orbits, floating particles, satellites ---
        function drawBackground(bg) {
            var bCtx = bg.ctx;
            var c = bg.color;
            var cfg = bg.cfg;
            var ox = bg.originX;
            var oy = bg.originY;

            bCtx.clearRect(0, 0, bg.w, bg.h);

            // Large halo around origin
            var haloR = Math.min(bg.w, bg.h) * 0.4;
            var halo = bCtx.createRadialGradient(ox, oy, 20, ox, oy, haloR);
            halo.addColorStop(0, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0.06)');
            halo.addColorStop(1, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0)');
            bCtx.beginPath();
            bCtx.arc(ox, oy, haloR, 0, Math.PI * 2);
            bCtx.fillStyle = halo;
            bCtx.fill();

            // Wide orbital rings expanding from icon position
            var maxOrbitR = Math.max(bg.w, bg.h) * 0.55;
            var ringStep = maxOrbitR / (cfg.bgRings + 1);

            for (var ring = 0; ring < cfg.bgRings; ring++) {
                var orbitR = ringStep * (ring + 1);
                var rotAngle = animTime * cfg.speed * 0.15 * (ring % 2 === 0 ? 1 : -1) + ring * 0.4 + bg.idx * 0.3;

                bCtx.save();
                bCtx.translate(ox, oy);
                bCtx.rotate(rotAngle);
                bCtx.scale(1, cfg.ringTilt + ring * 0.04);

                bCtx.beginPath();
                bCtx.arc(0, 0, orbitR, 0, Math.PI * 2);

                // Outer rings more subtle
                var ringAlpha = 0.06 - ring * 0.005;
                if (ringAlpha < 0.015) ringAlpha = 0.015;
                bCtx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + ringAlpha.toFixed(4) + ')';
                bCtx.lineWidth = ring < 2 ? 1 : 0.6;

                if (ring >= 2) {
                    bCtx.setLineDash([6, 10]);
                } else {
                    bCtx.setLineDash([]);
                }
                bCtx.stroke();
                bCtx.setLineDash([]);

                // Satellite dot on every other ring
                if (ring % 2 === 0) {
                    var satAngle = rotAngle + animTime * cfg.speed * 0.5 + ring * 1.3;
                    var satX = Math.cos(satAngle) * orbitR;
                    var satY = Math.sin(satAngle) * orbitR * (cfg.ringTilt + ring * 0.04);
                    bCtx.restore();

                    bCtx.beginPath();
                    bCtx.arc(ox + satX, oy + satY, 1.5 + (ring < 2 ? 0.5 : 0), 0, Math.PI * 2);
                    bCtx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + (0.12 + (ring < 2 ? 0.06 : 0)).toFixed(3) + ')';
                    bCtx.fill();
                } else {
                    bCtx.restore();
                }
            }

            // Floating particles along the orbits
            for (var pi = 0; pi < bg.particles.length; pi++) {
                var part = bg.particles[pi];
                part.angle += part.speed;

                var px = ox + Math.cos(part.angle + part.phaseOff) * part.radius;
                var py = oy + Math.sin(part.angle + part.phaseOff) * part.radius * part.tiltFactor;

                // Gentle wobble
                var wobble = Math.sin(animTime * 0.8 + pi * 2.1) * 8;
                px += wobble;
                py += wobble * 0.5;

                // Fade particles near edges
                var edgeFade = 1;
                if (px < 20) edgeFade = px / 20;
                else if (px > bg.w - 20) edgeFade = (bg.w - px) / 20;
                if (py < 20) edgeFade *= py / 20;
                else if (py > bg.h - 20) edgeFade *= (bg.h - py) / 20;
                edgeFade = Math.max(0, Math.min(1, edgeFade));

                var alpha = part.brightness * edgeFade;

                bCtx.beginPath();
                bCtx.arc(px, py, part.size, 0, Math.PI * 2);
                bCtx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + alpha.toFixed(3) + ')';
                bCtx.fill();
            }
        }

        // --- Draw icon planet (compact, 80×80) ---
        function drawPlanetStatic(p) {
            var cx = p.w * 0.5;
            var cy = p.h * 0.5;
            var c = p.color;
            var r = 10;

            p.ctx.clearRect(0, 0, p.w, p.h);

            var halo = p.ctx.createRadialGradient(cx, cy, r * 0.5, cx, cy, r * 3);
            halo.addColorStop(0, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0.15)');
            halo.addColorStop(1, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0)');
            p.ctx.beginPath();
            p.ctx.arc(cx, cy, r * 3, 0, Math.PI * 2);
            p.ctx.fillStyle = halo;
            p.ctx.fill();

            p.ctx.beginPath();
            p.ctx.arc(cx, cy, r, 0, Math.PI * 2);
            p.ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0.9)';
            p.ctx.fill();

            p.ctx.beginPath();
            p.ctx.arc(cx - r * 0.2, cy - r * 0.2, r * 0.4, 0, Math.PI * 2);
            p.ctx.fillStyle = 'rgba(255, 255, 255, 0.35)';
            p.ctx.fill();
        }

        function drawPlanet(p) {
            var cx = p.w * 0.5;
            var cy = p.h * 0.5;
            var c = p.color;
            var cfg = p.cfg;

            var pulse = 1 + Math.sin(animTime * 1.2 + p.idx * 1.5) * 0.06;
            var r = 10 * pulse;

            p.ctx.clearRect(0, 0, p.w, p.h);

            // Halo
            var haloR = r * 3.2;
            var halo = p.ctx.createRadialGradient(cx, cy, r * 0.5, cx, cy, haloR);
            halo.addColorStop(0, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0.12)');
            halo.addColorStop(1, 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0)');
            p.ctx.beginPath();
            p.ctx.arc(cx, cy, haloR, 0, Math.PI * 2);
            p.ctx.fillStyle = halo;
            p.ctx.fill();

            // Orbital rings on icon
            for (var ring = 0; ring < cfg.rings; ring++) {
                var orbitR = r * (2.2 + ring * 1.0);
                var angle = animTime * cfg.speed * (ring % 2 === 0 ? 1 : -0.7) + ring * 0.8 + p.idx * 0.5;

                p.ctx.save();
                p.ctx.translate(cx, cy);
                p.ctx.rotate(angle * 0.3 + p.idx * 0.7);
                p.ctx.scale(1, cfg.ringTilt);

                p.ctx.beginPath();
                p.ctx.arc(0, 0, orbitR, 0, Math.PI * 2);
                p.ctx.strokeStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0.25)';
                p.ctx.lineWidth = ring >= 1 ? 0.8 : 1;

                if (ring >= 1) {
                    p.ctx.setLineDash([3, 5]);
                } else {
                    p.ctx.setLineDash([]);
                }
                p.ctx.stroke();
                p.ctx.setLineDash([]);
                p.ctx.restore();
            }

            // Satellites on icon
            for (var mi = 0; mi < cfg.moonCount; mi++) {
                var moonOrbitR = r * (3.0 + mi * 1.2);
                var moonAngle = animTime * cfg.speed * 1.5 + mi * 2.3 + p.idx * 1.7;
                var moonX = cx + Math.cos(moonAngle) * moonOrbitR;
                var moonY = cy + Math.sin(moonAngle) * moonOrbitR * cfg.ringTilt;

                p.ctx.beginPath();
                p.ctx.arc(moonX, moonY, 1.5, 0, Math.PI * 2);
                p.ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0.6)';
                p.ctx.fill();
            }

            // Core
            p.ctx.beginPath();
            p.ctx.arc(cx, cy, r, 0, Math.PI * 2);
            p.ctx.fillStyle = 'rgba(' + c.r + ',' + c.g + ',' + c.b + ', 0.9)';
            p.ctx.fill();

            // Highlight
            p.ctx.beginPath();
            p.ctx.arc(cx - r * 0.2, cy - r * 0.2, r * 0.4, 0, Math.PI * 2);
            p.ctx.fillStyle = 'rgba(255, 255, 255, 0.35)';
            p.ctx.fill();
        }

        // --- Resize handler ---
        var detailResizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(detailResizeTimer);
            detailResizeTimer = setTimeout(function () {
                // Resize icon canvases
                for (var ri = 0; ri < planets.length; ri++) {
                    var pp = planets[ri];
                    var dw = pp.canvas.offsetWidth || 80;
                    var dh = pp.canvas.offsetHeight || 80;
                    if (dw !== pp.w || dh !== pp.h) {
                        pp.w = dw;
                        pp.h = dh;
                        pp.canvas.width = dw * pDpr;
                        pp.canvas.height = dh * pDpr;
                        pp.ctx.setTransform(pDpr, 0, 0, pDpr, 0, 0);
                    }
                }
                // Resize background canvases
                for (var rb = 0; rb < backgrounds.length; rb++) {
                    var bg = backgrounds[rb];
                    var sec = bg.section;
                    var sW = sec.offsetWidth;
                    var sH = sec.offsetHeight;
                    if (sW !== bg.w || sH !== bg.h) {
                        bg.w = sW;
                        bg.h = sH;
                        bg.canvas.width = sW * pDpr;
                        bg.canvas.height = sH * pDpr;
                        bg.ctx.setTransform(pDpr, 0, 0, pDpr, 0, 0);
                    }
                    // Recalculate origin from planet icon position
                    var planetIcon = sec.querySelector('.service-detail__planet');
                    if (planetIcon) {
                        var secRect = sec.getBoundingClientRect();
                        var iconRect = planetIcon.getBoundingClientRect();
                        bg.originX = iconRect.left - secRect.left + iconRect.width * 0.5;
                        bg.originY = iconRect.top - secRect.top + iconRect.height * 0.5;
                    }
                    // Redistribute particles for new dimensions
                    for (var rp = 0; rp < bg.particles.length; rp++) {
                        bg.particles[rp].radius = 60 + Math.random() * Math.min(sW, sH) * 0.45;
                    }
                }
            }, 250);
        });
    })();

})();
