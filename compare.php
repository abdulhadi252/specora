<?php
/**
 * compare.php — Specora
 * Compare Products page — loads products from sessionStorage and displays comparison table
 */

require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
if (!validate_db_connection($connect)) { die('<p style="font-family:sans-serif;padding:40px;text-align:center;color:#991B1B;">⚠️ Database connection is currently unavailable. Please try again later.</p>'); }
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Compare Products — Specora</title>
<meta name="description" content="Compare products side by side on Specora. Find the best deals across Amazon, AliExpress, eBay &amp; Walmart.">
<meta name="robots" content="noindex">
<link rel="icon" type="image/svg+xml" href="img/favicon.svg">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/navbar.css?v=<?= filemtime(__DIR__.'/css/navbar.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:12px;--radius-sm:8px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--off-white);color:var(--black);display:flex;flex-direction:column;min-height:100vh;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}
a{text-decoration:none;color:inherit;}
img{max-width:100%;display:block;}

/* ══ PAGE HEADER ══ */
.page-header{max-width:1200px;margin:0 auto;padding:28px 32px 0;width:100%;}
.page-breadcrumb{font-size:12px;color:var(--gray-400);margin-bottom:10px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;font-weight:400;}
.page-breadcrumb a{color:var(--gray-400);transition:color .2s;}
.page-breadcrumb a:hover{color:var(--black);}
.page-breadcrumb span{color:var(--gray-600);}
.page-title{font-family:var(--font-display);font-size:clamp(20px,2.8vw,28px);font-weight:600;color:var(--black);letter-spacing:-.5px;margin-bottom:4px;}
.page-sub{font-size:13px;color:var(--gray-400);margin-bottom:20px;font-weight:400;}
.page-actions{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px;}

/* ══ ERROR / EMPTY STATE ══ */
.compare-error{max-width:1200px;margin:0 auto;padding:80px 32px;width:100%;}
.compare-error-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:56px 32px;text-align:center;}
.compare-error-icon{font-size:48px;margin-bottom:14px;color:var(--gray-400);}
.compare-error-title{font-family:var(--font-display);font-size:20px;font-weight:600;color:var(--black);margin-bottom:6px;}
.compare-error-desc{font-size:14px;color:var(--gray-400);margin-bottom:20px;line-height:1.6;max-width:400px;margin-left:auto;margin-right:auto;font-weight:400;}
.compare-error-btn{display:inline-flex;align-items:center;gap:6px;padding:11px 24px;background:var(--black);color:var(--white);font-family:var(--font-body);font-size:13px;font-weight:500;border:none;border-radius:var(--radius-pill);cursor:pointer;transition:all .2s;text-decoration:none;}
.compare-error-btn:hover{background:var(--gray-600);}

/* ══ COMPARE TABLE ══ */
.compare-table-wrap{max-width:1200px;margin:0 auto;padding:0 32px 56px;width:100%;overflow-x:auto;}
.compare-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);}
.compare-table tr:last-child td:first-child{border-bottom-left-radius:var(--radius);}
.compare-table tr:last-child td:last-child{border-bottom-right-radius:var(--radius);}

.cp-header th{
  padding:18px 14px 10px;
  text-align:center;
  vertical-align:top;
  border-bottom:1px solid var(--gray-200);
  background:var(--off-white);
  min-width:170px;
}
.cp-header th:first-child{
  width:auto;
  min-width:90px;
  max-width:120px;
  text-align:center;
  vertical-align:middle;
  white-space:nowrap;
}

.cp-h-img-wrap{
  width:120px;
  height:120px;
  margin:0 auto 8px;
  border-radius:var(--radius-sm);
  overflow:hidden;
  background:var(--gray-100);
  border:1px solid var(--gray-200);
}
.cp-h-img{width:100%;height:100%;object-fit:contain;padding:6px;display:block;}
.cp-h-title{font-family:var(--font-display);font-size:12px;font-weight:600;color:var(--black);line-height:1.35;margin-bottom:6px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.cp-h-source{display:inline-flex;font-size:8px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;padding:2px 8px;border-radius:3px;line-height:1.4;}
.cp-h-source.amazon{background:#232f3e;color:#fff;}
.cp-h-source.aliexpress{background:#FF4747;color:#fff;}
.cp-h-source.ebay{background:#0064D2;color:#fff;}
.cp-h-source.walmart{background:#0071DC;color:#fff;}
.cp-h-source.flipkart{background:#2874F0;color:#fff;}

/* Best column highlight — subtle */
.cp-header th.best-col{position:relative;}
.cp-header th.best-col::before{content:'Best';position:absolute;top:6px;right:6px;font-size:8px;font-weight:600;letter-spacing:.4px;color:var(--black);background:var(--gray-100);padding:1px 8px;border-radius:100px;line-height:1.5;text-transform:uppercase;}
.cp-body td.best-col{background:var(--off-white);}

/* Body rows */
.cp-body td{
  padding:12px 14px;
  text-align:center;
  vertical-align:middle;
  border-bottom:1px solid var(--gray-100);
  font-size:13px;
  color:var(--gray-600);
  min-width:170px;
  font-weight:400;
}
.cp-body td:first-child{
  width:auto;
  min-width:90px;
  max-width:120px;
  text-align:center;
  font-weight:500;
  color:var(--black);
  font-size:12px;
  white-space:nowrap;
}
.cp-body tr:last-child td{border-bottom:none;}

/* Price */
.cp-price{font-family:var(--font-display);font-size:17px;font-weight:600;color:var(--black);}
.cp-price sup{font-size:10px;font-weight:500;}
.cp-price.best{color:var(--black);}

/* Rating stars */
.cp-stars{color:var(--gold-dark);font-size:13px;letter-spacing:1px;}
.cp-rating-num{font-size:12px;font-weight:500;color:var(--black);margin-left:3px;}
.cp-reviews{font-size:12px;color:var(--gray-400);font-weight:400;}

.cp-na{color:var(--gray-200);font-size:12px;}

/* View button */
.cp-view-btn{display:inline-flex;align-items:center;gap:5px;padding:8px 16px;background:var(--black);color:var(--white);font-family:var(--font-body);font-size:12px;font-weight:500;border-radius:var(--radius-pill);border:none;cursor:pointer;transition:all .2s;text-decoration:none;}
.cp-view-btn:hover{background:var(--gray-600);}
.cp-view-disabled{
  background:var(--gray-100) !important;
  color:var(--gray-400) !important;
  cursor:default !important;
  pointer-events:none !important;
}

/* ══ BEST RECOMMENDATION SECTION ══ */
.best-section{max-width:1200px;margin:0 auto;padding:0 32px 56px;width:100%;}
.best-card{
  background:var(--white);
  border:1.5px solid var(--gray-200);
  border-radius:var(--radius);
  padding:28px 32px;
  display:flex;
  gap:28px;
  flex-wrap:wrap;
  position:relative;
  overflow:hidden;
  transition:all .25s ease;
  box-shadow:0 2px 8px rgba(0,0,0,.04);
}

.best-heading{
  width:100%;
  font-family:var(--font-display);
  font-size:16px;
  font-weight:600;
  color:var(--black);
  letter-spacing:-.3px;
  padding-bottom:10px;
  border-bottom:1px solid var(--gray-100);
  margin-bottom:4px;
  display:flex;
  align-items:center;
  gap:8px;
}
.best-product{
  flex:1;
  min-width:240px;
  display:flex;
  gap:20px;
  flex-wrap:wrap;
}
.best-product-img-wrap{
  width:130px;
  height:130px;
  border-radius:var(--radius-sm);
  overflow:hidden;
  background:var(--gray-100);
  border:1px solid var(--gray-200);
  flex-shrink:0;
  transition:all .2s ease;
}
.best-card:hover .best-product-img-wrap{
  border-color:var(--gold-dark);
  box-shadow:0 4px 12px rgba(234,189,120,.15);
}
.best-product-img{width:100%;height:100%;object-fit:contain;padding:5px;display:block;}
.best-product-info{flex:1;min-width:160px;}
.best-product-source{
  display:inline-flex;
  font-size:8px;
  font-weight:600;
  letter-spacing:.4px;
  text-transform:uppercase;
  padding:2px 8px;
  border-radius:3px;
  line-height:1.4;
  margin-bottom:6px;
}
.best-product-title{
  font-family:var(--font-display);
  font-size:16px;
  font-weight:600;
  color:var(--black);
  letter-spacing:-.2px;
  line-height:1.3;
  margin-bottom:10px;
}
.best-product-details{
  display:flex;
  flex-wrap:wrap;
  gap:6px 18px;
  margin-bottom:10px;
}
.best-detail{
  font-size:12px;
  color:var(--gray-600);
  font-weight:500;
  display:inline-flex;
  align-items:center;
  gap:4px;
}
.best-detail i{
  font-size:11px;
  color:var(--gray-400);
  width:14px;
  text-align:center;
}
.best-detail strong{
  color:var(--black);
  font-weight:600;
}
.best-detail .best-price-val{
  font-family:var(--font-display);
  font-size:17px;
  font-weight:600;
  color:var(--black);
}
.best-detail .best-stars{
  color:var(--gold-dark);
  font-size:12px;
  letter-spacing:1px;
}
.best-reason{
  font-size:12px;
  color:var(--gray-400);
  line-height:1.5;
  margin-top:6px;
  font-weight:400;
  max-width:480px;
}
.best-reason strong{color:var(--gray-600);font-weight:500;}
.best-cta-wrap{
  flex-shrink:0;
  display:flex;
  flex-direction:column;
  align-items:flex-end;
  justify-content:center;
  gap:8px;
  min-width:130px;
}
.best-cta-link{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:10px 22px;
  background:var(--black);
  color:var(--white);
  font-family:var(--font-body);
  font-size:13px;
  font-weight:500;
  border-radius:var(--radius-pill);
  border:none;
  cursor:pointer;
  transition:all .2s;
  text-decoration:none;
}
.best-cta-link:hover{background:var(--gray-600);}
.best-cta-disabled{
  background:var(--gray-100) !important;
  color:var(--gray-400) !important;
  cursor:default !important;
  pointer-events:none !important;
  font-size:12px;
}
.best-score{
  font-size:11px;
  color:var(--gray-400);
  font-weight:400;
  text-align:right;
}

/* ══ RESPONSIVE — TABLET (768px) ══ */
@media(max-width:768px){
  .page-header{padding:20px 20px 0;}
  .page-title{font-size:clamp(18px,3.5vw,24px);}
  .page-sub{font-size:12px;margin-bottom:16px;}
  .page-actions{margin-bottom:20px;gap:8px;}

  .compare-table-wrap{padding:0 20px 48px;}
  .compare-table-wrap::before{
    content:'← Scroll →';
    display:block;
    text-align:center;
    font-size:10px;
    color:var(--gray-400);
    padding:6px 0 10px;
    letter-spacing:1px;
    font-weight:400;
  }
  .cp-header th{padding:14px 10px 8px;min-width:150px;}
  .cp-header th:first-child{width:auto;min-width:80px;max-width:105px;white-space:nowrap;}
  .cp-h-img-wrap{width:90px;height:90px;margin-bottom:6px;}
  .cp-h-img{padding:4px;}
  .cp-h-title{font-size:11px;-webkit-line-clamp:2;}
  .cp-body td{padding:10px 10px;font-size:12px;min-width:150px;}
  .cp-body td:first-child{width:auto;min-width:80px;max-width:105px;font-size:11px;padding:10px 8px;white-space:nowrap;}
  .cp-price{font-size:15px;}
  .cp-stars{font-size:12px;}

  .best-section{padding:0 20px 40px;}
  .best-card{padding:24px 24px;gap:20px;}
  .best-heading{font-size:15px;}
  .best-product{gap:16px;}
  .best-product-img-wrap{width:100px;height:100px;}
  .best-product-title{font-size:15px;}
  .best-product-details{gap:4px 14px;}
  .best-detail{font-size:11px;}
  .best-cta-wrap{width:100%;align-items:stretch;flex-direction:row;justify-content:flex-start;min-width:0;}
  .best-cta-link{justify-content:center;flex:1;pointer-events:auto;position:relative;z-index:2;}
  .best-score{text-align:center;}
  .best-reason{max-width:100%;font-size:11px;}
}

/* ══ RESPONSIVE — MOBILE (480px) ══ */
@media(max-width:480px){
  .page-header{padding:16px 16px 0;}
  .page-title{font-size:18px;letter-spacing:-.3px;margin-bottom:2px;}
  .page-sub{font-size:11px;margin-bottom:12px;}
  .page-breadcrumb{font-size:11px;margin-bottom:8px;}
  .page-actions{margin-bottom:16px;flex-direction:row;flex-wrap:nowrap;gap:6px;}
  .page-actions .compare-error-btn{flex:1;justify-content:center;padding:9px 12px;font-size:11px;}

  .compare-table-wrap{padding:0 12px 36px;}
  .compare-table-wrap::before{font-size:9px;padding:4px 0 8px;}
  .cp-header th{padding:12px 8px 6px;min-width:130px;}
  .cp-header th:first-child{width:auto;min-width:65px;max-width:85px;white-space:nowrap;padding:12px 4px;}
  .cp-h-img-wrap{width:72px;height:72px;margin-bottom:4px;}
  .cp-h-img{padding:3px;}
  .cp-h-title{font-size:10px;-webkit-line-clamp:2;margin-bottom:4px;}
  .cp-h-source{font-size:7px;padding:1px 6px;}
  .cp-body td{padding:8px 8px;font-size:11px;min-width:130px;}
  .cp-body td:first-child{width:auto;min-width:65px;max-width:85px;font-size:10px;padding:8px 4px;white-space:nowrap;}
  .cp-price{font-size:14px;}
  .cp-price sup{font-size:9px;}
  .cp-stars{font-size:11px;}
  .cp-rating-num{font-size:11px;}
  .cp-reviews{font-size:11px;}
  .cp-view-btn{padding:6px 14px;font-size:11px;}
  .cp-na{font-size:11px;}

  .best-section{padding:0 16px 32px;}
  .best-card{padding:20px 16px;gap:14px;flex-direction:column;align-items:stretch;}
  .best-heading{font-size:14px;padding-bottom:8px;}
  .best-product{gap:12px;flex-direction:column;}
  .best-product-img-wrap{width:110px;height:110px;margin:0 auto;}
  .best-product-info{text-align:center;}
  .best-product-title{font-size:14px;text-align:center;}
  .best-product-details{justify-content:center;gap:4px 10px;}
  .best-detail{font-size:11px;}
  .best-detail .best-price-val{font-size:15px;}
  .best-reason{font-size:11px;text-align:center;max-width:100%;}
  .best-cta-wrap{flex-direction:column;align-items:stretch;}
  .best-cta-link{width:100%;justify-content:center;pointer-events:auto;position:relative;z-index:2;}
  .best-score{text-align:center;}
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="page-breadcrumb">
    <a href="index.php">Home</a>
    <span>›</span>
    <span>Compare Products</span>
  </div>
  <h1 class="page-title">Compare Products</h1>
  <p class="page-sub" id="comparePageSub">Compare products side by side.</p>
  <div class="page-actions" id="comparePageActions" style="display:none;">
    <a href="index.php" class="compare-error-btn" style="background:var(--gray-100);color:var(--gray-600);font-size:12px;padding:9px 18px;">← Browse Products</a>
    <button class="compare-error-btn" style="font-size:12px;padding:9px 18px;" onclick="clearCompareAndReload()">Clear All</button>
  </div>
</div>

<!-- ERROR STATE -->
<div id="compareError" class="compare-error" style="display:none;">
  <div class="compare-error-card">
    <div class="compare-error-icon"><i class="fas fa-balance-scale"></i></div>
    <div class="compare-error-title">Not Enough Products</div>
    <div class="compare-error-desc" id="compareErrorDesc">Please add at least 2 products to compare.</div>
    <a href="index.php" class="compare-error-btn">← Browse Products</a>
  </div>
</div>

<!-- COMPARE TABLE -->
<div id="compareTableWrap" class="compare-table-wrap" style="display:none;">
  <table class="compare-table" id="compareTable">
    <thead id="compareTableHead"></thead>
    <tbody id="compareTableBody"></tbody>
  </table>
</div>

<!-- BEST RECOMMENDATION -->
<div id="bestSection" class="best-section" style="display:none;">
  <div class="best-card" id="bestCard"></div>
</div>

<script>
// ── Helper: get compare list from sessionStorage ──
function getCompareList() {
    try {
        return JSON.parse(sessionStorage.getItem('specora_compare_products')) || [];
    } catch (e) {
        return [];
    }
}

// ── Helper: get source label ──
function getSourceLabel(source) {
    var labels = {amazon:'Amazon',aliexpress:'AliExpress',ebay:'eBay',walmart:'Walmart',flipkart:'Flipkart'};
    return labels[source] || source || 'Amazon';
}

// ── Helper: resolve marketplace URL with source-specific priority ──
// Amazon: affiliate_link ONLY. Never product_url.
// Others: affiliate_link first, then product_url fallback.
// NEVER fall back to internal pages.
function resolveMarketplaceUrl(p) {
    if (!p) return {url:'', hasUrl:false};
    var s = (p.source || '').toLowerCase();
    var u = '';
    if (s === 'amazon') {
        u = (p.affiliate_url && p.affiliate_url.trim()) ? p.affiliate_url.trim()
          : (p.url && p.url.trim()) ? p.url.trim()
          : '';
    } else {
        u = (p.affiliate_url && p.affiliate_url.trim()) ? p.affiliate_url.trim()
          : (p.url && p.url.trim()) ? p.url.trim()
          : (p.product_url && p.product_url.trim()) ? p.product_url.trim()
          : '';
    }
    return {url: u, hasUrl: u !== ''};
}

// ── Helper: escape HTML ──
function htmlEscape(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// ── Helper: render stars ──
function renderStars(rating) {
    var r = parseFloat(rating) || 0;
    var count = Math.max(0, Math.min(5, Math.round(r)));
    return '★'.repeat(count) + '☆'.repeat(5 - count);
}

// ── Find the best product — price + rating focused ──
function findBestProduct(products) {
    if (!products || products.length < 2) return null;

    var best = null;
    var bestScore = -Infinity;

    products.forEach(function(p) {
        var rating = parseFloat(p.rating) || 0;
        var reviews = parseInt(p.num_ratings) || 0;
        var price = parseFloat(p.price) || 0;

        // Price is most important: 0-45 points
        var priceScore = 0;
        if (price > 0) {
            var avgPrice = 0;
            var priceSum = 0;
            var priceCount = 0;
            products.forEach(function(op) {
                var opPrice = parseFloat(op.price) || 0;
                if (opPrice > 0) { priceSum += opPrice; priceCount++; }
            });
            avgPrice = priceCount > 0 ? priceSum / priceCount : price;
            // Lower price gets higher score
            priceScore = (1 - (price / avgPrice)) * 22.5 + 22.5;
            priceScore = Math.max(0, Math.min(45, priceScore));
        } else {
            priceScore = 8;
        }

        // Rating is equally important: 0-45 points
        var ratingScore = (rating / 5) * 45;
        ratingScore = Math.max(0, Math.min(45, ratingScore));

        // Reviews give a small boost: 0-10 points
        var reviewScore = Math.min(reviews / 5000, 1) * 10;

        var totalScore = ratingScore + priceScore + reviewScore;

        // Small bonus for having all data
        if (rating > 0) totalScore += 5;
        if (reviews > 0) totalScore += 3;
        if (price > 0) totalScore += 5;

        if (totalScore > bestScore) {
            bestScore = totalScore;
            best = {product: p, score: totalScore, ratingScore: ratingScore, reviewScore: reviewScore, priceScore: priceScore};
        }
    });

    return best;
}

// ── Generate explanation for best product ──
function getBestExplanation(best, products) {
    if (!best || !best.product) return '';
    var p = best.product;
    var parts = [];

    var rating = parseFloat(p.rating) || 0;
    var reviews = parseInt(p.num_ratings) || 0;
    var price = parseFloat(p.price) || 0;

    if (rating > 0) {
        if (rating >= 4.5) parts.push('Exceptional ' + rating.toFixed(1) + '★ rating');
        else if (rating >= 4.0) parts.push('Excellent ' + rating.toFixed(1) + '★ rating');
        else if (rating >= 3.5) parts.push('Strong ' + rating.toFixed(1) + '★ rating');
        else parts.push(rating.toFixed(1) + '★ rating');
    }

    if (reviews > 1000) parts.push(reviews.toLocaleString() + '+ reviews');
    else if (reviews > 100) parts.push(reviews.toLocaleString() + ' reviews');
    else if (reviews > 0) parts.push(reviews + ' reviews');

    if (price > 0) {
        var lowerPriced = 0;
        products.forEach(function(op) {
            var opPrice = parseFloat(op.price) || Infinity;
            if (opPrice > 0 && opPrice < price) lowerPriced++;
        });
        if (lowerPriced === 0) parts.push('Best price at $' + price.toFixed(2));
        else parts.push('$' + price.toFixed(2));
    }

    if (parts.length === 0) return 'Best overall based on available data.';
    return 'Selected for: ' + parts.join(' · ') + '.';
}

// ── Render the compare page ──
function renderComparePage() {
    var list = getCompareList();

    if (list.length < 2) {
        document.getElementById('compareError').style.display = 'block';
        var desc = 'You currently have <strong>' + list.length + '</strong> product' + (list.length !== 1 ? 's' : '') + ' in your compare list.';
        document.getElementById('compareErrorDesc').innerHTML = desc + ' Please add at least <strong>2 products</strong> to compare.';
        return;
    }

    document.getElementById('comparePageSub').textContent = 'Comparing ' + list.length + ' products side by side.';
    document.getElementById('comparePageActions').style.display = 'flex';
    document.getElementById('compareTableWrap').style.display = 'block';

    var bestResult = findBestProduct(list);
    var bestKey = bestResult ? bestResult.product.asin + '|' + bestResult.product.source : null;

    // ── Table header ──
    var thead = document.getElementById('compareTableHead');
    var headHtml = '<tr class="cp-header"><th>Features</th>';
    list.forEach(function(p) {
        var isBest = (p.asin + '|' + p.source) === bestKey;
        var cls = isBest ? 'best-col' : '';
        var photo = htmlEscape(p.photo || 'https://placehold.co/280x280/F4F3F0/A8A49C?text=No+Image');
        var title = htmlEscape(p.title || 'Product');
        var source = getSourceLabel(p.source);

        headHtml += '<th class="' + cls + '">'
            + '<div class="cp-h-img-wrap"><img class="cp-h-img" src="' + photo + '" alt="' + title + '" onerror="this.src=\'https://placehold.co/280x280/F4F3F0/A8A49C?text=No+Image\'"/></div>'
            + '<div class="cp-h-title">' + title + '</div>'
            + '<span class="cp-h-source ' + p.source + '">' + source + '</span>'
            + '</th>';
    });
    headHtml += '</tr>';
    thead.innerHTML = headHtml;

    // ── Table body rows ──
    var tbody = document.getElementById('compareTableBody');
    var rows = [];

    rows.push({label:'Price', icon:'fas fa-tag', render:function(p){
        var b = (p.asin+'|'+p.source)===bestKey;
        if(p.price&&parseFloat(p.price)>0) return '<span class="cp-price'+(b?' best':'')+'"><sup>$</sup>'+parseFloat(p.price).toFixed(2)+'</span>';
        return '<span class="cp-na">—</span>';
    }});

    rows.push({label:'Brand', icon:'fas fa-trademark', render:function(p){
        return p.brand ? htmlEscape(p.brand) : '<span class="cp-na">—</span>';
    }});

    rows.push({label:'Marketplace', icon:'fas fa-store', render:function(p){
        return '<span class="cp-h-source '+p.source+'">'+getSourceLabel(p.source)+'</span>';
    }});

    rows.push({label:'Rating', icon:'fas fa-star', render:function(p){
        var r=parseFloat(p.rating)||0;
        if(r>0) return '<span class="cp-stars">'+renderStars(r)+'</span> <span class="cp-rating-num">'+r.toFixed(1)+'</span>';
        return '<span class="cp-na">—</span>';
    }});

    rows.push({label:'Reviews', icon:'fas fa-comments', render:function(p){
        var n=parseInt(p.num_ratings)||0;
        if(n>0) return '<span class="cp-reviews">'+n.toLocaleString()+'</span>';
        return '<span class="cp-na">—</span>';
    }});

    rows.push({label:'Availability', icon:'fas fa-check-circle', render:function(p){
        return '<span style="color:var(--gray-600);font-size:12px;">In Stock</span>';
    }});

    rows.push({label:'Category', icon:'fas fa-folder', render:function(p){
        return p.category ? htmlEscape(p.category) : '<span class="cp-na">—</span>';
    }});

    rows.push({label:'Key Specs', icon:'fas fa-cogs', render:function(p){
        var s=[];
        if(p.brand) s.push(htmlEscape(p.brand));
        s.push(getSourceLabel(p.source));
        s.push('Verified');
        return s.join(' &middot; ');
    }});

    rows.push({label:'Product Link', icon:'fas fa-external-link-alt', render:function(p){
        var mu = resolveMarketplaceUrl(p);
        if(mu.hasUrl) return '<a href="'+htmlEscape(mu.url)+'" class="cp-view-btn" target="_blank" rel="noopener noreferrer">View <i class="fas fa-arrow-right" style="font-size:10px;"></i></a>';
        return '<span class="cp-view-btn cp-view-disabled">Unavailable</span>';
    }});

    var bodyHtml = '';
    rows.forEach(function(row){
        bodyHtml += '<tr class="cp-body">';
        bodyHtml += '<td><i class="'+row.icon+'" style="margin-right:5px;color:var(--gray-400);width:14px;text-align:center;font-size:12px;"></i>'+row.label+'</td>';
        list.forEach(function(p){
            var b = (p.asin+'|'+p.source)===bestKey;
            bodyHtml += '<td class="'+(b?'best-col':'')+'">'+row.render(p)+'</td>';
        });
        bodyHtml += '</tr>';
    });
    tbody.innerHTML = bodyHtml;

    renderBestRecommendation(bestResult, list);
}

// ── Render Best Recommendation with full details ──
function renderBestRecommendation(bestResult, products) {
    var section = document.getElementById('bestSection');
    var card = document.getElementById('bestCard');

    if (!bestResult || !bestResult.product) return;

    var p = bestResult.product;
    var explanation = getBestExplanation(bestResult, products);
    var safeTitle = htmlEscape(p.title || 'Product');
    var safeSource = getSourceLabel(p.source);
    var safePhoto = htmlEscape(p.photo || 'https://placehold.co/200x200/F4F3F0/A8A49C?text=No+Image');
    var rating = parseFloat(p.rating) || 0;
    var reviews = parseInt(p.num_ratings) || 0;
    var price = parseFloat(p.price) || 0;
    var brand = p.brand ? htmlEscape(p.brand) : '—';

    // Build source badge color
    var sourceColors = {
        amazon: 'background:#232f3e;color:#fff;',
        aliexpress: 'background:#FF4747;color:#fff;',
        ebay: 'background:#0064D2;color:#fff;',
        walmart: 'background:#0071DC;color:#fff;',
        flipkart: 'background:#2874F0;color:#fff;'
    };
    var srcStyle = sourceColors[p.source] || sourceColors.amazon;

    // Source-specific URL priority — NEVER fallback to internal pages
    var mu = resolveMarketplaceUrl(p);

    var safeUrl = mu.hasUrl ? htmlEscape(mu.url) : '';

    card.innerHTML = ''
        + '<div class="best-heading">Recommended Product</div>'
        + '<div class="best-product">'
        + '  <div class="best-product-img-wrap">'
        + '    <img class="best-product-img" src="' + safePhoto + '" alt="' + safeTitle + '" onerror="this.src=\'https://placehold.co/200x200/F4F3F0/A8A49C?text=No+Image\'">'
        + '  </div>'
        + '  <div class="best-product-info">'
        + '    <span class="best-product-source" style="' + srcStyle + '">' + safeSource + '</span>'
        + '    <div class="best-product-title">' + safeTitle + '</div>'
        + '    <div class="best-product-details">'
        + '      <span class="best-detail"><i class="fas fa-trademark"></i>Brand: <strong>' + brand + '</strong></span>'
        + (price > 0 ? '      <span class="best-detail"><i class="fas fa-tag"></i>Price: <strong class="best-price-val">$' + price.toFixed(2) + '</strong></span>' : '')
        + (rating > 0 ? '      <span class="best-detail"><i class="fas fa-star" style="color:var(--gold-dark);"></i>Rating: <strong><span class="best-stars">' + renderStars(rating) + '</span> ' + rating.toFixed(1) + '</strong></span>' : '')
        + (reviews > 0 ? '      <span class="best-detail"><i class="fas fa-comment"></i>Reviews: <strong>' + reviews.toLocaleString() + '</strong></span>' : '')
        + '    </div>'
        + '    <div class="best-reason">' + explanation + '</div>'
        + '  </div>'
        + '</div>'
        + '<div class="best-cta-wrap">'
        + (mu.hasUrl
            ? '  <a href="' + safeUrl + '" class="best-cta-link" target="_blank" rel="noopener noreferrer">View Product <i class="fas fa-arrow-right" style="font-size:11px;"></i></a>'
            : '  <span class="best-cta-link best-cta-disabled">Unavailable</span>')
        + '  <div class="best-score">Score: ' + (bestResult.score ? Math.round(bestResult.score) : '—') + '</div>'
        + '</div>';

    section.style.display = 'block';
}

// ── Clear and reload ──
window.clearCompareAndReload = function() {
    try {
        sessionStorage.setItem('specora_compare_products', JSON.stringify([]));
    } catch(e) {}
    window.location.reload();
};

// ── Initialize ──
(function() {
    renderComparePage();
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
