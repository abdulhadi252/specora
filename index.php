<?php require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
if (!validate_db_connection($connect)) { die('<p style="font-family:sans-serif;padding:40px;text-align:center;color:#991B1B;">⚠️ Database connection is currently unavailable. Please try again later.</p>'); }
require_once __DIR__ . '/includes/check_tables.php';
check_specora_tables();

// ── Fetch approved testimonials ──
$approvedFeedback = [];
$tmResult = $connect->query("SELECT name, email, city, feedback, created_at FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 15");
if ($tmResult) {
  while ($tmRow = $tmResult->fetch_assoc()) {
    $approvedFeedback[] = [
      'name' => $tmRow['name'],
      'rating' => 5,
      'feedback_text' => $tmRow['feedback'],
      'city' => $tmRow['city'] ?: 'Verified Shopper',
      'created_at' => $tmRow['created_at'],
    ];
  }
}
// Fallback testimonials if no approved messages yet
if (empty($approvedFeedback)) {
  $approvedFeedback = [
    ['name' => 'Sarah Mitchell', 'rating' => 5, 'feedback_text' => 'SPECORA helped me compare prices across multiple stores in seconds. I saved both time and money.', 'city' => 'New York, USA'],
    ['name' => 'David Wilson', 'rating' => 5, 'feedback_text' => 'I no longer waste hours checking different websites. Everything I need is available in one place.', 'city' => 'London, UK'],
    ['name' => 'Emma Johnson', 'rating' => 5, 'feedback_text' => 'The product comparison and buying guides made my purchase decision incredibly easy.', 'city' => 'Toronto, Canada'],
    ['name' => 'Michael Brown', 'rating' => 5, 'feedback_text' => 'I love how clean and simple the platform is. Shopping has never been this convenient.', 'city' => 'Sydney, Australia'],
    ['name' => 'Olivia Davis', 'rating' => 5, 'feedback_text' => 'Instead of opening ten tabs, I simply search on SPECORA and instantly find the best option.', 'city' => 'Berlin, Germany'],
  ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Specora — Find What Fits You</title>
<meta name="description" content="Specora — Pakistan's smartest product discovery platform. Search any product, compare prices across Amazon, AliExpress, eBay &amp; Walmart — all in one place.">
<meta name="keywords" content="product comparison, price compare, shopping Pakistan, Amazon, AliExpress, eBay, Walmart, best deals">
<meta property="og:title" content="Specora — Find What Fits You">
<meta property="og:description" content="Pakistan's smartest product discovery platform. Search any product, compare prices across Amazon, AliExpress, eBay &amp; Walmart — all in one place.">
<meta property="og:image" content="img/specora-logo.png">
<meta property="og:url" content="https://specora.pk">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Specora — Find What Fits You">
<meta name="twitter:description" content="Pakistan's smartest product discovery platform. Search any product, compare prices across Amazon, AliExpress, eBay &amp; Walmart — all in one place.">
<meta name="twitter:image" content="img/specora-logo.png">
<link rel="icon" type="image/svg+xml" href="img/favicon.svg">
<link rel="stylesheet" href="css/base.css?v=2">
<link rel="stylesheet" href="css/navbar.css?v=<?= filemtime(__DIR__.'/css/navbar.css') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/index.css?v=2">
<style>
.nav-search-wrap{position:relative;}
.mob-search-wrap{position:relative;}
.search-suggestions{top:calc(100% + 4px);}
@media(max-width:768px){
.hero{aspect-ratio:auto!important;max-height:none!important;overflow:visible!important;height:auto!important;}
.hero-carousel{position:relative!important;height:auto!important;}
.carousel-slide{position:relative!important;height:auto!important;opacity:1!important;overflow:visible!important;}
.carousel-slide:not(.active){display:none!important;}
.carousel-slide img{object-fit:contain!important;width:100%!important;height:auto!important;object-position:center!important;}
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- HERO -->
<section class="hero" id="heroSection">
  <!-- CAROUSEL BACKGROUND -->
  <div class="hero-carousel" id="heroCarousel">
    <div class="carousel-slide active" id="slide0">
      <picture>
        <source media="(max-width: 480px)" srcset="img/banner1.png">
        <source media="(max-width: 768px)" srcset="img/banner1.png">
        <img src="img/banner1.png" alt="Special offer 1"/>
      </picture>
    </div>
    <div class="carousel-slide" id="slide1">
      <picture>
        <source media="(max-width: 480px)" srcset="img/banner2.png">
        <source media="(max-width: 768px)" srcset="img/banner2.png">
        <img src="img/banner2.png" alt="Special offer 2"/>
      </picture>
    </div>
    <div class="carousel-slide" id="slide2">
      <picture>
        <source media="(max-width: 480px)" srcset="img/banner3.png">
        <source media="(max-width: 768px)" srcset="img/banner3.png">
        <img src="img/banner3.png" alt="Special offer 3"/>
      </picture>
    </div>
  </div>
  
</section>

<!-- CATEGORY CARDS -->
<section id="cat-cards">
  <div class="cat-card-grid">
    <?php
    // ── Helper: fetch category image from DB ──
    function getCategoryImage($connect, $search_term) {
        $st = mysqli_real_escape_string($connect, $search_term);
        $tables = array('aliexpress_products', 'products', 'walmart_products', 'ebay_products');
        foreach ($tables as $table) {
            $q = "SELECT photo FROM $table WHERE search_query LIKE '%$st%' AND photo != '' AND photo NOT LIKE '%placehold%' AND photo IS NOT NULL LIMIT 1";
            $r = mysqli_query($connect, $q);
            if ($r && $row = mysqli_fetch_assoc($r)) return $row['photo'];
        }
        // Fallback: try normalized search term (lowercase, remove apostrophes & special chars)
        // This handles mismatches like "Men's Shoes" (admin_category) vs "mens shoes" (categories table)
        $normalized = strtolower(trim($search_term));
        $normalized = str_replace(["'", "`", "\""], '', $normalized);  // remove apostrophes/quotes entirely
        $normalized = str_replace('&', ' and ', $normalized);              // "&" → "and"
        $normalized = preg_replace('/\s+/', ' ', $normalized);            // collapse whitespace
        $normalized = trim($normalized);
        if ($normalized !== '' && $normalized !== strtolower(trim($st))) {
            $ns = mysqli_real_escape_string($connect, $normalized);
            foreach ($tables as $table) {
                $q = "SELECT photo FROM $table WHERE search_query LIKE '%$ns%' AND photo != '' AND photo NOT LIKE '%placehold%' AND photo IS NOT NULL LIMIT 1";
                $r = mysqli_query($connect, $q);
                if ($r && $row = mysqli_fetch_assoc($r)) return $row['photo'];
            }
        }
        return '';
    }

    // ── Load grouped categories from admin_category table ──
    $cat_result = mysqli_query($connect, "SELECT * FROM admin_category ORDER BY sort_order ASC, created_at ASC");
    $all_cats = array();
    if ($cat_result && mysqli_num_rows($cat_result) > 0) {
        while ($cr = mysqli_fetch_assoc($cat_result)) $all_cats[] = $cr;
    }
    // First 2: hero layout (50% width each)
    // Next 4: one row of 4 sub-category cards
    $first_2 = array_slice($all_cats, 0, 2);
    $row_4 = array_slice($all_cats, 2, 4);
    $next_4 = array_slice($all_cats, 6, 4);
    $remaining = array_slice($all_cats, 10);
    ?>
  </div>
</section>

<!-- HERO CATEGORIES (first 2) -->
<?php if (count($first_2) > 0): ?>
<section id="cat-hero">
  <div class="cat-hero-grid">
    <?php foreach ($first_2 as $cat_row):
      $g_name  = htmlspecialchars($cat_row['category_name']);
      $g_slug  = htmlspecialchars($cat_row['category_slug']);
      // Get 4 sub-category images + slugs + names
      $hero_corner_imgs = array();
      $hero_corner_slugs = array();
      $hero_corner_names = array();
      for ($si = 1; $si <= 4; $si++) {
          $sn = $cat_row["sub{$si}_name"] ?? '';
          $ss = $cat_row["sub{$si}_slug"] ?? '';
          if ($sn !== '') {
              $ci = getCategoryImage($connect, $sn);
              $hero_corner_imgs[] = $ci ?: '';
              $hero_corner_slugs[] = htmlspecialchars($ss);
              $hero_corner_names[] = htmlspecialchars($sn);
          } else {
              $hero_corner_imgs[] = '';
              $hero_corner_slugs[] = '';
              $hero_corner_names[] = '';
          }
      }
    ?>
    <div class="cat-hero-card">
      <div class="cat-hero-header"><?= $g_name; ?></div>
      <div class="cat-hero-grid-inner">
        <?php for ($hi = 0; $hi < 4; $hi++):
          $has_hero_sub = ($hero_corner_names[$hi] !== '');
          if ($has_hero_sub):
        ?>
        <a href="category.php?cat=<?= $hero_corner_slugs[$hi]; ?>" class="cat-hero-sub-card">
          <?php if ($hero_corner_imgs[$hi]): ?>
          <img class="cat-hero-sub-img" src="<?= $hero_corner_imgs[$hi]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <?php else: ?>
          <div class="cat-hero-sub-img" style="display:flex;align-items:center;justify-content:center;background:var(--gray-200);color:var(--gray-400);font-size:24px;font-weight:bold;"><?= strtoupper(substr($hero_corner_names[$hi], 0, 1) ?: '?'); ?></div>
          <?php endif; ?>
          <span class="cat-hero-sub-label"><?= $hero_corner_names[$hi]; ?></span>
        </a>
        <?php endif; endfor; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- CATEGORY CARDS (3rd-6th: 4 sub-category cards in a row) -->
<?php if (count($row_4) > 0): ?>
<section id="cat-mid-2" style="background:var(--off-white);padding-top:0;">
  <div class="cat-more-grid">
    <?php foreach ($row_4 as $cat_row):
      $g_name  = htmlspecialchars($cat_row['category_name']);
      $g_slug  = htmlspecialchars($cat_row['category_slug']);
      // Get 4 sub-category images + slugs + names
      $corner_imgs = array();
      $corner_slugs = array();
      $corner_names = array();
      for ($si = 1; $si <= 4; $si++) {
          $sn = $cat_row["sub{$si}_name"] ?? '';
          $ss = $cat_row["sub{$si}_slug"] ?? '';
          if ($sn !== '') {
              $ci = getCategoryImage($connect, $sn);
              $corner_imgs[] = $ci ?: 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="#1a1a2e" width="200" height="200"/></svg>');
              $corner_slugs[] = htmlspecialchars($ss);
              $corner_names[] = htmlspecialchars($sn);
          } else {
              $corner_imgs[] = '';
              $corner_slugs[] = '';
              $corner_names[] = '';
          }
      }
    ?>
    <div class="cat-more-card">
      <div class="cat-more-title"><?= $g_name; ?></div>
      <div class="cat-more-grid-inner">
        <?php if ($corner_imgs[0] && $corner_slugs[0]): ?>
        <a href="category.php?cat=<?= $corner_slugs[0]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[0]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[0]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[1] && $corner_slugs[1]): ?>
        <a href="category.php?cat=<?= $corner_slugs[1]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[1]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[1]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[2] && $corner_slugs[2]): ?>
        <a href="category.php?cat=<?= $corner_slugs[2]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[2]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[2]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[3] && $corner_slugs[3]): ?>
        <a href="category.php?cat=<?= $corner_slugs[3]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[3]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[3]; ?></span>
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- CATEGORY IMAGES MARQUEE -->
<section id="cat-marquee">
  <div class="marquee-wrap">
    <div class="marquee-track">
      <?php
      // ── Fetch ALL distinct categories from the DB ──
      $all_cats_db_q = mysqli_query($connect, "SELECT DISTINCT category_name FROM categories WHERE category_name != '' ORDER BY category_name ASC");
      $all_cats_db = array();
      if ($all_cats_db_q && mysqli_num_rows($all_cats_db_q) > 0) {
          while ($acr = mysqli_fetch_assoc($all_cats_db_q)) {
              $name = $acr['category_name'];
              $slug = strtolower(trim($name));
              $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
              $slug = trim($slug, '-');
              $all_cats_db[] = array('category_name' => $name, 'category_slug' => $slug);
          }
      }
      // Split into two halves: first 40, next 40
      $half = ceil(count($all_cats_db) / 2);
      $marquee_items = array_slice($all_cats_db, 0, $half);
      $marquee2_items_final = array_slice($all_cats_db, $half);

      // No fallback — backend only

      // Render each item twice for seamless infinite loop
      for ($copy = 0; $copy < 2; $copy++):
          foreach ($marquee_items as $mc):
              $mc_name = htmlspecialchars($mc['category_name']);
              $mc_slug = htmlspecialchars($mc['category_slug']);
              $mc_img  = getCategoryImage($connect, $mc_name);
              if (!$mc_img) {
                  // No image found — use a gradient placeholder
                  $colors = array('#1a1a2e','#16213e','#0f3460','#533483','#e94560','#C9973A','#2d6a4f','#6b21a8','#0369a1','#be123c');
                  $ci = abs(crc32($mc_name)) % count($colors);
                  $mc_img = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="' . $colors[$ci] . '" width="200" height="200"/><text x="100" y="110" text-anchor="middle" fill="rgba(255,255,255,.3)" font-family="Arial" font-size="60" font-weight="bold">' . strtoupper($mc_name[0]) . '</text></svg>');
              }
      ?>
      <a href="category.php?cat=<?= $mc_slug; ?>" class="marquee-item">
        <div class="marquee-img-wrap">
          <img class="marquee-img" src="<?= $mc_img; ?>" alt="<?= $mc_name; ?>" loading="lazy" onerror="this.style.display='none';this.parentNode.style.background='linear-gradient(135deg,#1a1a2e,#16213e)'"/>
        </div>
        <div class="marquee-label"><?= $mc_name; ?></div>
      </a>
      <?php
          endforeach;
      endfor;
      ?>
    </div>
  </div>
</section>

<!-- MID CATEGORIES (5th-8th): cards with 4 corner sub-images -->
<?php if (count($next_4) > 0): ?>
<section id="cat-mid" style="background:var(--off-white);padding-top:0;">
  <div class="cat-more-grid">
    <?php foreach ($next_4 as $cat_row):
      $g_name  = htmlspecialchars($cat_row['category_name']);
      $g_slug  = htmlspecialchars($cat_row['category_slug']);
      // Get main category image
      $main_img = getCategoryImage($connect, $g_name);
      $bg_style = $main_img ? "background:url('" . htmlspecialchars($main_img) . "') center/cover no-repeat;" : "background:linear-gradient(135deg, #1a1a2e, #16213e);";
      // Get 4 sub-category images + slugs + names
      $corner_imgs = array();
      $corner_slugs = array();
      $corner_names = array();
      for ($si = 1; $si <= 4; $si++) {
          $sn = $cat_row["sub{$si}_name"] ?? '';
          $ss = $cat_row["sub{$si}_slug"] ?? '';
          if ($sn !== '') {
              $ci = getCategoryImage($connect, $sn);
              $corner_imgs[] = $ci ?: 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="#1a1a2e" width="200" height="200"/></svg>');
              $corner_slugs[] = htmlspecialchars($ss);
              $corner_names[] = htmlspecialchars($sn);
          } else {
              $corner_imgs[] = '';
              $corner_slugs[] = '';
              $corner_names[] = '';
          }
      }
    ?>
    <div class="cat-more-card reveal">
      <div class="cat-more-title"><?= $g_name; ?></div>
      <div class="cat-more-grid-inner">
        <?php if ($corner_imgs[0] && $corner_slugs[0]): ?>
        <a href="category.php?cat=<?= $corner_slugs[0]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[0]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[0]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[1] && $corner_slugs[1]): ?>
        <a href="category.php?cat=<?= $corner_slugs[1]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[1]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[1]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[2] && $corner_slugs[2]): ?>
        <a href="category.php?cat=<?= $corner_slugs[2]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[2]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[2]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[3] && $corner_slugs[3]): ?>
        <a href="category.php?cat=<?= $corner_slugs[3]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[3]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[3]; ?></span>
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- CATEGORY IMAGES MARQUEE #2 (related to 5th-8th cats) -->
<section id="cat-marquee-2" style="background:var(--off-white);padding:0 48px 60px;">
  <div class="marquee-wrap">
    <div class="marquee-track">
      <?php
      // ── Second carousel gets the other half ──
      $marquee2_items = $marquee2_items_final;

      // Render each item twice for seamless infinite loop
      if (count($marquee2_items) > 0):
      for ($copy = 0; $copy < 2; $copy++):
          foreach ($marquee2_items as $mc):
              $mc_name = htmlspecialchars($mc['category_name']);
              $mc_slug = htmlspecialchars($mc['category_slug']);
              $mc_img  = getCategoryImage($connect, $mc_name);
              if (!$mc_img) {
                  $colors = array('#1a1a2e','#16213e','#0f3460','#533483','#e94560','#C9973A','#2d6a4f','#6b21a8','#0369a1','#be123c');
                  $ci = abs(crc32($mc_name)) % count($colors);
                  $mc_img = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="' . $colors[$ci] . '" width="200" height="200"/><text x="100" y="110" text-anchor="middle" fill="rgba(255,255,255,.3)" font-family="Arial" font-size="60" font-weight="bold">' . strtoupper($mc_name[0]) . '</text></svg>');
              }
      ?>
      <a href="category.php?cat=<?= $mc_slug; ?>" class="marquee-item">
        <div class="marquee-img-wrap">
          <img class="marquee-img" src="<?= $mc_img; ?>" alt="<?= $mc_name; ?>" loading="lazy" onerror="this.style.display='none';this.parentNode.style.background='linear-gradient(135deg,#1a1a2e,#16213e)'"/>
        </div>
        <div class="marquee-label"><?= $mc_name; ?></div>
      </a>
      <?php
          endforeach;
      endfor;
      endif;
      ?>
    </div>
  </div>
</section>

<!-- MORE CATEGORIES (9th+): cards with 4 corner sub-images -->
<?php if (count($remaining) > 0): ?>
<section id="cat-more" style="background:var(--off-white);padding-top:0;">
  <div class="cat-more-grid">
    <?php foreach ($remaining as $cat_row):
      $g_name  = htmlspecialchars($cat_row['category_name']);
      $g_slug  = htmlspecialchars($cat_row['category_slug']);
      $main_img = getCategoryImage($connect, $g_name);
      $corner_imgs = array();
      $corner_slugs = array();
      $corner_names = array();
      for ($si = 1; $si <= 4; $si++) {
          $sn = $cat_row["sub{$si}_name"] ?? '';
          $ss = $cat_row["sub{$si}_slug"] ?? '';
          if ($sn !== '') {
              $ci = getCategoryImage($connect, $sn);
              $corner_imgs[] = $ci ?: 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="#1a1a2e" width="200" height="200"/></svg>');
              $corner_slugs[] = htmlspecialchars($ss);
              $corner_names[] = htmlspecialchars($sn);
          } else {
              $corner_imgs[] = '';
              $corner_slugs[] = '';
              $corner_names[] = '';
          }
      }
    ?>
    <div class="cat-more-card reveal">
      <div class="cat-more-title"><?= $g_name; ?></div>
      <div class="cat-more-grid-inner">
        <?php if ($corner_imgs[0] && $corner_slugs[0]): ?>
        <a href="category.php?cat=<?= $corner_slugs[0]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[0]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[0]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[1] && $corner_slugs[1]): ?>
        <a href="category.php?cat=<?= $corner_slugs[1]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[1]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[1]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[2] && $corner_slugs[2]): ?>
        <a href="category.php?cat=<?= $corner_slugs[2]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[2]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[2]; ?></span>
        </a>
        <?php endif; ?>
        <?php if ($corner_imgs[3] && $corner_slugs[3]): ?>
        <a href="category.php?cat=<?= $corner_slugs[3]; ?>" class="cat-more-corner">
          <img class="cat-more-corner-img" src="<?= $corner_imgs[3]; ?>" alt="" loading="lazy" onerror="this.style.display='none'"/>
          <span class="cat-more-corner-label"><?= $corner_names[3]; ?></span>
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>


<!-- ══ HOW SPECORA WORKS — 4 Circular Steps ══ -->
<section id="how">
  <div class="how-inner">
    <div class="how-tag">How Specora Works</div>
    <h2 class="how-heading">Smart Shopping in <span class="hl-gold">4</span> Simple Steps</h2>
    <p class="how-sub">Discover, compare, and shop smarter across multiple marketplaces — all from one place.</p>
    <div class="how-circles">
      <div class="how-circle how-circle-active">
        <div class="hc-num">01</div>
        <div class="hc-icon"><i class="fa-solid fa-search"></i></div>
        <div class="hc-title">Search Products</div>
        <div class="hc-desc">Search for any product across multiple categories using our powerful product discovery engine.</div>
      </div>
      <div class="how-circle how-circle-light">
        <div class="hc-num">02</div>
        <div class="hc-icon"><i class="fa-solid fa-balance-scale"></i></div>
        <div class="hc-title">Compare Options</div>
        <div class="hc-desc">Instantly compare prices, specifications, ratings, and features from multiple trusted marketplaces.</div>
      </div>
      <div class="how-circle how-circle-light">
        <div class="hc-num">03</div>
        <div class="hc-icon"><i class="fa-solid fa-file-alt"></i></div>
        <div class="hc-title">Explore Insights</div>
        <div class="hc-desc">Review detailed product information, buying guides, and customer feedback before making your decision.</div>
      </div>
      <div class="how-circle how-circle-light">
        <div class="hc-num">04</div>
        <div class="hc-icon"><i class="fa-solid fa-shopping-cart"></i></div>
        <div class="hc-title">Shop Smarter</div>
        <div class="hc-desc">Choose the best deal and securely purchase from your preferred trusted online store.</div>
      </div>
    </div>
  </div>
</section>

<!-- ══ TESTIMONIALS — Premium Slider ══ -->
<section id="testimonials">
  <div class="testi-header">
    <span class="th-tag">What People Say About Specora</span>
    <h2>Trusted by Smart Shoppers Worldwide</h2>
    <p class="th-sub">Thousands of shoppers rely on Specora to discover products, compare prices, and make smarter buying decisions with confidence.</p>
  </div>

  <div class="testi-carousel-wrap">
    <div class="testi-carousel-track" id="testiTrack">
      <?php
      foreach ($approvedFeedback as $af):
        $afName = htmlspecialchars($af['name'] ?? 'User');
        $afRating = (int) ($af['rating'] ?? 5);
        $afText = htmlspecialchars($af['feedback_text'] ?? '');
        $afCity = htmlspecialchars($af['city'] ?? '');
        $afStars = str_repeat('★', $afRating) . str_repeat('☆', 5 - $afRating);
        $afInitial = mb_strtoupper(mb_substr($afName, 0, 1));
      ?>
      <div class="testi-card">
        <div class="testi-top-row">
          <div class="testi-stars"><?= $afStars ?></div>
          <span class="testi-verified-badge"><i class="fa-solid fa-check-circle"></i> Verified</span>
        </div>
        <p class="testi-text"><?= htmlspecialchars($afText) ?></p>
        <div class="testi-author">
          <div class="testi-avatar"><?= $afInitial ?></div>
          <div class="testi-info">
            <div class="testi-name"><?= $afName ?></div>
            <?php if ($afCity): ?><div class="testi-location"><?= $afCity ?></div><?php endif; ?>
            <div class="testi-verified"><i class="fa-solid fa-circle-check"></i> Verified Shopper</div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Navigation -->
  <div class="testi-nav">
    <button class="testi-btn" id="testiPrev" aria-label="Previous"><i class="fa-solid fa-arrow-left"></i></button>
    <div class="testi-dots" id="testiDots"></div>
    <button class="testi-btn" id="testiNext" aria-label="Next"><i class="fa-solid fa-arrow-right"></i></button>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="js/index.js?v=<?= filemtime(__DIR__.'/js/index.js') ?>"></script>
</body>
</html>