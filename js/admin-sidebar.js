// ================================================
// js/admin-sidebar.js — Specora Admin
// Sidebar toggle and staggered link animation
// ================================================

(function() {
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var hamburger = document.getElementById('sidebarHamburger');
    var closeBtn = document.getElementById('sidebarClose');

    window.openSidebar = function() {
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
        if (hamburger) hamburger.classList.add('open');
    };

    window.closeSidebar = function() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
        if (hamburger) hamburger.classList.remove('open');
    };

    if (hamburger) hamburger.addEventListener('click', window.openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', window.closeSidebar);
    if (overlay) overlay.addEventListener('click', window.closeSidebar);

    // Animate sidebar links on page load (stagger)
    document.addEventListener('DOMContentLoaded', function() {
        var links = document.querySelectorAll('.sidebar-link');
        links.forEach(function(link, i) {
            link.style.opacity = '0';
            link.style.transform = 'translateX(-12px)';
            link.style.transition = 'opacity .4s ease, transform .4s ease, background .2s ease, color .2s ease';
            setTimeout(function() {
                link.style.opacity = '1';
                link.style.transform = 'translateX(0)';
            }, 80 + (i * 50));
        });
    });
})();
