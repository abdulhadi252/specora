// ================================================
// js/common.js — Specora
// Shared utility functions used across multiple pages
// ================================================

/**
 * HTML-escape a string for safe insertion into innerHTML.
 */
window.htmlEscape = function(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
};

/**
 * Smooth scroll to an element with optional offset.
 */
window.smoothScrollTo = function(targetSelector, offset) {
    offset = offset || 80;
    var target = document.querySelector(targetSelector);
    if (!target) return;
    var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top: top, behavior: 'smooth' });
};

/**
 * Ripple effect for buttons.
 * Attach to buttons that have a .ripple child element.
 */
window.initRippleEffect = function(selector) {
    selector = selector || '.btn-primary, .btn-secondary, .btn-submit, .cta-btn-primary, .cta-btn-secondary';
    document.querySelectorAll(selector).forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var ripple = this.querySelector('.ripple');
            if (!ripple) return;
            var rect = this.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            ripple.style.animation = 'none';
            ripple.offsetHeight; // force reflow
            ripple.style.animation = 'rippleAnim .6s ease-out';
        });
    });
};

/**
 * Create an IntersectionObserver for scroll-reveal animations.
 * Elements with `selector` class get 'visible' class when scrolled into view.
 */
window.initScrollReveal = function(selector, threshold) {
    threshold = threshold || 0.08;
    var els = document.querySelectorAll(selector);
    if (!els.length) return null;
    var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: threshold });
    els.forEach(function(el) { obs.observe(el); });
    return obs;
};

/**
 * Clear error class on form field focus.
 */
window.initClearErrorsOnFocus = function() {
    document.querySelectorAll('.fl-input, .fl-textarea, .fl-select').forEach(function(el) {
        el.addEventListener('focus', function() {
            var group = this.closest('.form-group');
            if (group) group.classList.remove('error');
        });
    });
};

/**
 * Smooth scroll for anchor links.
 */
window.initSmoothAnchors = function(offset) {
    offset = offset || 80;
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var href = this.getAttribute('href');
            if (href === '#') return;
            e.preventDefault();
            var target = document.querySelector(href);
            if (target) {
                var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });
};
