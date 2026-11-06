<?php
// ================================================
// returns.php — Specora Returns & Refunds
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
<title>Returns & Refunds — Specora</title>
<meta name="description" content="Learn about the return and refund policies for products found through Specora's marketplace search.">
<meta name="keywords" content="returns, refunds, return policy, Specora, product returns">
<meta property="og:title" content="Returns & Refunds — Specora">
<meta property="og:description" content="Learn about the return and refund policies for products found through Specora.">
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
.policy-table{width:100%;border-collapse:collapse;margin:24px 0;font-size:14px;}
.policy-table th{text-align:left;padding:12px 16px;background:var(--off-white);border-bottom:2px solid var(--gray-200);font-family:var(--font-display);font-weight:700;color:var(--black);}
.policy-table td{padding:12px 16px;border-bottom:1px solid var(--gray-200);color:var(--gray-600);line-height:1.6;}
.policy-table tr:hover td{background:var(--off-white);}
.alert-box{background:rgba(234,189,120,.08);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius);padding:20px 24px;margin:24px 0;}
.alert-box p{font-size:14px;color:var(--gray-600);margin-bottom:0;}
.alert-box strong{color:var(--black);}
@media(max-width:1024px){.page-hero{padding:48px 24px 56px;}nav{padding:0 24px;}.nav-links,.nav-actions{display:none;}.hamburger{display:flex;}.mobile-menu{top:68px;}footer{padding:52px 24px 28px;}.policy-table{font-size:13px;}}
@media(max-width:768px){.page-hero{padding:40px 20px 44px;margin-top:62px;}nav{padding:0 20px;height:62px;}.mobile-menu{top:62px;}.content-section{padding:56px 20px;}.policy-table,.policy-table th,.policy-table td{display:block;}.policy-table th{display:none;}.policy-table td{padding:8px 16px;border-bottom:none;}.policy-table td::before{content:attr(data-label);display:inline-block;font-weight:700;color:var(--black);width:120px;}.policy-table tr{border-bottom:2px solid var(--gray-200);display:block;padding:8px 0;}footer{padding:44px 20px 24px;}}
@media(max-width:480px){.page-hero{padding:32px 16px 36px;margin-top:58px;}.content-section{padding:44px 16px;}nav{height:58px;padding:0 16px;}.mobile-menu{top:58px;}footer{padding:36px 16px 20px;}}
</style>
</head>
<body>
<?php $basePath = '../'; require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="page-hero">
  <div class="page-hero-inner">
    <div class="page-badge">
      <span class="page-badge-text">RETURNS & REFUNDS</span>
    </div>
    <h1>Returns & <span class="hl-em">Refunds</span></h1>
    <p>Understanding return and refund policies across the marketplaces we search. Please note that Specora is a discovery platform and all purchases are handled by the respective marketplace where you complete your transaction.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Return Policies by <span class="hl-em">Marketplace</span></h2>
    <p>Each marketplace that Specora searches has its own established return and refund policy, and it is important to understand these differences before making a purchase. Amazon generally offers a thirty-day return window from the date of delivery for most items, provided the product is in new, unused condition with all original packaging and accessories. Once the returned item is received and inspected, Amazon typically processes refunds within three to five business days. AliExpress offers a more variable return window ranging from fifteen to thirty days depending on the individual seller's policy, and the refund timeline after approval is usually five to ten business days. eBay, like Amazon, offers a standard thirty-day return window for most items, with refunds typically processed within two to five business days after the return is received. Walmart provides a thirty-day return window for most items purchased through their platform, with refunds generally processed within five to seven business days after the return is received and inspected.</p>
    <p>It is critically important to understand that Specora does not process returns or refunds directly. We are a product discovery and comparison platform, not a seller or retailer. All return and refund requests must be initiated and processed through the marketplace where you made your purchase. We strongly recommend that you review the specific return policy of the marketplace and seller before completing any purchase, as policies can vary not only between marketplaces but also between different sellers on the same marketplace, and certain product categories such as electronics, personal care items, or customized goods may have different return conditions or be ineligible for returns altogether.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>How to <span class="hl-em">Return a Product</span></h2>
    <p>The process of returning a product varies slightly between marketplaces, but the general steps are consistent across most platforms. First, you need to log in to your account on the marketplace where you made the original purchase. Once logged in, navigate to the "My Orders" or "Order History" section of your account dashboard and locate the specific product you wish to return. From there, you should find an option labeled "Return or Replace Items" or similar wording, and clicking on this will guide you through the marketplace's return initiation process. You will typically be asked to select a reason for the return from a provided list of options, which may include reasons such as defective product, wrong item shipped, or simply a change of mind, depending on the marketplace's policy.</p>
    <p>After you submit your return request and it is approved by the marketplace or seller, you will be provided with a return shipping label that you need to print and attach to your package. It is essential that you pack the item securely in its original packaging with all accessories, manuals, and any included documentation to ensure that the return is accepted without issue. Once you ship the item back using the provided label, you should keep the tracking number so you can monitor the package's progress back to the seller or marketplace warehouse. After the returned item is received and inspected to confirm that it meets the marketplace's return conditions, the refund will be processed and credited back to your original payment method. The time it takes for the refund to appear in your account depends on the marketplace's refund processing timeline as well as your payment provider's processing speed.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Important Things to <span class="hl-em">Know</span></h2>
    <p>Before initiating a return, there are several important factors to consider that can significantly impact the success and cost of your return. First and foremost, each marketplace has a specific return window during which you must initiate the return process. This window typically ranges from fifteen to thirty days from the date of delivery, depending on the marketplace and seller. If you miss this window, your return request will almost certainly be rejected, and you will be unable to return the item or receive a refund. It is therefore essential that you inspect your purchase promptly upon delivery and initiate any return as soon as you identify an issue.</p>
    <p>The condition of the product is another critical factor. Most marketplaces require the product to be returned in its original condition, meaning it should be unused, unopened where applicable, and include all original packaging, accessories, manuals, and promotional items that came with it. Products that show signs of use, have missing parts or packaging, or are damaged due to customer mishandling may not qualify for a full refund, and in some cases, the return may be rejected entirely. Return shipping costs are another important consideration. Some marketplaces and sellers offer free return shipping, particularly for defective or incorrect items, while others may deduct the return shipping cost from your refund amount. For certain product categories, especially electronics, large appliances, or high-value items, sellers may charge restocking fees that are deducted from the refund to cover the cost of inspecting, repackaging, and restocking the returned item. These fees vary by marketplace and seller and can range from a nominal percentage to a significant portion of the purchase price.</p>
    <p>For products shipped internationally, the return process can be considerably more complex and expensive. International return shipping costs can be substantial, sometimes approaching or even exceeding the original purchase price of the item, and the logistics of returning a package across international borders involve customs documentation, longer transit times, and potentially additional fees. We strongly recommend that you carefully review the return policy before making any purchase from an international seller, particularly for high-value items. If you encounter any issues during the return process, such as a seller refusing to accept a legitimate return or delaying the refund, most marketplaces have dedicated customer support teams and buyer protection programs that can help mediate disputes between buyers and sellers. While we at Specora are happy to provide general guidance about the return process, please understand that we cannot process returns or refunds on behalf of any marketplace, as all transactions occur directly between you and the marketplace or seller.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Frequently Asked <span class="hl-em">Questions</span></h2>
    <p><strong>Can I return a product if I simply changed my mind about the purchase?</strong><br/>Most marketplaces do allow returns for change of mind within the applicable return window, provided that the item is in its original, unused condition with all packaging and accessories intact. However, some sellers may charge a restocking fee or require you to pay the return shipping costs, so it is advisable to check the specific return policy before initiating the return.</p>
    <p><strong>What should I do if the product arrives damaged or defective?</strong><br/>If your product arrives damaged, defective, or not as described, you should contact the marketplace's customer support team immediately. Most marketplaces have dedicated procedures for damaged or defective items, which typically include free return shipping and expedited refund processing. Be sure to take photographs of the damage and save all packaging materials as evidence, as the marketplace may request this documentation to process your claim.</p>
    <p><strong>How long does it typically take to receive a refund after returning a product?</strong><br/>Refund timelines vary by marketplace. Amazon generally processes refunds within three to five business days after receiving and inspecting the returned item. AliExpress may take five to ten business days after the return is approved. eBay usually processes refunds within two to five business days, while Walmart typically takes five to seven business days. In all cases, the refund is credited back to your original payment method, and the time it takes for the funds to appear in your account also depends on your payment provider's processing times.</p>
    <p><strong>What can I do if the seller refuses to accept my return request?</strong><br/>If a seller rejects your return request or is unresponsive, you can escalate the issue to the marketplace's resolution center or buyer protection program. All major marketplaces, including Amazon, eBay, and AliExpress, have formal dispute resolution processes and buyer protection policies designed to mediate conflicts between buyers and sellers. These programs can help you obtain a refund even if the seller is uncooperative, provided that your claim is valid and falls within the marketplace's protection guidelines.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
