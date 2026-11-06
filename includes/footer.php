<?php
/**
 * footer.php — Specora
 * Shared footer fragment.
 *
 * Supports a $basePath variable. Set $basePath before including
 * this file when the including page is in a subdirectory
 * (e.g. $basePath = '../' for pages/*.php).
 */
$basePath = isset($basePath) ? $basePath : '';
?>
<style>
footer{background:var(--white);padding:64px 48px 32px;color:var(--gray-600);margin-top:auto;border-top:1px solid var(--gray-200);}
.footer-top{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:40px;margin-bottom:52px;align-items:start;}
.footer-brand{display:flex;flex-direction:column;}
.footer-logo-link{display:flex;align-items:center;text-decoration:none;color:inherit;}
.footer-logo-img{height:58px;width:auto;display:block;object-fit:contain;mix-blend-mode:multiply;}
.footer-brand p{font-size:14px;font-weight:500;color:var(--gray-400);margin-top:16px;max-width:260px;line-height:1.7;}
.footer-brand-social{display:flex;gap:10px;margin-top:18px;}
.footer-social-link{width:36px;height:36px;display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--black);transition:all .2s;}
.footer-social-link:hover{transform:translateY(-2px);}
.footer-social-link:nth-child(1):hover{color:#1877F2;}
.footer-social-link:nth-child(2):hover{color:#E4405F;}
.footer-social-link:nth-child(3):hover{color:#000000;}
.footer-social-link:nth-child(4):hover{color:#FF0000;}
.footer-col h4{font-family:var(--font-display);font-size:12px;font-weight:700;color:var(--black);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:18px;}
.footer-col ul{list-style:none;display:flex;flex-direction:column;gap:12px;margin:0;padding:0;}
.footer-col ul a{font-size:13px;font-weight:500;color:var(--black);transition:all .2s;text-decoration:none;cursor:pointer;display:inline-flex;align-items:center;gap:6px;}
.footer-col ul a:hover{color:var(--gold-dark);text-decoration:none;}
.footer-bottom{border-top:1px solid var(--gray-200);padding-top:28px;display:flex;justify-content:center;align-items:center;text-align:center;}
.footer-bottom p{font-size:13px;font-weight:500;color:var(--gray-400);}
.footer-bottom p span{color:var(--gold-dark);font-weight:700;}

@media(max-width:1024px){
  footer{padding:52px 24px 28px;}
  .footer-top{grid-template-columns:1.2fr 1fr 1fr;gap:32px;}
  .footer-top .footer-col:last-child{grid-column:span 1;}
}
@media(max-width:768px){
  .footer-top{grid-template-columns:1fr 1fr;gap:28px;}
  .footer-brand{grid-column:span 2;}
  .footer-brand p{max-width:100%;}
  footer{padding:44px 20px 24px;}
  .footer-bottom{flex-direction:column;text-align:center;}
  .footer-logo-img{height:50px;}
}
@media(max-width:480px){
  .footer-top{grid-template-columns:1fr;gap:24px;}
  .footer-brand{grid-column:span 1;}
  footer{padding:36px 16px 20px;}
  .footer-logo-img{height:46px;}
}
</style>

<footer>
  <div class="footer-top">
    <div class="footer-brand">
      <a href="<?= $basePath ?>index.php" class="footer-logo-link">
        <img src="<?= $basePath ?>img/specora-logo.png" alt="Specora" class="footer-logo-img" />
      </a>
      <div class="footer-brand-social" style="margin-top:12px;">
        <a href="#" class="footer-social-link" aria-label="Facebook">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
        <a href="#" class="footer-social-link" aria-label="Instagram">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
        </a>
        <a href="#" class="footer-social-link" aria-label="Twitter">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>
        <a href="#" class="footer-social-link" aria-label="YouTube">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
        </a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="<?= $basePath ?>pages/about.php">About Specora</a></li>
        <li><a href="<?= $basePath ?>pages/contact.php">Contact Us</a></li>
        <li><a href="<?= $basePath ?>pages/our-team.php">Our Team</a></li>
        <li><a href="<?= $basePath ?>pages/careers.php">Careers</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Support</h4>
      <ul>
        <li><a href="<?= $basePath ?>pages/customer-support.php">Customer Support</a></li>
        <li><a href="<?= $basePath ?>pages/faq.php">FAQ / Help Center</a></li>
        <li><a href="<?= $basePath ?>pages/shipping.php">Shipping Information</a></li>
        <li><a href="<?= $basePath ?>pages/returns.php">Returns & Refunds</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Policies</h4>
      <ul>
        <li><a href="<?= $basePath ?>pages/privacy.php">Privacy Policy</a></li>
        <li><a href="<?= $basePath ?>pages/terms.php">Terms & Conditions</a></li>
        <li><a href="<?= $basePath ?>pages/cookie.php">Cookie Policy</a></li>
        <li><a href="<?= $basePath ?>pages/disclaimer.php">Disclaimer</a></li>
      </ul>
    </div>
  </div>
  <!-- NEWSLETTER -->
  <div class="footer-newsletter" id="newsletterSection">
    <div class="fn-wrap">
      <h4 class="fn-title">Stay Updated</h4>
      <p class="fn-desc">Get the latest products and deals delivered to your inbox.</p>
      <form id="nlForm" class="fn-form" action="<?= $basePath ?>newsletter_process.php" method="POST" novalidate>
        <div class="fn-input-wrap">
          <input type="email" id="nlEmail" class="fn-input" placeholder="Enter your email" required autocomplete="email" />
          <button type="submit" id="nlBtn" class="fn-btn">Subscribe →</button>
        </div>
        <div id="nlFeedback" class="fn-feedback"></div>
      </form>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 <span>Specora</span>. All rights reserved.</p>
  </div>
</footer>

<script>window.TRACKING_ENDPOINT = '<?= $basePath ?>api/track.php';</script>
<script src="<?= $basePath ?>js/visitor-tracker.js"></script>

<style>
/* ══ NEWSLETTER IN FOOTER ══ */
.footer-newsletter{border-top:1px solid var(--gray-200);padding:32px 0 28px;margin-top:8px;}
.fn-wrap{max-width:440px;margin:0 auto;text-align:center;}
.fn-title{font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--black);margin-bottom:4px;}
.fn-desc{font-size:13px;color:var(--gray-400);margin-bottom:18px;line-height:1.5;}
.fn-form{display:flex;flex-direction:column;gap:0;}
.fn-input-wrap{display:flex;gap:8px;width:100%;}
.fn-input{flex:1;min-width:0;font-family:var(--font-body);font-size:14px;color:var(--black);background:var(--gray-100);border:1.5px solid var(--gray-200);border-radius:var(--radius-pill);padding:12px 18px;outline:none;transition:all .2s;}
.fn-input:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px rgba(234,189,120,.1);}
.fn-input.error{border-color:#EF4444;background:#FEF2F2;}
.fn-btn{font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:12px 24px;cursor:pointer;transition:all .2s;white-space:nowrap;flex-shrink:0;}
.fn-btn:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-1px);}
.fn-btn:active{transform:scale(.97);}
.fn-btn:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.fn-feedback{font-size:12px;font-weight:500;margin-top:8px;min-height:18px;transition:all .2s;}
.fn-feedback.success{color:#10B981;}
.fn-feedback.error{color:#EF4444;}
@media(max-width:480px){
  .fn-input-wrap{flex-direction:column;gap:8px;}
  .fn-btn{width:100%;justify-content:center;}
}
.fn-form.loading .fn-btn{opacity:.6;pointer-events:none;}
.fn-form.loading .fn-input{opacity:.6;}
</style>
