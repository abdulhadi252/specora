<?php
/**
 * admin/dashboard.php — Specora Admin Dashboard
 * 
 * Protected page — requires admin login.
 * Shows welcome message and basic site stats.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/connect.php';

// Require login — redirects to login.php if not authenticated
require_admin_login();

// Check for flash messages
$logoutError = isset($_GET['error']) && $_GET['error'] === 'invalid_logout';

// Fetch some basic stats
$totalCategories = 0;
$totalAdmins = 0;
$totalContacts = 0;
$unreadContacts = 0;

// Count products from ALL product tables (products, aliexpress_products, walmart_products, ebay_products)
$productTables = ['products', 'aliexpress_products', 'walmart_products', 'ebay_products'];
$totalProducts = 0;
foreach ($productTables as $pt) {
    $r1 = mysqli_query($connect, "SELECT COUNT(*) as c FROM $pt");
    if ($r1) { $totalProducts += (int)mysqli_fetch_assoc($r1)['c']; }
}

$r2 = mysqli_query($connect, "SELECT COUNT(*) as c FROM admin_category");
if ($r2) { $totalCategories = (int)mysqli_fetch_assoc($r2)['c']; }

$r3 = mysqli_query($connect, "SELECT COUNT(*) as c FROM admins");
if ($r3) { $totalAdmins = (int)mysqli_fetch_assoc($r3)['c']; }

$r4 = mysqli_query($connect, "SELECT COUNT(*) as c FROM contact_messages");
if ($r4) { $totalContacts = (int)mysqli_fetch_assoc($r4)['c']; }

$r5 = mysqli_query($connect, "SELECT COUNT(*) as c FROM contact_messages WHERE status='unread'");
if ($r5) { $unreadContacts = (int)mysqli_fetch_assoc($r5)['c']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard — Specora Admin</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--gray-100);color:var(--black);min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;color:inherit;}

/* ── MAIN ── */
.main{padding:40px 48px;flex:1;max-width:1200px;margin:0 auto;width:100%;}

/* ── HEADER ── */
.page-header{margin-bottom:36px;}
.page-eyebrow{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold-dark);margin-bottom:6px;}
.page-title{font-family:var(--font-display);font-size:clamp(24px,3.5vw,36px);font-weight:800;color:var(--black);letter-spacing:-1px;margin-bottom:6px;}
.page-sub{font-size:15px;color:var(--gray-600);}

/* ── STATS GRID ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:40px;}
.stat-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:24px;transition:transform .2s,box-shadow .2s;}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.06);}
.stat-icon{font-size:28px;margin-bottom:10px;}
.stat-label{font-size:12px;color:var(--gray-400);font-weight:600;margin-bottom:4px;}
.stat-value{font-family:var(--font-display);font-size:32px;font-weight:800;color:var(--black);letter-spacing:-1px;}

/* ── WELCOME CARD ── */
.welcome-card{background:var(--black);border-radius:var(--radius);padding:32px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;}
.welcome-text{color:var(--white);}
.welcome-greeting{font-family:var(--font-display);font-size:20px;font-weight:700;margin-bottom:6px;}
.welcome-greeting em{font-style:normal;color:var(--gold);}
.welcome-desc{font-size:14px;color:rgba(255,255,255,.4);line-height:1.6;}
.welcome-btn{font-size:13px;font-weight:600;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:11px 24px;cursor:pointer;transition:all .2s;display:inline-block;}
.welcome-btn:hover{background:var(--gold-dark);color:var(--white);}

/* ── QUICK LINKS ── */
.quick-title{font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--black);margin-bottom:16px;}
.quick-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;}
.quick-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:18px;text-align:center;cursor:pointer;transition:all .2s;}
.quick-card:hover{border-color:var(--gold);transform:translateY(-2px);box-shadow:0 4px 16px rgba(234,189,120,.12);}
.quick-icon{font-size:24px;margin-bottom:8px;}
.quick-name{font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--gray-600);}
.quick-card:hover .quick-name{color:var(--black);}

/* ── FOOTER ── */
footer{background:var(--white);border-top:1px solid var(--gray-200);padding:20px 48px;text-align:center;font-size:13px;color:var(--gray-400);}
footer span{color:var(--gold);}

@media(max-width:1024px){
  .main{padding:32px 24px;}
}
@media(max-width:768px){
  .main{padding:24px 20px;}
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .welcome-card{flex-direction:column;align-items:flex-start;}
  footer{padding:16px 20px;}
}
</style>
</head>
<body>

<?php $currentPage = 'dashboard'; include __DIR__ . '/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="sidebar-page">
<div class="main">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title">Dashboard</h1>
    <p class="page-sub">Welcome to the Specora administration area.</p>
  </div>

  <?php if ($logoutError): ?>
    <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:var(--radius-sm);padding:14px 18px;margin-bottom:20px;font-size:13px;font-weight:500;color:#991B1B;display:flex;align-items:center;gap:8px;">
      <span style="width:16px;height:16px;display:flex;align-items:center;flex-shrink:0;"><?= svg_icon('alert', 16) ?></span>
      Logout failed: Invalid or expired request. Please try again.
    </div>
  <?php endif; ?>

  <!-- STATS -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon" style="color:var(--gold-dark);"><?= svg_icon('box', 28) ?></div>
      <div class="stat-label">Total Products</div>
      <div class="stat-value"><?= number_format($totalProducts) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><?= svg_icon('folder', 28) ?></div>
      <div class="stat-label">Categories</div>
      <div class="stat-value"><?= number_format($totalCategories) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><?= svg_icon('user', 28) ?></div>
      <div class="stat-label">Admins</div>
      <div class="stat-value"><?= number_format($totalAdmins) ?></div>
    </div>
    <a href="contact-messages.php" class="stat-card" style="display:block;text-decoration:none;">
      <div class="stat-icon"><?= svg_icon('mail', 28) ?></div>
      <div class="stat-label">Contact Messages</div>
      <div class="stat-value"><?= number_format($totalContacts) ?>
        <?php if ($unreadContacts > 0): ?>
          <span style="font-size:14px;background:rgba(234,189,120,.12);color:var(--gold-dark);padding:2px 10px;border-radius:100px;margin-left:6px;font-weight:700;vertical-align:middle;"><?= $unreadContacts ?> new</span>
        <?php endif; ?>
      </div>
    </a>
  </div>

  <!-- WELCOME -->
  <div class="welcome-card" style="margin-bottom:40px;">
    <div class="welcome-text">
      <div class="welcome-greeting">Welcome to <em>Specora</em>!</div>
      <div class="welcome-desc">You are logged in as <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong>.<br>Use the links below to navigate the admin area.</div>
    </div>
    <a href="../index.php" class="welcome-btn">View Website →</a>
  </div>

</div>

<!-- FOOTER -->
<footer>
  <p>© 2025 <span>Specora</span> Admin Panel. All rights reserved.</p>
</footer>

</div><!-- /sidebar-page -->
</body>
</html>
