/**
 * Saxho.net — Typed text effect for hero headline
 */

(function () {
    'use strict';

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var typedEl = document.getElementById('hero-typed');
    if (!typedEl) return;

    var text = typedEl.getAttribute('data-typed-text') || typedEl.textContent;
    var speed = parseInt(typedEl.getAttribute('data-typed-speed'), 10) || 50;

    if (prefersReducedMotion) {
        // Show text immediately
        typedEl.textContent = text;
        typedEl.style.opacity = '1';
        return;
    }

    // Clear and prepare
    typedEl.textContent = '';
    typedEl.style.opacity = '1';

    // Create cursor
    var cursor = document.createElement('span');
    cursor.className = 'typed-cursor';
    cursor.innerHTML = '&nbsp;';
    typedEl.parentNode.insertBefore(cursor, typedEl.nextSibling);

    // Type character by character
    var index = 0;

    function typeChar() {
        if (index < text.length) {
            typedEl.textContent += text.charAt(index);
            index++;
            setTimeout(typeChar, speed);
        } else {
            // Remove cursor after a pause
            setTimeout(function () {
                cursor.style.animation = 'none';
                cursor.style.opacity = '0';
                cursor.style.transition = 'opacity 0.5s ease';
            }, 2000);
        }
    }

    // Start typing after a short delay
    setTimeout(typeChar, 500);

})();
