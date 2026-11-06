<?php require_once __DIR__ . '/../includes/config.php'; ?>

<?php require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/functions.php';
if (!validate_db_connection($connect)) { die('<p style="font-family:sans-serif;padding:40px;text-align:center;color:#991B1B;">⚠️ Database connection is currently unavailable. Please try again later.</p>'); }
if(!isset($indexTotalProducts)){ $r=$connect->query("SELECT COUNT(*) as c FROM products"); $indexTotalProducts=$r?(int)$r->fetch_assoc()['c']:0; } ?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Contact Us — Specora</title>
<meta name="description" content="Get in touch with Specora. Contact our support team for help with product discovery, price comparison, or any questions about our platform.">
<meta name="keywords" content="contact Specora, support, product discovery help, price comparison, customer service">
<meta property="og:title" content="Contact Us — Specora">
<meta property="og:description" content="Get in touch with Specora. Contact our support team for help with product discovery, price comparison, or any questions about our platform.">
<meta property="og:image" content="img/specora-logo.png">
<meta property="og:url" content="https://specora.pk/pages/contact.php">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Contact Us — Specora">
<meta name="twitter:description" content="Get in touch with Specora. Contact our support team for help with product discovery, price comparison, or any questions about our platform.">
<meta name="twitter:image" content="img/specora-logo.png">
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="stylesheet" href="../css/base.css">
<link rel="stylesheet" href="../css/navbar.css?v=<?= filemtime(__DIR__.'/../css/navbar.css') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ══ RESET & VARIABLES ══ */
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--white);color:var(--black);overflow-x:hidden;}
a{text-decoration:none;color:inherit;}

/* ══ CONTACT INFO + FORM ══ */
.contact-main{position:relative;padding:100px 48px;background:var(--off-white);overflow:hidden;}
.contact-main::before{content:'';position:absolute;top:-120px;right:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(234,189,120,.05) 0%,transparent 65%);pointer-events:none;}
.contact-main-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:40% 1fr;gap:80px;align-items:start;position:relative;z-index:2;}
.contact-info{display:flex;flex-direction:column;gap:0;}
.contact-info-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:6px 14px 6px 10px;width:fit-content;margin-bottom:24px;}
.contact-info-badge-icon{width:18px;height:18px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;}
.contact-info-badge-text{font-size:11px;font-weight:600;color:var(--gold-dark);letter-spacing:.3px;}
.contact-info-heading{font-family:var(--font-display);font-weight:800;letter-spacing:-1.2px;color:var(--black);margin-bottom:20px;}
.contact-info-heading .hl{display:block;font-size:clamp(24px,3vw,36px);line-height:1.12;margin-bottom:2px;}
.contact-info-heading .hl-em{color:var(--gold-dark);}
.contact-info-desc{font-size:15px;color:var(--gray-600);line-height:1.7;max-width:380px;margin-bottom:28px;}
.info-cards{display:flex;flex-direction:column;gap:12px;margin-bottom:32px;}
.info-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:16px 18px;display:flex;align-items:center;gap:14px;transition:all .25s;cursor:default;}
.info-card:hover{transform:translateX(4px);border-color:var(--gold-light);box-shadow:0 4px 12px rgba(234,189,120,.08);}
.info-card-icon{width:38px;height:38px;border-radius:8px;background:rgba(234,189,120,.1);display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;transition:transform .25s;}
.info-card:hover .info-card-icon{transform:scale(1.08);}
.info-card-label{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gray-400);margin-bottom:2px;}
.info-card-val{font-size:13px;font-weight:600;color:var(--black);}
.why-contact{font-size:14px;color:var(--gray-600);line-height:1.7;margin-bottom:24px;padding:18px;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);}
.why-contact strong{color:var(--black);font-weight:600;}
.quick-categories{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.quick-cat{display:flex;align-items:center;gap:10px;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:12px 14px;cursor:pointer;transition:all .25s;}
.quick-cat:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.06);border-color:var(--gold-light);}
.quick-cat-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;transition:transform .25s;background:rgba(234,189,120,.1);}
.quick-cat:hover .quick-cat-icon{transform:scale(1.1) rotate(-3deg);}
.quick-cat-text{font-size:12px;font-weight:600;color:var(--gray-600);}

/* ── RIGHT — FORM ── */
.contact-form-wrap{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:40px;box-shadow:0 4px 24px rgba(0,0,0,.04);}
.contact-form-wrap h3{font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--black);letter-spacing:-.5px;margin-bottom:6px;}
.contact-form-wrap .form-sub{font-size:14px;color:var(--gray-400);margin-bottom:32px;line-height:1.5;}
.form-group{position:relative;margin-bottom:20px;}
.form-group .fl-label{position:absolute;left:16px;top:14px;font-size:14px;color:var(--gray-400);pointer-events:none;transition:all .2s;font-family:var(--font-body);background:transparent;padding:0 4px;}
.form-group .fl-input,.form-group .fl-textarea,.form-group .fl-select{width:100%;font-family:var(--font-body);font-size:14px;color:var(--black);background:var(--gray-100);border:1.5px solid transparent;border-radius:var(--radius-sm);padding:16px 16px 14px;outline:none;transition:all .2s;}
.form-group .fl-textarea{resize:vertical;min-height:130px;padding-top:20px;}
.form-group .fl-select{appearance:none;cursor:pointer;padding-right:36px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23A8A49C' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;}
.form-group .fl-input:focus,.form-group .fl-textarea:focus,.form-group .fl-select:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px rgba(234,189,120,.1);}
.form-group .fl-input:focus+.fl-label,.form-group .fl-input:not(:placeholder-shown)+.fl-label,.form-group .fl-textarea:focus+.fl-label,.form-group .fl-textarea:not(:placeholder-shown)+.fl-label,.form-group .fl-select:focus+.fl-label,.form-group .fl-select:not([value=""])+.fl-label{top:4px;font-size:10px;font-weight:600;color:var(--gold-dark);background:transparent;padding:0 4px;}
.form-group .fl-input::placeholder,.form-group .fl-textarea::placeholder{color:transparent;}
.char-counter{position:absolute;right:12px;bottom:8px;font-size:10px;color:var(--gray-400);pointer-events:none;}

.btn-submit{width:100%;font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:0 16px;height:56px;line-height:56px;cursor:pointer;transition:all .25s;letter-spacing:.3px;display:flex;align-items:center;justify-content:center;gap:0;position:relative;overflow:hidden;}
.btn-submit:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-2px);box-shadow:0 8px 24px rgba(234,189,120,.3);}
.btn-submit:active{transform:scale(.97);}
.btn-submit.loading{pointer-events:none;opacity:.8;}
.btn-submit.loading .btn-text{position:absolute;opacity:0;pointer-events:none;}
.btn-submit.loading .btn-spinner{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:block;}
.btn-text{transition:opacity .2s;}
.btn-spinner{width:20px;height:20px;border:2px solid rgba(0,0,0,.15);border-top-color:var(--black);border-radius:50%;animation:spin .6s linear infinite;display:none;}
@keyframes spin{to{transform:rotate(360deg);}}
.success-state{display:none;text-align:center;padding:40px 0;}
.success-icon{width:72px;height:72px;border-radius:50%;background:rgba(234,189,120,.1);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px;animation:successPop .4s ease forwards;}
@keyframes successPop{0%{transform:scale(0);}70%{transform:scale(1.1);}100%{transform:scale(1);}}
@keyframes alertSlideIn{0%{opacity:0;transform:translateY(-10px);}100%{opacity:1;transform:translateY(0);}}
.success-state h3{font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--black);margin-bottom:8px;}
.success-state p{font-size:15px;color:var(--gray-400);line-height:1.6;}
.form-group.error .fl-input,.form-group.error .fl-textarea,.form-group.error .fl-select{border-color:#EF4444;box-shadow:0 0 0 3px rgba(239,68,68,.08);}
.form-group.error .fl-label{color:#EF4444;}
.form-group.success .fl-input,.form-group.success .fl-textarea,.form-group.success .fl-select{border-color:#10B981;}
.form-error{font-size:11px;color:#EF4444;margin-top:4px;display:none;}
.form-group.error .form-error{display:block;}
.form-alert-top{z-index:10;box-shadow:0 4px 16px rgba(0,0,0,.08);}


/* ══ SCROLL REVEAL ══ */
.reveal-contact,.reveal-info,.reveal-form{opacity:0;transform:translateY(20px);transition:opacity .5s ease,transform .5s ease;}
.reveal-contact.visible,.reveal-info.visible,.reveal-form.visible{opacity:1;transform:translateY(0);}

/* ══ RESPONSIVE ══ */
@media(max-width:1200px){
  .contact-main{padding:72px 28px;}
  .contact-main-inner{gap:48px;}
  footer{padding:52px 28px 28px;}
}
@media(max-width:1024px){
  .contact-main{padding:64px 24px;}
  .contact-main-inner{grid-template-columns:1fr;gap:40px;}
  footer{padding:52px 24px 28px;}
}
@media(max-width:768px){
  .contact-main{padding:56px 20px;}
  .contact-form-wrap{padding:28px 20px;}
  .quick-categories{grid-template-columns:1fr;}
  footer{padding:44px 20px 24px;}
}
@media(max-width:480px){
  .contact-main{padding:44px 16px;}
  .contact-form-wrap{padding:24px 16px;}
  footer{padding:36px 16px 20px;}
}
</style>
</head>
<body><?php $basePath = '../'; require_once __DIR__ . '/../includes/navbar.php'; ?>



<!-- ══ SECTION 2 — CONTACT INFO + FORM ══ -->
<section class="contact-main" id="contactFormSection">
  <div class="contact-main-inner">
    <div class="contact-info">
      <div class="contact-info-badge reveal-contact" style="transition-delay:0s">
        <span class="contact-info-badge-icon">&#9674;</span>
        <span class="contact-info-badge-text">Contact Information</span>
      </div>
      <div class="contact-info-heading">
        <span class="hl reveal-contact" style="transition-delay:.1s">We're Here to</span>
        <span class="hl reveal-contact" style="transition-delay:.2s"><span class="hl-em">Help You</span></span>
      </div>
      <p class="contact-info-desc reveal-contact" style="transition-delay:.3s">
        Choose the best way to reach us. We respond to every inquiry within 24 hours during business days.
      </p>
      <div class="info-cards">
        <div class="info-card reveal-info" style="transition-delay:.15s">
          <div class="info-card-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div><div class="info-card-label">Email Address</div><div class="info-card-val">hello@specora.pk</div></div>
        </div>
        <div class="info-card reveal-info" style="transition-delay:.25s">
          <div class="info-card-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div><div class="info-card-label">Business Hours</div><div class="info-card-val">Mon-Fri, 9:00 AM - 6:00 PM PKT</div></div>
        </div>
        <div class="info-card reveal-info" style="transition-delay:.35s">
          <div class="info-card-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          </div>
          <div><div class="info-card-label">Average Response Time</div><div class="info-card-val">Within 4 Hours</div></div>
        </div>
        <div class="info-card reveal-info" style="transition-delay:.45s">
          <div class="info-card-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          </div>
          <div><div class="info-card-label">Support Availability</div><div class="info-card-val">Urdu & English</div></div>
        </div>
      </div>
      <div class="why-contact reveal-info" style="transition-delay:.5s">
        <strong>When to contact Specora?</strong><br/>
        General inquiries, business partnerships, affiliate support, technical assistance, product feedback,
        feature suggestions - whatever it is, we're all ears!
      </div>
      <div class="quick-categories">
        <div class="quick-cat reveal-info" style="transition-delay:.18s">
          <div class="quick-cat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <span class="quick-cat-text">General Inquiry</span>
        </div>
        <div class="quick-cat reveal-info" style="transition-delay:.28s">
          <div class="quick-cat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <span class="quick-cat-text">Technical Support</span>
        </div>
        <div class="quick-cat reveal-info" style="transition-delay:.38s">
          <div class="quick-cat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"/></svg>
          </div>
          <span class="quick-cat-text">Affiliate Program</span>
        </div>
        <div class="quick-cat reveal-info" style="transition-delay:.48s">
          <div class="quick-cat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6-4.8-6 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
          </div>
          <span class="quick-cat-text">Business Partnership</span>
        </div>
        <div class="quick-cat reveal-info" style="transition-delay:.58s">
          <div class="quick-cat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/></svg>
          </div>
          <span class="quick-cat-text">Feedback</span>
        </div>
        <div class="quick-cat reveal-info" style="transition-delay:.68s">
          <div class="quick-cat-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </div>
          <span class="quick-cat-text">Feature Request</span>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN — FORM -->
    <div style="display:flex;flex-direction:column;gap:40px;">
    <div class="contact-form-wrap reveal-form" style="transition-delay:.3s">
      <h3>Send Us a Message</h3>
      <p class="form-sub">Fill in the form below and we'll get back to you as soon as possible.</p>
      <div id="liveForm">
        <div class="form-group" id="fgName">
          <input type="text" class="fl-input" id="fldName" placeholder=" " />
          <label class="fl-label" for="fldName">Full Name *</label>
          <span class="form-error" id="nameError" aria-live="polite">Please enter your name</span>
        </div>
        <div class="form-group" id="fgEmail">
          <input type="email" class="fl-input" id="fldEmail" placeholder=" " />
          <label class="fl-label" for="fldEmail">Email Address *</label>
          <span class="form-error" id="emailError" aria-live="polite">Please enter a valid email</span>
        </div>
        <div class="form-group" id="fgSubject">
          <input type="text" class="fl-input" id="fldSubject" placeholder=" " />
          <label class="fl-label" for="fldSubject">Subject</label>
        </div>
        <div class="form-group" id="fgCategory">
          <select class="fl-select" id="fldCategory">
            <option value=""></option>
            <option value="General Inquiry">General Inquiry</option>
            <option value="Technical Support">Technical Support</option>
            <option value="Product Feedback">Product Feedback</option>
            <option value="Partnership">Partnership / Collaboration</option>
            <option value="Affiliate Program">Affiliate Program</option>
            <option value="Feature Request">Feature Request</option>
            <option value="Other">Other</option>
          </select>
          <label class="fl-label" for="fldCategory">Inquiry Category</label>
        </div>
        <div class="form-group" id="fgMessage">
          <textarea class="fl-textarea" id="fldMessage" placeholder=" " maxlength="1000"></textarea>
          <label class="fl-label" for="fldMessage">Your Message *</label>
          <span class="char-counter" id="charCounter">0 / 1000</span>
          <span class="form-error" id="msgError" aria-live="polite">Please write a message</span>
        </div>
        <button class="btn-submit" id="submitBtn" onclick="submitContactForm()">
          <span class="btn-text">Send Message →</span>
          <span class="btn-spinner"></span>
          <span class="ripple"></span>
        </button>
      </div>
      <div class="success-state" id="successState">
        <div class="success-icon">&#10003;</div>
        <h3>Message Sent Successfully!</h3>
        <p>Thank you for reaching out! Our team will review your message and get back to you within 24 hours. In the meantime, feel free to <a href="../index.php" style="color:var(--gold-dark);font-weight:600;">explore Specora</a>.</p>
      </div>
    </div>    </div><!-- /right column -->
  </div>
</section>

<!-- ══ TESTIMONIAL SUBMISSION FORM ══ -->
<section class="testimonial-form-section" id="testimonialForm">
  <div class="testimonial-form-inner">
    <div class="testi-form-header">
      <span class="testi-form-badge"><i class="fas fa-star"></i> Share Your Experience</span>
      <h2>What Do You Think About <span class="hl-em">Specora</span>?</h2>
      <p class="testi-form-sub">Your feedback helps us improve and helps others shop smarter. Drop a testimonial below!</p>
    </div>
    <div class="testi-form-box">
      <form id="testimonialForm" onsubmit="return submitTestimonial(event)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="tf-group">
            <input type="text" class="tf-input" id="tmName" placeholder=" " required>
            <label class="tf-label" for="tmName">Your Name *</label>
            <span class="tf-error" id="tmNameError">Please enter your name</span>
          </div>
          <div class="tf-group">
            <input type="email" class="tf-input" id="tmEmail" placeholder=" ">
            <label class="tf-label" for="tmEmail">Email Address</label>
            <span class="tf-error" id="tmEmailError">Please enter a valid email</span>
          </div>
        </div>
        <div class="tf-group" style="margin-top:14px;">
          <input type="text" class="tf-input" id="tmCity" placeholder=" ">
          <label class="tf-label" for="tmCity">City</label>
        </div>
        <div class="tf-group" style="margin-top:14px;">
          <textarea class="tf-textarea" id="tmFeedback" placeholder=" " required maxlength="500"></textarea>
          <label class="tf-label" for="tmFeedback">Your Testimonial *</label>
          <span class="tf-counter" id="tmCounter">0 / 500</span>
          <span class="tf-error" id="tmFeedbackError">Please write your testimonial</span>
        </div>
        <button type="submit" class="tf-submit" id="tmSubmitBtn">
          <span class="tf-btn-text">Submit Testimonial <i class="fas fa-arrow-right"></i></span>
          <span class="tf-spinner"></span>
        </button>
      </form>
      <div class="tf-success" id="tmSuccess">
        <div class="tf-success-icon"><i class="fas fa-check-circle"></i></div>
        <h3>Thank You!</h3>
        <p>Your testimonial has been submitted and will be visible after admin approval.</p>
      </div>
    </div>
  </div>
</section>

<style>
.testimonial-form-section{position:relative;padding:80px 48px;background:var(--off-white);overflow:hidden;}
.testimonial-form-section::before{content:'';position:absolute;top:-100px;right:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(234,189,120,.04) 0%,transparent 65%);pointer-events:none;}
.testimonial-form-inner{max-width:700px;margin:0 auto;position:relative;z-index:2;}
.testi-form-header{text-align:center;margin-bottom:32px;}
.testi-form-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-pill);padding:6px 14px 6px 10px;font-size:11px;font-weight:600;color:var(--gold-dark);margin-bottom:16px;}
.testi-form-header h2{font-family:var(--font-display);font-size:clamp(22px,3vw,32px);font-weight:800;color:var(--black);letter-spacing:-.8px;margin-bottom:10px;}
.testi-form-header h2 .hl-em{color:var(--gold-dark);}
.testi-form-sub{font-size:14px;color:var(--gray-600);line-height:1.6;}
.testi-form-box{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:36px;box-shadow:0 4px 24px rgba(0,0,0,.04);}
.tf-group{position:relative;}
.tf-input,.tf-textarea{width:100%;font-family:var(--font-body);font-size:14px;color:var(--black);background:var(--gray-100);border:1.5px solid transparent;border-radius:var(--radius-sm);padding:16px 16px 14px;outline:none;transition:all .2s;}
.tf-textarea{resize:vertical;min-height:100px;padding-top:20px;}
.tf-input:focus,.tf-textarea:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px rgba(234,189,120,.1);}
.tf-label{position:absolute;left:16px;top:14px;font-size:14px;color:var(--gray-400);pointer-events:none;transition:all .2s;font-family:var(--font-body);padding:0 4px;}
.tf-input:focus+.tf-label,.tf-input:not(:placeholder-shown)+.tf-label,
.tf-textarea:focus+.tf-label,.tf-textarea:not(:placeholder-shown)+.tf-label{top:4px;font-size:10px;font-weight:600;color:var(--gold-dark);}
.tf-input::placeholder,.tf-textarea::placeholder{color:transparent;}
.tf-error{display:none;font-size:11px;color:#EF4444;margin-top:4px;}
.tf-group.tf-error .tf-input,.tf-group.tf-error .tf-textarea{border-color:#EF4444;box-shadow:0 0 0 3px rgba(239,68,68,.08);}
.tf-group.tf-error .tf-label{color:#EF4444;}
.tf-group.tf-error .tf-error{display:block;}
.tf-counter{position:absolute;right:12px;bottom:8px;font-size:10px;color:var(--gray-400);pointer-events:none;}
.tf-submit{width:100%;font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:0 16px;height:50px;line-height:50px;cursor:pointer;transition:all .25s;letter-spacing:.3px;display:flex;align-items:center;justify-content:center;margin-top:20px;position:relative;overflow:hidden;}
.tf-submit:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-2px);box-shadow:0 8px 24px rgba(234,189,120,.3);}
.tf-submit:active{transform:scale(.97);}
.tf-submit.tf-loading{pointer-events:none;opacity:.8;}
.tf-submit.tf-loading .tf-btn-text{opacity:0;}
.tf-submit.tf-loading .tf-spinner{display:block;}
.tf-spinner{width:18px;height:18px;border:2px solid rgba(0,0,0,.15);border-top-color:var(--black);border-radius:50%;animation:tfSpin .6s linear infinite;display:none;position:absolute;}
@keyframes tfSpin{to{transform:rotate(360deg);}}
.tf-success{display:none;text-align:center;padding:30px 0;}
.tf-success-icon{width:64px;height:64px;border-radius:50%;background:rgba(16,185,129,.1);border:2px solid #10B981;display:flex;align-items:center;justify-content:center;font-size:26px;color:#10B981;margin:0 auto 14px;}
.tf-success h3{font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--black);margin-bottom:6px;}
.tf-success p{font-size:14px;color:var(--gray-400);line-height:1.6;}
@media(max-width:768px){
  .testimonial-form-section{padding:56px 20px;}
  .testi-form-box{padding:24px 18px;}
  .testi-form-header h2{font-size:20px;}
}
</style>

<!-- ══ GOOGLE MAP — KARACHI ══ -->
<section style="width:100%;position:relative;overflow:hidden;">
  <div style="width:100%;height:380px;">
    <iframe 
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d231917.90968632904!2d66.98559550590186!3d24.861383363148544!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb33e06651d4bbf%3A0x9cf92f44555a0c23!2sKarachi%2C%20Karachi%20City%2C%20Sindh%2C%20Pakistan!5e0!3m2!1sen!2s!4v1696789012345"
      width="100%" 
      height="100%" 
      style="border:0;display:block;" 
      allowfullscreen="" 
      loading="lazy" 
      referrerpolicy="no-referrer-when-downgrade"
      title="Specora Location — Karachi, Pakistan">
    </iframe>
  </div>
</section>

<!-- FOOTER -->
<?php $basePath = '../'; require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="../js/common.js"></script>
<script src="../js/contact.js"></script>
</body>
</html>