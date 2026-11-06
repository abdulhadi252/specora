<?php
/**
 * admin/manage_about.php — Specora About Page Manager
 * 
 * Fully customize every section of the About page:
 *   - Hero section (heading, description, stats, trust items)
 *   - Who We Are section (heading, paragraphs, image)
 *   - Features Bento Grid (6 cards with icon, title, description)
 *   - FAQ section (up to 8 Q&A items)
 *   - Support Card
 *   - Footer text
 * 
 * Protected page — requires admin login.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/check_tables.php';

require_admin_login();

// ── Auto-create tables (delegated to centralized checker) ──
check_specora_tables();

// ── Fetch Real Database Counts for Stats Defaults ──
$realTotalProducts = 0;
$realTotalSources = 0;
$realTotalCategories = 0;

$rCnt = $connect->query("SELECT COUNT(*) as c FROM products");
if ($rCnt) { $realTotalProducts = (int)$rCnt->fetch_assoc()['c']; }

$rCnt = $connect->query("SELECT COUNT(DISTINCT source) as c FROM products WHERE source IS NOT NULL AND source != ''");
if ($rCnt) { $realTotalSources = (int)$rCnt->fetch_assoc()['c']; }

$rCnt = $connect->query("SELECT COUNT(DISTINCT category_id) as c FROM products WHERE category_id IS NOT NULL AND category_id > 0");
if ($rCnt) { $realTotalCategories = (int)$rCnt->fetch_assoc()['c']; }

$statProductsNum = floor($realTotalProducts / 1000);
$statProductsSuffix = 'K+';
if ($realTotalProducts < 1000) {
    $statProductsNum = $realTotalProducts;
    $statProductsSuffix = '+';
}

// ── Default content for all sections ──
$defaults = [
    'hero' => json_encode([
        'badge_text' => 'Trusted Product Discovery Platform',
        'heading_line1' => 'Discover Smarter Products',
        'heading_line2' => 'Compare with <hl-em>Confidence</hl-em>',
        'heading_line3' => 'Shop Better Every Time',
        'description' => 'Specora is Pakistan\'s intelligent product discovery platform. Search across thousands of products from Amazon, compare prices & specs side-by-side, and make smarter buying decisions — all in one place. Free forever.',
        'trust_items' => [
            ['icon' => '🛒', 'text' => 'Multi Marketplace'],
            ['icon' => '⚖️', 'text' => 'Smart Comparison'],
            ['icon' => '🆓', 'text' => 'Free to Use'],
        ],
        'stats' => [
            ['number' => (string)$statProductsNum, 'suffix' => $statProductsSuffix, 'label' => 'Products'],
            ['number' => (string)$realTotalCategories, 'suffix' => '+', 'label' => 'Categories'],
            ['number' => (string)$realTotalSources, 'suffix' => '', 'label' => 'Marketplaces'],
        ],
    ]),
    'whoweare' => json_encode([
        'badge_text' => 'Who We Are',
        'heading_line1' => 'More Than a Product <hl-em>Listing</hl-em>',
        'heading_line2' => 'A Smart Discovery &',
        'heading_line3' => '<hl-em>Comparison</hl-em> Platform',
        'paragraphs' => [
            'Specora was created to solve a simple problem — Pakistani shoppers deserve a single place to discover, compare, and choose products without visiting dozens of websites. We believe that finding the right product should be smart, fast, and stress-free.',
            'Our platform brings together products from Amazon and multiple marketplaces, giving you side-by-side comparisons, price insights, and smart recommendations — so you can shop with confidence, not confusion.',
            '',
        ],
        'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRW-fAFLABiTA34k_WQNFBjQqjePkXwqPzciCtiYoL-hA&s=10',
    ]),
    'features' => json_encode([
        'badge_text' => 'What Makes Specora Different',
        'cards' => [
            [
                'icon' => '🔍',
                'icon_bg' => 'rgba(234,189,120,.15)',
                'title' => 'Smart Product Discovery',
                'description' => 'Discover products intelligently with smart filters, price insights, and real-time comparisons — not just a basic search.',
                'large_size' => true,
            ],
            [
                'icon' => '⚖️',
                'icon_bg' => 'rgba(59,130,246,.1)',
                'title' => 'Product Comparison',
                'description' => 'Side-by-side comparison of prices, specs & ratings',
                'large_size' => false,
            ],
            [
                'icon' => '🌐',
                'icon_bg' => 'rgba(16,185,129,.1)',
                'title' => 'Multi-Marketplace Search',
                'description' => 'Products from multiple trusted global marketplaces',
                'large_size' => false,
            ],
            [
                'icon' => '✓',
                'icon_bg' => 'rgba(16,185,129,.1)',
                'title' => 'Trusted Information',
                'description' => 'Verified product data from reliable, authentic sources',
                'large_size' => false,
            ],
            [
                'icon' => '🤖',
                'icon_bg' => 'rgba(139,92,246,.1)',
                'title' => 'Personalized Recommendations',
                'description' => 'AI-powered suggestions tailored to your preferences and browsing history',
                'large_size' => false,
            ],
            [
                'icon' => '🚀',
                'icon_bg' => 'rgba(234,189,120,.15)',
                'title' => 'Fast & Seamless',
                'description' => 'One platform. Thousands of products. Smarter decisions — every time.',
                'large_size' => false,
            ],
        ],
    ]),
    'faq' => json_encode([
        'badge_text' => 'Frequently Asked Questions',
        'heading_line1' => 'Everything You Need to',
        'heading_line2' => 'Know About <hl-em>Specora</hl-em>',
        'description' => 'Quick answers to the most common questions about how Specora works, what makes it different, and how you can get the most out of it.',
        'items' => [
            [
                'question' => 'What is Specora?',
                'answer' => 'Specora is Pakistan\'s intelligent product discovery and comparison platform. It lets you search, compare, and discover products from Amazon and other marketplaces — all in one place.',
            ],
            [
                'question' => 'How does Specora work?',
                'answer' => 'Specora fetches product data from the Amazon API and stores it in our database. When you search for a product, our system looks through thousands of products, filters them by your budget and preferences, and shows you the best matches.',
            ],
            [
                'question' => 'Does Specora sell products directly?',
                'answer' => 'No, Specora does not sell products directly. We are a product discovery platform that helps you find and compare products. When you click on a product, you are redirected to the original seller through our affiliate links.',
            ],
            [
                'question' => 'How do you compare products from multiple marketplaces?',
                'answer' => 'Currently, Specora pulls data from Amazon\'s product catalog. We show you prices in USD, ratings, reviews, and key specifications. In future updates, we plan to integrate more marketplaces.',
            ],
            [
                'question' => 'Is Specora free to use?',
                'answer' => 'Absolutely! Specora is 100% free to use. There are no hidden charges, subscription fees, or paywalls. You can search for products, compare prices, and browse categories without creating an account.',
            ],
            [
                'question' => 'How often is product information updated?',
                'answer' => 'Product data is updated each time you search or browse a category. Our system checks our database first — if we don\'t have enough fresh products, we fetch new data from the Amazon API.',
            ],
            [
                'question' => 'Can I trust the product information shown on Specora?',
                'answer' => 'Yes! All product data on Specora comes directly from Amazon\'s official product API. This includes authentic pricing, ratings, reviews, and product descriptions.',
            ],
            [
                'question' => 'Which marketplaces does Specora support?',
                'answer' => 'Currently, Specora is integrated with Amazon\'s product catalog. We are actively working on integrating additional marketplaces like AliExpress, Daraz, and more in future updates.',
            ],
        ],
    ]),
    'support' => json_encode([
        'icon' => '💬',
        'title' => 'Still have questions?',
        'description' => 'Contact our team and we\'ll be happy to help you with anything you need.',
        'button_text' => 'Contact Us →',
        'button_url' => 'contact.php',
    ]),
    'footer' => json_encode([
        'brand_description' => 'Pakistan\'s smartest product discovery platform. Search for anything — Specora finds it.',
        'copyright_year' => '2025',
        'copyright_text' => 'All rights reserved.',
        'made_with_text' => 'Made with ♥ in Karachi, Pakistan',
    ]),
];

// ── Initialize defaults on first run ──
$checkDefaults = $connect->query("SELECT COUNT(*) as c FROM about_page_settings");
$countRow = $checkDefaults->fetch_assoc();
if ((int)$countRow['c'] === 0) {
    $insertStmt = $connect->prepare("INSERT INTO about_page_settings (section_key, section_label, content) VALUES (?, ?, ?)");
    $labels = [
        'hero'    => 'Hero Section',
        'whoweare' => 'Who We Are',
        'features' => 'Features (Bento Grid)',
        'faq'     => 'FAQ Section',
        'support' => 'Support Card',
        'footer'  => 'Footer',
    ];
    foreach ($defaults as $key => $content) {
        $label = $labels[$key];
        $insertStmt->bind_param('sss', $key, $label, $content);
        $insertStmt->execute();
    }
    $insertStmt->close();
}

// ── Upload state variables (initialized for template safety) ──
$uploadError = '';
$uploadResult = '';

// ── CSRF token for AJAX (image upload needs it) ──
$csrfToken = generate_csrf_token();

// ── Handle Save ──
$saveSuccess = false;
$saveError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_section'])) {
    // ── CSRF check ──
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $saveError = 'Invalid form token. Please refresh the page and try again.';
    } else {
    $sectionKey = $_POST['section_key'] ?? '';
    $contentJSON = $_POST['content_json'] ?? '';

    if ($sectionKey && $contentJSON) {
        // Validate JSON
        json_decode($contentJSON);
        if (json_last_error() === JSON_ERROR_NONE) {
            $stmt = $connect->prepare("UPDATE about_page_settings SET content = ?, updated_at = NOW() WHERE section_key = ?");
            $stmt->bind_param('ss', $contentJSON, $sectionKey);
            if ($stmt->execute()) {
                $saveSuccess = true;
            } else {
                $saveError = 'Database error: ' . $connect->error;
            }
            $stmt->close();
        } else {
            $saveError = 'Invalid JSON format: ' . json_last_error_msg();
        }
    } else {
        $saveError = 'Missing section key or content.';
    }
}
}

// ── Handle Reset to Default ──
if (isset($_POST['reset_section'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $saveError = 'Invalid form token. Please refresh the page and try again.';
    } else {
    $sectionKey = $_POST['section_key'] ?? '';
    if ($sectionKey && isset($defaults[$sectionKey])) {
        $stmt = $connect->prepare("UPDATE about_page_settings SET content = ?, updated_at = NOW() WHERE section_key = ?");
        $stmt->bind_param('ss', $defaults[$sectionKey], $sectionKey);
        $stmt->execute();
        $stmt->close();
        $saveSuccess = true;
    }
    }
}

// ── Handle Reset ALL to Defaults ──
if (isset($_POST['reset_all'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $saveError = 'Invalid form token. Please refresh the page and try again.';
    } else {
    $stmt = $connect->prepare("UPDATE about_page_settings SET content = ?, updated_at = NOW() WHERE section_key = ?");
    foreach ($defaults as $key => $content) {
        $stmt->bind_param('ss', $content, $key);
        $stmt->execute();
    }
    $stmt->close();
    $saveSuccess = true;
    }
}

// ── Load all settings ──
$settings = [];
$result = $connect->query("SELECT section_key, section_label, content, updated_at FROM about_page_settings ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $settings[$row['section_key']] = $row;
}

// ── Get current admin info ──
$currentAdminName = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
$currentPage = 'manage-about';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Manage About Page — Specora Admin</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--gray-100);color:var(--black);min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;color:inherit;}
img{max-width:100%;display:block;}

.main{padding:40px 48px;flex:1;max-width:1200px;margin:0 auto;width:100%;}
.page-header{margin-bottom:28px;}
.page-eyebrow{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold-dark);margin-bottom:6px;}
.page-title{font-family:var(--font-display);font-size:clamp(24px,3.5vw,36px);font-weight:800;color:var(--black);letter-spacing:-1px;margin-bottom:6px;}
.page-sub{font-size:15px;color:var(--gray-600);}

/* ALERT */
.alert{padding:14px 18px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px;}
.alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#065F46;}
.alert-error{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}
.alert-close{margin-left:auto;background:none;border:none;font-size:18px;cursor:pointer;color:inherit;opacity:.5;}
.alert-close:hover{opacity:1;}

/* NAV TABS */
.section-tabs{display:flex;gap:6px;margin-bottom:28px;flex-wrap:wrap;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:6px;overflow-x:auto;}
.section-tab{display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;color:var(--gray-600);cursor:pointer;transition:all .2s;white-space:nowrap;border:none;background:none;font-family:var(--font-body);}
.section-tab:hover{background:var(--gray-100);color:var(--black);}
.section-tab.active{background:var(--gold);color:var(--black);box-shadow:0 2px 8px rgba(234,189,120,.25);}
.section-tab .tab-icon{font-size:16px;}
.section-tab .tab-label{font-size:12px;}

/* SECTION PANELS */
.section-panel{display:none;}
.section-panel.active{display:block;}

/* FORM CARD */
.form-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:32px;box-shadow:0 4px 16px rgba(0,0,0,.04);margin-bottom:20px;}
.form-card-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--gray-200);}
.form-card-title{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--black);letter-spacing:-.3px;}
.form-card-sub{font-size:13px;color:var(--gray-400);margin-top:2px;}
.form-card-actions{display:flex;gap:8px;flex-shrink:0;}
.form-card-updated{font-size:11px;color:var(--gray-400);margin-top:4px;}

/* FIELDS */
.field-group{margin-bottom:20px;}
.field-label{display:block;font-size:11px;font-weight:700;color:var(--gray-600);letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px;}
.field-label .required{color:#EF4444;margin-left:2px;}
.field-hint{font-size:11px;color:var(--gray-400);margin-top:4px;line-height:1.5;}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.field-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
@media(max-width:768px){.field-row,.field-row-3{grid-template-columns:1fr;}}

input[type="text"],input[type="url"],input[type="number"],textarea,select{width:100%;font-family:var(--font-body);font-size:14px;color:var(--black);background:var(--gray-100);border:1.5px solid transparent;border-radius:var(--radius-sm);padding:11px 14px;outline:none;transition:all .2s;}
input:focus,textarea:focus,select:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px rgba(234,189,120,.1);}
textarea{resize:vertical;min-height:80px;line-height:1.6;}
textarea.large{min-height:140px;}
.small-input{max-width:120px;}

/* CARD ITEM (repeater) */
.repeater-item{background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:18px;margin-bottom:12px;position:relative;}
.repeater-item-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.repeater-item-title{font-size:12px;font-weight:700;color:var(--gray-600);}
.repeater-remove{width:28px;height:28px;border-radius:6px;border:none;background:rgba(239,68,68,.08);color:#EF4444;font-size:14px;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;}
.repeater-remove:hover{background:rgba(239,68,68,.15);transform:scale(1.05);}
.repeater-add{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--gold-dark);background:rgba(234,189,120,.08);border:1px dashed rgba(234,189,120,.3);border-radius:var(--radius-sm);padding:8px 14px;cursor:pointer;transition:all .2s;margin-top:4px;font-family:var(--font-body);}
.repeater-add:hover{background:rgba(234,189,120,.15);border-color:var(--gold-dark);}

/* SUBMIT BAR */
.submit-bar{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:20px 0 0;border-top:1px solid var(--gray-200);margin-top:4px;}
.submit-bar-left{display:flex;gap:10px;flex-wrap:wrap;}
.submit-bar-right{display:flex;gap:10px;flex-wrap:wrap;}

.btn-primary{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:12px 28px;cursor:pointer;transition:all .2s;}
.btn-primary:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-1px);}
.btn-primary:active{transform:translateY(0);}
.btn-secondary{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--gray-600);background:var(--white);border:1.5px solid var(--gray-200);border-radius:var(--radius-pill);padding:11px 22px;cursor:pointer;transition:all .2s;}
.btn-secondary:hover{border-color:var(--gray-400);color:var(--black);}
.btn-danger{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:12px;font-weight:600;color:#EF4444;background:rgba(239,68,68,.06);border:1.5px solid rgba(239,68,68,.15);border-radius:var(--radius-pill);padding:8px 16px;cursor:pointer;transition:all .2s;}
.btn-danger:hover{background:rgba(239,68,68,.12);}
.btn-view{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--gold-dark);background:rgba(234,189,120,.08);border:1px solid rgba(234,189,120,.2);border-radius:var(--radius-pill);padding:11px 22px;cursor:pointer;transition:all .2s;text-decoration:none;}
.btn-view:hover{background:rgba(234,189,120,.15);}

/* CHECKBOX TOGGLE */
.toggle-wrap{display:flex;align-items:center;gap:10px;}
.toggle{position:relative;width:40px;height:22px;background:var(--gray-200);border-radius:11px;cursor:pointer;transition:background .2s;border:none;padding:0;flex-shrink:0;}
.toggle.active{background:var(--gold);}
.toggle::after{content:'';position:absolute;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:var(--white);box-shadow:0 1px 3px rgba(0,0,0,.15);transition:transform .2s;}
.toggle.active::after{transform:translateX(18px);}
.toggle-label{font-size:12px;font-weight:600;color:var(--gray-600);}

/* PREVIEW MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:999;display:none;align-items:center;justify-content:center;padding:20px;}
.modal-overlay.open{display:flex;}
.modal-content{background:var(--white);border-radius:var(--radius);max-width:900px;width:100%;max-height:85vh;overflow-y:auto;box-shadow:0 24px 80px rgba(0,0,0,.18);}
.modal-header{padding:20px 28px 14px;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--white);z-index:2;border-radius:var(--radius) var(--radius) 0 0;}
.modal-header h3{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--black);}
.modal-close{width:34px;height:34px;border-radius:50%;border:1px solid var(--gray-200);background:var(--white);font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--gray-400);transition:all .2s;}
.modal-close:hover{background:var(--gray-100);color:var(--black);}
.modal-body{padding:28px;}
.modal-body iframe{width:100%;height:500px;border:1px solid var(--gray-200);border-radius:var(--radius-sm);}

/* IMAGE UPLOAD */
.upload-zone{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.upload-zone input[type="url"]{flex:1;min-width:160px;}
.upload-btn-wrap{position:relative;overflow:hidden;display:inline-flex;}
.upload-btn-wrap input[type="file"]{position:absolute;top:0;right:0;min-width:100%;min-height:100%;opacity:0;cursor:pointer;}
.upload-btn{display:inline-flex;align-items:center;gap:6px;font-family:var(--font-body);font-size:12px;font-weight:600;color:var(--black);background:var(--gray-100);border:1.5px dashed var(--gray-400);border-radius:var(--radius-sm);padding:10px 16px;cursor:pointer;transition:all .2s;white-space:nowrap;}
.upload-btn:hover{border-color:var(--gold);background:rgba(234,189,120,.06);color:var(--gold-dark);}
.upload-btn.uploading{opacity:.6;pointer-events:none;}
.upload-progress{display:none;width:100%;height:4px;background:var(--gray-200);border-radius:4px;overflow:hidden;margin-top:8px;}
.upload-progress.active{display:block;}
.upload-progress-bar{height:100%;background:var(--gold);border-radius:4px;width:0%;transition:width .3s;}
.upload-status{font-size:11px;font-weight:600;margin-top:4px;}
.upload-status.success{color:#065F46;}
.upload-status.error{color:#EF4444;}
.upload-preview{position:relative;max-width:200px;border-radius:8px;overflow:hidden;border:1px solid var(--gray-200);margin-top:8px;}
.upload-preview img{width:100%;height:auto;display:block;}
.upload-preview-overlay{position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;}
.upload-preview:hover .upload-preview-overlay{opacity:1;}
.upload-preview-remove{width:32px;height:32px;border-radius:50%;background:rgba(239,68,68,.9);color:#fff;border:none;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;}

/* JSON EDITOR AREA */
.json-area{font-family:'Courier New',monospace;font-size:13px;line-height:1.5;min-height:200px;}

/* RESPONSIVE */
@media(max-width:1024px){
  .main{padding:32px 24px;}
  .form-card{padding:24px;}
}
@media(max-width:768px){
  .main{padding:24px 20px;}
  .form-card{padding:20px;}
  .form-card-header{flex-direction:column;}
  .section-tabs{flex-wrap:nowrap;overflow-x:auto;padding:4px;}
  .section-tab{flex-shrink:0;}
  .submit-bar{flex-direction:column;align-items:stretch;}
  .submit-bar-left,.submit-bar-right{justify-content:stretch;}
  .btn-primary,.btn-secondary,.btn-view{justify-content:center;}
}
</style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="sidebar-page">
<div class="main">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title"><?= svg_icon('file', 24) ?> Manage About Page</h1>
    <p class="page-sub">Fully customize every section of the About page. Changes are saved to the database and reflected immediately.</p>
  </div>

  <?php if ($saveSuccess): ?>
    <div class="alert alert-success">
      <?= svg_icon('check', 16) ?> Changes saved successfully!
      <button class="alert-close" onclick="this.parentElement.remove()"><?= svg_icon('x', 12) ?></button>
    </div>
  <?php endif; ?>
  <?php if ($saveError): ?>
    <div class="alert alert-error">
      <?= svg_icon('cross', 16) ?> <?= htmlspecialchars($saveError) ?>
      <button class="alert-close" onclick="this.parentElement.remove()"><?= svg_icon('x', 12) ?></button>
    </div>
  <?php endif; ?>
  <?php if ($uploadError): ?>
    <div class="alert alert-error">
      <?= svg_icon('cross', 16) ?> <?= htmlspecialchars($uploadError) ?>
      <button class="alert-close" onclick="this.parentElement.remove()"><?= svg_icon('x', 12) ?></button>
    </div>
  <?php endif; ?>
  <?php if ($uploadResult): ?>
    <div class="alert alert-success">
      <?= svg_icon('check', 16) ?> Image uploaded successfully! URL: <code style="font-size:12px;margin-left:6px;background:rgba(0,0,0,.06);padding:2px 6px;border-radius:4px;">uploads/about/<?= htmlspecialchars(basename($uploadResult)) ?></code>
      <button class="alert-close" onclick="this.parentElement.remove()"><?= svg_icon('x', 12) ?></button>
    </div>
  <?php endif; ?>

  <!-- SECTION TABS -->
  <div class="section-tabs" id="sectionTabs">
    <?php $sectionOrder = ['hero','whoweare','features','faq','support','footer']; ?>
    <?php $tabIcons = ['hero'=>'home','whoweare'=>'users','features'=>'sparkles','faq'=>'help-circle','support'=>'message-square','footer'=>'layout']; ?>
    <?php $tabLabels = ['hero'=>'Hero','whoweare'=>'Who We Are','features'=>'Features','faq'=>'FAQ','support'=>'Support','footer'=>'Footer']; ?>
    <?php foreach ($sectionOrder as $i => $key): ?>
      <button class="section-tab <?= $i === 0 ? 'active' : '' ?>" data-section="<?= $key ?>" onclick="switchSection('<?= $key ?>')">
        <span class="tab-icon"><?= svg_icon($tabIcons[$key], 16) ?></span>
        <span class="tab-label"><?= $tabLabels[$key] ?></span>
      </button>
    <?php endforeach; ?>
    <button class="section-tab" data-section="json-editor" onclick="switchSection('json-editor')" style="margin-left:auto;color:var(--gray-400);">
      <span class="tab-icon"><?= svg_icon('edit', 16) ?></span>
      <span class="tab-label">JSON Editor</span>
    </button>
  </div>

  <!-- HERO SECTION -->
  <div class="section-panel active" id="panel-hero">
    <?php $hero = json_decode($settings['hero']['content'] ?? $defaults['hero'], true); ?>
    <form method="POST" class="form-card">
      <div class="form-card-header">
        <div>
          <div class="form-card-title"><?= svg_icon('home', 18) ?> Hero Section</div>
          <div class="form-card-sub">Customize the main hero banner — heading, description, trust badges, and statistics.</div>
          <?php if (isset($settings['hero'])): ?>
            <div class="form-card-updated">Last updated: <?= date('M j, Y g:i A', strtotime($settings['hero']['updated_at'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-card-actions">
          <button type="button" class="btn-secondary" onclick="previewSection('hero')"><?= svg_icon('search', 14) ?> Preview</button>
        </div>
      </div>

      <input type="hidden" name="section_key" value="hero">
      <input type="hidden" name="content_json" id="hero-json">
      <?= csrf_field() ?>

      <div class="field-group">
        <label class="field-label">Badge Text</label>
        <input type="text" id="hero-badge" value="<?= htmlspecialchars($hero['badge_text'] ?? '') ?>" placeholder="e.g. Trusted Product Discovery Platform" oninput="buildHeroJSON()">
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Heading Line 1</label>
          <input type="text" id="hero-h1" value="<?= htmlspecialchars($hero['heading_line1'] ?? '') ?>" oninput="buildHeroJSON()">
        </div>
        <div class="field-group">
          <label class="field-label">Heading Line 2 <span class="field-hint">Use &lt;hl-em&gt;text&lt;/hl-em&gt; for gold highlight</span></label>
          <input type="text" id="hero-h2" value="<?= htmlspecialchars($hero['heading_line2'] ?? '') ?>" oninput="buildHeroJSON()">
        </div>
      </div>
      <div class="field-group" style="margin-top:-4px">
        <label class="field-label">Heading Line 3</label>
        <input type="text" id="hero-h3" value="<?= htmlspecialchars($hero['heading_line3'] ?? '') ?>" oninput="buildHeroJSON()">
      </div>

      <div class="field-group">
        <label class="field-label">Description</label>
        <textarea id="hero-desc" class="large" oninput="buildHeroJSON()"><?= htmlspecialchars($hero['description'] ?? '') ?></textarea>
      </div>

      <div class="field-group">
        <label class="field-label">Trust Indicators <span class="field-hint">3 items with emoji icon and text</span></label>
        <?php for ($t = 0; $t < 3; $t++): $ti = $hero['trust_items'][$t] ?? ['icon'=>'','text'=>'']; ?>
          <div class="field-row" style="margin-bottom:8px">
            <input type="text" placeholder="Icon (emoji)" value="<?= htmlspecialchars($ti['icon']) ?>" class="hero-ti-icon" style="max-width:80px" oninput="buildHeroJSON()">
            <input type="text" placeholder="Label text" value="<?= htmlspecialchars($ti['text']) ?>" class="hero-ti-text" oninput="buildHeroJSON()">
          </div>
        <?php endfor; ?>
      </div>

      <div class="field-group">
        <label class="field-label">Statistics <span class="field-hint">3 stats with number, suffix, and label</span></label>
        <?php for ($s = 0; $s < 3; $s++): $st = $hero['stats'][$s] ?? ['number'=>'','suffix'=>'','label'=>'']; ?>
          <div class="field-row-3" style="margin-bottom:8px">
            <input type="text" placeholder="Number" value="<?= htmlspecialchars($st['number']) ?>" class="hero-stat-num" oninput="buildHeroJSON()">
            <input type="text" placeholder="Suffix (e.g. K+)" value="<?= htmlspecialchars($st['suffix']) ?>" class="hero-stat-suffix" oninput="buildHeroJSON()" style="max-width:100px">
            <input type="text" placeholder="Label" value="<?= htmlspecialchars($st['label']) ?>" class="hero-stat-label" oninput="buildHeroJSON()">
          </div>
        <?php endfor; ?>
      </div>

      <div class="submit-bar">
        <div class="submit-bar-left">
          <button type="submit" name="save_section" class="btn-primary"><?= svg_icon('save', 16) ?> Save Hero Section</button>
        </div>
        <div class="submit-bar-right">
          <button type="submit" name="reset_section" class="btn-danger" onclick="return confirm('Reset hero section to default?')"><?= svg_icon('refresh', 14) ?> Reset to Default</button>
        </div>
      </div>
    </form>
  </div>

  <!-- WHO WE ARE SECTION -->
  <div class="section-panel" id="panel-whoweare">
    <?php $wwa = json_decode($settings['whoweare']['content'] ?? $defaults['whoweare'], true); ?>
    <form method="POST" class="form-card">
      <div class="form-card-header">
        <div>
          <div class="form-card-title"><?= svg_icon('users', 18) ?> Who We Are Section</div>
          <div class="form-card-sub">Customize the Who We Are section — heading, paragraphs, and image.</div>
          <?php if (isset($settings['whoweare'])): ?>
            <div class="form-card-updated">Last updated: <?= date('M j, Y g:i A', strtotime($settings['whoweare']['updated_at'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-card-actions">
          <button type="button" class="btn-secondary" onclick="previewSection('whoweare')"><?= svg_icon('search', 14) ?> Preview</button>
        </div>
      </div>

      <input type="hidden" name="section_key" value="whoweare">
      <input type="hidden" name="content_json" id="wwa-json">
      <?= csrf_field() ?>

      <div class="field-group">
        <label class="field-label">Badge Text</label>
        <input type="text" id="wwa-badge" value="<?= htmlspecialchars($wwa['badge_text'] ?? '') ?>" oninput="buildWWAJSON()">
      </div>

      <div class="field-row-3">
        <div class="field-group">
          <label class="field-label">Heading Line 1</label>
          <input type="text" id="wwa-h1" value="<?= htmlspecialchars($wwa['heading_line1'] ?? '') ?>" oninput="buildWWAJSON()">
        </div>
        <div class="field-group">
          <label class="field-label">Heading Line 2</label>
          <input type="text" id="wwa-h2" value="<?= htmlspecialchars($wwa['heading_line2'] ?? '') ?>" oninput="buildWWAJSON()">
        </div>
        <div class="field-group">
          <label class="field-label">Heading Line 3</label>
          <input type="text" id="wwa-h3" value="<?= htmlspecialchars($wwa['heading_line3'] ?? '') ?>" oninput="buildWWAJSON()">
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Paragraph 1</label>
        <textarea id="wwa-p1" oninput="buildWWAJSON()"><?= htmlspecialchars($wwa['paragraphs'][0] ?? '') ?></textarea>
      </div>
      <div class="field-group">
        <label class="field-label">Paragraph 2</label>
        <textarea id="wwa-p2" oninput="buildWWAJSON()"><?= htmlspecialchars($wwa['paragraphs'][1] ?? '') ?></textarea>
      </div>
      <div class="field-group">
        <label class="field-label">Paragraph 3</label>
        <textarea id="wwa-p3" oninput="buildWWAJSON()"><?= htmlspecialchars($wwa['paragraphs'][2] ?? '') ?></textarea>
      </div>

      <div class="field-group">
        <label class="field-label">Image</label>
        <div class="upload-zone">
          <input type="url" id="wwa-img" value="<?= htmlspecialchars($wwa['image_url'] ?? '') ?>" oninput="buildWWAJSON()" placeholder="Paste image URL or upload one...">
          <div class="upload-btn-wrap">
            <button type="button" class="upload-btn" id="uploadBtn"><?= svg_icon('folder', 14) ?> Upload</button>
            <input type="file" id="fileInput" accept="image/jpeg,image/png,image/gif,image/webp" onchange="uploadAboutImage(this)">
          </div>
        </div>
        <div class="upload-progress" id="uploadProgress"><div class="upload-progress-bar" id="uploadProgressBar"></div></div>
        <div class="upload-status" id="uploadStatus"></div>
        <div class="field-hint">Upload an image or paste a URL. Supported: JPG, PNG, GIF, WebP. Max 5MB. Recommended: 600x400px.</div>
        <div class="upload-preview" id="wwaPreview" style="<?= empty($wwa['image_url']) ? 'display:none' : '' ?>">
          <img id="wwaPreviewImg" src="<?= htmlspecialchars($wwa['image_url'] ?? '') ?>" alt="preview" onerror="this.closest('.upload-preview').style.display='none'">
          <div class="upload-preview-overlay">
            <button type="button" class="upload-preview-remove" onclick="clearImage()" title="Remove image"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
          </div>
        </div>
      </div>

      <div class="submit-bar">
        <div class="submit-bar-left">
          <button type="submit" name="save_section" class="btn-primary"><?= svg_icon('save', 16) ?> Save Who We Are</button>
        </div>
        <div class="submit-bar-right">
          <button type="submit" name="reset_section" class="btn-danger" onclick="return confirm('Reset Who We Are section to default?')"><?= svg_icon('refresh', 14) ?> Reset to Default</button>
        </div>
      </div>
    </form>
  </div>

  <!-- FEATURES BENTO GRID -->
  <div class="section-panel" id="panel-features">
    <?php $features = json_decode($settings['features']['content'] ?? $defaults['features'], true); ?>
    <form method="POST" class="form-card">
      <div class="form-card-header">
        <div>
          <div class="form-card-title"><?= svg_icon('sparkles', 18) ?> Features (Bento Grid)</div>
          <div class="form-card-sub">Customize the 6 feature cards displayed in the bento grid layout.</div>
          <?php if (isset($settings['features'])): ?>
            <div class="form-card-updated">Last updated: <?= date('M j, Y g:i A', strtotime($settings['features']['updated_at'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-card-actions">
          <button type="button" class="btn-secondary" onclick="previewSection('features')"><?= svg_icon('search', 14) ?> Preview</button>
        </div>
      </div>

      <input type="hidden" name="section_key" value="features">
      <input type="hidden" name="content_json" id="features-json">
      <?= csrf_field() ?>

      <div class="field-group">
        <label class="field-label">Badge Text</label>
        <input type="text" id="feat-badge" value="<?= htmlspecialchars($features['badge_text'] ?? '') ?>" oninput="buildFeaturesJSON()">
      </div>

      <div class="field-group">
        <label class="field-label">Feature Cards <span class="field-hint">6 cards showing your key selling points</span></label>
        <div id="features-repeater">
          <?php foreach (($features['cards'] ?? []) as $ci => $card): ?>
            <div class="repeater-item" data-index="<?= $ci ?>">
              <div class="repeater-item-header">
                <span class="repeater-item-title">Card <?= $ci + 1 ?></span>
            <button type="button" class="repeater-remove" onclick="removeFeatureCard(this)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
          </div>
              <div class="field-row-3" style="margin-bottom:8px">
                <input type="text" placeholder="Icon (emoji)" value="<?= htmlspecialchars($card['icon'] ?? '') ?>" class="feat-icon" oninput="buildFeaturesJSON()">
                <input type="text" placeholder="Icon BG color (e.g. rgba...)" value="<?= htmlspecialchars($card['icon_bg'] ?? 'rgba(234,189,120,.15)') ?>" class="feat-icon-bg" oninput="buildFeaturesJSON()">
                <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--gray-600)">
                  <input type="checkbox" class="feat-large" <?= !empty($card['large_size']) ? 'checked' : '' ?> oninput="buildFeaturesJSON()"> Large card
                </label>
              </div>
              <div class="field-group" style="margin-bottom:6px">
                <input type="text" placeholder="Card Title" value="<?= htmlspecialchars($card['title'] ?? '') ?>" class="feat-title" oninput="buildFeaturesJSON()">
              </div>
              <div class="field-group" style="margin-bottom:0">
                <textarea placeholder="Card Description" class="feat-desc" oninput="buildFeaturesJSON()"><?= htmlspecialchars($card['description'] ?? '') ?></textarea>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="repeater-add" onclick="addFeatureCard()">＋ Add Card</button>
      </div>

      <div class="submit-bar">
        <div class="submit-bar-left">
          <button type="submit" name="save_section" class="btn-primary"><?= svg_icon('save', 16) ?> Save Features</button>
        </div>
        <div class="submit-bar-right">
          <button type="submit" name="reset_section" class="btn-danger" onclick="return confirm('Reset Features section to default?')"><?= svg_icon('refresh', 14) ?> Reset to Default</button>
        </div>
      </div>
    </form>
  </div>

  <!-- FAQ SECTION -->
  <div class="section-panel" id="panel-faq">
    <?php $faq = json_decode($settings['faq']['content'] ?? $defaults['faq'], true); ?>
    <form method="POST" class="form-card">
      <div class="form-card-header">
        <div>
          <div class="form-card-title"><?= svg_icon('help-circle', 18) ?> FAQ Section</div>
          <div class="form-card-sub">Customize the Frequently Asked Questions section — heading, description, and Q&A items.</div>
          <?php if (isset($settings['faq'])): ?>
            <div class="form-card-updated">Last updated: <?= date('M j, Y g:i A', strtotime($settings['faq']['updated_at'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-card-actions">
          <button type="button" class="btn-secondary" onclick="previewSection('faq')"><?= svg_icon('search', 14) ?> Preview</button>
        </div>
      </div>

      <input type="hidden" name="section_key" value="faq">
      <input type="hidden" name="content_json" id="faq-json">
      <?= csrf_field() ?>

      <div class="field-group">
        <label class="field-label">Badge Text</label>
        <input type="text" id="faq-badge" value="<?= htmlspecialchars($faq['badge_text'] ?? '') ?>" oninput="buildFAQJSON()">
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Heading Line 1</label>
          <input type="text" id="faq-h1" value="<?= htmlspecialchars($faq['heading_line1'] ?? '') ?>" oninput="buildFAQJSON()">
        </div>
        <div class="field-group">
          <label class="field-label">Heading Line 2</label>
          <input type="text" id="faq-h2" value="<?= htmlspecialchars($faq['heading_line2'] ?? '') ?>" oninput="buildFAQJSON()">
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Description</label>
        <textarea id="faq-desc" oninput="buildFAQJSON()"><?= htmlspecialchars($faq['description'] ?? '') ?></textarea>
      </div>

      <div class="field-group">
        <label class="field-label">FAQ Items <span class="field-hint">Add, edit, or remove Q&A items</span></label>
        <div id="faq-repeater">
          <?php foreach (($faq['items'] ?? []) as $fi => $item): ?>
            <div class="repeater-item" data-index="<?= $fi ?>">
              <div class="repeater-item-header">
                <span class="repeater-item-title">Question <?= $fi + 1 ?></span>
                <button type="button" class="repeater-remove" onclick="removeFAQItem(this)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
              </div>
              <div class="field-group" style="margin-bottom:8px">
                <input type="text" placeholder="Question" value="<?= htmlspecialchars($item['question'] ?? '') ?>" class="faq-q" oninput="buildFAQJSON()">
              </div>
              <div class="field-group" style="margin-bottom:0">
                <textarea placeholder="Answer" class="faq-a" oninput="buildFAQJSON()"><?= htmlspecialchars($item['answer'] ?? '') ?></textarea>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="repeater-add" onclick="addFAQItem()">＋ Add Question</button>
      </div>

      <div class="submit-bar">
        <div class="submit-bar-left">
          <button type="submit" name="save_section" class="btn-primary"><?= svg_icon('save', 16) ?> Save FAQ Section</button>
        </div>
        <div class="submit-bar-right">
          <button type="submit" name="reset_section" class="btn-danger" onclick="return confirm('Reset FAQ section to default?')"><?= svg_icon('refresh', 14) ?> Reset to Default</button>
        </div>
      </div>
    </form>
  </div>

  <!-- SUPPORT CARD -->
  <div class="section-panel" id="panel-support">
    <?php $support = json_decode($settings['support']['content'] ?? $defaults['support'], true); ?>
    <form method="POST" class="form-card">
      <div class="form-card-header">
        <div>
          <div class="form-card-title"><?= svg_icon('message-square', 18) ?> Support Card</div>
          <div class="form-card-sub">Customize the support card displayed next to the FAQ section.</div>
          <?php if (isset($settings['support'])): ?>
            <div class="form-card-updated">Last updated: <?= date('M j, Y g:i A', strtotime($settings['support']['updated_at'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-card-actions">
          <button type="button" class="btn-secondary" onclick="previewSection('support')"><?= svg_icon('search', 14) ?> Preview</button>
        </div>
      </div>

      <input type="hidden" name="section_key" value="support">
      <input type="hidden" name="content_json" id="support-json">
      <?= csrf_field() ?>

      <div class="field-row-3">
        <div class="field-group">
          <label class="field-label">Icon (emoji)</label>
          <input type="text" id="sup-icon" value="<?= htmlspecialchars($support['icon'] ?? '💬') ?>" oninput="buildSupportJSON()" style="max-width:80px">
        </div>
        <div class="field-group">
          <label class="field-label">Title</label>
          <input type="text" id="sup-title" value="<?= htmlspecialchars($support['title'] ?? '') ?>" oninput="buildSupportJSON()">
        </div>
        <div class="field-group">
          <label class="field-label">Button Text</label>
          <input type="text" id="sup-btn" value="<?= htmlspecialchars($support['button_text'] ?? 'Contact Us →') ?>" oninput="buildSupportJSON()">
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Description</label>
        <textarea id="sup-desc" oninput="buildSupportJSON()"><?= htmlspecialchars($support['description'] ?? '') ?></textarea>
      </div>

      <div class="field-group">
        <label class="field-label">Button URL</label>
        <input type="text" id="sup-url" value="<?= htmlspecialchars($support['button_url'] ?? 'contact.php') ?>" oninput="buildSupportJSON()">
      </div>

      <div class="submit-bar">
        <div class="submit-bar-left">
          <button type="submit" name="save_section" class="btn-primary"><?= svg_icon('save', 16) ?> Save Support Card</button>
        </div>
        <div class="submit-bar-right">
          <button type="submit" name="reset_section" class="btn-danger" onclick="return confirm('Reset Support Card to default?')"><?= svg_icon('refresh', 14) ?> Reset to Default</button>
        </div>
      </div>
    </form>
  </div>

  <!-- FOOTER -->
  <div class="section-panel" id="panel-footer">
    <?php $footer = json_decode($settings['footer']['content'] ?? $defaults['footer'], true); ?>
    <form method="POST" class="form-card">
      <div class="form-card-header">
        <div>
          <div class="form-card-title"><?= svg_icon('layout', 18) ?> Footer</div>
          <div class="form-card-sub">Customize the footer text and branding.</div>
          <?php if (isset($settings['footer'])): ?>
            <div class="form-card-updated">Last updated: <?= date('M j, Y g:i A', strtotime($settings['footer']['updated_at'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="form-card-actions">
          <button type="button" class="btn-secondary" onclick="previewSection('footer')"><?= svg_icon('search', 14) ?> Preview</button>
        </div>
      </div>

      <input type="hidden" name="section_key" value="footer">
      <input type="hidden" name="content_json" id="footer-json">
      <?= csrf_field() ?>

      <div class="field-group">
        <label class="field-label">Brand Description</label>
        <textarea id="foot-desc" oninput="buildFooterJSON()"><?= htmlspecialchars($footer['brand_description'] ?? '') ?></textarea>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label class="field-label">Copyright Year</label>
          <input type="text" id="foot-year" value="<?= htmlspecialchars($footer['copyright_year'] ?? '2025') ?>" oninput="buildFooterJSON()">
        </div>
        <div class="field-group">
          <label class="field-label">Copyright Text</label>
          <input type="text" id="foot-copy" value="<?= htmlspecialchars($footer['copyright_text'] ?? 'All rights reserved.') ?>" oninput="buildFooterJSON()">
        </div>
      </div>

      <div class="field-group">
        <label class="field-label">Made With Text</label>
        <input type="text" id="foot-made" value="<?= htmlspecialchars($footer['made_with_text'] ?? '') ?>" oninput="buildFooterJSON()">
      </div>

      <div class="submit-bar">
        <div class="submit-bar-left">
          <button type="submit" name="save_section" class="btn-primary"><?= svg_icon('save', 16) ?> Save Footer</button>
        </div>
        <div class="submit-bar-right">
          <button type="submit" name="reset_section" class="btn-danger" onclick="return confirm('Reset Footer to default?')"><?= svg_icon('refresh', 14) ?> Reset to Default</button>
        </div>
      </div>
    </form>
  </div>

  <!-- JSON EDITOR -->
  <div class="section-panel" id="panel-json-editor">
    <div class="form-card">
      <div class="form-card-header">
        <div>
          <div class="form-card-title"><?= svg_icon('edit', 18) ?> JSON Editor</div>
          <div class="form-card-sub">Directly edit the raw JSON data for any section. Advanced users only.</div>
        </div>
        <div class="form-card-actions">
          <button type="button" class="btn-danger" onclick="document.getElementById('resetAllForm').submit();return confirm('Reset ALL sections to default?')"><?= svg_icon('refresh', 14) ?> Reset All to Default</button>
        </div>
      </div>
      <form method="POST" id="resetAllForm" style="display:none"><input type="hidden" name="reset_all" value="1"><?= csrf_field() ?></form>

      <?php foreach ($sectionOrder as $key): $sec = $settings[$key] ?? null; if (!$sec) continue; ?>
        <form method="POST" style="margin-bottom:16px;padding:16px;background:var(--gray-100);border-radius:var(--radius-sm);">
          <input type="hidden" name="section_key" value="<?= $key ?>">
          <?= csrf_field() ?>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <strong style="font-size:13px;color:var(--gray-600);text-transform:capitalize;"><?= htmlspecialchars($sec['section_label']) ?></strong>
            <button type="submit" name="save_section" class="btn-primary" style="padding:6px 16px;font-size:12px;"><?= svg_icon('save', 14) ?> Save</button>
          </div>
          <textarea name="content_json" class="json-area" style="width:100%;font-size:12px;min-height:120px;"><?= htmlspecialchars($sec['content']) ?></textarea>
        </form>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- GLOBAL ACTIONS -->
  <div style="display:flex;gap:12px;justify-content:space-between;flex-wrap:wrap;padding:16px 0;">
    <a href="../pages/about.php" target="_blank" class="btn-view" style="padding:12px 28px;"><?= svg_icon('search', 16) ?> View Live About Page</a>
    <form method="POST" onsubmit="return confirm('This will reset ALL sections to their default values. Are you sure?')">
      <?= csrf_field() ?>
      <button type="submit" name="reset_all" class="btn-danger" style="padding:12px 28px;"><?= svg_icon('refresh', 14) ?> Reset Everything to Defaults</button>
    </form>
  </div>

</div>

<!-- FOOTER -->
<footer style="background:var(--white);border-top:1px solid var(--gray-200);padding:20px 48px;text-align:center;font-size:13px;color:var(--gray-400);">
  <p>© 2025 <span style="color:var(--gold);">Specora</span> Admin Panel. All rights reserved.</p>
</footer>

</div><!-- /sidebar-page -->

<script>
var CSRF_TOKEN = '<?= addslashes($csrfToken) ?>';

// ── SECTION SWITCHING ──
function switchSection(key) {
  document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
  document.querySelector(`.section-tab[data-section="${key}"]`).classList.add('active');
  document.getElementById(`panel-${key}`).classList.add('active');
}

// ── BUILD HERO JSON ──
function buildHeroJSON() {
  const data = {
    badge_text: document.getElementById('hero-badge').value,
    heading_line1: document.getElementById('hero-h1').value,
    heading_line2: document.getElementById('hero-h2').value,
    heading_line3: document.getElementById('hero-h3').value,
    description: document.getElementById('hero-desc').value,
    trust_items: [],
    stats: [],
  };
  document.querySelectorAll('.hero-ti-icon').forEach((el, i) => {
    data.trust_items[i] = data.trust_items[i] || {};
    data.trust_items[i].icon = el.value;
  });
  document.querySelectorAll('.hero-ti-text').forEach((el, i) => {
    data.trust_items[i] = data.trust_items[i] || {};
    data.trust_items[i].text = el.value;
  });
  document.querySelectorAll('.hero-stat-num').forEach((el, i) => {
    data.stats[i] = data.stats[i] || {};
    data.stats[i].number = el.value;
  });
  document.querySelectorAll('.hero-stat-suffix').forEach((el, i) => {
    data.stats[i] = data.stats[i] || {};
    data.stats[i].suffix = el.value;
  });
  document.querySelectorAll('.hero-stat-label').forEach((el, i) => {
    data.stats[i] = data.stats[i] || {};
    data.stats[i].label = el.value;
  });
  document.getElementById('hero-json').value = JSON.stringify(data);
}
document.addEventListener('DOMContentLoaded', buildHeroJSON);

// ── BUILD WWA JSON ──
function buildWWAJSON() {
  const data = {
    badge_text: document.getElementById('wwa-badge').value,
    heading_line1: document.getElementById('wwa-h1').value,
    heading_line2: document.getElementById('wwa-h2').value,
    heading_line3: document.getElementById('wwa-h3').value,
    paragraphs: [
      document.getElementById('wwa-p1').value,
      document.getElementById('wwa-p2').value,
      document.getElementById('wwa-p3').value,
    ],
    image_url: document.getElementById('wwa-img').value,
  };
  document.getElementById('wwa-json').value = JSON.stringify(data);
}
document.addEventListener('DOMContentLoaded', buildWWAJSON);

// ── BUILD FEATURES JSON ──
function buildFeaturesJSON() {
  const data = {
    badge_text: document.getElementById('feat-badge').value,
    cards: [],
  };
  document.querySelectorAll('#features-repeater .repeater-item').forEach(item => {
    data.cards.push({
      icon: item.querySelector('.feat-icon').value,
      icon_bg: item.querySelector('.feat-icon-bg').value,
      title: item.querySelector('.feat-title').value,
      description: item.querySelector('.feat-desc').value,
      large_size: item.querySelector('.feat-large').checked,
    });
  });
  document.getElementById('features-json').value = JSON.stringify(data);
}
document.addEventListener('DOMContentLoaded', buildFeaturesJSON);

// ── ADD / REMOVE FEATURE CARD ──
let featCardCount = document.querySelectorAll('#features-repeater .repeater-item').length;
function addFeatureCard() {
  featCardCount++;
  const div = document.createElement('div');
  div.className = 'repeater-item';
  div.innerHTML = `
    <div class="repeater-item-header">
      <span class="repeater-item-title">Card ${featCardCount}</span>
      <button type="button" class="repeater-remove" onclick="removeFeatureCard(this)">✕</button>
    </div>
    <div class="field-row-3" style="margin-bottom:8px">
      <input type="text" placeholder="Icon (emoji)" class="feat-icon" oninput="buildFeaturesJSON()">
      <input type="text" placeholder="Icon BG color" value="rgba(234,189,120,.15)" class="feat-icon-bg" oninput="buildFeaturesJSON()">
      <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--gray-600)">
        <input type="checkbox" class="feat-large" oninput="buildFeaturesJSON()"> Large card
      </label>
    </div>
    <div class="field-group" style="margin-bottom:6px">
      <input type="text" placeholder="Card Title" class="feat-title" oninput="buildFeaturesJSON()">
    </div>
    <div class="field-group" style="margin-bottom:0">
      <textarea placeholder="Card Description" class="feat-desc" oninput="buildFeaturesJSON()"></textarea>
    </div>`;
  document.getElementById('features-repeater').appendChild(div);
  buildFeaturesJSON();
}
function removeFeatureCard(btn) {
  btn.closest('.repeater-item').remove();
  buildFeaturesJSON();
}

// ── BUILD FAQ JSON ──
function buildFAQJSON() {
  const data = {
    badge_text: document.getElementById('faq-badge').value,
    heading_line1: document.getElementById('faq-h1').value,
    heading_line2: document.getElementById('faq-h2').value,
    description: document.getElementById('faq-desc').value,
    items: [],
  };
  document.querySelectorAll('#faq-repeater .repeater-item').forEach(item => {
    data.items.push({
      question: item.querySelector('.faq-q').value,
      answer: item.querySelector('.faq-a').value,
    });
  });
  document.getElementById('faq-json').value = JSON.stringify(data);
}
document.addEventListener('DOMContentLoaded', buildFAQJSON);

// ── ADD / REMOVE FAQ ITEM ──
let faqCount = document.querySelectorAll('#faq-repeater .repeater-item').length;
function addFAQItem() {
  faqCount++;
  const div = document.createElement('div');
  div.className = 'repeater-item';
  div.innerHTML = `
    <div class="repeater-item-header">
      <span class="repeater-item-title">Question ${faqCount}</span>
      <button type="button" class="repeater-remove" onclick="removeFAQItem(this)"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="field-group" style="margin-bottom:8px">
      <input type="text" placeholder="Question" class="faq-q" oninput="buildFAQJSON()">
    </div>
    <div class="field-group" style="margin-bottom:0">
      <textarea placeholder="Answer" class="faq-a" oninput="buildFAQJSON()"></textarea>
    </div>`;
  document.getElementById('faq-repeater').appendChild(div);
  buildFAQJSON();
}
function removeFAQItem(btn) {
  btn.closest('.repeater-item').remove();
  buildFAQJSON();
}

// ── BUILD SUPPORT JSON ──
function buildSupportJSON() {
  const data = {
    icon: document.getElementById('sup-icon').value,
    title: document.getElementById('sup-title').value,
    description: document.getElementById('sup-desc').value,
    button_text: document.getElementById('sup-btn').value,
    button_url: document.getElementById('sup-url').value,
  };
  document.getElementById('support-json').value = JSON.stringify(data);
}
document.addEventListener('DOMContentLoaded', buildSupportJSON);

// ── BUILD FOOTER JSON ──
function buildFooterJSON() {
  const data = {
    brand_description: document.getElementById('foot-desc').value,
    copyright_year: document.getElementById('foot-year').value,
    copyright_text: document.getElementById('foot-copy').value,
    made_with_text: document.getElementById('foot-made').value,
  };
  document.getElementById('footer-json').value = JSON.stringify(data);
}
document.addEventListener('DOMContentLoaded', buildFooterJSON);

// ── PREVIEW SECTION (opens about.php with a section anchor) ──
function previewSection(section) {
  const anchors = {hero:'hero',whoweare:'whoweare',features:'diff',faq:'faq',support:'faq',footer:'footer'};
  window.open('../pages/about.php#' + (anchors[section] || section), '_blank');
}

// ── IMAGE UPLOAD HANDLER ──
function uploadAboutImage(input) {
  const file = input.files[0];
  if (!file) return;

  const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  if (!validTypes.includes(file.type)) {
    showUploadStatus('Only JPG, PNG, GIF & WebP images are allowed.', 'error');
    input.value = '';
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    showUploadStatus('File is too large. Maximum size is 5MB.', 'error');
    input.value = '';
    return;
  }

  const formData = new FormData();
  formData.append('about_image', file);
  formData.append('upload_image', '1');
  formData.append('csrf_token', '<?= $csrfToken ?>');

  const progressBar = document.getElementById('uploadProgress');
  const progressFill = document.getElementById('uploadProgressBar');
  progressBar.classList.add('active');
  progressFill.style.width = '0%';
  document.getElementById('uploadBtn').classList.add('uploading');
  showUploadStatus('Uploading...', '');

  const xhr = new XMLHttpRequest();
  xhr.open('POST', window.location.href, true);

  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      const pct = Math.round((e.loaded / e.total) * 100);
      progressFill.style.width = pct + '%';
    }
  };

  xhr.onload = function() {
    progressFill.style.width = '100%';
    document.getElementById('uploadBtn').classList.remove('uploading');
    setTimeout(() => { progressBar.classList.remove('active'); }, 1500);

    if (xhr.status === 200) {
      try {
        const resp = JSON.parse(xhr.responseText);
        if (resp.success) {
          // Auto-populate the URL field and preview
          const urlField = document.getElementById('wwa-img');
          urlField.value = resp.url;
          const preview = document.getElementById('wwaPreview');
          const previewImg = document.getElementById('wwaPreviewImg');
          previewImg.src = '../' + resp.url;
          preview.style.display = 'block';
          showUploadStatus('✅ ' + resp.message, 'success');
          buildWWAJSON(); // Update JSON with new URL
        } else {
          showUploadStatus('❌ ' + resp.message, 'error');
        }
      } catch(e) {
        showUploadStatus('Invalid server response.', 'error');
      }
    } else {
      showUploadStatus('Upload failed. Server error.', 'error');
    }
    input.value = '';
  };

  xhr.onerror = function() {
    document.getElementById('uploadBtn').classList.remove('uploading');
    progressBar.classList.remove('active');
    showUploadStatus('Network error. Please try again.', 'error');
    input.value = '';
  };

  xhr.send(formData);
}

function showUploadStatus(msg, type) {
  const el = document.getElementById('uploadStatus');
  el.textContent = msg;
  el.className = 'upload-status' + (type ? ' ' + type : '');
}

function clearImage() {
  document.getElementById('wwa-img').value = '';
  document.getElementById('wwaPreview').style.display = 'none';
  document.getElementById('wwaPreviewImg').src = '';
  buildWWAJSON();
}

// ── AUTO-ALERT DISMISS ──
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(el => el.remove());
}, 5000);
</script>
</body>
</html>
