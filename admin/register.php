<?php
/**
 * admin/register.php — Specora Admin Registration + Admin List
 * 
 * Now includes sidebar layout and shows all registered admins
 * with the currently logged-in admin highlighted.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';

// Require login — only admins can access this page
require_admin_login();

// Get current admin info
$currentAdminId    = (int)($_SESSION['admin_id'] ?? 0);
$currentAdminName  = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');

// Fetch all admins
$admins = [];
$rAll = mysqli_query($connect, "SELECT id, username, email, created_at FROM admins ORDER BY created_at ASC");
if ($rAll) {
    while ($row = mysqli_fetch_assoc($rAll)) {
        $admins[] = $row;
    }
}

$errors     = [];
$formErrors = [];
$success    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── CSRF check ──
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $formErrors[] = 'Invalid form token. Please try again.';
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ── Validate ──
    $errUser  = validate_username($username);
    $errEmail = validate_email($email);
    $errPass  = validate_password($password);
    $errConf  = validate_confirm_password($password, $confirm);

    if ($errUser) $errors['username'] = $errUser;
    if ($errEmail) $errors['email'] = $errEmail;
    if ($errPass) $errors['password'] = $errPass;
    if ($errConf) $errors['confirm_password'] = $errConf;

    // ── Uniqueness checks ──
    if (!isset($errors['email']) && email_exists($connect, $email)) {
        $errors['email'] = 'This email is already registered.';
    }
    if (!isset($errors['username']) && username_exists($connect, $username)) {
        $errors['username'] = 'This username is already taken.';
    }

    // ── Save to DB if no errors ──
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connect->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
            if ($stmt->execute()) {
                $success = true;
                // Refresh admin list
                $admins = [];
                $rAll = mysqli_query($connect, "SELECT id, username, email, created_at FROM admins ORDER BY created_at ASC");
                if ($rAll) {
                    while ($row = mysqli_fetch_assoc($rAll)) {
                        $admins[] = $row;
                    }
                }
            } else {
                $formErrors[] = 'Database error: Could not create admin account.';
            }
            $stmt->close();
        } else {
            $formErrors[] = 'Database error: Failed to prepare statement.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Add Admin — Specora Admin</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--gray-100);color:var(--black);min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;color:inherit;}

/* ── MAIN ── */
.main{padding:40px 48px;flex:1;max-width:1200px;margin:0 auto;width:100%;}

/* ── HEADER ── */
.page-header{margin-bottom:36px;}
.page-eyebrow{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold-dark);margin-bottom:6px;}
.page-title{font-family:var(--font-display);font-size:clamp(24px,3.5vw,36px);font-weight:800;color:var(--black);letter-spacing:-1px;margin-bottom:6px;}
.page-sub{font-size:15px;color:var(--gray-600);}

/* ── TWO-COLUMN ── */
.admin-grid{display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start;}

/* ── CARD ── */
.card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:32px;box-shadow:0 4px 16px rgba(0,0,0,.04);}
.card-title{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--black);letter-spacing:-.5px;margin-bottom:6px;}
.card-sub{font-size:14px;color:var(--gray-400);margin-bottom:24px;line-height:1.5;}

/* ── SUCCESS ── */
.success-box{text-align:center;padding:20px 0;}
.success-icon{width:68px;height:68px;border-radius:50%;background:rgba(234,189,120,.1);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;}
.success-box h3{font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--black);margin-bottom:6px;}
.success-box p{font-size:14px;color:var(--gray-400);margin-bottom:20px;line-height:1.6;}
.success-box .btn-gold{display:inline-block;font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);background:var(--gold);border-radius:var(--radius-pill);padding:12px 28px;cursor:pointer;transition:all .2s;border:none;text-decoration:none;}
.success-box .btn-gold:hover{background:var(--gold-dark);color:var(--white);}

/* ── FORM ERRORS ── */
.form-errors{background:#FEF2F2;border:1px solid #FECACA;border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:20px;}
.form-errors p{font-size:13px;font-weight:600;color:#991B1B;margin-bottom:4px;}
.form-errors ul{list-style:none;padding:0;}
.form-errors li{font-size:12px;color:#B91C1C;padding:2px 0;padding-left:14px;position:relative;}
.form-errors li::before{content:'•';position:absolute;left:2px;color:#EF4444;}

/* ── FIELD ── */
.field{display:flex;flex-direction:column;gap:6px;margin-bottom:18px;}
.field label{font-size:11px;font-weight:700;color:var(--gray-600);letter-spacing:.8px;text-transform:uppercase;}
.field input{font-family:var(--font-body);font-size:14px;color:var(--black);background:var(--gray-100);border:1.5px solid transparent;border-radius:var(--radius-sm);padding:13px 16px;outline:none;transition:all .2s;width:100%;}
.field input:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px rgba(234,189,120,.1);}
.field input.input-err{border-color:#EF4444;background:#FEF2F2;}
.field input.input-err:focus{border-color:#EF4444;box-shadow:0 0 0 3px rgba(239,68,68,.1);}
.field input::placeholder{color:var(--gray-400);}
.field-err{font-size:12px;color:#DC2626;font-weight:500;display:flex;align-items:center;gap:4px;}
.field-err::before{content:'⚠';font-size:11px;}
.pw-hint{font-size:11px;color:var(--gray-400);margin-top:2px;line-height:1.5;padding-left:2px;}

/* ── SUBMIT ── */
.btn-primary{width:100%;font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:15px;cursor:pointer;transition:all .2s;margin-top:6px;}
.btn-primary:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-1px);}
.btn-primary:active{transform:translateY(0);}

/* ── ADMIN LIST ── */
.list-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;}
.list-count{font-size:13px;color:var(--gray-400);}
.list-count strong{color:var(--gray-600);}
.admin-list{display:flex;flex-direction:column;gap:8px;}
.admin-row{display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);transition:all .2s;}
.admin-row.current{background:rgba(234,189,120,.06);border-color:rgba(234,189,120,.3);}
.admin-row:hover{transform:translateX(3px);}
.admin-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;font-family:var(--font-display);flex-shrink:0;}
.admin-avatar.self{background:var(--gold-light);color:var(--gold-dark);}
.admin-avatar.other{background:var(--gray-200);color:var(--gray-600);}
.admin-details{flex:1;min-width:0;}
.admin-name{font-size:14px;font-weight:600;color:var(--black);}
.admin-email{font-size:12px;color:var(--gray-400);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.admin-date{font-size:11px;color:var(--gray-400);white-space:nowrap;}
.admin-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:var(--radius-pill);text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;}
.admin-badge.you{background:rgba(234,189,120,.15);color:var(--gold-dark);}
.admin-badge.registered{background:rgba(16,185,129,.08);color:#065F46;}
.no-admins{text-align:center;padding:40px 20px;color:var(--gray-400);}
.no-admins-icon{font-size:40px;margin-bottom:10px;}

/* ── FOOTER ── */
footer{background:var(--white);border-top:1px solid var(--gray-200);padding:20px 48px;text-align:center;font-size:13px;color:var(--gray-400);}
footer span{color:var(--gold);}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .main{padding:32px 24px;}
  .admin-grid{grid-template-columns:1fr;gap:24px;}
}
@media(max-width:768px){
  .main{padding:24px 20px;}
  .card{padding:24px 20px;}
  .admin-row{flex-wrap:wrap;}
  .admin-date{width:100%;padding-left:54px;}
  footer{padding:16px 20px;}
}
</style>
</head>
<body>

<?php $currentPage = 'register'; include __DIR__ . '/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="sidebar-page">
<div class="main">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title">Manage Admins</h1>
    <p class="page-sub">Create new admin accounts and view all registered admins.</p>
  </div>

  <div class="admin-grid">

    <!-- LEFT: REGISTER FORM -->
    <div class="card">
      <div class="card-title">Create New Admin</div>
      <div class="card-sub">Add another administrator to manage Specora.</div>

      <?php if ($success): ?>
        <div class="success-box">
          <div class="success-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
          <h3>Admin Created!</h3>
          <p>New admin account has been registered successfully.</p>
          <a href="register.php" class="btn-gold">Add Another →</a>
        </div>
      <?php else: ?>

        <?php if (!empty($formErrors)): ?>
          <div class="form-errors">
            <p>Please fix the following errors:</p>
            <ul>
              <?php foreach ($formErrors as $fe): ?>
                <li><?= htmlspecialchars($fe) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
          <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   placeholder="e.g. admin_specora"
                   class="<?= isset($errors['username']) ? 'input-err' : '' ?>"
                   value="<?= old_value('username') ?>" maxlength="30" />
            <?= field_error('username', $errors) ?>
          </div>

          <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   placeholder="admin@specora.pk"
                   class="<?= isset($errors['email']) ? 'input-err' : '' ?>"
                   value="<?= old_value('email') ?>" />
            <?= field_error('email', $errors) ?>
          </div>

          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="At least 8 characters"
                   class="<?= isset($errors['password']) ? 'input-err' : '' ?>" />
            <?= field_error('password', $errors) ?>
            <div class="pw-hint">Must contain: uppercase, lowercase, number, and special character.</div>
          </div>

          <div class="field">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Re-enter password"
                   class="<?= isset($errors['confirm_password']) ? 'input-err' : '' ?>" />
            <?= field_error('confirm_password', $errors) ?>
          </div>

          <?= csrf_field() ?>
          <button type="submit" class="btn-primary">Register Admin →</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- RIGHT: ADMIN LIST -->
    <div class="card">
      <div class="list-header">
        <div>
          <div class="card-title" style="margin-bottom:2px;">All Admins</div>
          <div class="list-count"><strong><?= count($admins) ?></strong> admin<?= count($admins) !== 1 ? 's' : '' ?> registered</div>
        </div>
      </div>

      <?php if (empty($admins)): ?>
        <div class="no-admins">
          <div class="no-admins-icon"><?= svg_icon('user', 36) ?></div>
          <div style="font-size:15px;font-weight:600;color:var(--gray-600);">No admins found</div>
          <div style="font-size:13px;margin-top:4px;">Create the first admin account using the form.</div>
        </div>
      <?php else: ?>
        <div class="admin-list">
          <?php foreach ($admins as $admin):
            $isCurrent = ((int)$admin['id'] === $currentAdminId);
            $initial   = strtoupper(substr($admin['username'], 0, 1));
            $created   = date('M j, Y', strtotime($admin['created_at']));
          ?>
            <div class="admin-row <?= $isCurrent ? 'current' : '' ?>">
              <div class="admin-avatar <?= $isCurrent ? 'self' : 'other' ?>"><?= $initial ?></div>
              <div class="admin-details">
                <div class="admin-name">
                  <?= htmlspecialchars($admin['username']) ?>
                  <?php if ($isCurrent): ?>
                    <span class="admin-badge you">You</span>
                  <?php endif; ?>
                </div>
                <div class="admin-email"><?= htmlspecialchars($admin['email']) ?></div>
              </div>
              <div class="admin-date">
                <?php if ($isCurrent): ?>
                  <span class="admin-badge you">Active Now</span>
                <?php else: ?>
                  <span class="admin-badge registered"><?= $created ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

</div>

<!-- FOOTER -->
<footer>
  <p>© 2025 <span>Specora</span> Admin Panel. All rights reserved.</p>
</footer>

</div><!-- /sidebar-page -->
<script src="../js/admin-validation.js"></script>
</body>
</html>
