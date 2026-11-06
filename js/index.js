// ================================================
// js/index.js — Specora
// Index page JavaScript — infinite scroll, product search, hero carousel, testimonials
// ================================================

// ── CAROUSEL STATE ──
var currentSlide = 0;
var slideInterval = null;

// ── HERO CAROUSEL ──
function goToSlide(n) {
    var slides = document.querySelectorAll('.carousel-slide');
    slides.forEach(function(s) { s.classList.remove('active'); });
    if (slides[n]) slides[n].classList.add('active');
    currentSlide = n;
    resetAutoPlay();
}

function nextSlide() {
    goToSlide((currentSlide + 1) % 3);
}

function prevSlide() {
    goToSlide((currentSlide + 2) % 3);
}

function startAutoPlay() {
    stopAutoPlay();
    slideInterval = setInterval(function() { nextSlide(); }, 3000);
}

function stopAutoPlay() {
    if (slideInterval) clearInterval(slideInterval);
}

function resetAutoPlay() { startAutoPlay(); }

(function() {
    var heroSection = document.getElementById('heroSection');
    if (heroSection) {
        heroSection.addEventListener('mouseenter', stopAutoPlay);
        heroSection.addEventListener('mouseleave', startAutoPlay);
    }
})();

// ── URL SEARCH PARAM HANDLING ──
(function() {
    var params = new URLSearchParams(window.location.search);
    var queryParam = params.get('q');
    if (queryParam && queryParam.trim() !== '') {
        var trimmedQ = queryParam.trim();
        // Redirect to category.php for search
        window.location.href = 'category.php?q=' + encodeURIComponent(trimmedQ);
        return;
    }
})();

// ── TESTIMONIALS CAROUSEL ──
(function() {
    var track = document.getElementById('testiTrack');
    var dotsEl = document.getElementById('testiDots');
    var prevBtn = document.getElementById('testiPrev');
    var nextBtn = document.getElementById('testiNext');
    if (!track) return;
    var cards = track.querySelectorAll('.testi-card');
    var total = cards.length;
    if (total === 0) return;
    var current = 0;

    function getPerPage() {
        if (window.innerWidth < 768) return 1;
        if (window.innerWidth < 1024) return 2;
        return 3;
    }

    function update() {
        var wrapper = track.parentElement;
        var perPage = getPerPage();
        var wrapperW = wrapper.offsetWidth;
        if (wrapperW === 0) { setTimeout(update, 100); return; }
        var cardGap = 24;
        // Calculate card width: wrapperW = perPage * cardW + (perPage-1) * cardGap
        var cardW = (wrapperW - cardGap * (perPage - 1)) / perPage;
        // Set all cards to same width
        cards.forEach(function(c) { c.style.width = cardW + 'px'; });
        // Calculate max page
        var maxPage = Math.ceil(total / perPage) - 1;
        if (current > maxPage) current = maxPage;
        if (current < 0) current = 0;
        // Translate by exact group width: perPage cards + perPage gaps (including leading gap)
        var slideBy = current * perPage * (cardW + cardGap);
        track.style.transform = 'translateX(-' + slideBy + 'px)';
        // Update dots
        if (dotsEl) {
            dotsEl.innerHTML = '';
            for (var i = 0; i <= maxPage; i++) {
                var dot = document.createElement('button');
                dot.className = 'testi-dot' + (i === current ? ' active' : '');
                dot.onclick = (function(idx) { return function() { current = idx; update(); }; })(i);
                dotsEl.appendChild(dot);
            }
        }
        // Update buttons
        if (prevBtn) { prevBtn.style.opacity = current === 0 ? '0.4' : '1'; prevBtn.style.pointerEvents = current === 0 ? 'none' : 'auto'; }
        if (nextBtn) { nextBtn.style.opacity = current === maxPage ? '0.4' : '1'; nextBtn.style.pointerEvents = current === maxPage ? 'none' : 'auto'; }
    }

    if (prevBtn) prevBtn.addEventListener('click', function() { current = Math.max(0, current - 1); update(); });
    if (nextBtn) nextBtn.addEventListener('click', function() {
        var perPage = getPerPage();
        var maxPage = Math.ceil(total / perPage) - 1;
        current = Math.min(maxPage, current + 1); update();
    });
    var resizeTimer;
    window.addEventListener('resize', function() { clearTimeout(resizeTimer); resizeTimer = setTimeout(update, 150); });
    update();
})();

// ── HERO CAROUSEL AUTO-START ──
startAutoPlay();
