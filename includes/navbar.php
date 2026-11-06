<?php

/**
 * navbar.php — Specora
 * Shared navbar fragment.
 *
 * Supports a $basePath variable. Set $basePath before including
 * this file when the including page is in a subdirectory
 * (e.g. $basePath = '../' for pages/*.php).
 */
$basePath = isset($basePath) ? $basePath : '';
$connect = $connect ?? null;
?>
<!-- NB: Shared CSS (variables, reset, navbar, footer) loaded from /css/ via each page's <head> -->
<!-- TICKER -->
<div class="nav-ticker">
  <div class="nav-ticker-track">
    <?php for ($ti = 0; $ti < 2; $ti++): ?>
    <span class="nav-ticker-text">
      <span class="dot"></span> Specora — Select Your Own Selection
      <span class="dot"></span> Specora — Select Your Own Selection
      <span class="dot"></span> Specora — Select Your Own Selection
      <span class="dot"></span> Specora — Select Your Own Selection
      <span class="dot"></span> Specora — Select Your Own Selection
    </span>
    <?php endfor; ?>
  </div>
</div>

<!-- NAV-GROUP: wraps nav + mobile-search + dropdown for scroll-safe absolute positioning -->
<div class="nav-group" id="navGroup">
  <nav>
    <a href="<?= $basePath ?>index.php" class="nav-logo">
      <img src="<?= $basePath ?>img/specora-logo.png" alt="Specora" class="nav-logo-img" />
    </a>
    <!-- MAIN SEARCH BAR -->
    <div class="nav-search-wrap" id="navSearchWrap">
      <div class="nav-search-form">

        <input type="text" class="nav-search-input" id="navSearchInput" placeholder="Search products, categories, brands..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" oninput="toggleClearBtn()"/>
        <button class="nav-clear-btn" id="navClearBtn" onclick="clearNavSearch()" style="display:none">✕</button>
        <button class="nav-search-btn" onclick="navSearch()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </button>
      </div>
      <!-- SEARCH DROPDOWN (default: categories + recent searches / typing: suggestions) -->
      <div class="search-dropdown" id="searchSuggestions"></div>
    </div>

    <div class="nav-actions" id="navActions">
      <button class="mob-search-icon" id="mobSearchIcon" aria-label="Search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      </button>
      <!-- COMPARE ICON -->
      <button class="nav-compare-icon" id="navCompareIcon" onclick="toggleCompareSidebar()" aria-label="Compare Products">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
          <path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M3 16v5h5"/><path d="M21 16v5h-5"/>
          <path d="M21 12h-4a2 2 0 01-2-2V6"/><path d="M3 12h4a2 2 0 002-2V6"/>
          <path d="M3 12h4a2 2 0 012 2v4"/><path d="M21 12h-4a2 2 0 00-2 2v4"/>
        </svg>
        <span class="nav-compare-badge" id="compareBadge">0</span>
      </button>
    </div>

    <!-- MOBILE MENU — built dynamically by JS from JSON data (avoids sending HTML for every category on every page load) -->
    <div class="mobile-menu" id="mobileMenu" aria-hidden="true" style="display:none!important;"></div>
  </nav>

  <!-- MOBILE SEARCH BAR (inside nav-group, below nav) -->
  <div class="mob-search-wrap" id="mobNavSearchWrap">
    <div class="nav-search-form">

      <input type="text" class="nav-search-input" id="mobNavSearchInput" placeholder="Search products, categories..." autocomplete="off" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" oninput="toggleClearBtn()"/>
      <button class="nav-clear-btn" id="mobNavClearBtn" onclick="clearNavSearch()" style="display:none">✕</button>
      <button class="nav-search-btn" onclick="navSearch()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      </button>
    </div>
    <!-- MOBILE SEARCH DROPDOWN -->
    <div class="search-dropdown" id="mobSearchSuggestions"></div>
  </div>

</div>

<!-- COMPARE SIDEBAR OVERLAY -->
<div class="compare-overlay" id="compareOverlay"></div>

<!-- COMPARE SIDEBAR -->
<aside class="compare-sidebar" id="compareSidebar">
  <div class="compare-sidebar-header">
    <div class="compare-sidebar-title">
      <i class="fas fa-balance-scale" style="font-size:18px;color:var(--gold-dark);"></i>
      Compare Products
      <span class="compare-sidebar-count" id="compareSidebarBadge">0</span>
    </div>
    <button class="compare-sidebar-close" onclick="closeCompareSidebar()"><i class="fas fa-times"></i></button>
  </div>
  <div class="compare-sidebar-content">
    <div class="compare-empty">
      <div class="compare-empty-icon"><i class="fas fa-balance-scale"></i></div>
      <div class="compare-empty-text">No products added for comparison.</div>
      <div class="compare-empty-sub">Browse products and click Compare to add them here.</div>
    </div>
  </div>
</aside>

<!-- CATEGORY STRIP -->
<div class="cat-strip">
  <a href="<?= $basePath ?>category.php?cat=footwear">Footwear</a>
  <a href="<?= $basePath ?>category.php?cat=clothing">Clothing</a>
  <a href="<?= $basePath ?>category.php?cat=electronics">Electronics</a>
  <a href="<?= $basePath ?>category.php?cat=computer-office">Computer &amp; Office</a>
  <a href="<?= $basePath ?>category.php?cat=gaming">Gaming</a>
  <a href="<?= $basePath ?>category.php?cat=home-kitchen">Home &amp; Kitchen</a>
  <a href="<?= $basePath ?>category.php?cat=beauty-personal-care">Beauty &amp; Personal Care</a>
  <a href="<?= $basePath ?>category.php?cat=health-fitness">Health &amp; Fitness</a>
  <a href="<?= $basePath ?>category.php?cat=sports-outdoors">Sports &amp; Outdoors</a>
  <a href="<?= $basePath ?>category.php?cat=watches">Watches</a>
  <a href="<?= $basePath ?>category.php?cat=bags-luggage">Bags &amp; Luggage</a>
  <a href="<?= $basePath ?>category.php?cat=jewelry">Jewelry</a>
  <a href="<?= $basePath ?>category.php?cat=toys-games">Toys &amp; Games</a>
  <a href="<?= $basePath ?>category.php?cat=baby-products">Baby Products</a>
  <a href="<?= $basePath ?>category.php?cat=automotive">Automotive</a>
  <a href="<?= $basePath ?>category.php?cat=tools-hardware">Tools &amp; Hardware</a>
  <a href="<?= $basePath ?>category.php?cat=lighting">Lighting</a>
  <a href="<?= $basePath ?>category.php?cat=mobile-accessories">Mobile Accessories</a>
  <a href="<?= $basePath ?>category.php?cat=books-stationery">Books &amp; Stationery</a>
  <a href="<?= $basePath ?>category.php?cat=musical-instruments">Musical Instruments</a>
</div>


<script>
// ── POPULAR CATEGORIES for search dropdown — generated by PHP ──
var POPULAR_CATEGORIES_DATA = [
    <?php
    // 5 popular categories with high-quality Unsplash images + DB fallback
    $pop_cats = [
        ['slug' => 'men-shoes',      'name' => 'Men\'s Shoes',      'img' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=300&h=300&fit=crop&auto=format'],
        ['slug' => 'kids-shoes',     'name' => 'Kids Shoes',        'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=300&h=300&fit=crop&auto=format'],
        ['slug' => 'men-watches',    'name' => 'Men\'s Watches',    'img' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=300&h=300&fit=crop&auto=format'],
        ['slug' => 'cookware',       'name' => 'Cookware',          'img' => 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?w=300&h=300&fit=crop&auto=format'],
        ['slug' => 'action-figures', 'name' => 'Action Figures',    'img' => 'https://images.unsplash.com/photo-1558060370-d644479cb6f7?w=300&h=300&fit=crop&auto=format'],
    ];
    $first = true;
    foreach ($pop_cats as $pc) {
        if (!$first) echo ",\n    ";
        $first = false;
        $img_url = $pc['img'];
        // Try to fetch a better image from DB if available
        if (isset($connect) && $connect) {
            $db_img = getNavCategoryImage($connect, $pc['name']);
            if ($db_img !== '' && strpos($db_img, 'placehold') === false) {
                $img_url = $db_img;
            }
        }
        echo "{slug:'" . $pc['slug'] . "',name:'" . addslashes($pc['name']) . "',img:'" . addslashes($img_url) . "'}";
    }
    ?>
];

// ── CATEGORY LOOKUP for search — generated by PHP ──
var SEARCH_CATEGORIES = [
    <?php
    // Fetch all category slugs & names from DB for search detection
    $search_cats_js = array();
    if (isset($connect) && $connect) {
        $sc_r = mysqli_query($connect, "SELECT DISTINCT category_name FROM categories WHERE category_name != '' ORDER BY category_name ASC");
        if ($sc_r && mysqli_num_rows($sc_r) > 0) {
            while ($sc = mysqli_fetch_assoc($sc_r)) {
                $sc_name = $sc['category_name'];
                $sc_slug = strtolower(trim($sc_name));
                $sc_slug = preg_replace('/[^a-z0-9]+/', '-', $sc_slug);
                $sc_slug = trim($sc_slug, '-');
                $search_cats_js[] = "{slug:'" . addslashes($sc_slug) . "',name:'" . addslashes($sc_name) . "'}";
            }
        }
        // Also include admin_category slugs
        $sc_r2 = mysqli_query($connect, "SELECT category_name FROM admin_category WHERE category_name != ''");
        if ($sc_r2 && mysqli_num_rows($sc_r2) > 0) {
            while ($sc2 = mysqli_fetch_assoc($sc_r2)) {
                $sc2_name = $sc2['category_name'];
                $sc2_slug = strtolower(trim($sc2_name));
                $sc2_slug = preg_replace('/[^a-z0-9]+/', '-', $sc2_slug);
                $sc2_slug = trim($sc2_slug, '-');
                // Check if already added
                $found = false;
                foreach ($search_cats_js as $existing) {
                    if (strpos($existing, "slug:'" . addslashes($sc2_slug) . "'") !== false) { $found = true; break; }
                }
                if (!$found) {
                    $search_cats_js[] = "{slug:'" . addslashes($sc2_slug) . "',name:'" . addslashes($sc2_name) . "'}";
                }
            }
        }
    }
    echo implode(",\n    ", $search_cats_js);
    ?>
];
</script>
<script src="<?= $basePath ?>js/navbar.js?v=<?= filemtime(__DIR__.'/../js/navbar.js') ?>"></script>
<script src="<?= $basePath ?>js/compare.js?v=<?= filemtime(__DIR__.'/../js/compare.js') ?>"></script>