// ================================================
// js/compare.js — Specora
// Compare Products feature — sessionStorage-based
// (per-tab storage: every screen/window keeps its OWN compare list)
// ================================================

var COMPARE_KEY = 'specora_compare_products';
var COMPARE_MAX = 10;

// ── Get compare list from sessionStorage ──
// sessionStorage is per-tab, so the compare list of one screen
// never leaks into another screen/window of the same browser.
function getCompareList() {
    try {
        return JSON.parse(sessionStorage.getItem(COMPARE_KEY)) || [];
    } catch (e) {
        return [];
    }
}

// ── Save compare list to sessionStorage ──
function saveCompareList(list) {
    try {
        sessionStorage.setItem(COMPARE_KEY, JSON.stringify(list));
    } catch (e) {
        // sessionStorage full or unavailable
    }
}

// ── Add a product to compare list ──
function addToCompare(product) {
    var list = getCompareList();
    
    // Check for duplicates by asin + source
    var exists = list.some(function(p) {
        return p.asin === product.asin && p.source === product.source;
    });
    
    if (exists) {
        showCompareToast('Already in compare list!');
        return;
    }
    
    if (list.length >= COMPARE_MAX) {
        showCompareToast('Maximum ' + COMPARE_MAX + ' products allowed!');
        return;
    }
    
    list.push(product);
    saveCompareList(list);
    updateCompareBadge();
    showCompareToast('Added to compare!');
}

// ── Show delete confirmation modal ──
function showDeleteConfirm(asin, source) {
    var existingModal = document.querySelector('.compare-confirm-modal');
    if (existingModal) existingModal.remove();

    var overlay = document.createElement('div');
    overlay.className = 'compare-confirm-overlay';

    var modal = document.createElement('div');
    modal.className = 'compare-confirm-modal';

    modal.innerHTML = ''
        + '<div class="compare-confirm-icon"><i class="fas fa-trash-alt"></i></div>'
        + '<div class="compare-confirm-text">Remove this product from Compare?</div>'
        + '<div class="compare-confirm-actions">'
        + '  <button class="compare-confirm-btn compare-confirm-cancel" id="compareConfirmCancel">Cancel</button>'
        + '  <button class="compare-confirm-btn compare-confirm-delete" id="compareConfirmDelete">Delete</button>'
        + '</div>';

    document.body.appendChild(overlay);
    document.body.appendChild(modal);

    // Trigger entrance animation
    setTimeout(function() {
        overlay.classList.add('open');
        modal.classList.add('open');
    }, 10);

    // Cancel handler
    document.getElementById('compareConfirmCancel').addEventListener('click', function() {
        closeDeleteConfirm(overlay, modal);
    });

    // Delete handler
    document.getElementById('compareConfirmDelete').addEventListener('click', function() {
        closeDeleteConfirm(overlay, modal);
        removeFromCompare(asin, source);
    });

    // Overlay click to close
    overlay.addEventListener('click', function() {
        closeDeleteConfirm(overlay, modal);
    });

    // Escape key to close
    function escHandler(e) {
        if (e.key === 'Escape') {
            closeDeleteConfirm(overlay, modal);
            document.removeEventListener('keydown', escHandler);
        }
    }
    document.addEventListener('keydown', escHandler);
}

function closeDeleteConfirm(overlay, modal) {
    overlay.classList.remove('open');
    modal.classList.remove('open');
    setTimeout(function() {
        if (overlay.parentNode) overlay.remove();
        if (modal.parentNode) modal.remove();
    }, 250);
}

// ── Remove a product from compare list ──
function removeFromCompare(asin, source) {
    var list = getCompareList();
    list = list.filter(function(p) {
        return !(p.asin === asin && p.source === source);
    });
    saveCompareList(list);
    updateCompareBadge();
    renderCompareSidebar();
}

// ── Clear all compare products ──
function clearCompare() {
    saveCompareList([]);
    updateCompareBadge();
    renderCompareSidebar();
}

// ── Update badge count ──
function updateCompareBadge() {
    var list = getCompareList();
    var badge = document.getElementById('compareBadge');
    var sidebarBadge = document.getElementById('compareSidebarBadge');
    var count = list.length;
    
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
    if (sidebarBadge) {
        sidebarBadge.textContent = count;
    }
}

// ── Toggle compare sidebar ──
function toggleCompareSidebar() {
    var sidebar = document.getElementById('compareSidebar');
    var overlay = document.getElementById('compareOverlay');
    if (!sidebar) return;
    
    var isOpen = sidebar.classList.contains('open');
    
    if (isOpen) {
        closeCompareSidebar();
    } else {
        openCompareSidebar();
    }
}

function openCompareSidebar() {
    var sidebar = document.getElementById('compareSidebar');
    var overlay = document.getElementById('compareOverlay');
    if (!sidebar) return;
    
    renderCompareSidebar();
    sidebar.classList.add('open');
    if (overlay) overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeCompareSidebar() {
    var sidebar = document.getElementById('compareSidebar');
    var overlay = document.getElementById('compareOverlay');
    if (!sidebar) return;
    
    sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
}

// ── Render compare sidebar content ──
function renderCompareSidebar() {
    var sidebar = document.getElementById('compareSidebar');
    if (!sidebar) return;
    
    var list = getCompareList();
    var content = sidebar.querySelector('.compare-sidebar-content');
    if (!content) return;
    
    if (list.length === 0) {
        content.innerHTML = ''
            + '<div class="compare-empty">'
            + '  <div class="compare-empty-icon"><i class="fas fa-balance-scale"></i></div>'
            + '  <div class="compare-empty-text">No products added for comparison.</div>'
            + '  <div class="compare-empty-sub">Browse products and click Compare to add them here.</div>'
            + '</div>';
        return;
    }
    
    var html = '';
    list.forEach(function(p, index) {
        var safeTitle = htmlEscapeCompare(p.title || 'Product');
        var safePhoto = p.photo || 'https://placehold.co/100x100/F4F3F0/A8A49C?text=No+Image';
        var priceDisplay = p.price && parseFloat(p.price) > 0 
            ? '$' + parseFloat(p.price).toFixed(2) 
            : 'See price';
        var sourceLabel = getSourceLabel(p.source);
        var detailUrl = 'product.php?asin=' + encodeURIComponent(p.asin) + '&source=' + encodeURIComponent(p.source);
        
        html += '<div class="compare-item">'
            + '  <button class="compare-item-remove" onclick="showDeleteConfirm(\'' + escapeJsString(p.asin) + '\',\'' + escapeJsString(p.source) + '\')" title="Remove"><i class="fas fa-trash-alt"></i></button>'
            + '  <a href="' + detailUrl + '" class="compare-item-link">'
            + '    <div class="compare-item-img-wrap">'
            + '      <img class="compare-item-img" src="' + htmlEscapeCompare(safePhoto) + '" alt="' + safeTitle + '" loading="lazy" onerror="this.src=\'https://placehold.co/100x100/F4F3F0/A8A49C?text=No+Image\'"/>'
            + '    </div>'
            + '    <div class="compare-item-info">'
            + '      <div class="compare-item-title">' + safeTitle + '</div>'
            + '      <div class="compare-item-price">' + priceDisplay + '</div>'
            + '      <div class="compare-item-source"><span class="compare-source-badge compare-source-' + p.source + '">' + sourceLabel + '</span></div>'
            + '    </div>'
            + '  </a>'
            + '</div>';
    });
    
    html += '<div class="compare-sidebar-footer">'
        + '  <button class="compare-all-btn" onclick="openComparePage()"><i class="fas fa-arrow-right"></i> Compare All</button>'
        + '  <button class="compare-clear-btn" onclick="clearCompare()"><i class="fas fa-trash-alt"></i> Clear All</button>'
        + '</div>';
    
    content.innerHTML = html;
    updateCompareBadge();
}

// ── Navigate to compare page ──
function openComparePage() {
    var list = getCompareList();
    if (list.length < 2) {
        showCompareToast('Please add at least 2 products to compare.');
        return;
    }
    closeCompareSidebar();
    window.location.href = 'compare.php';
}

// ── Toast notification ──
function showCompareToast(msg) {
    var existing = document.querySelector('.compare-toast');
    if (existing) existing.remove();
    
    var toast = document.createElement('div');
    toast.className = 'compare-toast';
    toast.textContent = msg;
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(function() { toast.classList.add('show'); }, 10);
    
    // Auto-remove
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
    }, 2000);
}

// ── Helper: escape for HTML ──
function htmlEscapeCompare(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ── Helper: escape for JS string ──
function escapeJsString(str) {
    return (str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

// ── Helper: get source display label ──
function getSourceLabel(source) {
    var labels = {
        'amazon': 'Amazon',
        'aliexpress': 'AliExpress',
        'ebay': 'eBay',
        'walmart': 'Walmart',
        'flipkart': 'Flipkart'
    };
    return labels[source] || source || 'Amazon';
}

// ── Initialize on DOM ready ──
(function() {
    // Update badge on page load
    updateCompareBadge();
    
    // Close sidebar on overlay click
    var overlay = document.getElementById('compareOverlay');
    if (overlay) {
        overlay.addEventListener('click', closeCompareSidebar);
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCompareSidebar();
        }
    });
})();
