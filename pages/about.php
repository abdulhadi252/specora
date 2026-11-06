<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/check_tables.php';
check_specora_tables();

if(!isset($indexTotalProducts)){ $r=$connect->query("SELECT COUNT(*) as c FROM products"); $indexTotalProducts=$r?(int)$r->fetch_assoc()['c']:0; }
require_once __DIR__ . '/../includes/functions.php';

// ── Load About Page Settings from DB ──
$aboutSettings = [];
$aboutDefaults = [];

// Load settings
$aboutRes = $connect->query("SELECT section_key, content FROM about_page_settings");
if ($aboutRes) {
    while ($row = $aboutRes->fetch_assoc()) {
        $decoded = json_decode($row['content'], true);
        $aboutSettings[$row['section_key']] = is_array($decoded) ? $decoded : [];
    }
}

// Helper function: get about setting with fallback
function aboutSetting(array &$settings, string $section, string $key, $default = '') {
    return $settings[$section][$key] ?? $default;
}

// ── Hero section ──
$heroBadge    = aboutSetting($aboutSettings, 'hero', 'badge_text', 'Trusted Product Discovery Platform');
$heroH1       = aboutSetting($aboutSettings, 'hero', 'heading_line1', 'Discover Smarter Products');
$heroH2       = aboutSetting($aboutSettings, 'hero', 'heading_line2', 'Compare with <span class="hl-em">Confidence</span>');
$heroH3       = aboutSetting($aboutSettings, 'hero', 'heading_line3', 'Shop Better Every Time');
$heroDesc     = aboutSetting($aboutSettings, 'hero', 'description', 'Specora is Pakistan\'s intelligent product discovery platform.');
$heroTrust    = aboutSetting($aboutSettings, 'hero', 'trust_items', [
    ['icon' => '🛒', 'text' => 'Multi Marketplace'],
    ['icon' => '⚖️', 'text' => 'Smart Comparison'],
    ['icon' => '🆓', 'text' => 'Free to Use'],
]);
// ── Who We Are section ──
$wwaBadge     = aboutSetting($aboutSettings, 'whoweare', 'badge_text', 'Who We Are');
$wwaH1        = aboutSetting($aboutSettings, 'whoweare', 'heading_line1', 'More Than a Product <span class="hl-em">Listing</span>');
$wwaH2        = aboutSetting($aboutSettings, 'whoweare', 'heading_line2', 'A Smart Discovery &');
$wwaH3        = aboutSetting($aboutSettings, 'whoweare', 'heading_line3', '<span class="hl-em">Comparison</span> Platform');
$wwaParas     = aboutSetting($aboutSettings, 'whoweare', 'paragraphs', ['', '', '']);
$wwaImgUrl    = aboutSetting($aboutSettings, 'whoweare', 'image_url', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRW-fAFLABiTA34k_WQNFBjQqjePkXwqPzciCtiYoL-hA&s=10');

// ── Features (Bento) section ──
$featBadge    = aboutSetting($aboutSettings, 'features', 'badge_text', 'What Makes Specora Different');
$featCards    = aboutSetting($aboutSettings, 'features', 'cards', []);

// ── FAQ section ──
$faqBadge     = aboutSetting($aboutSettings, 'faq', 'badge_text', 'Frequently Asked Questions');
$faqH1        = aboutSetting($aboutSettings, 'faq', 'heading_line1', 'Everything You Need to');
$faqH2        = aboutSetting($aboutSettings, 'faq', 'heading_line2', 'Know About <span class="hl-em">Specora</span>');
$faqDesc      = aboutSetting($aboutSettings, 'faq', 'description', 'Quick answers to common questions.');
$faqItems     = aboutSetting($aboutSettings, 'faq', 'items', []);

// ── Support card ──
$supIcon      = aboutSetting($aboutSettings, 'support', 'icon', '💬');
$supTitle     = aboutSetting($aboutSettings, 'support', 'title', 'Still have questions?');
$supDesc      = aboutSetting($aboutSettings, 'support', 'description', 'Contact our team.');
$supBtnText   = aboutSetting($aboutSettings, 'support', 'button_text', 'Contact Us →');
$supBtnUrl    = aboutSetting($aboutSettings, 'support', 'button_url', 'contact.php');

// ── Footer ──
$footDesc     = aboutSetting($aboutSettings, 'footer', 'brand_description', 'Pakistan\'s smartest product discovery platform.');
$footYear     = aboutSetting($aboutSettings, 'footer', 'copyright_year', '2025');
$footCopy     = aboutSetting($aboutSettings, 'footer', 'copyright_text', 'All rights reserved.');
$footMade     = aboutSetting($aboutSettings, 'footer', 'made_with_text', 'Made with ♥ in Karachi, Pakistan');

// ── Ensure FAQ items have default fallback ──
if (empty($faqItems)) {
    $faqItems = [
        ['question' => 'What is Specora?', 'answer' => 'Specora is Pakistan\'s intelligent product discovery and comparison platform.'],
        ['question' => 'How does Specora work?', 'answer' => 'Specora fetches product data from the Amazon API and stores it in our database.'],
        ['question' => 'Does Specora sell products directly?', 'answer' => 'No, Specora does not sell products directly. We are a product discovery platform.'],
        ['question' => 'Is Specora free to use?', 'answer' => 'Absolutely! Specora is 100% free to use.']
    ];
}

// ── Fetch Real Database Counts for Hero Stats ──
$realTotalProducts = 0;
$realTotalSources = 0;
$realTotalCategories = 0;
$rCnt = $connect->query("SELECT COUNT(*) as c FROM products");
if ($rCnt) { $realTotalProducts = (int)$rCnt->fetch_assoc()['c']; }

$rCnt = $connect->query("SELECT COUNT(DISTINCT source) as c FROM products WHERE source IS NOT NULL AND source != ''");
if ($rCnt) { $realTotalSources = (int)$rCnt->fetch_assoc()['c']; }

$rCnt = $connect->query("SELECT COUNT(DISTINCT category_id) as c FROM products WHERE category_id IS NOT NULL AND category_id > 0");
if ($rCnt) { $realTotalCategories = (int)$rCnt->fetch_assoc()['c']; }

// Format product count to nearest thousand (e.g. 20906 → 20K+)
$statProductsNum = floor($realTotalProducts / 1000);
$statProductsSuffix = 'K+';
// Use exact count for small numbers
if ($realTotalProducts < 1000) {
    $statProductsNum = $realTotalProducts;
    $statProductsSuffix = '+';
}

// Override hero stats defaults with real DB counts
$heroStats = aboutSetting($aboutSettings, 'hero', 'stats', [
    ['number' => (string)$statProductsNum, 'suffix' => $statProductsSuffix, 'label' => 'Products'],
    ['number' => (string)$realTotalCategories, 'suffix' => '+', 'label' => 'Categories'],
    ['number' => (string)$realTotalSources, 'suffix' => '', 'label' => 'Marketplaces'],
]);

// Fetch 6 products with real images from DB for the dashboard
$dashProducts = [];
// Connection ($connect) is already established via config.php -> connect.php
$r = mysqli_query($connect, "SELECT photo, title, price_usd, brand FROM products WHERE photo IS NOT NULL AND photo != '' AND price_usd > 0 ORDER BY RAND() LIMIT 6");
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) {
        $dashProducts[] = $row;
    }
}
// Fallback if no products found
if (empty($dashProducts)) {
    $dashProducts = [
        ['photo' => '', 'title' => 'Gaming Laptop',   'price_usd' => 665, 'brand' => 'ASUS'],
        ['photo' => '', 'title' => 'Wireless Headphones', 'price_usd' => 45,  'brand' => 'Sony'],
        ['photo' => '', 'title' => 'Smartphone Pro',  'price_usd' => 324,  'brand' => 'Samsung'],
        ['photo' => '', 'title' => 'Gaming Console',  'price_usd' => 521, 'brand' => 'PlayStation'],
        ['photo' => '', 'title' => 'Smart Watch Ultra','price_usd' => 162,  'brand' => 'Apple'],
        ['photo' => '', 'title' => 'DSLR Camera',     'price_usd' => 773, 'brand' => 'Canon'],
    ];
}

?>
<title>About Us — Specora</title>
<meta name="description" content="Learn about Specora — Pakistan's smartest product discovery platform. Our mission is to help you find the best products at the best prices across multiple marketplaces.">
<meta name="keywords" content="about Specora, product discovery, price comparison, Pakistan, shopping platform">
<meta property="og:title" content="About Us — Specora">
<meta property="og:description" content="Learn about Specora — Pakistan's smartest product discovery platform. Search any product, compare prices across Amazon, AliExpress, eBay &amp; Walmart.">
<meta property="og:image" content="img/specora-logo.png">
<meta property="og:url" content="https://specora.pk/pages/about.php">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="About Us — Specora">
<meta name="twitter:description" content="Learn about Specora — Pakistan's smartest product discovery platform. Search any product, compare prices across Amazon, AliExpress, eBay &amp; Walmart.">
<meta name="twitter:image" content="img/specora-logo.png">
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/navbar.css?v=<?= filemtime(__DIR__.'/../css/navbar.css') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ══ RESET & VARIABLES ══ */
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--white);color:var(--black);overflow-x:hidden;}
a{text-decoration:none;color:inherit;}

/* prefers-reduced-motion */
@media(prefers-reduced-motion:reduce){
  *,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;}
  .hero-el,.fade-el,.reveal-disc,.reveal-wwa,.reveal-disc.visible,.reveal-wwa.visible{opacity:1!important;transform:none!important;}
  .float-card,.wwa-img,.disc-float{animation:none!important;}
}
/* ══ HERO SECTION ══ */
.hero{position:relative;min-height:100vh;display:flex;align-items:center;padding:48px 48px 80px;margin-top:68px;overflow:hidden;background:var(--white);}
.hero::before{content:'';position:absolute;top:-200px;right:-160px;width:600px;height:600px;background:radial-gradient(circle,rgba(234,189,120,.08) 0%,transparent 65%);pointer-events:none;}
.hero::after{content:'';position:absolute;bottom:-240px;left:-120px;width:500px;height:500px;background:radial-gradient(circle,rgba(234,189,120,.05) 0%,transparent 65%);pointer-events:none;}

/* Decorative blobs */
.hero-blob-1{position:absolute;top:15%;right:5%;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(234,189,120,.06) 0%,transparent 70%);pointer-events:none;}
.hero-blob-2{position:absolute;bottom:20%;left:40%;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(234,189,120,.04) 0%,transparent 70%);pointer-events:none;}
.hero-grid-dots{position:absolute;top:0;left:0;right:0;bottom:0;background-image:radial-gradient(var(--gray-200) .8px,transparent .8px);background-size:32px 32px;opacity:.25;pointer-events:none;}

/* ══ HERO INNER ══ */
.hero-inner{max-width:1280px;width:100%;margin:0 auto;display:grid;grid-template-columns:42% 1fr;gap:64px;align-items:center;position:relative;z-index:2;}

/* ── LEFT COLUMN ── */
.hero-left{display:flex;flex-direction:column;gap:0;}

/* Badge */
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:7px 16px 7px 12px;width:fit-content;margin-bottom:24px;opacity:0;transform:translateY(16px);animation:heroFadeUp .55s ease forwards;}
.hero-badge-icon{width:20px;height:20px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;}
.hero-badge-text{font-size:11.5px;font-weight:600;color:var(--gold-dark);letter-spacing:.3px;}

/* Heading */
.hero-heading{font-family:var(--font-display);font-weight:800;letter-spacing:-1.5px;color:var(--black);margin-bottom:24px;}
.hero-heading .hl{display:block;line-height:1.15;opacity:0;transform:translateY(20px);}
.hero-heading .hl:nth-child(1){animation:heroFadeUp .5s ease .2s forwards;}
.hero-heading .hl:nth-child(2){animation:heroFadeUp .5s ease .35s forwards;}
.hero-heading .hl:nth-child(3){animation:heroFadeUp .5s ease .5s forwards;}
.hero-heading .hl-em{color:var(--gold-dark);}

/* Description */
.hero-desc{font-size:16px;color:var(--gray-600);line-height:1.7;max-width:440px;margin-bottom:32px;opacity:0;transform:translateY(16px);animation:heroFadeUp .5s ease .65s forwards;}

/* Buttons */
.hero-btns{display:flex;gap:12px;margin-bottom:32px;opacity:0;transform:translateY(16px);animation:heroFadeUp .5s ease .8s forwards;}
.btn-primary{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:14px 26px;cursor:pointer;transition:all .25s;position:relative;overflow:hidden;}
.btn-primary:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-2px);box-shadow:0 8px 24px rgba(234,189,120,.35);}
.btn-primary:active{transform:scale(.97);}
.btn-secondary{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);background:transparent;border:1.5px solid var(--gray-200);border-radius:var(--radius-pill);padding:14px 26px;cursor:pointer;transition:all .25s;position:relative;overflow:hidden;}
.btn-secondary:hover{border-color:var(--black);transform:translateY(-2px);box-shadow:0 4px 16px rgba(0,0,0,.06);}
.btn-secondary:active{transform:scale(.97);}
.ripple{position:absolute;border-radius:50%;background:rgba(255,255,255,.4);transform:scale(0);animation:rippleAnim .6s ease-out;pointer-events:none;}

/* Trust indicators */
.hero-trust{display:flex;gap:16px;margin-bottom:40px;opacity:0;transform:translateY(16px);}
.hero-trust .ti{animation:heroFadeUp .5s ease forwards;opacity:0;transform:translateY(12px);}
.hero-trust .ti:nth-child(1){animation-delay:.95s;}
.hero-trust .ti:nth-child(2){animation-delay:1.05s;}
.hero-trust .ti:nth-child(3){animation-delay:1.15s;}
.ti{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:var(--gray-600);}
.ti-icon{width:28px;height:28px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}

/* Statistics */
.hero-stats{display:flex;gap:32px;opacity:0;transform:translateY(16px);animation:heroFadeUp .5s ease 1.3s forwards;}
.stat{text-align:left;}
.stat-num{font-family:var(--font-display);font-size:clamp(28px,3vw,38px);font-weight:800;color:var(--black);letter-spacing:-1px;line-height:1;}
.stat-label{font-size:12px;color:var(--gray-400);font-weight:500;margin-top:4px;letter-spacing:.3px;}
.stat-divider{width:1px;background:var(--gray-200);align-self:stretch;}

@keyframes heroFadeUp{to{opacity:1;transform:translateY(0);}}
@keyframes rippleAnim{to{transform:scale(4);opacity:0;}}

/* ══ RIGHT COLUMN — DASHBOARD ══ */
.hero-right{position:relative;min-height:520px;opacity:0;transform:translateY(24px);animation:heroFadeUp .6s ease .5s forwards;}

/* Browser mockup */
.dashboard{position:relative;width:100%;max-width:640px;margin-left:auto;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);box-shadow:0 24px 64px rgba(0,0,0,.08),0 2px 8px rgba(0,0,0,.04);overflow:hidden;transform:perspective(1200px) rotateY(-1deg);transition:transform .15s ease-out;}
.dash-header{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--gray-200);background:var(--off-white);}
.dash-dots{display:flex;gap:6px;}
.dash-dot{width:10px;height:10px;border-radius:50%;}
.dash-dot.r{background:#FF5F57;}
.dash-dot.y{background:#FFBD2E;}
.dash-dot.g{background:#28C840;}
.dash-url{flex:1;background:var(--white);border:1px solid var(--gray-200);border-radius:6px;padding:6px 12px;font-size:11px;color:var(--gray-400);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;}
.dash-url span{color:var(--gray-600);}

/* Dashboard body */
.dash-body{padding:20px;}
.dash-toolbar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.dash-search{flex:1;min-width:120px;display:flex;align-items:center;gap:8px;background:var(--gray-100);border:1px solid var(--gray-200);border-radius:var(--radius-pill);padding:8px 14px;}
.dash-search input{border:none;background:transparent;outline:none;font-family:var(--font-body);font-size:12px;color:var(--black);width:100%;}
.dash-search input::placeholder{color:var(--gray-400);}
.dash-filter-bar{display:flex;gap:6px;flex-wrap:wrap;}
.dash-filter{font-size:10px;font-weight:600;padding:5px 12px;border-radius:var(--radius-pill);background:var(--gray-100);color:var(--gray-600);cursor:pointer;transition:all .15s;white-space:nowrap;border:1px solid transparent;}
.dash-filter.active{background:var(--gold);color:var(--black);border-color:var(--gold);}

/* Product grid inside dashboard */
.dash-products{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;}
.dash-product{background:var(--gray-100);border-radius:var(--radius-sm);overflow:hidden;cursor:pointer;transition:transform .2s,box-shadow .2s;border:1px solid var(--gray-200);}
.dash-product:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.06);}
.dash-prod-img{width:100%;aspect-ratio:1;background:var(--gray-200);display:flex;align-items:center;justify-content:center;font-size:28px;position:relative;}
.dash-prod-img .wishlist{position:absolute;top:6px;right:6px;width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.85);display:flex;align-items:center;justify-content:center;font-size:11px;cursor:pointer;transition:transform .2s;}
.dash-prod-img .wishlist:hover{transform:scale(1.15);}
.dash-prod-info{padding:8px;}
.dash-prod-name{font-size:10px;font-weight:600;color:var(--black);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:2px;}
.dash-prod-price{font-size:10px;font-weight:700;color:var(--gold-dark);}

/* Comparison row */
.dash-compare{display:flex;align-items:center;gap:8px;background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:10px 14px;}
.dash-compare-icon{width:28px;height:28px;border-radius:6px;background:var(--gold-light);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.dash-compare-text{font-size:12px;font-weight:600;color:var(--gray-600);flex:1;}
.dash-compare-text span{color:var(--black);}
.dash-compare-btn{font-size:10px;font-weight:700;color:var(--gold-dark);background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:5px 14px;cursor:pointer;transition:all .15s;}
.dash-compare-btn:hover{background:var(--gold);color:var(--black);}

/* ══ FLOATING CARDS ══ */
.float-card{position:absolute;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:12px 16px;box-shadow:0 8px 24px rgba(0,0,0,.06);display:flex;align-items:center;gap:10px;animation:floatAnim 5s ease-in-out infinite;pointer-events:none;}
.float-card-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.float-card-label{font-size:11px;font-weight:600;color:var(--gray-600);}
.float-card-value{font-size:13px;font-weight:700;color:var(--black);font-family:var(--font-display);}

.fc-1{top:-8px;right:10%;animation-delay:0s;}
.fc-1 .float-card-icon{background:rgba(16,185,129,.1);}
.fc-2{top:24%;right:-18px;animation-delay:.8s;}
.fc-2 .float-card-icon{background:rgba(59,130,246,.1);}
.fc-3{bottom:28%;left:-20px;animation-delay:1.6s;}
.fc-3 .float-card-icon{background:rgba(239,68,68,.1);}
.fc-4{bottom:-10px;right:20%;animation-delay:2.4s;}
.fc-4 .float-card-icon{background:rgba(234,189,120,.15);}
.fc-5{top:50%;left:-28px;animation-delay:3.2s;padding:10px 14px;}
.fc-5 .float-card-icon{background:rgba(139,92,246,.1);font-size:14px;width:26px;height:26px;}
.fc-5 .float-card-label{font-size:10px;}

.fc-stat{position:absolute;bottom:40px;right:-14px;animation-delay:.4s;background:var(--black);border-color:var(--black);padding:10px 16px;}
.fc-stat .float-card-label{color:rgba(255,255,255,.5);}
.fc-stat .float-card-value{color:var(--gold);}

@keyframes floatAnim{
  0%,100%{transform:translateY(0px);}
  50%{transform:translateY(-8px);}
}

/* ══ WHO WE ARE SECTION ══ */
.whoweare{position:relative;padding:100px 48px;background:var(--off-white);overflow:hidden;}
.whoweare::before{content:'';position:absolute;top:-120px;left:-120px;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(234,189,120,.05) 0%,transparent 65%);pointer-events:none;}
.whoweare::after{content:'';position:absolute;bottom:20%;right:5%;width:200px;height:200px;background:radial-gradient(circle,rgba(234,189,120,.04) 0%,transparent 65%);pointer-events:none;}
.wwa-grid{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center;position:relative;z-index:2;}

/* ── LEFT ── */
.wwa-left{display:flex;flex-direction:column;gap:0;}
.wwa-label{display:inline-flex;align-items:center;gap:7px;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:6px 14px 6px 10px;width:fit-content;margin-bottom:22px;}
.wwa-label-icon{width:18px;height:18px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;}
.wwa-label-text{font-size:11px;font-weight:600;color:var(--gold-dark);letter-spacing:.3px;}

.wwa-heading{font-family:var(--font-display);font-weight:700;letter-spacing:-1.2px;color:var(--black);margin-bottom:24px;}
.wwa-heading .hl{display:block;font-size:clamp(24px,3.2vw,38px);line-height:1.12;margin-bottom:2px;}
.wwa-heading .hl-em{color:var(--gold-dark);}

.wwa-text{font-size:15px;color:var(--gray-600);line-height:1.7;max-width:500px;margin-bottom:12px;}
.wwa-text:last-of-type{margin-bottom:0;}

.reveal-wwa{opacity:0;transform:translateY(16px);transition:opacity .5s ease,transform .5s ease;}
.reveal-wwa.visible{opacity:1;transform:translateY(0);}

/* ── RIGHT (Visual) ── */
.wwa-right{display:flex;flex-direction:column;gap:0}
/* .wwa-visual{position:relative;width:100%;display:flex;align-items:stretch;} */
.wwa-dots-bg{position:absolute;inset:0;background-image:radial-gradient(var(--gray-200) .7px,transparent .7px);background-size:24px 24px;opacity:.3;pointer-events:none;}

@keyframes wwaImgFloat{
  0%,100%{transform:translateY(0px) scale(1);}
  50%{transform:translateY(-10px) scale(1.02);}
}

/* Full-height image */
.wwa-img-wrap{position:relative;width:100%;display:flex;align-items:center;justify-content:center;}
.wwa-img-bg{position:absolute;inset:-60px;background:radial-gradient(circle at 30% 40%,rgba(234,189,120,.07) 0%,transparent 50%),radial-gradient(circle at 70% 60%,rgba(234,189,120,.04) 0%,transparent 50%);pointer-events:none;}
.wwa-img{border-radius:10px;position:relative;z-index:2;width:100%;height:auto;object-fit:contain;display:block;
transition:opacity .4s ease;will-change:opacity,transform;animation:wwaImgFloat 6s ease-in-out infinite;}

/* ══ WHAT MAKES SPECORA DIFFERENT ══ */
.diff{position:relative;padding:100px 48px;background:var(--white);overflow:hidden;}
.diff::before{content:'';position:absolute;top:0;right:0;width:500px;height:500px;background:radial-gradient(circle,rgba(234,189,120,.06) 0%,transparent 65%);pointer-events:none;}
.diff::after{content:'';position:absolute;bottom:-100px;left:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(234,189,120,.04) 0%,transparent 65%);pointer-events:none;}
.diff-dots{position:absolute;top:0;left:0;right:0;bottom:0;background-image:radial-gradient(var(--gray-200) .7px,transparent .7px);background-size:28px 28px;opacity:.2;pointer-events:none;}
.diff-inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr;gap:0;position:relative;z-index:2;}

/* ── LEFT ── */
.diff-left{display:flex;flex-direction:column;gap:0;}
.diff-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:6px 14px 6px 10px;width:fit-content;margin-bottom:24px;}
.diff-badge-icon{width:18px;height:18px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;}
.diff-badge-text{font-size:11px;font-weight:600;color:var(--gold-dark);letter-spacing:.3px;}


/* ── RIGHT — BENTO GRID ── */
.diff-right{position:relative;}
.diff-grid{display:grid;grid-template-columns:1fr 1fr 1fr;grid-template-rows:auto auto auto;gap:24px;}

/* Grid areas */
.disc-large{grid-column:1/3;grid-row:1;}
.disc-compare{grid-column:3/4;grid-row:1;}
.disc-market{grid-column:1/2;grid-row:2;}
.disc-trusted{grid-column:2/3;grid-row:2;}
.disc-personal{grid-column:3/4;grid-row:2/4;}
.disc-bottom{grid-column:1/4;grid-row:3;}
.disc-extra{grid-column:auto;grid-row:auto;}

/* Card base */
.disc-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:24px;box-shadow:0 4px 16px rgba(0,0,0,.04);transition:all .35s cubic-bezier(.25,.46,.45,.94);position:relative;overflow:hidden;}
.disc-card:hover{transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,.08);border-color:var(--gold-light);}
.disc-card:active{transform:scale(.98);}

/* Card icon */
.disc-card-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:14px;background:rgba(234,189,120,.1);transition:transform .3s;}
.disc-card:hover .disc-card-icon{transform:scale(1.1) rotate(-3deg);}

/* Card titles */
.disc-card-title{font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--black);margin-bottom:6px;}
.disc-card-desc{font-size:13px;color:var(--gray-600);line-height:1.6;}

/* Large card specific */
.disc-large{padding:28px;}
.disc-large .disc-card-icon{width:48px;height:48px;border-radius:12px;font-size:24px;margin-bottom:16px;background:rgba(234,189,120,.15);}
.disc-large .disc-card-title{font-size:17px;margin-bottom:8px;}
.disc-large .disc-card-desc{font-size:14px;}

/* Large card mini dashboard preview */
.disc-preview{margin-top:18px;background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:14px;display:flex;gap:10px;flex-wrap:wrap;}
.disc-preview-item{display:flex;align-items:center;gap:6px;background:var(--white);border:1px solid var(--gray-200);border-radius:6px;padding:5px 10px;font-size:10px;font-weight:600;color:var(--gray-600);}
.disc-preview-item span{font-size:12px;}

/* Comparison card */
.disc-compare{text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.disc-compare .disc-card-icon{width:44px;height:44px;border-radius:50%;font-size:22px;background:rgba(59,130,246,.1);margin:0 auto 12px;}
.disc-compare-preview{display:flex;gap:6px;margin-top:12px;}
.disc-compare-bar{height:32px;width:14px;border-radius:4px;background:var(--gray-200);position:relative;}
.disc-compare-bar.fill{background:var(--gold);}
.disc-compare-bar.fill2{background:var(--gold-dark);height:24px;margin-top:8px;}

/* Multi-marketplace card */
.disc-market{} 
.disc-market-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;}
.disc-chip{font-size:9px;font-weight:700;padding:4px 10px;border-radius:var(--radius-pill);background:rgba(234,189,120,.1);color:var(--gold-dark);border:1px solid rgba(234,189,120,.2);letter-spacing:.2px;}

/* Trusted card */
.disc-trusted{}
.disc-trusted .disc-card-icon{background:rgba(16,185,129,.1);}
.disc-trusted-badge{display:inline-flex;align-items:center;gap:5px;font-size:10px;font-weight:700;color:var(--gold-dark);background:rgba(234,189,120,.08);border:1px solid rgba(234,189,120,.15);border-radius:var(--radius-pill);padding:3px 10px;margin-top:10px;}

/* Personal recommendations — large vertical */
.disc-personal{padding:28px;display:flex;flex-direction:column;}
.disc-personal .disc-card-icon{width:44px;height:44px;border-radius:12px;font-size:22px;background:rgba(139,92,246,.1);margin-bottom:16px;}
.disc-personal .disc-card-title{font-size:16px;}
.disc-personal .disc-card-desc{font-size:13px;flex:1;}
.disc-personal-widgets{display:flex;flex-direction:column;gap:8px;margin-top:14px;}
.disc-personal-widget{display:flex;align-items:center;gap:8px;background:var(--off-white);border:1px solid var(--gray-200);border-radius:8px;padding:8px 12px;font-size:11px;color:var(--gray-600);transition:transform .2s;}
.disc-personal-widget:hover{transform:translateX(4px);}
.disc-personal-widget .w-icon{width:22px;height:22px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;}

/* Bottom full-width card */
.disc-bottom{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:20px 28px;background:var(--black);border-color:var(--black);}
.disc-bottom:hover{border-color:var(--black);}
.disc-bottom-left{display:flex;align-items:center;gap:16px;flex:1;}
.disc-bottom-icon{width:48px;height:48px;border-radius:12px;background:rgba(234,189,120,.12);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
.disc-bottom-text{flex:1;}
.disc-bottom-title{font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--white);margin-bottom:2px;}
.disc-bottom-desc{font-size:12px;color:rgba(255,255,255,.4);line-height:1.5;}
.disc-bottom-cta{font-size:12px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:9px 20px;cursor:pointer;transition:all .2s;white-space:nowrap;}
.disc-bottom-cta:hover{background:var(--gold-dark);color:var(--white);}

/* Decorative elements */
.disc-float{position:absolute;pointer-events:none;z-index:1;}
.disc-float-1{top:5%;right:-20px;width:60px;height:60px;border-radius:50%;background:radial-gradient(circle,rgba(234,189,120,.1) 0%,transparent 70%);animation:discFloat 7s ease-in-out infinite;}
.disc-float-2{bottom:10%;left:-30px;width:80px;height:80px;border-radius:50%;background:radial-gradient(circle,rgba(234,189,120,.07) 0%,transparent 70%);animation:discFloat 9s ease-in-out infinite reverse;}
.disc-float-3{top:40%;right:5%;width:40px;height:40px;border:2px solid rgba(234,189,120,.1);border-radius:50%;animation:discFloat 6s ease-in-out infinite 1s;}

@keyframes discFloat{
  0%,100%{transform:translateY(0px) scale(1);}
  50%{transform:translateY(-12px) scale(1.05);}
}

/* Scroll reveal */
.reveal-disc{opacity:0;transform:translateY(20px);transition:opacity .6s ease,transform .6s ease;}
.reveal-disc.visible{opacity:1;transform:translateY(0);}

/* Card focus for keyboard accessibility */
.disc-card:focus-visible{outline:2px solid var(--gold);outline-offset:2px;}
    <style>
/* ── DIFF RESPONSIVE ── */
@media(max-width:1200px){
  .diff-inner{gap:0;}
  .disc-preview{gap:6px;}
  .disc-preview-item{font-size:9px;padding:4px 8px;}
}

/* ── WWA responsive ── */
@media(max-width:1200px){
  .wwa-grid{gap:48px;}
}

/* ══ RESPONSIVE ══ */
@media(max-width:1200px){
  nav{padding:0 28px;}.nav-links{gap:18px;}
  .hero-inner{gap:40px;grid-template-columns:44% 1fr;}
  .hero{padding:42px 28px 64px;}
}

@media(max-width:1024px){
  .diff{padding:72px 24px;}
  .diff-inner{grid-template-columns:1fr;gap:48px;}
  .diff-grid{grid-template-columns:1fr 1fr;grid-template-rows:auto;gap:12px;}
  .disc-large{grid-column:1/3;grid-row:auto;}
  .disc-compare{grid-column:1/2;grid-row:auto;}
  .disc-market{grid-column:2/3;grid-row:auto;}
  .disc-trusted{grid-column:1/2;grid-row:auto;}
  .disc-personal{grid-column:2/3;grid-row:auto;}
  .disc-bottom{grid-column:1/3;grid-row:auto;}
  .disc-bottom-cta{display:none;}
  .whoweare{padding:72px 24px;}
  .wwa-grid{grid-template-columns:1fr;gap:48px;}
  .wwa-right{min-height:360px;}
  .wwa-img-wrap{min-height:360px;}
  .wwa-img{width:80%;}
  nav{padding:0 24px;}.nav-links,.nav-actions{display:none;}.hamburger{display:flex;}
  .mobile-menu{top:68px;}
  .hero-inner{grid-template-columns:1fr;gap:48px;}
  .hero{padding:40px 24px 64px;min-height:auto;}
  .hero-right{min-height:auto;}.dashboard{max-width:100%;margin-left:0;transform:none;}
  .float-card{position:absolute;}
  .fc-1{top:-10px;right:8%;}
  .fc-2{top:22%;right:-12px;}
  .fc-3{bottom:30%;left:-12px;}
  .fc-4{bottom:-10px;right:16%;}
  .fc-5{top:48%;left:-18px;}
  .fc-stat{bottom:50px;right:-10px;}
  footer{padding:52px 24px 28px;}
}

@media(max-width:768px){
  .diff{padding:56px 20px;}
  .diff-grid{grid-template-columns:1fr;gap:12px;}
  .disc-large{grid-column:1/2;}
  .disc-compare{grid-column:1/2;}
  .disc-market{grid-column:1/2;}
  .disc-trusted{grid-column:1/2;}
  .disc-personal{grid-column:1/2;}
  .disc-bottom{grid-column:1/2;}
  .disc-bottom{flex-direction:column;text-align:center;padding:20px;}
  .disc-bottom-left{flex-direction:column;text-align:center;}
  .whoweare{padding:56px 20px;}
  .wwa-grid{gap:36px;}
  .wwa-heading .hl{font-size:clamp(22px,6vw,30px);}
  .wwa-right{min-height:300px;}
  .wwa-img-wrap{min-height:300px;}
  .wwa-img{width:70%;}
  nav{padding:0 20px;height:62px;}.mobile-menu{top:62px;}
  .hero{padding:36px 20px 48px;margin-top:62px;}
  .hero-heading .hl{font-size:clamp(28px,8vw,38px);}
  .hero-desc{font-size:15px;}
  .hero-btns{flex-direction:column;}
  .btn-primary,.btn-secondary{justify-content:center;}
  .hero-trust{flex-direction:column;gap:12px;}
  .hero-stats{gap:20px;flex-wrap:wrap;}
  .stat{flex:1;min-width:80px;}
  .stat-divider{display:none;}
  .float-card{display:none;}
  .fc-1,.fc-4{display:flex;}
  .dash-products{grid-template-columns:repeat(3,1fr);}
  .footer-top{flex-direction:column;gap:28px;}
  footer{padding:44px 20px 24px;}
  .footer-bottom{flex-direction:column;text-align:center;}
}

@media(max-width:480px){
  .diff{padding:44px 16px;}
  .disc-card{padding:18px;}
  .disc-large{padding:20px;}
  .disc-personal{padding:20px;}
  .disc-preview{display:none;}
  .disc-compare-preview{display:none;}
  .disc-personal-widgets{gap:6px;}
  .whoweare{padding:44px 16px;}
  .wwa-right{min-height:220px;}
  .wwa-img-wrap{min-height:220px;}
  .wwa-img{width:65%;}
  nav{height:58px;padding:0 16px;}.mobile-menu{top:58px;}
  .nav-logo-img{height:36px;}
  .hero{padding:32px 16px 40px;margin-top:58px;}
  .hero-heading .hl{font-size:clamp(24px,7vw,32px);}
  .dash-products{grid-template-columns:repeat(2,1fr);}
  .fc-1,.fc-4{display:none;}
  footer{padding:36px 16px 20px;}
}

/* ══ FAQ SECTION ══ */
.faq{position:relative;padding:100px 48px;background:var(--off-white);overflow:hidden;}
.faq::before{content:'';position:absolute;top:-160px;right:-80px;width:500px;height:500px;background:radial-gradient(circle,rgba(234,189,120,.06) 0%,transparent 65%);pointer-events:none;}
.faq::after{content:'';position:absolute;bottom:-120px;left:-40px;width:350px;height:350px;background:radial-gradient(circle,rgba(234,189,120,.04) 0%,transparent 65%);pointer-events:none;}
.faq-dots{position:absolute;top:0;left:0;right:0;bottom:0;background-image:radial-gradient(var(--gray-200) .7px,transparent .7px);background-size:28px 28px;opacity:.2;pointer-events:none;}
.faq-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:35% 1fr;gap:80px;align-items:center;position:relative;z-index:2;}

/* ── LEFT ── */
.faq-left{display:flex;flex-direction:column;gap:0;}
.faq-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:6px 14px 6px 10px;width:fit-content;margin-bottom:24px;}
.faq-badge-icon{width:18px;height:18px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;}
.faq-badge-text{font-size:11px;font-weight:600;color:var(--gold-dark);letter-spacing:.3px;}

.faq-heading{font-family:var(--font-display);font-weight:800;letter-spacing:-1.5px;color:var(--black);margin-bottom:24px;}
.faq-heading .hl{display:block;font-size:clamp(24px,3vw,38px);line-height:1.12;margin-bottom:2px;}
.faq-heading .hl-em{color:var(--gold-dark);}

.faq-desc{font-size:15px;color:var(--gray-600);line-height:1.7;max-width:380px;margin-bottom:32px;}

/* Support card */
.faq-support{background:var(--black);border-radius:var(--radius);padding:24px;display:flex;flex-direction:column;gap:14px;position:relative;overflow:hidden;}
.faq-support::before{content:'';position:absolute;top:-40px;right:-30px;width:100px;height:100px;border-radius:50%;background:rgba(234,189,120,.06);pointer-events:none;}
.faq-support-icon{width:40px;height:40px;border-radius:10px;background:rgba(234,189,120,.12);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;position:relative;z-index:1;}
.faq-support h4{font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--white);position:relative;z-index:1;}
.faq-support p{font-size:13px;color:rgba(255,255,255,.45);line-height:1.6;position:relative;z-index:1;}
.faq-support-btn{display:inline-flex;align-items:center;gap:8px;width:fit-content;font-family:var(--font-display);font-size:12px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:9px 20px;cursor:pointer;transition:all .25s;position:relative;z-index:1;}
.faq-support-btn:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-2px);box-shadow:0 6px 20px rgba(234,189,120,.25);}
.faq-support-btn:active{transform:scale(.97);}

/* ── RIGHT — ACCORDION ── */
.faq-right{display:flex;flex-direction:column;gap:18px;}

.faq-item{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;transition:all .35s cubic-bezier(.25,.46,.45,.94);box-shadow:0 2px 8px rgba(0,0,0,.03);}
.faq-item:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.07);border-color:var(--gold-light);}
.faq-item:active{transform:scale(.99);}
.faq-item.open{border-color:var(--gold);box-shadow:0 4px 20px rgba(234,189,120,.12);}

.faq-trigger{width:100%;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 24px;background:none;border:none;cursor:pointer;text-align:left;font-family:var(--font-body);font-size:15px;font-weight:600;color:var(--black);transition:color .2s;position:relative;}
.faq-trigger:hover{color:var(--gold-dark);}
.faq-item.open .faq-trigger{color:var(--gold-dark);}

.faq-trigger-text{flex:1;line-height:1.4;}

.faq-icon-wrap{width:28px;height:28px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .3s cubic-bezier(.25,.46,.45,.94);position:relative;}
.faq-icon-wrap svg{width:14px;height:14px;color:var(--gray-400);transition:all .3s cubic-bezier(.25,.46,.45,.94);}
.faq-item.open .faq-icon-wrap{background:var(--gold);}
.faq-item.open .faq-icon-wrap svg{color:var(--black);transform:rotate(45deg);}
.faq-item:hover .faq-icon-wrap{background:var(--gold-light);}
.faq-item:hover .faq-icon-wrap svg{color:var(--gold-dark);}
.faq-item.open:hover .faq-icon-wrap{background:var(--gold);}

.faq-answer{max-height:0;overflow:hidden;transition:max-height .4s cubic-bezier(.25,.46,.45,.94),opacity .3s ease;opacity:0;}
.faq-answer-inner{padding:0 24px 20px;font-size:14px;color:var(--gray-600);line-height:1.75;}
.faq-item.open .faq-answer{opacity:1;}

/* Scroll reveal */
.reveal-faq{opacity:0;transform:translateY(20px);transition:opacity .5s ease,transform .5s ease;}
.reveal-faq.visible{opacity:1;transform:translateY(0);}

/* ── FAQ FOCUS ── */
.faq-trigger:focus-visible{outline:2px solid var(--gold);outline-offset:-2px;border-radius:var(--radius);}

/* ── FAQ RESPONSIVE ── */
@media(max-width:1200px){
  .faq-inner{gap:48px;}
}

@media(max-width:1024px){
  .faq{padding:72px 24px;}
  .faq-inner{gap:40px;}
  .faq-desc{max-width:100%;}
}

@media(max-width:768px){
  .faq{padding:56px 20px;}
  .faq-inner{grid-template-columns:1fr;gap:36px;}
  .faq-left{order:1;}
  .faq-right{order:2;}
  .faq-support{margin-bottom:0;}
  .faq-trigger{padding:16px 20px;font-size:14px;}
  .faq-answer-inner{padding:0 20px 16px;font-size:13px;}
}

@media(max-width:480px){
  .faq{padding:44px 16px;}
  .faq-item{border-radius:var(--radius-sm);}
  .faq-trigger{padding:14px 16px;font-size:13px;}
  .faq-answer-inner{padding:0 16px 14px;}
  .faq-support{padding:20px;}
}

@media(prefers-reduced-motion:reduce){
  .faq-item,.faq-icon-wrap,.faq-icon-wrap svg,.faq-answer,.reveal-faq{transition-duration:.01ms!important;animation-duration:.01ms!important;}
  .reveal-faq{opacity:1!important;transform:none!important;}
}

/* ══ MEGA DROPDOWN — Categories Grid ══ */
.nav-dropdown{position:relative;}.nav-dropdown>a{display:flex;align-items:center;gap:5px;}.nav-dropdown>a::after{display:none!important;}.dropdown-arrow{width:14px;height:14px;transition:transform .25s;opacity:.5;}.nav-dropdown:hover .dropdown-arrow{transform:rotate(180deg);opacity:1;}.nav-dropdown::after{content:"";position:absolute;top:100%;left:-20px;right:-20px;height:18px;background:transparent;}.dropdown-menu{position:absolute;top:calc(100% + 18px);left:50%;transform:translateX(-50%) translateY(6px);background:var(--white);border:1px solid var(--gray-200);border-radius:16px;padding:10px;min-width:380px;max-height:480px;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.13);opacity:0;visibility:hidden;pointer-events:none;transition:opacity .22s,transform .22s,visibility .22s;z-index:200;}.nav-dropdown:hover .dropdown-menu{opacity:1;visibility:visible;pointer-events:all;transform:translateX(-50%) translateY(0);}.dropdown-menu::-webkit-scrollbar{width:4px;}.dropdown-menu::-webkit-scrollbar-thumb{background:var(--gray-200);border-radius:4px;}.dropdown-header{display:flex;align-items:center;justify-content:space-between;padding:6px 6px 10px;border-bottom:1px solid var(--gray-100);margin-bottom:6px;}.dropdown-header-label{font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--gray-400);}.dropdown-header-link{font-size:11px;font-weight:600;color:var(--gold-dark);transition:color .15s;}.dropdown-header-link:hover{color:var(--black);}.dropdown-grid{display:grid;grid-template-columns:1fr 1fr;gap:2px;}.dropdown-group{margin-bottom:4px;break-inside:avoid;}.dropdown-group-label{font-size:9px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gray-400);padding:4px 8px 2px;}.dropdown-item{display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;cursor:pointer;transition:background .12s;}.di-icon{font-size:14px;width:20px;text-align:center;flex-shrink:0;}.di-label{font-size:12px;font-weight:500;color:var(--gray-600);}.dropdown-item:hover .di-label{color:var(--black);}.dropdown-footer{border-top:1px solid var(--gray-100);margin-top:6px;padding:8px 6px 2px;display:flex;justify-content:center;}.dropdown-cta{font-size:12px;font-weight:600;color:var(--gold-dark);padding:6px 14px;border-radius:var(--radius-pill);transition:background .15s;}.dropdown-cta:hover{background:rgba(234,189,120,.1);}.mob-header{font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--gray-400);padding:0 0 12px;margin-bottom:4px;border-bottom:1px solid var(--gray-100);letter-spacing:.5px;text-transform:uppercase;}.mob-link{display:flex!important;align-items:center;gap:10px;}.mob-link-icon{width:22px;text-align:center;font-size:15px;flex-shrink:0;}.mob-divider{height:1px;background:var(--gray-200);margin:8px 0;}.mob-cat-group{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gray-400);padding:10px 0 4px 16px;}
</style>
</head>
<body>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/navbar.php'; ?>



<!-- ══ HERO SECTION (NO scroll indicator — clean bottom spacing) ══ -->
<section class="hero">
  <div class="hero-grid-dots"></div>
  <div class="hero-blob-1"></div>
  <div class="hero-blob-2"></div>

  <div class="hero-inner">

    <!-- ── LEFT COLUMN ── -->
    <div class="hero-left">

      <!-- Badge -->
      <div class="hero-badge">
        <span class="hero-badge-icon">✓</span>
        <span class="hero-badge-text"><?= htmlspecialchars($heroBadge) ?></span>
      </div>

      <!-- Heading (3 lines staggered) -->
      <h1 class="hero-heading">
        <span class="hl"><?= $heroH1 ?></span>
        <span class="hl"><?= $heroH2 ?></span>
        <span class="hl"><?= $heroH3 ?></span>
      </h1>

      <!-- Description -->
      <p class="hero-desc"><?= htmlspecialchars($heroDesc) ?></p>

      <!-- CTA Buttons -->
      <div class="hero-btns">
        <a href="../index.php" class="btn-primary" id="btnPrimary">
          <span>🏪</span> Explore Products
          <span class="ripple" id="ripple1"></span>
        </a>
        <a href="#learn-more" class="btn-secondary" id="btnSecondary">
          Learn More <span>→</span>
          <span class="ripple" id="ripple2"></span>
        </a>
      </div>

      <!-- Trust Indicators -->
      <div class="hero-trust">
        <?php foreach ($heroTrust as $hti): ?>
          <div class="ti"><span class="ti-icon"><?= htmlspecialchars($hti['icon'] ?? '') ?></span> <?= htmlspecialchars($hti['text'] ?? '') ?></div>
        <?php endforeach; ?>
      </div>

      <!-- Statistics (animated counters) -->
      <div class="hero-stats">
        <?php $si = 0; foreach ($heroStats as $hs): $si++; ?>
          <div class="stat">
            <div class="stat-num"><span class="counter" data-target="<?= htmlspecialchars($hs['number'] ?? '0') ?>">0</span><?= htmlspecialchars($hs['suffix'] ?? '') ?></div>
            <div class="stat-label"><?= htmlspecialchars($hs['label'] ?? '') ?></div>
          </div>
          <?php if ($si < count($heroStats)): ?>
            <div class="stat-divider"></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- ── RIGHT COLUMN — DASHBOARD + FLOATING CARDS ── -->
    <div class="hero-right">

      <!-- Browser Dashboard Mockup -->
      <div class="dashboard" id="dashboard">

        <!-- Browser header -->
        <div class="dash-header">
          <div class="dash-dots">
            <span class="dash-dot r"></span>
            <span class="dash-dot y"></span>
            <span class="dash-dot g"></span>
          </div>
          <div class="dash-url"><span>specora.pk</span> / discover</div>
        </div>

        <!-- Dashboard body -->
        <div class="dash-body">

          <!-- Toolbar -->
          <div class="dash-toolbar">
            <div class="dash-search">
              <span style="font-size:12px;color:var(--gray-400)">🔍</span>
              <input type="text" placeholder="Search products..." value="wireless headphones" readonly />
            </div>
            <div class="dash-filter-bar">
              <span class="dash-filter active">All</span>
              <span class="dash-filter">Tech</span>
              <span class="dash-filter">Fashion</span>
            </div>
          </div>

          <!-- Product cards (from database) -->
          <div class="dash-products">
            <?php foreach ($dashProducts as $dp): 
              $dPhoto = htmlspecialchars($dp['photo'] ?: '');
              $dTitle = htmlspecialchars(mb_substr($dp['title'] ?? 'Product', 0, 22));
              $dPrice = !empty($dp['price_usd']) ? '$' . number_format((float)$dp['price_usd'], 2) : 'See Price';
              $dBrand = htmlspecialchars($dp['brand'] ?? 'Specora');
            ?>
            <div class="dash-product">
              <div class="dash-prod-img" style="background:var(--gray-200)">
                <?php if ($dPhoto): ?>
                  <img src="<?= $dPhoto ?>" alt="<?= $dTitle ?>" 
                       style="width:100%;height:100%;object-fit:cover;display:block;"
                       onerror="this.style.display='none';this.parentNode.style.background='var(--gray-200)';this.parentNode.innerHTML+='<span style=font-size:24px>🛍️</span>'" />
                <?php else: ?>
                  <span style="font-size:24px">🛍️</span>
                <?php endif; ?>
                <span class="wishlist">♡</span>
              </div>
              <div class="dash-prod-info">
                <div class="dash-prod-name"><?= $dTitle ?></div>
                <div class="dash-prod-price"><?= $dPrice ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Comparison row -->
          <div class="dash-compare">
            <div class="dash-compare-icon">⚖️</div>
            <div class="dash-compare-text">Comparing <span>3 products</span> — 2 matches found</div>
            <button class="dash-compare-btn">View →</button>
          </div>

        </div>
      </div>

      <!-- Floating Cards -->
      <div class="float-card fc-1">
        <div class="float-card-icon">🏷️</div>
        <div><div class="float-card-label">Price Drop</div><div class="float-card-value">-35%</div></div>
      </div>
      <div class="float-card fc-2">
        <div class="float-card-icon">⭐</div>
        <div><div class="float-card-label">Rating</div><div class="float-card-value">4.8 ★</div></div>
      </div>
      <div class="float-card fc-3">
        <div class="float-card-icon">🔥</div>
        <div><div class="float-card-label">Trending</div><div class="float-card-value">#1 This Week</div></div>
      </div>
      <div class="float-card fc-4">
        <div class="float-card-icon">🎯</div>
        <div><div class="float-card-label">Best Match</div><div class="float-card-value">98% Score</div></div>
      </div>
      <div class="float-card fc-5">
        <div class="float-card-icon">🛡️</div>
        <div><div class="float-card-label">Verified Seller</div></div>
      </div>
      <div class="float-card fc-stat">
        <div><div class="float-card-label">Products Today</div><div class="float-card-value">1,284</div></div>
      </div>

    </div>

  </div>

  <!-- NO SCROLL INDICATOR — section ends naturally with generous spacing -->
</section>

<!-- ══ WHO WE ARE SECTION ══ -->
<section class="whoweare" id="whoweare">
  <div class="wwa-grid">

    <!-- ── LEFT COLUMN ── -->
    <div class="wwa-left">

      <!-- Label -->
      <div class="wwa-label reveal-wwa" style="transition-delay:0s">
        <span class="wwa-label-icon">◈</span>
        <span class="wwa-label-text"><?= htmlspecialchars($wwaBadge) ?></span>
      </div>

      <!-- Heading -->
      <h2 class="wwa-heading">
        <span class="hl reveal-wwa" style="transition-delay:.15s"><?= $wwaH1 ?></span>
        <span class="hl reveal-wwa" style="transition-delay:.3s"><?= $wwaH2 ?></span>
        <span class="hl reveal-wwa" style="transition-delay:.45s"><?= $wwaH3 ?></span>
      </h2>

      <!-- Paragraphs -->
      <?php foreach ($wwaParas as $pi => $para): if (empty(trim($para ?? ''))) continue; ?>
        <p class="wwa-text reveal-wwa" style="transition-delay:<?= .55 + ($pi * .1) ?>s"><?= htmlspecialchars($para) ?></p>
      <?php endforeach; ?>

    </div>

    <!-- ── RIGHT COLUMN — Full-height Image with Scroll Fade ── -->
    <div class="wwa-right">
      <div class="wwa-visual" id="wwaVisual">
        <div class="wwa-dots-bg"></div>
        <div class="wwa-img-wrap" id="wwaImgWrap">
          <div class="wwa-img-bg"></div>
          <img
 src="<?= htmlspecialchars($wwaImgUrl) ?>"
  alt="Specora Platform" class="wwa-img" id="wwaFadeImg" />        </div>
      </div>
    </div>

  </div>
</section>


<!-- ══ WHAT MAKES SPECORA DIFFERENT ══ -->
<section class="diff" id="diff">
  <div class="diff-dots"></div>
  <div class="diff-float diff-float-1"></div>
  <div class="diff-float diff-float-2"></div>
  <div class="diff-float diff-float-3"></div>

  <div class="diff-inner">

    <!-- ── LEFT COLUMN (badge only) ── -->
    <div class="diff-left">

      <div class="diff-badge reveal-disc" style="transition-delay:0s">
        <span class="diff-badge-icon">✦</span>
        <span class="diff-badge-text"><?= htmlspecialchars($featBadge) ?></span>
      </div>

    </div>

    <!-- ── RIGHT COLUMN — BENTO GRID ── -->
    <div class="diff-right">

      <div class="diff-grid">
        <?php 
        $gridClasses = ['disc-large','disc-compare','disc-market','disc-trusted','disc-personal','disc-bottom'];
        foreach ($featCards as $fcIndex => $fc): 
          $fcDelay = .6 + ($fcIndex * .1);
          $posClass = $gridClasses[$fcIndex] ?? 'disc-extra';
          if ($posClass === 'disc-large') $posClass = 'disc-card disc-large';
          else $posClass = 'disc-card ' . $posClass;
        ?>
          <div class="<?= $posClass ?> reveal-disc" style="transition-delay:<?= $fcDelay ?>s">
            <div class="disc-card-icon" style="background:<?= htmlspecialchars($fc['icon_bg'] ?? 'rgba(234,189,120,.15)') ?>"><?= htmlspecialchars($fc['icon'] ?? '') ?></div>
            <div class="disc-card-title"><?= htmlspecialchars($fc['title'] ?? '') ?></div>
            <div class="disc-card-desc"><?= htmlspecialchars($fc['description'] ?? '') ?></div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>

  </div>
</section>

<!-- ══ FAQ SECTION ══ -->
<section class="faq" id="faq">
  <div class="faq-dots"></div>

  <div class="faq-inner">

    <!-- ── LEFT COLUMN ── -->
    <div class="faq-left">

      <!-- Badge -->
      <div class="faq-badge reveal-faq" style="transition-delay:0s">
        <span class="faq-badge-icon">?</span>
        <span class="faq-badge-text"><?= htmlspecialchars($faqBadge) ?></span>
      </div>

      <!-- Heading -->
      <h2 class="faq-heading">
        <span class="hl reveal-faq" style="transition-delay:.1s"><?= $faqH1 ?></span>
        <span class="hl reveal-faq" style="transition-delay:.2s"><?= $faqH2 ?></span>
      </h2>

      <!-- Description -->
      <p class="faq-desc reveal-faq" style="transition-delay:.3s"><?= htmlspecialchars($faqDesc) ?></p>

      <!-- Support Card -->
      <div class="faq-support reveal-faq" style="transition-delay:.4s">
        <div class="faq-support-icon"><?= htmlspecialchars($supIcon) ?></div>
        <h4><?= htmlspecialchars($supTitle) ?></h4>
        <p><?= htmlspecialchars($supDesc) ?></p>
        <a href="<?= htmlspecialchars($supBtnUrl) ?>" class="faq-support-btn"><?= htmlspecialchars($supBtnText) ?></a>
      </div>

    </div>

    <!-- ── RIGHT COLUMN — ACCORDION ── -->
    <div class="faq-right">

      <?php foreach ($faqItems as $fiIndex => $fi): 
        $fiDelay = .15 + ($fiIndex * .1);
        $fiId = 'faqBtn' . ($fiIndex + 1);
        $fiAns = 'faqAnswer' . ($fiIndex + 1);
      ?>
      <div class="faq-item reveal-faq" style="transition-delay:<?= $fiDelay ?>s">
        <button class="faq-trigger" aria-expanded="false" aria-controls="<?= $fiAns ?>" id="<?= $fiId ?>" onclick="toggleFaq(this)">
          <span class="faq-trigger-text"><?= htmlspecialchars($fi['question'] ?? '') ?></span>
          <span class="faq-icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <path d="M12 5v14M5 12h14"/>
            </svg>
          </span>
        </button>
        <div class="faq-answer" id="<?= $fiAns ?>" role="region" aria-labelledby="<?= $fiId ?>">
          <div class="faq-answer-inner"><?= htmlspecialchars($fi['answer'] ?? '') ?></div>
        </div>
      </div>
      <?php endforeach; ?>

    </div>

  </div>
</section>

<!-- FOOTER -->
    <!-- ══ FOOTER ══ -->
  <?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="../js/common.js"></script>
<script src="../js/about.js"></script>
</body>
</html>
