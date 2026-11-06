<?php
// ================================================
// privacy.php — Specora Privacy Policy
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
<title>Privacy Policy — Specora</title>
<meta name="description" content="Specora's privacy policy. Learn how we collect, use, and protect your personal information.">
<meta name="keywords" content="privacy policy, data protection, Specora, personal information">
<meta property="og:title" content="Privacy Policy — Specora">
<meta property="og:description" content="Learn how Specora collects, uses, and protects your personal information.">
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
      <span class="page-badge-text">PRIVACY POLICY</span>
    </div>
    <h1>Our Commitment to Your <span class="hl-em">Privacy</span></h1>
    <p>We take your privacy seriously. This policy describes how Specora collects, uses, and protects your information when you use our platform.</p>
    <p class="last-updated">Last Updated: July 2025</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h3>1. Information We Collect</h3>
    <p>When you use Specora, we collect certain types of information to provide, maintain, and improve our product discovery platform. The information we collect includes search queries that you enter into our platform, which we use to improve product discovery and personalize your search experience over time. We also automatically collect usage data about how you interact with Specora, including which pages you visit, how much time you spend on each page, which products you click on, and your navigation patterns across the platform. Additionally, we collect device information such as your browser type and version, operating system, IP address, and screen resolution to help us optimize the platform for different devices and troubleshoot technical issues. We use cookies and similar tracking technologies to enhance your experience on Specora, and you can find more details about this in our Cookie Policy. If you choose to contact us through our contact form, we collect your name, email address, and the contents of your message so that we can respond to your inquiry effectively.</p>

    <h3>2. How We Use Your Information</h3>
    <p>We use the information we collect for a variety of purposes that are essential to providing you with a high-quality product discovery experience. The primary purpose is to provide, maintain, and continuously improve our product discovery platform, ensuring that it remains fast, reliable, and useful for our users. We use your search queries and browsing behavior to personalize your search experience, showing you more relevant results based on your interests and previous interactions with the platform. We analyze aggregated usage patterns to understand how users interact with Specora, which helps us identify areas for improvement, optimize performance, and develop new features that address real user needs. If you contact us with questions or concerns, we use the information you provide to respond to your inquiries and provide customer support. In cases where you have opted in, we may send occasional updates, newsletters, or information about new features, but you can unsubscribe from these communications at any time. We also use collected information to detect, prevent, and address technical issues, security threats, or abusive behavior that could harm the platform or its users.</p>

    <h3>3. Information Sharing</h3>
    <p>We take your privacy seriously and do not sell your personal information to third parties under any circumstances. However, there are limited circumstances in which we may share information with third parties. We may share data with trusted third-party service providers who assist us in operating our platform, such as hosting providers, analytics services, and email communication services. These service providers are contractually obligated to handle your information securely, to use it only for the specific purposes we authorize, and to delete it when it is no longer needed. We may disclose information if required to do so by applicable law, regulation, or legal process, or in response to valid legal requests from government authorities. In the event of a merger, acquisition, reorganization, or sale of all or substantially all of our assets, user information may be transferred as part of that transaction, and we will notify you of any such change in ownership or control of your personal information.</p>

    <h3>4. Data Security</h3>
    <p>We implement a comprehensive set of appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include encryption of data in transit using secure protocols, regular security audits and vulnerability assessments, secure server infrastructure with restricted access controls, and strict internal policies governing how our team members handle user data. While we strive to use commercially acceptable means to protect your personal information, no method of transmission over the Internet or method of electronic storage is 100 percent secure, and we cannot guarantee absolute security. We encourage you to use strong passwords and take appropriate measures to protect your own devices and accounts.</p>

    <h3>5. Your Rights</h3>
    <p>Depending on your location and applicable data protection laws, you may have certain rights regarding your personal data. These rights include the right to request access to a copy of the personal data we hold about you, the right to request that we correct any inaccurate or incomplete data we have about you, the right to request the deletion of your personal data subject to certain legal obligations that may require us to retain certain information, and the right to opt out of or unsubscribe from marketing communications at any time. If you wish to exercise any of these rights, please contact us through the contact page, and we will respond to your request in accordance with applicable data protection laws. We may need to verify your identity before processing your request to ensure the security of your personal information.</p>

    <h3>6. Third-Party Links</h3>
    <p>Specora contains links to third-party marketplaces including Amazon, AliExpress, eBay, and Walmart, as well as other external websites that are not operated by us. When you click on a link to a third-party website, you will be subject to that website's own privacy practices, data collection policies, and terms of use. We are not responsible for the privacy practices, content, or security of these third-party sites, and we strongly encourage you to review their privacy policies and terms of service before providing any personal information or making any purchases on their platforms. This Privacy Policy applies solely to information collected by Specora and does not govern the data practices of any third-party websites or services.</p>

    <h3>7. Changes to This Policy</h3>
    <p>We may update this Privacy Policy from time to time to reflect changes in our data practices, legal requirements, or the features and functionality of our platform. When we make material changes to this policy, we will update the "Last Updated" date at the top of this page and may provide additional notice through the platform or via email if we have your contact information. We encourage you to review this Privacy Policy periodically to stay informed about how we are protecting your information. Your continued use of Specora after any changes to this policy constitutes your acceptance of the updated terms.</p>

    <h3>8. Contact Us</h3>
    <p>If you have any questions, concerns, requests, or complaints regarding this Privacy Policy or our data practices, please do not hesitate to contact us through our contact page. We are committed to addressing your concerns and will respond to your inquiry as promptly as possible. We value your trust and are dedicated to protecting your privacy as you use Specora to discover and compare products across the world's leading marketplaces.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
