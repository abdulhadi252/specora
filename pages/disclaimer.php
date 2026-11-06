<?php
// ================================================
// disclaimer.php — Specora Disclaimer
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
<title>Disclaimer — Specora</title>
<meta name="description" content="Important disclaimers regarding the use of Specora, product information accuracy, and third-party marketplace links.">
<meta name="keywords" content="disclaimer, Specora, legal, product information, third-party">
<meta property="og:title" content="Disclaimer — Specora">
<meta property="og:description" content="Important disclaimers regarding the use of Specora and third-party marketplace information.">
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
.content-inner h3{font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--black);margin-bottom:12px;margin-top:32px;}
.content-inner h3:first-of-type{margin-top:0;}
.content-inner p{font-size:15px;color:var(--gray-600);line-height:1.8;margin-bottom:16px;}
.content-inner ul{list-style:none;margin-bottom:24px;}
.content-inner ul li{font-size:15px;color:var(--gray-600);line-height:1.7;padding:6px 0 6px 28px;position:relative;}
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
      <span class="page-badge-text">DISCLAIMER</span>
    </div>
    <h1>Important <span class="hl-em">Disclaimer</span></h1>
    <p>Please read this disclaimer carefully. It outlines the limitations of our platform and your responsibilities as a user. By using Specora, you acknowledge and accept the terms of this disclaimer.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h3>General Information Only</h3>
    <p>Specora is a product discovery and comparison platform that aggregates product information from multiple third-party marketplaces for general informational purposes only. The product information, prices, ratings, reviews, and availability displayed on our platform are provided as a convenience to our users, and we do not guarantee the accuracy, completeness, reliability, or timeliness of any such information. Specora does not sell products directly to consumers. We are not a seller, retailer, vendor, or merchant. We do not process payments, handle inventory, fulfill orders, or ship products. All transactions between users and sellers occur directly on the respective third-party marketplace, including but not limited to Amazon, AliExpress, eBay, and Walmart, and Specora has no involvement in or control over any aspect of these transactions.</p>

    <h3>Product Information Accuracy</h3>
    <p>While we strive to provide accurate and up-to-date product information through regular data updates, prices, availability, and product details displayed on Specora may change without notice due to actions taken by the respective marketplaces or sellers. Product prices can fluctuate frequently based on market conditions, promotional events, seller pricing changes, and currency exchange rates. Availability status can change rapidly as inventory levels are updated by sellers. We strongly recommend that you verify all product information, including the final price, shipping costs, applicable taxes, product specifications, and availability, directly on the marketplace's website before making any purchase decision. Specora shall not be held responsible for any discrepancies between the information displayed on our platform and the actual information on the marketplace's website.</p>

    <h3>Affiliate Disclosure</h3>
    <p>Specora may participate in affiliate marketing programs with various marketplaces, including but not limited to Amazon, AliExpress, eBay, and Walmart. This means that when you click on a product link on Specora and subsequently make a purchase on the linked marketplace, we may earn a small commission or referral fee at no additional cost to you. These commissions are paid by the marketplace or seller and do not increase the price you pay for the product. The affiliate commissions we earn help us maintain, operate, and continuously improve the Specora platform, including covering costs for server infrastructure, data updates, and development. We are committed to providing unbiased product discovery and comparison services regardless of affiliate relationships, and our product rankings and search results are not influenced by affiliate commissions.</p>

    <h3>No Endorsement</h3>
    <p>The listing, display, or appearance of any product, brand, seller, or marketplace on Specora does not constitute an endorsement, recommendation, or approval by Specora. Product ratings, reviews, star ratings, and search rankings displayed on our platform are based on data and algorithms provided by the respective marketplaces and may not reflect your personal experience, preferences, or satisfaction with any particular product or seller. We do not verify the authenticity of product reviews or ratings, and we make no representations regarding the quality, safety, legality, or suitability of any product listed on our platform. Users are encouraged to conduct their own research and due diligence before making any purchase.</p>

    <h3>External Links</h3>
    <p>Specora contains numerous links to external websites and third-party marketplaces, including links to product listings on Amazon, AliExpress, eBay, Walmart, and other platforms. These links are provided solely as a convenience to our users and do not imply any endorsement, sponsorship, or affiliation. We have no control over the content, privacy practices, security practices, terms of use, or business operations of these external websites. We are not responsible for any damages, losses, claims, or liabilities arising from your access to or use of any third-party website, including but not limited to any purchases you make, any personal information you provide, or any interactions you have on those sites. Your use of third-party websites is subject to their respective terms and privacy policies, and we encourage you to review these documents before engaging in any activity on those platforms.</p>

    <h3>Limitation of Liability</h3>
    <p>To the fullest extent permitted by applicable law, Specora, its operators, employees, agents, and affiliates expressly disclaim all liability for any loss, damage, claim, or expense arising from or relating to your use of the platform, including but not limited to reliance on any product information displayed on Specora, transactions conducted on third-party marketplaces accessed through our platform, technical issues, downtime, service interruptions, or any direct, indirect, incidental, special, consequential, or punitive damages of any kind whatsoever. This limitation of liability applies regardless of the legal theory under which a claim is brought, whether in contract, tort, negligence, strict liability, or otherwise, and even if Specora has been advised of the possibility of such damages. Because some jurisdictions do not allow the exclusion or limitation of liability for certain types of damages, the above limitations may not apply to you in their entirety.</p>

    <h3>Changes to This Disclaimer</h3>
    <p>We reserve the right to modify, update, or amend this disclaimer at any time and without prior notice. Any changes will be effective immediately upon posting the revised disclaimer on this page. It is your responsibility to review this disclaimer periodically for any updates or changes. Your continued use of Specora after any modifications to this disclaimer constitutes your acceptance of and agreement to be bound by the updated terms. If you do not agree with any part of this disclaimer, you should discontinue your use of the platform immediately.</p>

    <h3>Contact Information</h3>
    <p>If you have any questions, concerns, or requests for clarification regarding this disclaimer or any aspect of how Specora operates, please do not hesitate to contact us through our contact page. We are committed to transparency and will make every effort to respond to your inquiries in a timely and thorough manner.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
