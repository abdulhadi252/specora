// ================================================
// js/category.js — Specora
// Category page JavaScript — mobile sidebar toggle, info modal
// ================================================

function toggleMobileSidebar() {
    var sidebar = document.getElementById('catSidebar');
    var overlay = document.getElementById('catSidebarOverlay');
    var btn = document.getElementById('fbCatBtn');
    var isOpen = sidebar.classList.contains('mobile-open');
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('open');
    btn.classList.toggle('active');
    document.body.style.overflow = isOpen ? '' : 'hidden';
}

function closeMobileSidebar() {
    var sidebar = document.getElementById('catSidebar');
    var overlay = document.getElementById('catSidebarOverlay');
    var btn = document.getElementById('fbCatBtn');
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('open');
    btn.classList.remove('active');
    document.body.style.overflow = '';
}

// ── Sidebar accordion: toggle sub-category visibility ──
function toggleSidebarGroup(btn) {
    var header = btn.parentElement; // .cat-sidebar-group-header
    var subList = header.nextElementSibling; // .cat-sidebar-sub-list
    var isOpen = subList.classList.contains('open');
    
    // Toggle this group
    subList.classList.toggle('open');
    btn.classList.toggle('open');
    header.classList.toggle('active');
    
    // Smooth max-height transition
    if (isOpen) {
        subList.style.maxHeight = '0';
    } else {
        subList.style.maxHeight = subList.scrollHeight + 'px';
        // After transition completes, set to auto
        setTimeout(function() {
            if (subList.classList.contains('open')) {
                subList.style.maxHeight = subList.scrollHeight + 'px';
            }
        }, 350);
    }
}

// ── Auto-expand group on page load if it contains active sub-category ──
document.addEventListener('DOMContentLoaded', function() {
    var activeLinks = document.querySelectorAll('.cat-sidebar-link.active');
    activeLinks.forEach(function(link) {
        var subList = link.closest('.cat-sidebar-sub-list');
        if (subList && !subList.classList.contains('open')) {
            var header = subList.previousElementSibling;
            var toggleBtn = header.querySelector('.cat-sidebar-toggle');
            subList.classList.add('open');
            toggleBtn.classList.add('open');
            header.classList.add('active');
            subList.style.maxHeight = subList.scrollHeight + 'px';
        }
    });
});
