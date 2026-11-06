<?php
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
if (!validate_db_connection($connect)) { die('<p style="font-family:sans-serif;padding:40px;text-align:center;color:#991B1B;">⚠️ Database connection is currently unavailable. Please try later.</p>'); }

$asin   = isset($_GET['asin'])   ? trim($_GET['asin'])   : '';
$source = isset($_GET['source']) ? trim($_GET['source']) : 'amazon';

if ($asin == '') {
    http_response_code(302);
    header('Location: index.php');
    exit;
}

$asin_safe = mysqli_real_escape_string($connect, $asin);



$tableMap = array(
    'amazon'    => array('table' => 'products',            'id_col' => 'asin'),
    'aliexpress'=> array('table' => 'aliexpress_products', 'id_col' => 'product_id'),
    'walmart'   => array('table' => 'walmart_products',    'id_col' => 'item_id'),
    'ebay'      => array('table' => 'ebay_products',       'id_col' => 'item_id'),
    'flipkart'  => array('table' => 'flipkart_products',   'id_col' => 'item_id'),
);

// Default to amazon if source is unknown
$srcMap     = isset($tableMap[$source]) ? $tableMap[$source] : $tableMap['amazon'];
$srcTable   = $srcMap['table'];
$srcIdCol   = $srcMap['id_col'];


$sourceLabels = array(
    'amazon'    => 'Amazon',
    'aliexpress'=> 'AliExpress',
    'walmart'   => 'Walmart',
    'ebay'      => 'eBay',
    'flipkart'  => 'Flipkart',
);
$sourceLabel = isset($sourceLabels[$source]) ? $sourceLabels[$source] : 'Amazon';

if ($source == 'aliexpress') {
    $result = mysqli_query($connect, "
        SELECT p.*, c.category_name,
               p.product_id AS asin
        FROM $srcTable p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.product_id = '$asin_safe'
        LIMIT 1
    ");
} else {
    $result = mysqli_query($connect, "
        SELECT p.*, c.category_name,
               p.$srcIdCol AS asin
        FROM $srcTable p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.$srcIdCol = '$asin_safe'
        LIMIT 1
    ");
}

$product = mysqli_fetch_assoc($result);

if (!$product) {
    http_response_code(302);
    header('Location: index.php');
    exit;
}

$rating     = floatval($product['rating']);
$full_stars = floor($rating);
$stars_html = str_repeat('★', $full_stars) . str_repeat('☆', 5 - $full_stars);

// AliExpress also uses num_ratings as column name (like Amazon does)
$num_ratings_display = isset($product['num_ratings']) ? $product['num_ratings'] : 0;


$category_id  = (int)$product['category_id'];
$price_usd    = (float)$product['price_usd'];
$related      = array();
$relatedTable = $srcTable;
$relatedIdCol = ($source == 'aliexpress') ? 'product_id' : $srcIdCol;

// Step 1: Same category_id + price range ±40%
if ($category_id && $price_usd > 0) {
    $min_price = $price_usd * 0.6;
    $max_price = $price_usd * 1.4;

    $related_result = mysqli_query($connect, "
        SELECT p.*, c.category_name, p.$relatedIdCol AS asin FROM $relatedTable p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = $category_id
        AND p.$relatedIdCol != '$asin_safe'
        AND p.price_usd BETWEEN $min_price AND $max_price
        ORDER BY RAND()
        LIMIT 6
    ");
    while ($row = mysqli_fetch_assoc($related_result)) {
        $related[] = $row;
    }
}

// Step 2: If less than 6 found, fill remaining from same category (ignore price)
if ($category_id && count($related) < 6) {
    $existing_ids   = array_map(function($r) { return $r['asin']; }, $related);
    $existing_ids[] = $asin;
    $exclude_list = "'" . implode("','", array_map(function($a) use ($connect) {
        return mysqli_real_escape_string($connect, $a);
    }, $existing_ids)) . "'";

    $needed = 6 - count($related);
    $fill_result = mysqli_query($connect, "
        SELECT p.*, c.category_name, p.$relatedIdCol AS asin FROM $relatedTable p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.category_id = $category_id
        AND p.$relatedIdCol NOT IN ($exclude_list)
        ORDER BY RAND()
        LIMIT $needed
    ");
    while ($row = mysqli_fetch_assoc($fill_result)) {
        $related[] = $row;
    }
}

// Step 3: Last resort — if nothing found, get random products (from same table)
if (count($related) == 0) {
    $related_result2 = mysqli_query($connect, "
        SELECT p.*, c.category_name, p.$relatedIdCol AS asin FROM $relatedTable p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.$relatedIdCol != '$asin_safe'
        ORDER BY RAND() LIMIT 6
    ");
    while ($row = mysqli_fetch_assoc($related_result2)) {
        $related[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars(substr($product['title'], 0, 60)); ?> — Specora</title>
<meta name="description" content="<?= htmlspecialchars(substr($product['title'], 0, 120)) ?> — Compare prices and find the best deals on Specora. Check ratings, features, and more from top marketplaces.">
<meta name="keywords" content="<?= htmlspecialchars(strtolower(substr($product['title'], 0, 60))) ?>, product comparison, price compare, shopping, best deals">
<meta property="og:title" content="<?= htmlspecialchars(substr($product['title'], 0, 60)); ?> — Specora">
<meta property="og:description" content="Compare prices for <?= htmlspecialchars(substr($product['title'], 0, 120)) ?> on Specora. Find the best deals from Amazon, AliExpress, eBay &amp; Walmart.">
<meta property="og:image" content="<?= htmlspecialchars($product['photo'] ?? 'img/specora-logo.png') ?>">
<meta property="og:url" content="https://specora.pk/product.php?asin=<?= htmlspecialchars($product['asin'] ?? '') ?>&amp;source=<?= htmlspecialchars($product['source'] ?? '') ?>">
<meta property="og:type" content="product">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars(substr($product['title'], 0, 60)); ?> — Specora">
<meta name="twitter:description" content="Compare prices for <?= htmlspecialchars(substr($product['title'], 0, 120)) ?> on Specora. Find the best deals from top marketplaces.">
<meta name="twitter:image" content="<?= htmlspecialchars($product['photo'] ?? 'img/specora-logo.png') ?>">
<link rel="icon" type="image/svg+xml" href="img/favicon.svg">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/navbar.css?v=<?= filemtime(__DIR__.'/css/navbar.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
:root{
  --gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;
  --gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;
  --font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;
  --radius:14px;--radius-sm:10px;--radius-pill:100px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;font-size:16px;}
body{font-family:var(--font-body);background:var(--white);color:var(--black);display:flex;flex-direction:column;min-height:100vh;font-size:14px;line-height:1.5;}
a{text-decoration:none;color:inherit;}
img{max-width:100%;display:block;}
.breadcrumb{max-width:1100px;margin:0 auto;padding:16px 32px 0;display:flex;align-items:center;gap:6px;font-size:12px;color:var(--gray-400);flex-wrap:wrap;}
.breadcrumb a{color:var(--gray-400);transition:color .2s;}
.breadcrumb a:hover{color:var(--gold-dark);}
.breadcrumb span{color:var(--gray-600);}
.breadcrumb .current{max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;}
.product-detail{max-width:1100px;margin:0 auto;padding:24px 32px 56px;display:grid;grid-template-columns:380px 1fr;gap:40px;align-items:start;}
.product-image-wrap{position:sticky;top:76px;}
.product-main-img{width:100%;aspect-ratio:1;border-radius:var(--radius);overflow:hidden;background:var(--gray-100);border:1px solid var(--gray-200);}
.product-main-img img{width:100%;height:100%;object-fit:contain;padding:16px;}
.product-badges{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;}
.badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:var(--radius-pill);white-space:nowrap;}
.badge-gold{background:var(--gold);color:var(--black);}
.badge-dark{background:#232f3e;color:#fff;}
.badge-orange{background:#e47911;color:#fff;}
.badge-blue{background:#00A8E0;color:#fff;}
.badge-red{background:#FF4747;color:#fff;}
.product-title{font-family:var(--font-display);font-size:17px;font-weight:700;color:var(--black);letter-spacing:-.2px;line-height:1.4;margin-bottom:12px;word-break:break-word;}
.product-rating-row{display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;}
.stars{color:var(--gold);font-size:14px;letter-spacing:1px;}
.rating-num{font-size:13px;font-weight:700;color:var(--black);}
.rating-count{font-size:12px;color:var(--gray-400);}
.product-price-box{background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:16px 18px;margin-bottom:18px;}
.price-pkr{font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--black);}
.price-pkr sup{font-size:12px;font-weight:600;}
.price-usd{font-size:12px;color:var(--gray-400);margin-top:3px;}
.price-note{font-size:11px;color:var(--gray-400);margin-top:6px;line-height:1.4;}
.product-meta{display:flex;flex-direction:column;gap:9px;margin-bottom:20px;}
.meta-row{display:flex;gap:10px;font-size:12.5px;flex-wrap:wrap;}
.meta-label{color:var(--gray-400);min-width:68px;font-weight:500;}
.meta-val{color:var(--black);font-weight:600;}
.btn-amazon{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:var(--gold);color:var(--black);font-family:var(--font-display);font-size:14px;font-weight:700;padding:13px;border-radius:var(--radius-pill);border:none;cursor:pointer;transition:all .2s;text-decoration:none;margin-bottom:10px;}
.btn-amazon:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-2px);}
.btn-compare{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:var(--gold);color:var(--black);font-family:var(--font-display);font-size:14px;font-weight:700;padding:13px;border-radius:var(--radius-pill);border:none;cursor:pointer;transition:all .2s;text-decoration:none;margin-bottom:10px;}
.btn-compare:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-2px);}
.asin-box{background:var(--gray-100);border-radius:var(--radius-sm);padding:8px 12px;margin-top:12px;font-size:11px;color:var(--gray-400);word-break:break-all;}
.asin-box span{color:var(--black);font-weight:600;font-family:monospace;}
.related-section{max-width:1100px;margin:0 auto;padding:0 32px 56px;}
.related-title{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--black);letter-spacing:-.3px;margin-bottom:4px;}
.related-sub{font-size:12px;color:var(--gray-400);margin-bottom:18px;}
.related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;}
.product-card{background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;transition:transform .25s,box-shadow .25s;cursor:pointer;}
.product-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(0,0,0,.09);}
.product-img{width:100%;aspect-ratio:1;overflow:hidden;background:var(--gray-100);position:relative;}
.product-img img{width:100%;height:100%;object-fit:cover;transition:transform .3s;}
.product-card:hover .product-img img{transform:scale(1.04);}
.product-badge-sm{position:absolute;top:6px;left:6px;background:var(--gold);font-size:8.5px;font-weight:700;color:var(--black);padding:2px 7px;border-radius:var(--radius-pill);}
.product-body{padding:10px;}
.product-name-sm{font-family:var(--font-display);font-size:11.5px;font-weight:600;color:var(--black);margin-bottom:5px;line-height:1.35;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;}
.product-price-sm{font-size:12px;font-weight:700;color:var(--black);}
.product-rating-sm{font-size:10px;color:var(--gray-400);margin-bottom:3px;}
.product-rating-sm span{color:var(--gold-dark);}
@media(max-width:900px){.product-detail{grid-template-columns:320px 1fr;gap:28px;}}
@media(max-width:768px){
  .breadcrumb{padding:12px 20px 0;}
  .product-detail{grid-template-columns:1fr;padding:18px 20px 40px;gap:20px;}
  .product-image-wrap{position:static;max-width:340px;margin:0 auto;width:100%;}
  .product-title{font-size:16px;}
  .price-pkr{font-size:20px;}
  .related-section{padding:0 20px 40px;}
  .related-grid{grid-template-columns:repeat(2,1fr);gap:10px;}
}
@media(max-width:480px){
  .breadcrumb{padding:10px 16px 0;font-size:11px;}
  .breadcrumb .current{max-width:160px;}
  .product-detail{padding:16px 16px 32px;}
  .product-title{font-size:15px;}
  .price-pkr{font-size:18px;}
  .product-price-box{padding:14px 16px;}
  .related-section{padding:0 16px 32px;}
  .related-grid{grid-template-columns:repeat(2,1fr);gap:8px;}
  .related-title{font-size:16px;}
}
@media(max-width:360px){
  .related-grid{grid-template-columns:1fr 1fr;}
  .product-title{font-size:14px;}
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="breadcrumb">
  <a href="index.php">Home</a>
  <span>›</span>
  <span class="current">Products</span>
</div>

<div class="product-detail">
  <div class="product-image-wrap">
    <div class="product-main-img">
      <img src="<?= htmlspecialchars($product['photo'] ?: 'https://placehold.co/600x600/F4F3F0/A8A49C?text=No+Image'); ?>"
           alt="<?= htmlspecialchars($product['title']); ?>"
           onerror="this.src='https://placehold.co/600x600/F4F3F0/A8A49C?text=No+Image'"/>
    </div>
  </div>

  <div>
    <div class="product-badges">
      <?php if ($source != 'aliexpress'): ?>
        <?php if (!empty($product['badge'])): ?><span class="badge badge-gold"><?= htmlspecialchars($product['badge']); ?></span><?php endif; ?>
        <?php if (!empty($product['is_choice'])): ?><span class="badge badge-dark">Amazon's Choice</span><?php endif; ?>
        <?php if (!empty($product['is_best_seller'])): ?><span class="badge badge-orange">Best Seller</span><?php endif; ?>
        <?php if (!empty($product['is_prime'])): ?><span class="badge badge-blue">prime</span><?php endif; ?>
      <?php endif; ?>
    </div>

    <h1 class="product-title"><?= htmlspecialchars($product['title']); ?></h1>

    <?php if ($product['rating'] > 0): ?>
    <div class="product-rating-row">
      <span class="stars"><?= $stars_html; ?></span>
      <span class="rating-num"><?= number_format($product['rating'], 1); ?></span>
      <span class="rating-count">
        (<?= number_format($num_ratings_display); ?> <?= $source == 'aliexpress' ? 'sold' : 'reviews'; ?>)      </span>
    </div>
    <?php endif; ?>

    <div class="product-price-box">
      <?php if ($product['price_usd'] > 0): ?>
        <div class="price-pkr"><sup>$</sup><?= number_format($product['price_usd'], 2); ?> USD</div>
        <div class="price-note">* Price is approximate. Check on <?= $sourceLabel; ?> for the final price.</div>
      <?php else: ?>
        <div class="price-pkr" style="font-size:15px;color:var(--gray-400)">Check price on <?= $sourceLabel; ?></div>
      <?php endif; ?>
    </div>

    <div class="product-meta">
      <?php if (!empty($product['brand'])): ?>
      <div class="meta-row">
        <span class="meta-label">Brand</span>
        <span class="meta-val"><?= htmlspecialchars($product['brand']); ?></span>
      </div>
      <?php endif; ?>
      <?php if (!empty($product['category_name'])): ?>
      <div class="meta-row">
        <span class="meta-label">Category</span>
        <span class="meta-val"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $product['category_name']))); ?></span>
      </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($product['affiliate_link'])): ?>
    <a href="<?= htmlspecialchars($product['affiliate_link']); ?>" target="_blank" rel="noopener" class="btn-amazon">
      🛒 View on <?= $sourceLabel; ?> →
    </a>
    <?php endif; ?>
    <button class="btn-compare" onclick="addToCompare({asin:'<?= htmlspecialchars($product['asin'] ?? '') ?>',source:'<?= htmlspecialchars($source) ?>',title:'<?= htmlspecialchars(str_replace("'","\\'",$product['title'] ?? '')) ?>',photo:'<?= htmlspecialchars(str_replace("'","\\'",$product['photo'] ?? '')) ?>',price:'<?= $price_usd > 0 ? number_format($price_usd,2) : '' ?>',brand:'<?= htmlspecialchars(str_replace("'","\\'",$product['brand'] ?? '')) ?>',rating:'<?= $product['rating'] ?>',num_ratings:'<?= $num_ratings_display ?>',url:'<?= htmlspecialchars(str_replace("'","\\'",$product['affiliate_link'] ?? '')) ?>'})">
      ⚖️ Add to Compare
    </button>
  </div>
</div>

<?php if (count($related) > 0): ?>
<div class="related-section">
  <div class="related-title">Related Products</div>
  <div class="related-sub">Products in the same category & similar price range</div>
  <div class="related-grid">
    <?php foreach ($related as $r): ?>
    <a href="product.php?asin=<?= urlencode($r['asin']); ?>&source=<?= urlencode($source); ?>" style="text-decoration:none;color:inherit;">
      <div class="product-card">
        <div class="product-img">
          <img src="<?= htmlspecialchars($r['photo'] ?: 'https://placehold.co/300x300/F4F3F0/A8A49C?text=No+Image'); ?>"
               alt="product" loading="lazy"
               onerror="this.src='https://placehold.co/300x300/F4F3F0/A8A49C?text=No+Image'"/>
          <?php if (!empty($r['badge'])): ?>
            <span class="product-badge-sm"><?= htmlspecialchars($r['badge']); ?></span>
          <?php endif; ?>
        </div>
        <div class="product-body">
          <div class="product-rating-sm"><span>★</span> <?= $r['rating'] > 0 ? number_format($r['rating'],1) : '—'; ?></div>
          <div class="product-name-sm"><?= htmlspecialchars(substr($r['title'], 0, 65)) . (strlen($r['title']) > 65 ? '…' : ''); ?></div>
          <div class="product-price-sm">
            <?= $r['price_usd'] > 0 ? '$' . number_format($r['price_usd'], 2) : 'See on site'; ?>
          </div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script src="js/compare.js?v=<?= filemtime(__DIR__.'/js/compare.js') ?>"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>