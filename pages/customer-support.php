<?php
// ================================================
// customer-support.php — Specora Customer Support
// ================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/check_tables.php';
check_specora_tables();
if (!isset($indexTotalProducts)) { $r = $connect->query("SELECT COUNT(*) as c FROM products"); $indexTotalProducts = $r ? (int)$r->fetch_assoc()['c'] : 0; }
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Customer Support — Specora</title>
<meta name="description" content="Get help and support from Specora. Contact our support team for assistance with product discovery, account issues, and more.">
<meta name="keywords" content="customer support, help, Specora support, contact support, assistance">
<meta property="og:title" content="Customer Support — Specora">
<meta property="og:description" content="Get help and support from Specora's dedicated customer support team.">
<meta property="og:image" content="../img/specora-logo.png">
<meta property="og:type" content="website">
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/navbar.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111;--white:#FFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--white);color:var(--black);overflow-x:hidden;}
a{text-decoration:none;color:inherit;}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important;}}
.page-hero{position:relative;padding:56px 48px 72px;margin-top:68px;background:var(--white);overflow:hidden;}
.page-hero::before{content:'';position:absolute;top:-200px;right:-160px;width:600px;height:600px;background:radial-gradient(circle,rgba(234,189,120,.08) 0%,transparent 65%);pointer-events:none;}
.page-hero-inner{max-width:900px;margin:0 auto;position:relative;z-index:2;text-align:center;}
.page-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:6px 14px 6px 10px;margin-bottom:20px;}
.page-badge-icon{width:18px;height:18px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;}
.page-badge-text{font-size:11px;font-weight:600;color:var(--gold-dark);letter-spacing:.3px;}
.page-hero h1{font-family:var(--font-display);font-size:clamp(32px,4vw,52px);font-weight:800;letter-spacing:-1.5px;color:var(--black);margin-bottom:16px;line-height:1.1;}
.page-hero h1 .hl-em{color:var(--gold-dark);}
.page-hero p{font-size:16px;color:var(--gray-600);line-height:1.7;max-width:600px;margin:0 auto 28px;}
.content-section{padding:80px 48px;background:var(--white);}
.content-section:nth-child(even){background:var(--off-white);}
.content-inner{max-width:900px;margin:0 auto;}
.content-inner h2{font-family:var(--font-display);font-size:clamp(22px,2.5vw,32px);font-weight:700;color:var(--black);margin-bottom:20px;letter-spacing:-.5px;}
.content-inner h2 .hl-em{color:var(--gold-dark);}
.content-inner h3{font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--black);margin-bottom:12px;margin-top:28px;}
.content-inner p{font-size:15px;color:var(--gray-600);line-height:1.8;margin-bottom:16px;}
.content-inner ul{list-style:none;margin-bottom:24px;}
.content-inner ul li{font-size:15px;color:var(--gray-600);line-height:1.7;padding:8px 0 8px 28px;position:relative;}
.content-inner ul li::before{content:'→';position:absolute;left:0;top:50%;transform:translateY(-50%);color:var(--gold-dark);font-weight:700;}
.cta-btn{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:12px 24px;cursor:pointer;transition:all .25s;text-decoration:none;}
.cta-btn:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-2px);box-shadow:0 6px 20px rgba(234,189,120,.25);}
.cta-btn:active{transform:scale(.97);}
@media(max-width:1024px){.page-hero{padding:48px 24px 56px;}nav{padding:0 24px;}.nav-links,.nav-actions{display:none;}.hamburger{display:flex;}.mobile-menu{top:68px;}footer{padding:52px 24px 28px;}}
@media(max-width:768px){.page-hero{padding:40px 20px 44px;margin-top:62px;}nav{padding:0 20px;height:62px;}.mobile-menu{top:62px;}.content-section{padding:56px 20px;}footer{padding:44px 20px 24px;}}
@media(max-width:480px){.page-hero{padding:32px 16px 36px;margin-top:58px;}.content-section{padding:44px 16px;}nav{height:58px;padding:0 16px;}.mobile-menu{top:58px;}footer{padding:36px 16px 20px;}}
</style>
</head>
<body>
<?php $basePath = '../'; require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-hero">
  <div class="page-hero-inner">
    <div class="page-badge">
      <span class="page-badge-text">CUSTOMER SUPPORT</span>
    </div>
    <h1>We're Here to <span class="hl-em">Help You</span></h1>
    <p>Our dedicated support team is ready to assist you with any questions, concerns, or feedback about Specora and its product discovery services.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>How Can We <span class="hl-em">Help You</span>?</h2>
    <p>Our support team is available to assist you with a wide range of inquiries related to Specora and the product discovery services we provide. If you are experiencing difficulties finding products or using our search and filter features, we can help you optimize your search queries to get the best possible results from our platform. If you have questions about the products listed from Amazon, AliExpress, eBay, or Walmart that appear in your search results, we can provide guidance on how to interpret product data, understand pricing information, and navigate to the correct listing on the marketplace. For issues related to your saved searches, browsing preferences, or any account-related features on Specora, our team is ready to assist you with troubleshooting and resolving any problems you may encounter. We also welcome your feedback and suggestions for improving Specora, as user input is invaluable in helping us prioritize new features, fix issues, and make the platform more useful for everyone. If you encounter a product listing that appears incorrect, outdated, or inappropriate, please let us know so we can investigate and take appropriate action.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Ways to <span class="hl-em">Reach Us</span></h2>
    <p>There are several convenient ways to get in touch with the Specora support team. You can reach us by email at our dedicated support address, where we aim to respond to all inquiries within twenty-four hours on business days. Alternatively, you can use the contact form available on our contact page, which allows you to submit a detailed description of your question or issue directly through the website. Before reaching out to our support team, we encourage you to visit our FAQ or Help Center page, where many common questions about using Specora, understanding product data, and troubleshooting issues are already answered in detail. Browsing the FAQ first may provide you with an immediate solution to your inquiry without needing to wait for a support response. Our support hours are Monday through Friday from nine in the morning to six in the evening, and Saturday from ten in the morning to four in the evening, Pakistan Standard Time. We are closed on Sunday, but any emails or messages received over the weekend will be responded to promptly on Monday.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Support <span class="hl-em">Hours</span></h2>
    <p>Our support team maintains regular operating hours to ensure that we can provide timely and thorough assistance to all Specora users. Our team is available Monday through Friday from nine in the morning to six in the evening, Pakistan Standard Time, which covers the standard business day. On Saturdays, we operate with reduced hours from ten in the morning to four in the evening to accommodate users who may need assistance over the weekend. We are closed on Sundays, which allows our team to rest and prepare for the week ahead, but any emails, contact form submissions, or messages received during this time will be queued and responded to promptly when we resume operations on Monday morning. We strive to respond to all inquiries within twenty-four hours during business days, and we prioritize responses based on the urgency and nature of the issue. For the fastest possible response time, we recommend using the contact form on our website, as this directly routes your inquiry to our support queue and helps us capture all the relevant details needed to assist you efficiently.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Common <span class="hl-em">Solutions</span></h2>
    <p>Before reaching out to our support team, you may find that some common issues can be resolved quickly on your own with a few simple troubleshooting steps. If you are not finding the products you are looking for in your search results, try broadening your search terms by using more general keywords or removing any filters you may have applied. You can also try specifying a particular marketplace in your search query, such as including the marketplace name like "amazon wireless headphones" to narrow results to that specific source. If the product prices displayed on Specora appear outdated or significantly different from what you see on the marketplace website, please note that prices are updated regularly from marketplace data but can change frequently on the source. We always recommend clicking through to the marketplace product page to verify the most current pricing, availability, and shipping costs before making a purchase decision. If you are experiencing issues with the Specora website not displaying or functioning correctly on your mobile device, ensure that your browser is updated to the latest version, try clearing your browser cache and cookies, or switch to a different browser such as Chrome, Firefox, or Safari, as Specora is optimized for the latest versions of these browsers. If product links on Specora are not opening correctly or are redirecting to incorrect pages, try copying the link address and pasting it directly into a new browser tab, as some browser extensions or pop-up blockers may interfere with automatic link redirections. Disabling such extensions temporarily may also resolve the issue.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
