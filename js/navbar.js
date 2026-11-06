// ================================================
// js/navbar.js — Specora
// Navbar behavior: search, mobile menu, recent searches, suggestions
// NOTE: SEARCH_CATEGORIES variable is defined inline by PHP in navbar.php
// NOTE: POPULAR_CATEGORIES_DATA variable is defined inline by PHP in navbar.php
// ================================================

// ── CLEAR NAVBAR SEARCH ──
window.clearNavSearch = function() {
    var desktopInput = document.getElementById('navSearchInput');
    var mobileInput = document.getElementById('mobNavSearchInput');
    if (desktopInput) { desktopInput.value = ''; desktopInput.focus(); }
    if (mobileInput) mobileInput.value = '';
    toggleClearBtn();
};

// ── TOGGLE CLEAR BUTTON VISIBILITY ──
window.toggleClearBtn = function() {
    var desktopInput = document.getElementById('navSearchInput');
    var mobileInput = document.getElementById('mobNavSearchInput');
    var desktopClear = document.getElementById('navClearBtn');
    var mobileClear = document.getElementById('mobNavClearBtn');
    var hasValue = (desktopInput && desktopInput.value.trim() !== '') || (mobileInput && mobileInput.value.trim() !== '');
    if (desktopClear) desktopClear.style.display = hasValue ? 'flex' : 'none';
    if (mobileClear) mobileClear.style.display = hasValue ? 'flex' : 'none';
};

// ── SAVE SEARCH TO localStorage ──
window.saveSearch = function(query) {
    if (!query) return;
    var searches = JSON.parse(localStorage.getItem('specora_recent_searches') || '[]');
    searches = searches.filter(function(s) { return s.toLowerCase() !== query.toLowerCase(); });
    searches.unshift(query);
    if (searches.length > 10) searches = searches.slice(0, 10);
    localStorage.setItem('specora_recent_searches', JSON.stringify(searches));
};

// ── DELETE A RECENT SEARCH ──
window.deleteRecentSearch = function(query) {
    var searches = JSON.parse(localStorage.getItem('specora_recent_searches') || '[]');
    searches = searches.filter(function(s) { return s.toLowerCase() !== query.toLowerCase(); });
    localStorage.setItem('specora_recent_searches', JSON.stringify(searches));
    // Re-render the default dropdown if it's visible
    var dd = getActiveDropdown();
    if (dd && dd.classList.contains('open') && !dd.classList.contains('suggestions-mode')) {
        renderDefaultDropdown(dd);
    }
};

// ── NAV SEARCH ──
window.navSearch = function() {
    var desktopInput = document.getElementById('navSearchInput');
    var mobileInput  = document.getElementById('mobNavSearchInput');
    var query = desktopInput ? desktopInput.value.trim() : '';
    if (!query && mobileInput) query = mobileInput.value.trim();
    if (!query) return;

    // ── Category detection: if query matches a category name, redirect to category page ──
    if (typeof SEARCH_CATEGORIES !== 'undefined' && SEARCH_CATEGORIES && SEARCH_CATEGORIES.length > 0) {
        var searchSlug = query.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        for (var ci = 0; ci < SEARCH_CATEGORIES.length; ci++) {
            var cat = SEARCH_CATEGORIES[ci];
            if (cat.slug === searchSlug) {
                if (typeof window.saveSearch === 'function') window.saveSearch(query);
                hideSearchDropdown();
                window.location.href = 'category.php?cat=' + encodeURIComponent(cat.slug) + '&q=' + encodeURIComponent(query);
                return;
            }
        }
    }

    if (typeof window.saveSearch === 'function') {
        window.saveSearch(query);
    }

    // ── If on category.php, search within the same category ──
    var isCategoryPage = window.location.pathname.indexOf('category.php') > -1;
    var urlParams = new URLSearchParams(window.location.search);
    var currentCat = urlParams.get('cat');
    if (isCategoryPage && currentCat) {
        hideSearchDropdown();
        window.location.href = 'category.php?cat=' + encodeURIComponent(currentCat) + '&q=' + encodeURIComponent(query);
        return;
    }

    // ── Redirect to category.php with the query for search results ──
    hideSearchDropdown();
    window.location.href = 'category.php?q=' + encodeURIComponent(query);
    return;
};

// ── SELECT A SUGGESTION ──
window.selectSuggestion = function(text) {
    var navIn = document.getElementById('navSearchInput');
    var mobNavIn = document.getElementById('mobNavSearchInput');
    if (navIn) navIn.value = text;
    if (mobNavIn) mobNavIn.value = text;
    hideSearchDropdown();
    if (typeof window.saveSearch === 'function') window.saveSearch(text);
    if (typeof window.navSearch === 'function') window.navSearch();
};

// ── SELECT A POPULAR CATEGORY ──
window.selectCategory = function(slug, name) {
    if (typeof window.saveSearch === 'function') window.saveSearch(name);
    hideSearchDropdown();
    window.location.href = 'category.php?cat=' + encodeURIComponent(slug) + '&q=' + encodeURIComponent(name);
};

// ── SELECT A RECENT SEARCH ──
window.selectRecentSearch = function(query) {
    var navIn = document.getElementById('navSearchInput');
    var mobNavIn = document.getElementById('mobNavSearchInput');
    if (navIn) navIn.value = query;
    if (mobNavIn) mobNavIn.value = query;
    hideSearchDropdown();
    if (typeof window.saveSearch === 'function') window.saveSearch(query);
    if (typeof window.navSearch === 'function') window.navSearch();
};

// ══════════════════════════════════════════════════════════
//  ENHANCED SEARCH DROPDOWN SYSTEM
// ══════════════════════════════════════════════════════════

// ── Get the active (visible) dropdown element for a given input ──
function getDropdownForInput(inputEl) {
    if (!inputEl) return null;
    var wrap = inputEl.closest('.nav-search-wrap, .mob-search-wrap');
    return wrap ? wrap.querySelector('.search-dropdown') : null;
}

// ── Get ANY currently open dropdown ──
function getActiveDropdown() {
    var dd1 = document.getElementById('searchSuggestions');
    var dd2 = document.getElementById('mobSearchSuggestions');
    if (dd1 && dd1.classList.contains('open')) return dd1;
    if (dd2 && dd2.classList.contains('open')) return dd2;
    return null;
}

// ── HTML escape helper ──
function htmlEscape(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// ── Image error fallback for category cards ──
window.handleCatImgError = function(imgEl) {
    var name = imgEl.getAttribute('data-name');
    if (!name) name = '?';
    imgEl.parentElement.innerHTML = '<div class="sd-cat-img-fallback">' + name.charAt(0).toUpperCase() + '</div>';
};

// ── Render the default dropdown (popular categories + recent searches) ──
function renderDefaultDropdown(dd) {
    if (!dd) return;
    var html = '';

    // ── Section 1: Popular Categories ──
    html += '<div class="sd-header">Popular Categories</div>';
    html += '<div class="sd-categories-row">';

    var cats = (typeof POPULAR_CATEGORIES_DATA !== 'undefined' && POPULAR_CATEGORIES_DATA.length > 0)
        ? POPULAR_CATEGORIES_DATA
        : [
            {slug:'men-shoes',     name:"Men's Shoes",     img:''},
            {slug:'kids-shoes',    name:'Kids Shoes',       img:''},
            {slug:'men-watches',   name:"Men's Watches",   img:''},
            {slug:'cookware',      name:'Cookware',         img:''},
            {slug:'action-figures',name:'Action Figures',   img:''}
        ];

    cats.forEach(function(c) {
        var imgTag = '';
        if (c.img && c.img !== '' && c.img.indexOf('placehold') === -1) {
            var safeName = htmlEscape(c.name);
            imgTag = '<img class="sd-cat-img" src="' + htmlEscape(c.img) + '" alt="' + safeName + '" data-name="' + safeName + '" onerror="handleCatImgError(this)" />';
        } else {
            imgTag = '<div class="sd-cat-img-fallback">' + htmlEscape(c.name.charAt(0).toUpperCase()) + '</div>';
        }
        html += '<a class="sd-cat-card" href="javascript:void(0)" onclick="selectCategory(\'' + htmlEscape(c.slug) + '\',\'' + htmlEscape(c.name) + '\')">'
            + '<div class="sd-cat-img-wrap">' + imgTag + '</div>'
            + '<div class="sd-cat-name">' + htmlEscape(c.name) + '</div>'
            + '</a>';
    });

    html += '</div>';

    // ── Section 2: Recent Searches ──
    html += '<div class="sd-divider"></div>';
    html += '<div class="sd-header">Recent Searches</div>';

    var searches = JSON.parse(localStorage.getItem('specora_recent_searches') || '[]');
    if (searches.length === 0) {
        html += '<div class="sd-recent-empty">No recent searches yet</div>';
    } else {
        html += '<div class="sd-recent-list">';
        searches.forEach(function(s) {
            html += '<div class="sd-recent-item" onclick="selectRecentSearch(\'' + htmlEscape(s).replace(/'/g, "\\'") + '\')">'
                + '<span class="sd-recent-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></span>'
                + '<span class="sd-recent-text">' + htmlEscape(s) + '</span>'
                + '<button class="sd-recent-del" onclick="event.stopPropagation();deleteRecentSearch(\'' + htmlEscape(s).replace(/'/g, "\\'") + '\')">✕</button>'
                + '</div>';
        });
        html += '</div>';
    }

    dd.innerHTML = html;
    dd.classList.remove('suggestions-mode');
    dd.classList.remove('scroll-hidden');
    _scrollHideApplied = false;
    openDropdown(dd);
}

// ── Render suggestions (when typing) ──
function renderSuggestions(dd, suggestions) {
    if (!dd) return;
    if (!suggestions || suggestions.length === 0) {
        closeDropdown(dd);
        return;
    }

    var html = '';
    html += '<div class="sd-header" style="padding-bottom:4px;">Suggestions</div>';

    suggestions.forEach(function(s) {
        var safe = htmlEscape(s).replace(/'/g, "\\'");
        html += '<div class="suggestion-item" onclick="selectSuggestion(\'' + safe + '\')">'
            + '<span class="suggestion-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></span>'
            + '<span class="suggestion-text">' + htmlEscape(s) + '</span>'
            + '</div>';
    });

    dd.innerHTML = html;
    dd.classList.add('suggestions-mode');
    dd.classList.remove('scroll-hidden');
    _scrollHideApplied = false;
    openDropdown(dd);
}

// ── Open dropdown with animation ──
function openDropdown(dd) {
    if (!dd) return;
    dd.classList.remove('closing');
    dd.classList.add('open');
    // If inside mobile search wrap: make overflow visible + match width to search form
    var mobWrap = dd.closest('.mob-search-wrap');
    if (mobWrap) {
        mobWrap.style.overflow = 'visible';
        var form = mobWrap.querySelector('.nav-search-form');
        if (form) dd.style.width = form.offsetWidth + 'px';
    }
}

// ── Close dropdown with animation ──
function closeDropdown(dd) {
    if (!dd) return;
    dd.classList.add('closing');
    // Remove open class after animation completes
    var onEnd = function() {
        dd.classList.remove('open');
        dd.classList.remove('closing');
        dd.classList.remove('suggestions-mode');
        dd.classList.remove('scroll-hidden');
        dd.removeEventListener('transitionend', onEnd);
        // Reset mobile overflow after close
        var mobWrap = dd.closest('.mob-search-wrap');
        if (mobWrap) mobWrap.style.overflow = '';
    };
    dd.addEventListener('transitionend', onEnd);
    // Fallback: if transition doesn't fire, clean up after 250ms
    setTimeout(function() {
        if (dd.classList.contains('closing')) {
            onEnd();
        }
    }, 260);
}

// ── Hide ALL search dropdowns ──
function hideSearchDropdown() {
    var dd1 = document.getElementById('searchSuggestions');
    var dd2 = document.getElementById('mobSearchSuggestions');
    if (dd1 && dd1.classList.contains('open')) closeDropdown(dd1);
    if (dd2 && dd2.classList.contains('open')) closeDropdown(dd2);
    _suggestDebounceTimer = null;
}

// ── Track scroll-hide state ──
var _scrollHideApplied = false;

// ── Handle scroll: hide dropdown but keep input focused ──
function handleScrollWhileFocused() {
    var dd = getActiveDropdown();
    if (!dd) return;
    // Only apply if not already hidden by scroll
    if (!_scrollHideApplied) {
        dd.classList.add('scroll-hidden');
        _scrollHideApplied = true;
    }
}

// ── Fetch and show search suggestions ──
var _suggestDebounceTimer = null;

function fetchAndShowSuggestions(query, dd) {
    if (!dd) return;
    if (query.length < 2) {
        // If less than 2 chars, show default dropdown instead
        renderDefaultDropdown(dd);
        return;
    }

    // Remove suggestions-mode so we can show loading state
    dd.classList.remove('suggestions-mode');

    fetch('api/suggestions.php?q=' + encodeURIComponent(query))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!dd) return;
            if (!data.suggestions || data.suggestions.length === 0) {
                // No suggestions — show "no results" in suggestion mode
                dd.innerHTML = '<div class="sd-header" style="padding-bottom:4px;">Suggestions</div>'
                    + '<div class="sd-recent-empty">No suggestions found</div>';
                dd.classList.add('suggestions-mode');
                openDropdown(dd);
                return;
            }
            renderSuggestions(dd, data.suggestions);
        })
        .catch(function() {
            if (dd) closeDropdown(dd);
        });
}

// ── Handle input value changes ──
function handleSearchInputChange(inputEl) {
    var val = inputEl.value.trim();
    var dd = getDropdownForInput(inputEl);
    if (!dd) return;

    // Reset scroll-hide state on new input
    _scrollHideApplied = false;
    dd.classList.remove('scroll-hidden');

    if (val === '') {
        // Empty input: show default state (categories + recent searches)
        renderDefaultDropdown(dd);
    } else {
        // Has input: show suggestions
        clearTimeout(_suggestDebounceTimer);
        _suggestDebounceTimer = setTimeout(function() {
            fetchAndShowSuggestions(val, dd);
        }, 200);
    }
}

// ══════════════════════════════════════════════════════════
//  EVENT LISTENERS SETUP
// ══════════════════════════════════════════════════════════

(function() {
    var navIn = document.getElementById('navSearchInput');
    var mobNavIn = document.getElementById('mobNavSearchInput');

    // ── Focus: show default dropdown if empty ──
    function handleFocus() {
        var dd = getDropdownForInput(this);
        if (!dd) return;
        var val = this.value.trim();
        if (val === '') {
            renderDefaultDropdown(dd);
        } else if (val.length >= 2) {
            fetchAndShowSuggestions(val, dd);
        }
    }

    // ── Input: switch between default and suggestions ──
    function handleInput() {
        handleSearchInputChange(this);
        // Sync clear button
        if (typeof toggleClearBtn === 'function') toggleClearBtn();
    }

    // ── Blur: close dropdown after a short delay (to allow click on item) ──
    function handleBlur() {
        // Delay to allow click events on dropdown items to fire
        setTimeout(function() {
            var dd = getDropdownForInput(this);
            if (dd && dd.classList.contains('open') && !this.value.trim()) {
                closeDropdown(dd);
            }
        }.bind(this), 200);
    }

    // ── Keydown: Enter triggers search, Escape closes dropdown ──
    function handleKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.navSearch();
        }
        if (e.key === 'Escape') {
            hideSearchDropdown();
        }
    }

    if (navIn) {
        navIn.addEventListener('focus', handleFocus);
        navIn.addEventListener('input', handleInput);
        navIn.addEventListener('blur', handleBlur);
        navIn.addEventListener('keydown', handleKeydown);
    }
    if (mobNavIn) {
        mobNavIn.addEventListener('focus', handleFocus);
        mobNavIn.addEventListener('input', handleInput);
        mobNavIn.addEventListener('blur', handleBlur);
        mobNavIn.addEventListener('keydown', handleKeydown);
    }

    // ── DESKTOP HOVER: show dropdown on mouse enter, hide on leave (if not focused) ──
    var navWrapEl = document.getElementById('navSearchWrap');
    if (navWrapEl && navIn) {
        navWrapEl.addEventListener('mouseenter', function() {
            if (window.innerWidth <= 1024) return;
            var dd = getDropdownForInput(navIn);
            if (dd && !dd.classList.contains('open') && !dd.classList.contains('closing') && navIn.value.trim() === '') {
                renderDefaultDropdown(dd);
            }
        });
        navWrapEl.addEventListener('mouseleave', function(e) {
            if (window.innerWidth <= 1024) return;
            var dd = getDropdownForInput(navIn);
            if (dd && dd.classList.contains('open') && document.activeElement !== navIn && !navIn.value.trim()) {
                var related = e.relatedTarget;
                if (related && (dd.contains(related) || navWrapEl.contains(related))) return;
                closeDropdown(dd);
            }
        });
    }

    // ── Scroll detection: hide dropdown on page scroll ──
    var scrollTimeout = null;
    window.addEventListener('scroll', function() {
        if (getActiveDropdown()) {
            handleScrollWhileFocused();
        }
    }, { passive: true });

    // ── Click outside: close dropdown ──
    document.addEventListener('click', function(e) {
        var dd1 = document.getElementById('searchSuggestions');
        var dd2 = document.getElementById('mobSearchSuggestions');
        var navWrap = document.getElementById('navSearchWrap');
        var mobWrap = document.getElementById('mobNavSearchWrap');
        var mobIcon = document.getElementById('mobSearchIcon');

        var isInside = false;
        if (navWrap && (navWrap.contains(e.target) || navWrap === e.target)) isInside = true;
        if (mobWrap && (mobWrap.contains(e.target) || mobWrap === e.target)) isInside = true;
        if (mobIcon && (mobIcon.contains(e.target) || mobIcon === e.target)) isInside = true;

        if (!isInside) {
            if (dd1 && dd1.classList.contains('open')) closeDropdown(dd1);
            if (dd2 && dd2.classList.contains('open')) closeDropdown(dd2);
        }
    });

})();

// ── MOBILE SEARCH TOGGLE (slide down on search icon click) ──
(function() {
    var icon = document.getElementById('mobSearchIcon');
    var wrap = document.getElementById('mobNavSearchWrap');
    var input = document.getElementById('mobNavSearchInput');
    if (!icon || !wrap || !input) return;

    icon.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = wrap.classList.contains('open');
        if (isOpen) {
            wrap.classList.remove('open');
            hideSearchDropdown();
        } else {
            wrap.classList.add('open');
            // Focus the input after the animation completes
            setTimeout(function() { input.focus(); }, 300);
        }
    });

    // Close search bar on outside click
    document.addEventListener('click', function(e) {
        if (wrap.classList.contains('open') &&
            !icon.contains(e.target) &&
            !wrap.contains(e.target)) {
            wrap.classList.remove('open');
            hideSearchDropdown();
        }
    });

    // Close search bar on Escape (handled above for dropdown, also close the bar)
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            wrap.classList.remove('open');
            input.blur();
            hideSearchDropdown();
        }
    });
})();

// ── Show clear button if input has initial value (e.g. when ?q= is in URL) ──
if (typeof toggleClearBtn === 'function') toggleClearBtn();

// ── CATEGORY STRIP DRAG-TO-SCROLL ──
(function() {
    var strip = document.querySelector('.cat-strip');
    if (!strip) return;

    var isDown = false;
    var startX;
    var scrollLeft;

    strip.addEventListener('mousedown', function(e) {
        isDown = true;
        strip.classList.add('grabbing');
        startX = e.pageX - strip.offsetLeft;
        scrollLeft = strip.scrollLeft;
    });

    strip.addEventListener('mouseleave', function() {
        isDown = false;
        strip.classList.remove('grabbing');
    });

    strip.addEventListener('mouseup', function() {
        isDown = false;
        strip.classList.remove('grabbing');
    });

    strip.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        var x = e.pageX - strip.offsetLeft;
        var walk = (x - startX) * 1.5;
        strip.scrollLeft = scrollLeft - walk;
    });

    var links = strip.querySelectorAll('a');
    var dragThreshold = 5;
    var dragDistance = 0;
    var startPos = 0;

    links.forEach(function(link) {
        link.addEventListener('mousedown', function(e) {
            startPos = e.pageX;
            dragDistance = 0;
        });

        link.addEventListener('mousemove', function(e) {
            if (isDown) {
                dragDistance = Math.abs(e.pageX - startPos);
            }
        });

        link.addEventListener('click', function(e) {
            if (dragDistance > dragThreshold) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
})();
