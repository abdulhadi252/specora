<?php
/**
 * admin/sidebar.php — Specora Admin Sidebar Include
 * 
 * Include this in any admin page that needs a sidebar.
 * Requires: $currentPage variable to be set before include.
 * Example: $currentPage = 'dashboard'; include __DIR__ . '/sidebar.php';
 */

$currentPage = $currentPage ?? 'dashboard';

// ── Logged-in admin name (from session, with DB fallback) ──
// Always shows the name of whoever is logged in, never a hardcoded name.
$adminNameRaw = trim($_SESSION['admin_username'] ?? '');
$adminId      = (int)($_SESSION['admin_id'] ?? 0);

if ($adminNameRaw === '' && $adminId > 0 && isset($connect) && $connect instanceof mysqli) {
    $nameStmt = $connect->prepare("SELECT username FROM admins WHERE id = ? LIMIT 1");
    if ($nameStmt) {
        $nameStmt->bind_param('i', $adminId);
        $nameStmt->execute();
        $nameRes = $nameStmt->get_result();
        if ($nameRes && $nameRow = $nameRes->fetch_assoc()) {
            $adminNameRaw = $nameRow['username'];
            $_SESSION['admin_username'] = $adminNameRaw;
        }
        $nameStmt->close();
    }
}

$adminName    = htmlspecialchars($adminNameRaw !== '' ? $adminNameRaw : 'Admin');
$adminInitial = strtoupper(substr($adminNameRaw !== '' ? $adminNameRaw : 'A', 0, 1));

// Generate CSRF token for logout links
$logoutToken = generate_csrf_token();
$logoutUrl   = 'logout.php?csrf_token=' . urlencode($logoutToken);

// Sidebar navigation items — add new pages here in future!
$navItems = [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon'  => svg_icon('chart'),
        'url'   => 'index.php',
    ],
    'testimonials' => [
        'label' => 'Testimonials',
        'icon'  => svg_icon('star'),
        'url'   => 'testimonials.php',
    ],
    'contact-messages' => [
        'label' => 'Contact Messages',
        'icon'  => svg_icon('mail'),
        'url'   => 'contact-messages.php',
    ],
    'register' => [
        'label' => 'Add Admin',
        'icon'  => svg_icon('plus'),
        'url'   => 'register.php',
    ],
    'manage-category' => [
        'label' => 'Categories',
        'icon'  => svg_icon('folder'),
        'url'   => 'manage_category.php',
    ],
    'analytics' => [
        'label' => 'Analytics',
        'icon'  => svg_icon('chart'),
        'url'   => 'analytics.php',
    ],
    'manage-about' => [
        'label' => 'About Page',
        'icon'  => svg_icon('file'),
        'url'   => 'manage_about.php',
    ],
    'profile' => [
        'label' => 'Edit Profile',
        'icon'  => svg_icon('user'),
        'url'   => 'profile.php',
    ],
];

// External links
$externalLinks = [
    [
        'label' => 'View Website',
        'icon'  => svg_icon('home'),
        'url'   => '../index.php',
    ],

];
?>
<!-- SIDEBAR OVERLAY (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-header">
    <a href="../index.php" class="sidebar-logo">
      <img src="../img/specora-logo.png" alt="Specora" class="sidebar-logo-img" />
    </a>
    <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
  </div>

  <div class="sidebar-user">
    <div class="sidebar-user-avatar"><?= $adminInitial ?></div>
    <div class="sidebar-user-info">
      <div class="sidebar-user-name"><?= $adminName ?></div>
      <div class="sidebar-user-role">Admin</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-nav-label">Main</div>
    <?php foreach ($navItems as $key => $item): 
      $isActive = ($currentPage === $key);
    ?>
      <a href="<?= $item['url'] ?>" 
         class="sidebar-link <?= $isActive ? 'active' : '' ?>"
         onclick="closeSidebar()">
        <span class="sidebar-link-icon"><?= $item['icon'] ?></span>
        <span class="sidebar-link-text"><?= $item['label'] ?></span>
        <?php if ($isActive): ?>
          <span class="sidebar-link-indicator"></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <div style="display:flex;gap:6px;">
      <a href="../index.php" class="sidebar-footer-btn" onclick="closeSidebar()" title="View Website">
        <span>→</span>
      </a>
      <a href="<?= $logoutUrl ?>" class="sidebar-logout" onclick="closeSidebar()">
        <span class="sidebar-link-icon"><?= svg_icon('logout') ?></span>
        <span>Logout</span>
      </a>
    </div>
  </div>
</aside>

<!-- TOP BAR (mobile only) -->
<div class="sidebar-topbar">
  <button class="sidebar-hamburger" id="sidebarHamburger" aria-label="Toggle sidebar">
    <span></span><span></span><span></span>
  </button>
  <div class="topbar-title"><?= $navItems[$currentPage]['label'] ?? 'Admin' ?></div>
  <a href="<?= $logoutUrl ?>" class="topbar-logout" title="Logout"><?= svg_icon('logout') ?></a>
</div>

<style>
/* ══ SIDEBAR VARIABLES — Light Theme ══ */
:root {
  --sidebar-width: 260px;
  --sidebar-bg: #ffffff;
  --sidebar-hover: #f3f4f6;
  --sidebar-active: #f9fafb;
  --sidebar-text: #6b7280;
  --sidebar-text-active: #111827;
  --sidebar-accent: #C9973A;
  --sidebar-border: #e5e7eb;
  --sidebar-radius: 12px;
}

/* ══ SIDEBAR CONTAINER ══ */
.admin-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: var(--sidebar-width);
  background: var(--sidebar-bg);
  z-index: 500;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  transition: transform .35s cubic-bezier(.4,0,.2,1);
  border-right: 1px solid var(--sidebar-border);
  box-shadow: 0 0 20px rgba(0,0,0,.04);
}

/* ══ HEADER ══ */
.sidebar-header {
  padding: 20px 20px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--sidebar-border);
}
.sidebar-logo-img {
  height: 40px;
  width: auto;
  display: block;
}
.sidebar-close {
  display: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--sidebar-text);
  font-size: 16px;
  cursor: pointer;
  transition: all .2s;
  align-items: center;
  justify-content: center;
}
.sidebar-close:hover {
  background: var(--sidebar-hover);
  color: var(--sidebar-text-active);
}

/* ══ USER ══ */
.sidebar-user {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--sidebar-border);
}
.sidebar-user-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(201,151,58,.1);
  color: var(--sidebar-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 700;
  font-family: 'Syne', sans-serif;
  flex-shrink: 0;
}
.sidebar-user-info {
  overflow: hidden;
}
.sidebar-user-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--sidebar-text-active);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.sidebar-user-role {
  font-size: 11px;
  color: var(--sidebar-text);
  margin-top: 1px;
}

/* ══ NAVIGATION ══ */
.sidebar-nav {
  padding: 12px 12px 4px;
}
.sidebar-nav-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #9ca3af;
  padding: 0 8px;
  margin-bottom: 6px;
}
.sidebar-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: var(--sidebar-radius);
  color: var(--sidebar-text);
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: all .2s ease;
  position: relative;
  margin-bottom: 2px;
}
.sidebar-link:hover {
  background: var(--sidebar-hover);
  color: var(--sidebar-text-active);
  transform: translateX(3px);
}
.sidebar-link.active {
  background: var(--sidebar-active);
  color: var(--sidebar-text-active);
  font-weight: 600;
}
.sidebar-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 20px;
  border-radius: 0 3px 3px 0;
  background: var(--sidebar-accent);
}
.sidebar-link-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  flex-shrink: 0;
  transition: transform .2s ease;
}
.sidebar-link:hover .sidebar-link-icon {
  transform: scale(1.1);
}
.sidebar-link.active .sidebar-link-icon {
  background: rgba(201,151,58,.08);
}
.sidebar-link-text {
  flex: 1;
}
.sidebar-link-indicator {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--sidebar-accent);
  animation: sidebarPulse 2s ease-in-out infinite;
}
@keyframes sidebarPulse {
  0%, 100% { opacity: .6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.2); }
}

/* ══ FOOTER ══ */
.sidebar-footer {
  margin-top: auto;
  padding: 12px;
  border-top: 1px solid var(--sidebar-border);
}
.sidebar-logout {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: var(--sidebar-radius);
  color: var(--sidebar-text);
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: all .2s ease;
}
.sidebar-logout:hover {
  background: rgba(239,68,68,.06);
  color: #EF4444;
}
.sidebar-footer-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: var(--sidebar-radius);
  color: var(--sidebar-text);
  font-size: 18px;
  text-decoration: none;
  transition: all .2s ease;
  flex-shrink: 0;
}
.sidebar-footer-btn:hover {
  background: rgba(201,151,58,.08);
  color: var(--sidebar-accent);
  transform: translateX(2px);
}

/* ══ OVERLAY (mobile) ══ */
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.3);
  backdrop-filter: blur(4px);
  z-index: 499;
  opacity: 0;
  visibility: hidden;
  transition: all .35s ease;
}
.sidebar-overlay.open {
  opacity: 1;
  visibility: visible;
}

/* ══ TOP BAR (mobile only) ══ */
.sidebar-topbar {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 62px;
  background: #ffffff;
  border-bottom: 1px solid var(--sidebar-border);
  z-index: 400;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
}
.sidebar-hamburger {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 5px;
  width: 40px;
  height: 40px;
  background: none;
  border: none;
  cursor: pointer;
  flex-shrink: 0;
}
.sidebar-hamburger span {
  display: block;
  width: 22px;
  height: 2px;
  background: #6b7280;
  border-radius: 2px;
  transition: all .3s;
}
.sidebar-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.sidebar-hamburger.open span:nth-child(2) { opacity: 0; }
.sidebar-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
.topbar-title {
  font-family: 'Syne', sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}
.topbar-logout {
  font-size: 18px;
  text-decoration: none;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: background .2s;
}
.topbar-logout:hover {
  background: rgba(239,68,68,.08);
}

/* ══ MAIN CONTENT WRAPPER ══ */
.sidebar-page {
  margin-left: var(--sidebar-width);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: margin-left .35s cubic-bezier(.4,0,.2,1);
}

/* ══ RESPONSIVE ══ */
@media(max-width: 1024px) {
  .admin-sidebar {
    transform: translateX(-100%);
  }
  .admin-sidebar.open {
    transform: translateX(0);
  }
  .sidebar-close {
    display: flex;
  }
  .sidebar-topbar {
    display: flex;
  }
  .sidebar-page {
    margin-left: 0;
    padding-top: 62px;
  }
}

/* ══ SCROLLBAR ══ */
.admin-sidebar::-webkit-scrollbar { width: 4px; }
.admin-sidebar::-webkit-scrollbar-track { background: transparent; }
.admin-sidebar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
.admin-sidebar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

<script src="../js/admin-sidebar.js"></script>
