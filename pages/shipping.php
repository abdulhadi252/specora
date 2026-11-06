<?php
// ================================================
// shipping.php — Specora Shipping Information
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
<title>Shipping Information — Specora</title>
<meta name="description" content="Learn about shipping policies, delivery times, and shipping costs for products found on Specora.">
<meta name="keywords" content="shipping, delivery, shipping policy, Specora, product delivery">
<meta property="og:title" content="Shipping Information — Specora">
<meta property="og:description" content="Learn about shipping policies, delivery times, and costs for products found on Specora.">
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
      <span class="page-badge-text">SHIPPING INFORMATION</span>
    </div>
    <h1>Shipping & <span class="hl-em">Delivery</span></h1>
    <p>Everything you need to know about shipping times, costs, and delivery for products found through Specora. Understanding how shipping works is essential to having a smooth online shopping experience.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <p>Specora is a product discovery platform that helps you find the best products across multiple marketplaces. When you click on a product listing and decide to make a purchase, you are redirected to the respective marketplace where the transaction takes place. Shipping is handled entirely by the marketplace or the individual seller you choose to purchase from, and Specora has no role in the shipping process whatsoever. Different marketplaces offer different shipping options, speeds, and costs, and these can vary significantly depending on the seller's location, the destination address, the product category, and the shipping method selected at checkout.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>How <span class="hl-em">Shipping Works</span></h2>
    <p>When you use Specora to find a product and click through to make a purchase, the shipping process begins on the marketplace's website, not on Specora. Each marketplace has its own established shipping infrastructure, policies, and timeframes. Generally speaking, Amazon offers international shipping to Pakistan with estimated delivery times ranging from five to twelve business days, and items that are eligible for Amazon Prime may ship significantly faster. AliExpress typically offers delivery windows of ten to twenty business days depending on the shipping method selected at checkout, with many sellers providing free shipping options as an incentive for buyers. eBay shipping times vary more widely because the platform is composed of individual sellers, each with their own shipping arrangements and location, so delivery can range from seven to fourteen business days on average. Walmart, which primarily serves the United States market, offers international shipping to Pakistan with delivery estimates of eight to fifteen business days for most items.</p>
    <p>Shipping costs are determined entirely by the marketplace or individual seller from whom you make your purchase. Many products on these platforms offer free shipping promotions, especially for items above a certain price threshold or during seasonal sales events. However, some sellers may charge shipping fees based on the weight, dimensions, or destination of the package. We strongly recommend that you verify the total cost, including all shipping charges and any applicable taxes, on the marketplace's product page before completing your purchase. Shipping costs displayed on Specora are estimates only and may differ from the final amount charged by the marketplace at checkout.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Important <span class="hl-em">Notes</span></h2>
    <p>International shipments to Pakistan may be subject to customs fees, import duties, and taxes levied by Pakistani customs authorities. These additional charges are entirely the responsibility of the buyer and are not included in the shipping costs displayed at checkout on the marketplace. We advise you to familiarize yourself with Pakistan's import regulations and duty structures for the type of product you are purchasing, as these can vary significantly depending on the product category and its declared value.</p>
    <p>It is crucial that you ensure your shipping address is accurate, complete, and properly formatted before finalizing any purchase. An incorrect or incomplete address can result in significant delivery delays, additional forwarding costs, or the package being returned to the seller, which may incur further fees. If your order has not arrived within the estimated delivery timeframe provided by the marketplace, you should contact the marketplace's customer support team directly, as they are responsible for the fulfillment and tracking of your order. Specora is not involved in the shipping, tracking, or delivery process and cannot provide updates on individual shipments. Our role begins and ends with helping you discover and compare products across multiple marketplaces, and all transactions, including shipping and delivery, occur entirely on the respective marketplace where the purchase is made.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
