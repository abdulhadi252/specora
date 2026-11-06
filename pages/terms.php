<?php
// ================================================
// terms.php — Specora Terms & Conditions
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
<title>Terms & Conditions — Specora</title>
<meta name="description" content="Read Specora's terms and conditions governing the use of our product discovery platform.">
<meta name="keywords" content="terms and conditions, terms of service, Specora, legal">
<meta property="og:title" content="Terms & Conditions — Specora">
<meta property="og:description" content="Read Specora's terms and conditions governing the use of our product discovery platform.">
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
.page-hero .last-updated{font-size:13px;color:var(--gray-400);}
.content-section{padding:80px 48px;background:var(--white);}
.content-section:nth-child(even){background:var(--off-white);}
.content-inner{max-width:900px;margin:0 auto;}
.content-inner h2{font-family:var(--font-display);font-size:clamp(22px,2.5vw,32px);font-weight:700;color:var(--black);margin-bottom:20px;letter-spacing:-.5px;}
.content-inner h2 .hl-em{color:var(--gold-dark);}
.content-inner h3{font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--black);margin-bottom:12px;margin-top:32px;}
.content-inner h3:first-of-type{margin-top:0;}
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
      <span class="page-badge-text">TERMS & CONDITIONS</span>
    </div>
    <h1>Terms & <span class="hl-em">Conditions</span></h1>
    <p>Please read these terms carefully before using Specora. By accessing our platform, you agree to be bound by these terms. These terms constitute a legally binding agreement between you and Specora governing your use of the product discovery platform.</p>
    <p class="last-updated">Last Updated: July 2025</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h3>1. Acceptance of Terms</h3>
    <p>By accessing or using Specora, which we refer to as "the Platform," you agree to be bound by these Terms and Conditions in their entirety. These terms form a legally enforceable agreement between you, the user, and Specora. If you do not agree with any part of these terms, whether in whole or in part, you should immediately discontinue your use of the platform. We recommend that you read these terms thoroughly before using any of the services offered by Specora, and we encourage you to revisit this page periodically as the terms may be updated from time to time without prior notice.</p>

    <h3>2. Description of Service</h3>
    <p>Specora is a product discovery and comparison platform that aggregates product information from multiple third-party marketplaces, including but not limited to Amazon, AliExpress, eBay, and Walmart. Our platform allows users to search for products, compare prices across different marketplaces, filter results by various criteria such as ratings and price ranges, and access direct links to product listings on the respective marketplace websites. It is important to understand that Specora does not sell products directly to consumers, does not process any form of payment, does not handle inventory or fulfillment, and is not a party to any transaction that occurs between users and third-party sellers on the linked marketplaces. Our role is strictly limited to providing a search and comparison service that helps users make more informed purchasing decisions.</p>

    <h3>3. User Responsibilities</h3>
    <p>As a user of Specora, you accept certain responsibilities designed to ensure that the platform remains safe, functional, and accessible for all users. You must not use Specora for any unlawful purpose or in violation of any applicable local, national, or international laws or regulations. You must not attempt to disrupt, damage, impair, or gain unauthorized access to the platform's functionality, servers, networks, or security systems. You are expressly prohibited from scraping, crawling, extracting, or harvesting data from Specora using automated tools, bots, scripts, or any other mechanical or electronic means without our explicit written permission. You must not upload or transmit any viruses, malware, or other harmful code, nor engage in any activity that could overload or interfere with the proper operation of the platform. Any violation of these responsibilities may result in immediate termination of your access to Specora and may subject you to legal liability.</p>

    <h3>4. Intellectual Property</h3>
    <p>All content, trademarks, service marks, trade names, logos, and intellectual property displayed on Specora, including but not limited to the platform's design, source code, layout, graphics, and original written content, are owned by or licensed to Specora and are protected by applicable intellectual property laws. You may not reproduce, distribute, modify, create derivative works from, publicly display, or otherwise use any of Specora's intellectual property without our prior written consent. Product data, including images, descriptions, prices, and specifications, displayed on Specora are sourced from third-party marketplaces and remain the property of their respective owners. Such data is used solely for the purpose of providing our product discovery service and is not claimed as our own.</p>

    <h3>5. Third-Party Links</h3>
    <p>Specora contains links to third-party websites and marketplaces, including but not limited to Amazon, AliExpress, eBay, and Walmart. These links are provided solely as a convenience to our users and do not imply any endorsement, approval, or affiliation with the linked websites. We have no control over the content, privacy practices, terms of use, security practices, or business operations of these external sites. We are not responsible for any damages, losses, or liabilities arising from your use of or reliance on any content, products, or services available on these third-party websites. Your interactions with these third parties, including any purchases you make, are solely between you and them, and you should review their respective terms and privacy policies before engaging in any transaction.</p>

    <h3>6. Disclaimer of Warranties</h3>
    <p>Specora is provided on an "as is" and "as available" basis without any warranties of any kind, whether express, implied, or statutory. To the fullest extent permitted by applicable law, we disclaim all warranties, including but not limited to implied warranties of merchantability, fitness for a particular purpose, title, and non-infringement. We do not guarantee that the platform will be uninterrupted, timely, secure, or error-free, nor do we make any warranty regarding the accuracy, completeness, reliability, or currency of any product information, pricing, ratings, or availability displayed on the platform. Product information, pricing, and availability are subject to change by the respective marketplaces at any time without notice, and we are not responsible for any discrepancies between the information displayed on Specora and the information on the marketplace's website.</p>

    <h3>7. Limitation of Liability</h3>
    <p>To the maximum extent permitted by applicable law, Specora, its operators, employees, agents, and affiliates shall not be liable for any indirect, incidental, special, consequential, exemplary, or punitive damages arising out of or relating to your use of, or inability to use, the platform. This limitation of liability applies to all causes of action, whether based on contract, tort, negligence, strict liability, or any other legal theory, and includes but is not limited to damages for loss of profits, loss of data, loss of business opportunity, personal injury, or property damage, even if we have been advised of the possibility of such damages. Because some jurisdictions do not allow the exclusion or limitation of liability for consequential or incidental damages, the above limitation may not apply to you in its entirety.</p>

    <h3>8. Indemnification</h3>
    <p>You agree to indemnify, defend, and hold harmless Specora, its operators, officers, directors, employees, agents, and affiliates from and against any and all claims, demands, losses, liabilities, costs, and expenses, including reasonable attorneys' fees, arising out of or relating to your use of the platform, your violation of these terms, or your violation of any rights of another person or entity. We reserve the right, at our own expense, to assume the exclusive defense and control of any matter otherwise subject to indemnification by you, in which case you agree to cooperate with us in asserting any available defenses.</p>

    <h3>9. Modifications to Terms</h3>
    <p>We reserve the right to modify, update, or replace these terms at any time and for any reason at our sole discretion. Any changes will be effective immediately upon posting the revised terms on this page, with the updated date noted at the top of the page. It is your responsibility to review these terms periodically for any changes. Your continued use of Specora after any modifications to the terms constitutes your acceptance of and agreement to be bound by the updated terms. If you do not agree to any modifications, you should discontinue your use of the platform immediately.</p>

    <h3>10. Governing Law</h3>
    <p>These terms shall be governed by, construed, and enforced in accordance with the laws of Pakistan, without regard to its conflict of law provisions. Any disputes, claims, or controversies arising out of or relating to these terms or your use of Specora shall be subject to the exclusive jurisdiction of the courts located in Karachi, Pakistan. You agree to submit to the personal jurisdiction of such courts and waive any objection to the venue of such courts.</p>

    <h3>11. Contact Information</h3>
    <p>If you have any questions, concerns, or requests regarding these Terms and Conditions, please do not hesitate to contact us through our contact page. We will make every effort to respond to your inquiry in a timely manner and address any concerns you may have about how these terms apply to your use of Specora.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
