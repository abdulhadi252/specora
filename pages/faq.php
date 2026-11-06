<?php
// ================================================
// faq.php — Specora FAQ / Help Center
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
<title>FAQ / Help Center — Specora</title>
<meta name="description" content="Frequently asked questions about Specora. Find answers about product discovery, price comparison, marketplaces, and more.">
<meta name="keywords" content="FAQ, help center, Specora help, product discovery, frequently asked questions">
<meta property="og:title" content="FAQ / Help Center — Specora">
<meta property="og:description" content="Find answers to frequently asked questions about Specora's product discovery platform.">
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
.content-inner p{font-size:15px;color:var(--gray-600);line-height:1.8;margin-bottom:16px;}
.faq-accordion{display:flex;flex-direction:column;gap:12px;margin-top:8px;}
.faq-item{border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;transition:border-color .3s ease,box-shadow .3s ease;background:var(--white);}
.faq-item:hover{border-color:var(--gold-light);}
.faq-item.open{border-color:var(--gold);box-shadow:0 4px 20px rgba(234,189,120,.12);}
.faq-trigger{width:100%;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 24px;background:none;border:none;cursor:pointer;text-align:left;font-family:var(--font-body);font-size:15px;font-weight:600;color:var(--black);transition:color .2s;line-height:1.5;}
.faq-trigger:hover{color:var(--gold-dark);}
.faq-item.open .faq-trigger{color:var(--gold-dark);}
.faq-indicator{width:28px;height:28px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .35s ease;font-size:16px;font-weight:300;color:var(--gray-400);line-height:1;}
.faq-item.open .faq-indicator{background:var(--gold);color:var(--black);transform:rotate(45deg);}
.faq-answer{max-height:0;overflow:hidden;transition:max-height .4s ease,opacity .35s ease,padding .3s ease;opacity:0;padding:0 24px;}
.faq-item.open .faq-answer{max-height:800px;opacity:1;padding:0 24px 22px;}
.faq-answer p{font-size:14px;color:var(--gray-600);line-height:1.8;margin:0;}
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
      <span class="page-badge-text">HELP CENTER</span>
    </div>
    <h1>How Can We <span class="hl-em">Help You</span>?</h1>
    <p>Find answers to commonly asked questions about Specora, product discovery, and how to get the most out of our platform. Browse through our comprehensive list of questions and detailed answers below.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Frequently Asked <span class="hl-em">Questions</span></h2>
    <p>Welcome to the Specora Help Center. Below you will find comprehensive answers to the most common questions about our product discovery platform. Whether you are a first-time visitor trying to understand how Specora works, a regular user looking to optimize your search experience, or someone curious about how we operate as a business, this collection of frequently asked questions has been carefully curated to address the topics that matter most to our users. We have organized the questions to cover everything from basic platform usage and marketplace information to technical support and privacy practices, so you can quickly find the information you need without having to search through multiple pages.</p>
    <p>If you are new to Specora, we recommend starting with the first few questions that explain what we do, how our search engine works, and whether you need to create an account to get started. For more experienced users, the later sections cover advanced topics such as price accuracy, data freshness, and how we generate revenue while keeping the platform free. Our goal is to make this FAQ as comprehensive and helpful as possible, and we continuously update it based on the questions and feedback we receive from our community of users across Pakistan.</p>
    <div class="faq-accordion">
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>1. What is Specora and how does it work?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Specora is a product discovery and comparison platform that aggregates product information from multiple major online marketplaces into a single, unified search interface. When you enter a search query on Specora, our platform retrieves relevant product listings from Amazon, AliExpress, eBay, and Walmart simultaneously, displaying them in organized results grouped by marketplace. You can compare prices across different marketplaces, filter results by ratings and price ranges, and click through to the marketplace of your choice to complete your purchase. Specora is free to use and does not sell products directly, process payments, or handle any aspect of the transaction between you and the seller.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>2. Is Specora completely free to use?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Yes, Specora is one hundred percent free for all users. You can search for products, compare prices across multiple marketplaces, filter and sort results, and browse product details without any charges, subscription fees, or hidden costs. Specora may earn a small commission through affiliate marketing programs when you click on a product link and make a purchase on the linked marketplace, but this commission is paid by the marketplace or seller and does not increase the price you pay for the product in any way. Your use of Specora will always remain free, regardless of how many searches you perform or how many products you explore.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>3. Do I need to create an account to use Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>No account is required to use Specora. You can start searching for products and browsing results immediately without any registration, login, or personal information. However, creating a free optional account gives you access to additional features designed to enhance your product discovery experience, including the ability to save your favorite products for later reference, maintain a history of your recent searches, receive personalized product recommendations based on your browsing behavior, and customize your search preferences and filter settings across sessions. An account also allows you to access your saved data from any device when you log in.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>4. Which marketplaces does Specora search across?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Specora currently searches across four major global marketplaces: Amazon, which is the world's largest online retailer with an extensive catalog of products across virtually every category; AliExpress, a leading Chinese e-commerce platform known for competitive pricing and a vast selection of products from international sellers; eBay, a global online marketplace that connects individual sellers and businesses with buyers around the world; and Walmart, one of the largest retail corporations in the world with a comprehensive online product catalog. Together, these four marketplaces provide access to millions of products across all categories, giving you a comprehensive and competitive view of pricing and availability.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>5. How current is the product data displayed on Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>We update our product database on a regular basis to ensure that pricing, availability, and product information displayed on Specora is as current and accurate as possible. However, prices, stock levels, and product details on the source marketplaces can change rapidly due to factors such as promotional events, inventory updates, seller pricing changes, and currency fluctuations. We therefore recommend that you always verify the final price, shipping costs, applicable taxes, and product availability directly on the marketplace's product page before making any purchase decision, as Specora cannot guarantee that the information displayed on our platform matches the current information on the marketplace at any given moment.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>6. How does the product search engine work on Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Our search engine uses intelligent query parsing technology that analyzes your search terms to identify key components such as the product name, brand, category, desired color, price range, rating preferences, and even specific marketplace names. It then searches across all four marketplaces simultaneously and returns results that are organized by marketplace for easy comparison. Results are sorted by default based on a combination of relevance to your search query and product ratings, ensuring that the most relevant and highly-rated products appear at the top of your results. You can further refine your results using the available filter options.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>7. Can I search for products from a specific marketplace only?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Yes, you can easily focus your search on a specific marketplace by including the marketplace name in your search query. For example, searching for "amazon wireless headphones" will prioritize results from Amazon while still showing relevant results from other marketplaces. You can use this feature with any of the four marketplaces we support, including aliexpress, ebay, and walmart. This is particularly useful when you have a preferred marketplace for certain types of purchases, when you want to compare prices across marketplaces for the same product, or when you know that the product you are looking for is more likely to be available on a specific platform.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>8. How do the rating filters work on Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>The rating filter allows you to find products that meet your quality standards by specifying a minimum rating threshold in your search query. For example, if you search for "blue shoes 4.5", Specora will display products with ratings starting from four point five and descending. This approach ensures that you see the highest-rated products first, giving you access to the most popular and well-reviewed items in your search category. You can adjust the rating threshold up or down depending on how selective you want to be, with higher thresholds yielding fewer but typically better-reviewed products and lower thresholds returning a broader selection.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>9. How do I purchase a product that I find on Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>When you find a product that interests you on Specora, click on the product listing to view detailed information including price, rating, and a brief description. From the product detail view, you will find a link or button that takes you directly to the product listing on the marketplace where it is sold. Clicking this link will redirect you to the marketplace's website, where you can review the product in full detail, check the most current pricing and availability, and complete your purchase through the marketplace's standard checkout process. Specora does not handle payments, process transactions, or collect any payment information, as all purchases occur entirely on the respective marketplace platform.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>10. Does Specora handle shipping, returns, or refunds?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>No, Specora does not handle shipping, returns, or refunds in any capacity. All shipping arrangements, return processes, and refund requests are handled entirely by the marketplace or seller where you made your purchase. Each marketplace has its own shipping policies, delivery timeframes, return windows, and refund procedures that you must follow if you need to return a product or request a refund. We provide detailed information about shipping policies and return procedures on our Shipping Information and Returns and Refunds pages, but we cannot process returns, issue refunds, or provide updates on individual shipments on behalf of any marketplace. If you have issues with a purchase, please contact the marketplace's customer support team directly.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>11. Why are some products not appearing in my search results?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Several factors can affect whether a product appears in your search results on Specora. The product may not be available in our database if it has not been indexed yet or if our data update cycle has not captured the latest listings from the marketplace. Your search filters, such as rating thresholds or specific keywords, may be too restrictive and are excluding relevant products from the results. The product may have been removed or become unavailable on the source marketplace since our last data update. We recommend trying broader search terms, removing any filters you have applied, or checking the marketplace directly if you know the specific product you are looking for but cannot find it on Specora.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>12. How accurate are the prices displayed on Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>We strive to provide accurate and up-to-date pricing information through regular data updates from the source marketplaces. However, prices on marketplaces can change frequently due to promotional events, seller pricing adjustments, currency exchange rate fluctuations, and time-limited deals. The price displayed on Specora may not always reflect the most current price on the marketplace, especially for products that are subject to frequent price changes or limited-time promotions. We strongly recommend clicking through to the marketplace product page to verify the final price, including any applicable taxes, shipping costs, and discounts, before completing your purchase.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>13. Can I save products or searches for later on Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>If you create a free account on Specora, you can save products to a favorites list that allows you to quickly access them later without having to search again. You can also maintain a history of your recent searches, making it easy to revisit product categories or specific queries you have used in the past. These features are designed to make your product discovery experience more efficient and convenient, especially if you are researching multiple products or comparing options over several days. Without an account, your search history and favorites are stored temporarily in your browser's local storage and may be cleared if you delete your browser data or switch devices.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>14. What kind of customer support does Specora offer?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Specora offers customer support to help you with questions, issues, or feedback related to using our product discovery platform. Our support team can assist you with optimizing your search queries, understanding how to interpret product data displayed on the platform, troubleshooting technical issues you may encounter while using Specora, and providing general guidance about the product discovery process. For issues related to purchases, shipping, returns, or refunds on the marketplace where you made your purchase, we can provide general information and direct you to the appropriate marketplace's customer support resources, but we cannot intervene in transactions that occur on third-party platforms. You can reach our support team through the contact form on our website, and we aim to respond to all inquiries within twenty-four hours on business days.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>15. Does Specora use cookies or track my browsing activity?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Yes, Specora uses cookies and similar local storage technologies for essential functionality and to improve your browsing experience on the platform. We use session cookies to maintain your browsing session as you navigate through the site, preference cookies to remember your settings and saved preferences, and analytics cookies to understand how users interact with Specora so we can improve performance and usability. We do not use third-party tracking cookies from advertisers, social media platforms, or marketing networks, and we do not sell your browsing data to any third parties. You can manage your cookie preferences through your browser settings, and more detailed information about our use of cookies is available in our Cookie Policy.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>16. Can I use Specora on my mobile phone or tablet?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Yes, Specora is designed to work seamlessly across all devices, including desktop computers, laptops, tablets, and smartphones. Our platform uses responsive design technology that automatically adjusts the layout, spacing, and functionality to provide an optimal experience on screens of all sizes. On mobile devices, you can search for products, compare prices, filter results, and navigate through product listings just as easily as on a desktop computer. We recommend using the latest versions of Chrome, Firefox, or Safari browsers for the best performance and user experience on any device.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>17. How does Specora make money if it is free to use?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Specora generates revenue primarily through affiliate marketing programs with the marketplaces we search. When you click on a product link on Specora and make a purchase on the linked marketplace, we may receive a small commission or referral fee from the marketplace or seller. This commission does not increase the price you pay for the product and is funded by the marketplace as a referral fee for sending customers to their platform. These affiliate commissions help us cover our operational costs, including server infrastructure, data updates, development, and maintenance, allowing us to keep Specora free for all users. We also display optional promotional content in a transparent manner, clearly distinguished from organic search results.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>18. How can I provide feedback or suggest improvements for Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>We greatly value feedback from our users, as it helps us identify areas for improvement, prioritize new features, and make Specora more useful for everyone. You can share your feedback, suggestions, or feature requests by contacting us through our website's contact form. Whether you have an idea for a new search feature, a suggestion for improving the user interface, a report of a bug or issue you encountered, or any other input that you believe would make Specora better, we want to hear from you. Our team reviews all feedback and uses it to inform our product development roadmap and prioritization decisions.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>19. What should I do if I find incorrect or outdated product information on Specora?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>If you come across product information on Specora that appears to be incorrect, outdated, or inappropriate, please let us know by contacting our support team through the contact form on our website. Please include as much detail as possible about the issue, including the product name, the marketplace it was listed from, and the specific information that appears to be incorrect. Our team will investigate the issue, verify the information against the source marketplace, and take appropriate action, which may include updating our database, correcting the data, or removing the listing if it violates our content policies. Your help in identifying and reporting data quality issues is invaluable in helping us maintain the accuracy and reliability of our platform.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-trigger" onclick="toggleFaq(this)">
          <span>20. Can Specora help me find products that are not available in Pakistan?</span>
          <span class="faq-indicator">+</span>
        </button>
        <div class="faq-answer"><p>Yes, Specora can help you find products from international marketplaces that may offer shipping to Pakistan or alternative purchasing options. Many products listed on Amazon, AliExpress, eBay, and Walmart can be shipped internationally to Pakistan, though shipping times, costs, and availability vary by seller and product category. When using Specora, you can see which marketplace a product is from and estimate whether it can be shipped to your location. Please be aware that international shipments may be subject to customs duties, import taxes, and other fees imposed by Pakistani customs authorities, which are the responsibility of the buyer. We recommend checking the marketplace's shipping policy for the specific product to confirm whether international shipping to Pakistan is available and what additional costs may apply.</p></div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleFaq(trigger) {
  var item = trigger.parentElement;
  var isOpen = item.classList.contains('open');
  var allItems = document.querySelectorAll('.faq-item');
  for (var i = 0; i < allItems.length; i++) {
    allItems[i].classList.remove('open');
  }
  if (!isOpen) item.classList.add('open');
}
</script>

<div class="content-section">
  <div class="content-inner">
    <h2>Still Have <span class="hl-em">Questions</span>?</h2>
    <p>We understand that every user's situation is unique, and while we have done our best to cover the most common questions in this FAQ, we recognize that you may have a specific query that is not addressed here. If you have read through the questions above and still need assistance, please do not hesitate to reach out to our dedicated support team. We are here to help you with any aspect of using Specora, whether it is a question about how a particular feature works, an issue you are encountering while searching for products, or feedback you would like to share about your experience on our platform.</p>
    <p>You can contact our support team by visiting the <a href="contact.php" style="color:var(--gold-dark);text-decoration:underline;">contact page</a> on our website and filling out the contact form with your inquiry. Please provide as much detail as possible about your question or issue so that we can assist you efficiently. Our team aims to respond to all inquiries within twenty-four hours during business days, and we take every question seriously. Your feedback also helps us identify gaps in our FAQ and improve our platform, so do not hesitate to let us know if there is a topic you believe should be covered here but is currently missing. We are committed to providing the best possible support to our users and to continuously improving the Specora experience for everyone in Pakistan.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
