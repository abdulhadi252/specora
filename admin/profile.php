<?php
/**
 * admin/profile.php — Specora Admin Profile
 * 
 * Allows admins to edit their own username, email, and password.
 * Uses existing validation helpers from admin_functions.php.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';

// Require login — only admins can access this page
require_admin_login();

$adminId       = (int)($_SESSION['admin_id'] ?? 0);
$adminUsername = $_SESSION['admin_username'] ?? '';
$adminEmail    = $_SESSION['admin_email'] ?? '';

$errors     = [];
$formErrors = [];
$success    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── CSRF check ──
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $formErrors[] = 'Invalid form token. Please try again.';
    }

    $newUsername = trim($_POST['username'] ?? '');
    $newEmail    = trim($_POST['email'] ?? '');
    $curPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $changingPassword = ($curPassword !== '' || $newPassword !== '' || $confirmPass !== '');

    // ── Validate username ──
    $errUser = validate_username($newUsername);
    if ($errUser) {
        $errors['username'] = $errUser;
    } elseif ($newUsername !== $adminUsername && username_exists($connect, $newUsername, $adminId)) {
        $errors['username'] = 'This username is already taken.';
    }

    // ── Validate email ──
    $errEmail = validate_email($newEmail);
    if ($errEmail) {
        $errors['email'] = $errEmail;
    } elseif ($newEmail !== $adminEmail && email_exists($connect, $newEmail, $adminId)) {
        $errors['email'] = 'This email is already registered.';
    }

    // ── If changing password, validate ──
    if ($changingPassword) {
        // Verify current password
        $stmt = $connect->prepare("SELECT password FROM admins WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $result = $stmt->get_result();
            $adminRow = $result->fetch_assoc();
            $stmt->close();

            if (!$adminRow || !password_verify($curPassword, $adminRow['password'])) {
                $errors['current_password'] = 'Current password is incorrect.';
            }
        } else {
            $formErrors[] = 'Database error. Please try again.';
        }

        $errPass = validate_password($newPassword);
        if ($errPass) {
            $errors['new_password'] = $errPass;
        }

        $errConf = validate_confirm_password($newPassword, $confirmPass);
        if ($errConf) {
            $errors['confirm_password'] = $errConf;
        }
    }

    // ── Save changes if no errors ──
    if (empty($errors) && empty($formErrors)) {
        $hasUpdates = false;
        $updates    = [];
        $params     = [];
        $types      = '';

        if ($newUsername !== $adminUsername) {
            $updates[] = "username = ?";
            $params[]  = $newUsername;
            $types    .= 's';
            $hasUpdates = true;
        }

        if ($newEmail !== $adminEmail) {
            $updates[] = "email = ?";
            $params[]  = $newEmail;
            $types    .= 's';
            $hasUpdates = true;
        }

        if ($changingPassword && $newPassword !== '') {
            $hashedPw  = password_hash($newPassword, PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[]  = $hashedPw;
            $types    .= 's';
            $hasUpdates = true;
        }

        if ($hasUpdates) {
            $params[]  = $adminId;
            $types    .= 'i';
            $sql = "UPDATE admins SET " . implode(', ', $updates) . " WHERE id = ?";

            $stmt = $connect->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    // Update session variables
                    $_SESSION['admin_username'] = $newUsername;
                    $_SESSION['admin_email']    = $newEmail;
                    $adminUsername = $newUsername;
                    $adminEmail    = $newEmail;
                    $success = true;
                } else {
                    $formErrors[] = 'Database error: Could not update profile.';
                }
                $stmt->close();
            } else {
                $formErrors[] = 'Database error: Failed to prepare statement.';
            }
        } else {
            $success = true; // Nothing changed — that's fine
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Edit Profile — Specora Admin</title>
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

/* ── PROFILE GRID ── */
.profile-grid{display:grid;grid-template-columns:280px 1fr;gap:32px;align-items:start;}

/* ── AVATAR CARD ── */
.avatar-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:36px 24px;text-align:center;box-shadow:0 4px 16px rgba(0,0,0,.04);}
.avatar-circle{width:100px;height:100px;border-radius:50%;background:rgba(234,189,120,.12);border:3px solid var(--gold-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-family:var(--font-display);font-size:42px;font-weight:800;color:var(--gold-dark);}
.avatar-name{font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--black);letter-spacing:-.5px;margin-bottom:4px;}
.avatar-email{font-size:13px;color:var(--gray-400);margin-bottom:8px;}
.avatar-role{display:inline-block;font-size:11px;font-weight:700;padding:4px 14px;border-radius:var(--radius-pill);background:rgba(234,189,120,.1);color:var(--gold-dark);text-transform:uppercase;letter-spacing:.8px;}

/* ── CARD ── */
.card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:32px;box-shadow:0 4px 16px rgba(0,0,0,.04);}
.card-title{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--black);letter-spacing:-.5px;margin-bottom:6px;}
.card-sub{font-size:14px;color:var(--gray-400);margin-bottom:24px;line-height:1.5;}

/* ── SUCCESS ── */
.success-box{text-align:center;padding:20px 0;}
.success-icon{width:68px;height:68px;border-radius:50%;background:rgba(16,185,129,.06);border:2px solid #10B981;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;color:#065F46;}
.success-box h3{font-family:var(--font-display);font-size:20px;font-weight:800;color:var(--black);margin-bottom:6px;}
.success-box p{font-size:14px;color:var(--gray-400);margin-bottom:4px;line-height:1.6;}

/* ── DIVIDER ── */
.form-divider{border:none;border-top:1px solid var(--gray-200);margin:24px 0;}
.form-divider-label{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--gray-400);margin-bottom:16px;}

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
.field-hint{font-size:11px;color:var(--gray-400);margin-top:2px;line-height:1.5;padding-left:2px;}
.field-readonly{opacity:.6;pointer-events:none;}

/* ── BUTTONS ── */
.btn-primary{font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:15px 32px;cursor:pointer;transition:all .2s;margin-top:6px;display:inline-flex;align-items:center;gap:8px;}
.btn-primary:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-1px);}
.btn-primary:active{transform:translateY(0);}
.btn-secondary{font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--gray-600);background:transparent;border:1.5px solid var(--gray-200);border-radius:var(--radius-pill);padding:11px 24px;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:6px;text-decoration:none;}
.btn-secondary:hover{background:var(--gray-100);color:var(--black);border-color:var(--gray-400);}
.btn-row{display:flex;align-items:center;gap:12px;margin-top:6px;}

/* ── FOOTER ── */
footer{background:var(--white);border-top:1px solid var(--gray-200);padding:20px 48px;text-align:center;font-size:13px;color:var(--gray-400);}
footer span{color:var(--gold);}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .main{padding:32px 24px;}
  .profile-grid{grid-template-columns:1fr;gap:24px;}
  .avatar-card{display:flex;align-items:center;gap:20px;text-align:left;padding:24px;}
  .avatar-circle{margin:0;width:80px;height:80px;font-size:34px;flex-shrink:0;}
}
@media(max-width:768px){
  .main{padding:24px 20px;}
  .card{padding:24px 20px;}
  .btn-row{flex-direction:column;align-items:stretch;}
  footer{padding:16px 20px;}
}
</style>
</head>
<body>

<?php $currentPage = 'profile'; include __DIR__ . '/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="sidebar-page">
<div class="main">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title">Edit Profile</h1>
    <p class="page-sub">Update your account details and password.</p>
  </div>

  <div class="profile-grid">

    <!-- LEFT: AVATAR -->
    <div class="avatar-card">
      <?php
        $avInitial = strtoupper(substr($adminUsername, 0, 1) ?: 'A');
      ?>
      <div class="avatar-circle"><?= $avInitial ?></div>
      <div class="avatar-name"><?= htmlspecialchars($adminUsername) ?></div>
      <div class="avatar-email"><?= htmlspecialchars($adminEmail) ?></div>
      <div class="avatar-role">Administrator</div>
    </div>

    <!-- RIGHT: EDIT FORM -->
    <div class="card">
      <div class="card-title">Account Details</div>
      <div class="card-sub">Change your username, email, or password below.</div>

      <?php if ($success): ?>
        <div class="success-box">
          <div class="success-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
          <h3>Profile Updated!</h3>
          <p>Your account details have been saved successfully.</p>
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

          <!-- ── BASIC INFO ── -->
          <div class="form-divider-label">Basic Information</div>

          <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   placeholder="Your username"
                   class="<?= isset($errors['username']) ? 'input-err' : '' ?>"
                   value="<?= htmlspecialchars($_POST['username'] ?? $adminUsername) ?>" maxlength="30" />
            <?= field_error('username', $errors) ?>
          </div>

          <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   placeholder="admin@specora.pk"
                   class="<?= isset($errors['email']) ? 'input-err' : '' ?>"
                   value="<?= htmlspecialchars($_POST['email'] ?? $adminEmail) ?>" />
            <?= field_error('email', $errors) ?>
          </div>

          <hr class="form-divider"/>

          <!-- ── PASSWORD CHANGE ── -->
          <div class="form-divider-label">Change Password (optional)</div>

          <div class="field">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password"
                   placeholder="Enter current password"
                   class="<?= isset($errors['current_password']) ? 'input-err' : '' ?>" />
            <?= field_error('current_password', $errors) ?>
          </div>

          <div class="field">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password"
                   placeholder="At least 8 characters"
                   class="<?= isset($errors['new_password']) ? 'input-err' : '' ?>" />
            <?= field_error('new_password', $errors) ?>
            <div class="field-hint">Must contain: uppercase, lowercase, number, and special character.</div>
          </div>

          <div class="field">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Re-enter new password"
                   class="<?= isset($errors['confirm_password']) ? 'input-err' : '' ?>" />
            <?= field_error('confirm_password', $errors) ?>
          </div>

          <?= csrf_field() ?>

          <div class="btn-row">
            <button type="submit" class="btn-primary">Save Changes →</button>
            <a href="index.php" class="btn-secondary">Cancel</a>
          </div>
        </form>

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
