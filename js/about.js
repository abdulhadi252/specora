// ================================================
// js/about.js — Specora
// About page JavaScript — scroll reveal, counters, FAQ accordion, parallax
// ================================================

// ── WHO WE ARE SCROLL REVEAL ──
(function() {
    var wwaObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) { e.target.classList.add('visible'); wwaObs.unobserve(e.target); }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal-wwa').forEach(function(el) { wwaObs.observe(el); });
})();

// ── ANIMATED COUNTERS (trigger on scroll) ──
function animateCounters() {
    document.querySelectorAll('.counter').forEach(function(counter) {
        var target = parseInt(counter.dataset.target);
        if (counter.dataset.animated) return;
        counter.dataset.animated = 'true';
        var duration = 1200;
        var start = performance.now();
        function update(now) {
            var elapsed = now - start;
            var progress = Math.min(elapsed / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            counter.textContent = Math.floor(eased * target);
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    });
}

(function() {
    var counterObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) { animateCounters(); counterObs.unobserve(e.target); }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('.hero-stats').forEach(function(el) { counterObs.observe(el); });
})();

// ── WWA IMAGE SCROLL FADE-OUT ──
(function() {
    var wwaFadeImg = document.getElementById('wwaFadeImg');
    var wwaImgWrap = document.getElementById('wwaImgWrap');
    if (wwaFadeImg) {
        var thresholds = Array.from({ length: 21 }, function(_, i) { return i * 0.05; });
        var fadeObs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                var ratio = Math.max(0.1, Math.min(1, e.intersectionRatio));
                wwaFadeImg.style.opacity = ratio;
                if (wwaImgWrap) wwaImgWrap.style.opacity = ratio;
            });
        }, { threshold: thresholds });
        fadeObs.observe(wwaImgWrap || wwaFadeImg);
    }
})();

// ── WHAT MAKES SPECORA DIFFERENT SCROLL REVEAL ──
(function() {
    var discObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) { e.target.classList.add('visible'); discObs.unobserve(e.target); }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal-disc').forEach(function(el) { discObs.observe(el); });
})();

// ── FAQ ACCORDION ──
function toggleFaq(btn) {
    var item = btn.closest('.faq-item');
    var wasOpen = item.classList.contains('open');
    var answer = item.querySelector('.faq-answer');
    if (wasOpen) {
        btn.setAttribute('aria-expanded', 'false');
        answer.style.maxHeight = '0';
        setTimeout(function() { item.classList.remove('open'); }, 350);
    } else {
        document.querySelectorAll('.faq-item.open').forEach(function(other) {
            var otherBtn = other.querySelector('.faq-trigger');
            var otherAns = other.querySelector('.faq-answer');
            if (otherBtn) otherBtn.setAttribute('aria-expanded', 'false');
            if (otherAns) otherAns.style.maxHeight = '0';
            setTimeout(function() { other.classList.remove('open'); }, 350);
        });
        setTimeout(function() {
            item.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        }, 50);
    }
}

// ── FAQ SCROLL REVEAL ──
(function() {
    var faqObs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) { e.target.classList.add('visible'); faqObs.unobserve(e.target); }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.reveal-faq').forEach(function(el) { faqObs.observe(el); });
})();

// ── DASHBOARD MOUSE PARALLAX (desktop only) ──
(function() {
    var dashboard = document.getElementById('dashboard');
    if (dashboard && window.innerWidth > 1024) {
        var heroRight = document.querySelector('.hero-right');
        if (heroRight) {
            heroRight.addEventListener('mousemove', function(e) {
                var rect = this.getBoundingClientRect();
                var x = (e.clientX - rect.left) / rect.width - 0.5;
                var y = (e.clientY - rect.top) / rect.height - 0.5;
                dashboard.style.transform = 'perspective(1200px) rotateY(' + (x * 1.5) + 'deg) rotateX(' + (-y * 1.2) + 'deg)';
            });
            heroRight.addEventListener('mouseleave', function() {
                dashboard.style.transform = 'perspective(1200px) rotateY(-1deg) rotateX(0deg)';
            });
        }
    }
})();

// ═══════════════════════════════════════════════
// INITIALIZE COMMON UTILITIES
// ═══════════════════════════════════════════════
(function() {
    if (typeof initRippleEffect === 'function') {
        initRippleEffect('.btn-primary, .btn-secondary');
    }
})();
