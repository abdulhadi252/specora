// ================================================
// js/footer.js — Specora
// Back-to-top button scroll visibility
// Newsletter AJAX subscription handler
// ================================================

(function() {
    // ── Back to Top ──
    var backTopBtn = document.getElementById('backToTop');
    if (backTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 600) {
                backTopBtn.classList.add('visible');
            } else {
                backTopBtn.classList.remove('visible');
            }
        });
    }

    // ── Newsletter Form ──
    var nlForm = document.getElementById('nlForm');
    var nlBtn = document.getElementById('nlBtn');
    var nlFeedback = document.getElementById('nlFeedback');

    if (nlForm && nlBtn && nlFeedback) {
        nlForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var email = document.getElementById('nlEmail').value.trim();
            if (!email) {
                showFeedback('Please enter your email address.', 'error');
                return;
            }

            // Basic email validation
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showFeedback('Please enter a valid email address.', 'error');
                return;
            }

            // Loading state
            nlBtn.disabled = true;
            nlBtn.textContent = 'Subscribing…';
            nlForm.classList.add('loading');
            nlFeedback.className = 'fn-feedback';
            nlFeedback.textContent = '';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', nlForm.getAttribute('action'), true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                nlBtn.disabled = false;
                nlBtn.textContent = 'Subscribe \u2192';
                nlForm.classList.remove('loading');

                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        showFeedback(res.message, 'success');
                        document.getElementById('nlEmail').value = '';
                    } else {
                        showFeedback(res.message, 'error');
                    }
                } catch (err) {
                    showFeedback('Something went wrong. Please try again.', 'error');
                }
            };
            xhr.onerror = function() {
                nlBtn.disabled = false;
                nlBtn.textContent = 'Subscribe \u2192';
                nlForm.classList.remove('loading');
                showFeedback('Network error. Please check your connection and try again.', 'error');
            };
            xhr.send('email=' + encodeURIComponent(email));
        });
    }

    function showFeedback(msg, type) {
        if (!nlFeedback) return;
        nlFeedback.textContent = msg;
        nlFeedback.className = 'fn-feedback ' + type;
    }
})();
