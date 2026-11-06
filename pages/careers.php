<?php
// ================================================
// careers.php — Specora Careers
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
<title>Careers — Specora</title>
<meta name="description" content="Join the Specora team. Explore career opportunities in product discovery, engineering, design, and more.">
<meta name="keywords" content="careers, jobs, Specora, product discovery, join our team">
<meta property="og:title" content="Careers — Specora">
<meta property="og:description" content="Join the Specora team and help build the future of product discovery.">
<meta property="og:image" content="../img/specora-logo.png">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
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
.content-inner ul{list-style:none;margin-bottom:24px;}
.content-inner ul li{font-size:15px;color:var(--gray-600);line-height:1.7;padding:8px 0 8px 28px;position:relative;}
.content-inner ul li::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:8px;height:8px;border-radius:50%;background:var(--gold);}
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
      <span class="page-badge-text">JOIN OUR TEAM</span>
    </div>
    <h1>Build the Future of <span class="hl-em">Product Discovery</span></h1>
    <p>At Specora, we are on a mission to make shopping smarter and more transparent for everyone in Pakistan. Join our passionate team of engineers, designers, and innovators who are shaping the way products are discovered across the world's largest marketplaces.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Why Work at <span class="hl-em">Specora</span>?</h2>
    <p>We believe in the power of transparency, innovation, and measurable impact. Every day, Specora helps thousands of users across Pakistan make better purchasing decisions by providing access to product information from multiple global marketplaces in a single, unified platform. The work you do at Specora has direct and meaningful impact on real people who rely on our platform to find the best products at the best prices. Our technology stack is modern and constantly evolving, spanning real-time data pipelines that process millions of product listings, intelligent search algorithms that understand user intent, responsive frontend interfaces, and robust backend infrastructure that ensures reliability and performance at scale. We invest heavily in the growth of our team members through learning budgets for courses, conferences, books, and tools, and we foster a culture where experimentation and continuous improvement are encouraged. We operate on a remote-first basis, allowing you to work from wherever you are most productive, with team members located across Pakistan. We offer competitive compensation packages that include market-leading salaries, equity participation, and comprehensive benefits designed to support your financial security, health, and well-being.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Open <span class="hl-em">Positions</span></h2>
    <p>We are always looking for talented, passionate individuals who are excited about the opportunity to shape the future of product discovery in Pakistan. Our current openings span multiple disciplines, and we welcome applications from candidates who share our values of transparency, innovation, and user-centric design. For our engineering team, we are seeking a Senior Full-Stack Developer who will build and scale our product search platform using PHP, JavaScript, MySQL, and modern frontend frameworks. This role involves working on both the data processing pipelines that aggregate product information from multiple marketplaces and the user-facing interfaces that make that data accessible and useful to our users. We are also looking for a UI and UX Designer who can craft intuitive, beautiful interfaces that make product discovery effortless and delightful, with a strong focus on user research, interaction design, and visual consistency across all platforms and devices. Our Data Engineer position involves building and maintaining the complex data pipelines that power real-time product data from Amazon, AliExpress, eBay, and Walmart, requiring expertise in data processing, API integration, and database optimization. We are also seeking a Product Manager who will define the product roadmap, gather and synthesize user insights, prioritize features based on impact and feasibility, and drive cross-functional initiatives that deliver measurable value to our users.</p>
    <p>If none of the specific roles listed above match your skills and experience, we encourage you to reach out to us anyway. We are always open to hearing from talented individuals who believe they can contribute to Specora's mission, even if there is not a formal open position that matches their profile. We value diverse perspectives and skill sets, and we are willing to create roles for exceptional candidates who can demonstrate how their unique talents can help us build a better product discovery platform for Pakistan.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Life at <span class="hl-em">Specora</span></h2>
    <p>We firmly believe that great products are built by happy, motivated, and well-supported teams. At Specora, we have cultivated a culture that values creativity, individual ownership, collaboration, and genuine work-life balance. Our team members enjoy flexible working hours that allow them to work when they are most productive, because we focus on outcomes and impact rather than the number of hours spent at a desk. Every team member receives an annual learning budget that they can use for online courses, professional certifications, conference attendance, books, and tools that support their professional growth and skill development. We actively encourage and sponsor contributions to open source projects that align with our mission and technology stack, recognizing that giving back to the developer community strengthens both our team and the broader ecosystem. Twice a year, we organize in-person team retreats where we come together to brainstorm new ideas, strengthen team bonds, and celebrate our collective achievements, with past retreats held in Karachi, Lahore, and Islamabad. We provide an equipment stipend that allows each team member to acquire the tools they need to do their best work, whether that is a new laptop, a larger monitor, a standing desk, or noise-cancelling headphones. We also offer comprehensive health coverage and wellness benefits designed to ensure that you and your family are taken care of, because we believe that your well-being is foundational to your ability to do great work.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>How to <span class="hl-em">Apply</span></h2>
    <p>Ready to join the Specora team and help us build the future of product discovery in Pakistan? Our hiring process is designed to be thorough, fair, and respectful of your time. The first step is to submit your application through our contact page, including your resume and a brief note explaining why you are interested in joining Specora and how your skills and experience align with our mission. After we review your application, if there is a potential match, we will invite you for an initial chat, which is a thirty-minute video call with our hiring manager to discuss your background, skills, career aspirations, and what you are looking for in your next role. This conversation is as much for you to learn about Specora as it is for us to learn about you. The next stage is a practical technical assessment or technical interview that is tailored to the specific role you are applying for, designed to evaluate your relevant skills in a realistic context rather than through abstract puzzles or brainteasers. If you successfully pass the technical assessment, you will be invited for a team interview where you will meet several members of the Specora team to discuss how you work, how you collaborate, and whether there is a mutual fit both culturally and technically. Finally, if everything aligns and both you and Specora are excited about moving forward, we will extend an offer and begin the onboarding process, during which we will provide you with all the tools, access, documentation, and support you need to hit the ground running and make an impact from your very first week.</p>
  </div>
</div>

<div class="content-section">
  <div class="content-inner">
    <h2>Learn More <span class="hl-em">About Specora</span></h2>
    <p>To learn more about Specora, our policies, and the resources available to you, we encourage you to explore the other pages on our website. Our Privacy Policy, Terms and Conditions, Cookie Policy, and Disclaimer pages provide detailed information about how we operate, how we protect your data, and your rights as a user. Our Customer Support page provides information about how to get in touch with our team, and our FAQ page offers comprehensive answers to many commonly asked questions about using Specora and understanding product data. For practical information about shipping times, costs, and return procedures for products found through our platform, please refer to our Shipping Information and Returns and Refunds pages. You can also learn more about the people behind Specora on our Our Team page.</p>
  </div>
</div>

<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
