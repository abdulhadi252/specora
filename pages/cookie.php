<?php
// ================================================
// cookie.php — Specora Cookie Policy
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
<title>Cookie Policy — Specora</title>
<meta name="description" content="Learn how Specora uses cookies and similar tracking technologies to enhance your browsing experience.">
<meta name="keywords" content="cookie policy, cookies, tracking, Specora, data privacy">
<meta property="og:title" content="Cookie Policy — Specora">
<meta property="og:description" content="Learn how Specora uses cookies and similar tracking technologies.">
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
.content-inner ul{list-style:none;margin-bottom:24px;}
.content-inner ul li{font-size:15px;color:var(--gray-600);line-height:1.7;padding:6px 0 6px 28px;position:relative;}
.content-inner ul li::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:6px;height:6px;border-radius:50%;background:var(--gold);}
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
      <span class="page-badge-text">COOKIE POLICY</span>
    </div>
    <h1>How We Use <span class="hl-em">Cookies</span></h1>
    <p>This policy explains what cookies are, how we use them on Specora, and the choices available to you regarding their use. Understanding our use of cookies will help you make informed decisions about your privacy.</p>
    <p class="last-updated">Last Updated: July 2025</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h3>What Are Cookies?</h3>
    <p>Cookies are small text files, typically consisting of letters and numbers, that are stored on your computer, tablet, smartphone, or other device when you visit a website. They are widely used by websites to make them work more efficiently, remember your preferences, provide a personalized browsing experience, and gather anonymous information about how visitors use the site. Cookies can be classified as either session cookies, which are temporary and are deleted from your device when you close your browser, or persistent cookies, which remain on your device for a specified period or until you manually delete them.</p>

    <h3>How We Use Cookies on Specora</h3>
    <p>Specora uses cookies and similar local storage technologies for several purposes that are essential to providing you with a smooth and functional browsing experience. Essential cookies are required for the platform to function properly, enabling core features such as site navigation, search functionality, and secure access to different areas of the platform. Without these essential cookies, certain features of Specora may not work correctly or may become unavailable to you. Preference cookies allow us to remember your settings and preferences, such as saved searches, filter selections, viewing preferences, and other customizable aspects of the platform, so that you do not have to reconfigure these settings each time you visit. Analytics cookies help us understand how users interact with Specora by collecting anonymous information about which pages are visited most frequently, how users navigate through the platform, where they encounter errors or difficulties, and what features are most popular. This information allows us to continuously improve the performance, usability, and overall user experience of Specora. Session cookies are temporary cookies that expire when you close your browser and are used to maintain your session state as you navigate through different pages of the platform during a single visit.</p>

    <h3>Types of Cookies and Similar Technologies We Use</h3>
    <p>We use several types of cookies and local storage technologies on Specora. Session cookies are stored temporarily on your device during your browsing session and are automatically deleted when you close your browser. These cookies are essential for maintaining your session and tracking your movement through the platform during a single visit. Local storage is a similar technology that allows us to store data persistently on your device, such as your search history, recently viewed products, and user interface preferences. Unlike cookies, local storage data is not automatically transmitted to our servers and remains on your device until it is cleared by you or our application. Analytics cookies are persistent cookies that may remain on your device for up to twelve months and help us collect aggregated, anonymous data about usage patterns on Specora, including which features are used most, how users arrive at our platform, and how we can improve the overall experience. We do not use any third-party advertising cookies, tracking cookies from social media platforms, or marketing cookies that would allow third parties to track your browsing activity across different websites.</p>

    <h3>Your Cookie Choices and Controls</h3>
    <p>You have full control over how cookies are used on your device, and you can manage your cookie preferences at any time through your browser settings. Most web browsers provide options to block all cookies, block only third-party cookies, delete existing cookies, or set preferences for how cookies are handled on a per-site basis. You can also use your browser's private or incognito browsing mode, which automatically limits or blocks the storage of cookies during your browsing session and deletes any cookies created when you close the private window. Additionally, you can clear your browser's cache and cookies at any time through your browser's settings or history menu. Please be aware that if you choose to block or delete essential cookies, certain features and functionality of Specora may be affected, and some parts of the platform may not work as intended or may become completely unavailable to you. Blocking essential cookies may also affect your ability to search for products, save preferences, or navigate the platform effectively.</p>

    <h3>Third-Party Cookies and Services</h3>
    <p>Specora does not use third-party tracking cookies from advertisers, social media platforms, marketing networks, or data brokers. The only external services we use on the platform are limited to hosting infrastructure and basic analytics services, and the cookies or similar technologies used by these services are strictly limited to essential functionality required for the platform to operate. These third-party service providers are contractually bound to use any data collected solely for the purpose of providing their services to us and are prohibited from using such data for their own purposes, including tracking your activity across other websites. We do not allow any advertising networks, social media companies, or other third-party trackers to place cookies on Specora for the purpose of building a profile of your interests or targeting you with advertisements.</p>

    <h3>Updates to This Cookie Policy</h3>
    <p>We may update this Cookie Policy from time to time to reflect changes in our use of cookies and similar technologies, changes in applicable legal or regulatory requirements, or changes in the features and functionality of the Specora platform. Any material changes to this policy will be posted on this page with an updated "Last Updated" date, and we encourage you to review this policy periodically to stay informed about how we use cookies and how you can control their use. Your continued use of Specora after any changes to this policy constitutes your acceptance of the updated terms.</p>

    <h3>Contact Information</h3>
    <p>If you have any questions, concerns, or requests for additional information about our use of cookies and similar technologies on Specora, please do not hesitate to contact us through our contact page. We are committed to transparency about our data practices and will respond to your inquiries promptly and thoroughly.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
