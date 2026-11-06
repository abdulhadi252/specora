<?php
// ================================================
// our-team.php — Specora Our Team
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
<title>Our Team — Specora</title>
<meta name="description" content="Meet the passionate team behind Specora. Learn about the people building Pakistan's smartest product discovery platform.">
<meta name="keywords" content="team, Specora team, about us, founders, developers, Pakistan">
<meta property="og:title" content="Our Team — Specora">
<meta property="og:description" content="Meet the passionate team behind Specora — Pakistan's smartest product discovery platform.">
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
.page-badge{display:inline-flex;align-items:center;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:6px 16px;margin-bottom:20px;}
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
.cta-box{background:var(--black);border-radius:var(--radius);padding:36px;text-align:center;margin-top:48px;}
.cta-box h3{font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--white);margin-bottom:8px;}
.cta-box p{font-size:14px;color:rgba(255,255,255,.45);margin-bottom:20px;}
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
      <span class="page-badge-text">OUR TEAM</span>
    </div>
    <h1>Meet the People Behind <span class="hl-em">Specora</span></h1>
    <p>We're a passionate team of engineers, designers, and product enthusiasts dedicated to making product discovery smarter and easier for everyone in Pakistan.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Our Core <span class="hl-em">Team</span></h2>
    <p>At the heart of Specora is a dedicated group of individuals who bring together decades of combined experience in technology, design, and e-commerce. Each member of our team plays a vital role in ensuring that Specora remains the most reliable and user-friendly product discovery platform in Pakistan. We operate with a flat hierarchy where every voice matters, ideas flow freely regardless of seniority, and collaboration is the bedrock of everything we build.</p>

    <p><strong>Muhammad</strong>, the founder and driving force behind Specora, is a passionate technologist who identified a critical gap in Pakistan's online shopping landscape. With a deep understanding of both the technical and consumer sides of e-commerce, Muhammad architected the platform from the ground up — writing the core code, designing the data aggregation pipelines that pull real-time product information from multiple global marketplaces, and overseeing every aspect of the platform's evolution. His relentless focus on user experience and his commitment to solving real problems have shaped Specora into what it is today.</p>

    <p>Our <strong>Design and User Experience</strong> team is responsible for crafting the visual language and interaction patterns that make Specora intuitive and enjoyable to use. They believe that great design is invisible — the best interfaces are the ones users don't have to think about. Every layout, every color choice, every micro-interaction on the platform is the result of meticulous research and iterative refinement. This team spends countless hours studying user behavior, running usability tests, and analyzing feedback to ensure that Specora feels natural and effortless, whether you are browsing on a desktop computer or a mobile phone.</p>

    <p>The <strong>Engineering and Data</strong> team forms the technical backbone of Specora. They have built and maintain the scalable infrastructure that processes millions of product listings from Amazon, AliExpress, eBay, and Walmart in real time. This team deals with the immense complexity of parsing diverse data formats, handling rate limits across APIs, normalizing product specifications into a unified schema, and delivering lightning-fast search results to users across Pakistan. Their work involves sophisticated algorithms for price comparison, intelligent categorization, and relevance-based search ranking. Behind every search query on Specora is a carefully orchestrated chain of data pipelines, caching layers, and optimization techniques engineered by this team to deliver results in milliseconds.</p>

    <p>Beyond these core roles, Specora is supported by a growing network of contributors including quality assurance specialists who rigorously test every new feature, content writers who ensure product descriptions are accurate and helpful, and community managers who engage with users to understand their needs and pain points. Every single person on the Specora team shares a common belief: that technology should empower consumers, not confuse them. We are united by a mission to democratize access to product information and to help shoppers in Pakistan make informed, confident purchasing decisions.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Our <span class="hl-em">Values</span></h2>
    <p><strong>User First.</strong> Every feature we build, every line of code we write, and every design decision we make starts with the same fundamental question: How does this help our users? We place the needs of Pakistani shoppers at the center of everything we do. This means we prioritize functionality over flash, clarity over complexity, and value over vanity. Our users are not just visitors to our platform — they are the reason Specora exists, and their satisfaction is the only metric that truly matters to us.</p>

    <p><strong>Transparency.</strong> In a world where online shopping is often clouded by hidden fees, misleading product descriptions, and opaque pricing, Specora stands for clarity and honesty. We believe in showing users exactly what they are getting, what they will pay, and where they can find the best deal. We do not manipulate prices, we do not hide fees, and we do not promote products based on anything other than their relevance and value to the user. Trust is the currency of e-commerce, and we work every day to earn and maintain that trust with our community.</p>

    <p><strong>Innovation.</strong> The technology landscape evolves at breakneck speed, and so do we. Our team is constantly exploring new ways to enhance the product discovery experience — whether that means implementing smarter search algorithms that understand user intent, building real-time price tracking systems that alert users when prices drop, or developing intuitive comparison tools that break down complex product specifications into easy-to-understand formats. We are not satisfied with the status quo; we are always asking how things can be done better, faster, and more intelligently.</p>

    <p><strong>Collaboration.</strong> We firmly believe that the best ideas emerge when diverse perspectives come together. Our team members come from different backgrounds, disciplines, and ways of thinking. Engineers learn from designers, designers learn from data analysts, and everyone learns from our users. We foster an environment where open communication is encouraged, where constructive feedback is welcomed, and where every team member feels empowered to challenge assumptions and propose new ideas. Collaboration is not just a value we talk about — it is how we work, every single day.</p>

    <p><strong>Continuous Learning.</strong> Technology does not stand still, and neither do we. Our team invests heavily in personal and professional growth through regular knowledge-sharing sessions, online courses, conference attendance, and hands-on experimentation with emerging technologies. We encourage every team member to dedicate time to learning new skills, exploring new tools, and staying current with industry trends. This culture of continuous improvement ensures that Specora remains at the cutting edge of product discovery technology, always ready to adapt to the changing needs of our users and the evolving digital marketplace.</p>

    <h2>Our <span class="hl-em">Story</span></h2>
    <p>Specora was born from a simple observation — finding the best products across multiple online marketplaces was unnecessarily difficult. Shoppers in Pakistan had to visit Amazon, AliExpress, eBay, and Walmart separately, comparing prices and specifications manually across different websites. The process was time-consuming, frustrating, and often led to missed deals. The idea for Specora emerged not from a boardroom or a business plan, but from the real, everyday frustration of someone who wanted a better way to shop online.</p>
    <p>Our founder, Muhammad, recognized this problem and set out to build a solution. What started as a small project to aggregate product data quickly grew into a full-fledged product discovery platform. The early days were challenging — building reliable data pipelines that could extract structured information from multiple e-commerce giants required countless hours of reverse-engineering APIs, handling inconsistent data formats, and building robust error-handling systems. But the vision was clear: create a single place where anyone in Pakistan could search for any product and instantly see prices, specifications, and availability across all major online marketplaces.</p>
    <p>Today, Specora helps thousands of users across Pakistan find the best products at the best prices — all from a single search. But we see this as only the beginning. Our roadmap includes features like personalized price drop alerts, intelligent product recommendations based on browsing behavior, detailed product comparisons with side-by-side specification breakdowns, and community-driven reviews and ratings. We are building not just a search tool, but a complete product discovery ecosystem that empowers consumers with the information they need to make confident purchasing decisions.</p>
    <p>Our team has grown from a solo founder to a passionate group of engineers, designers, and product thinkers who share a common mission: make product discovery smarter, faster, and more transparent for everyone in Pakistan. As we continue to grow, we remain committed to the principles that brought us here — putting users first, staying transparent in everything we do, embracing innovation, collaborating openly, and never stopping the pursuit of knowledge. This is Specora, and this is our team.</p>

    <div class="cta-box">
      <h3>Join Our Team</h3>
      <p>We're always looking for talented individuals who share our passion for product discovery.</p>
      <a href="careers.php" class="cta-btn">View Openings →</a>
    </div>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
