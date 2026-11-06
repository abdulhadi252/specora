<?php
/**
 * admin/login.php — Specora Admin Login
 * 
 * Session-based authentication using password_verify().
 * Shows field-level errors when validation fails.
 * Preserves entered email after validation failure.
 * Uses existing database connection from connect.php.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';

$errors  = [];
$timeoutNotice = isset($_GET['timeout']);
$loggedOutNotice = isset($_GET['logged_out']);

// If already logged in, redirect to dashboard
ensure_session();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    http_response_code(302);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── CSRF check ──
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['email'] = 'Invalid form token. Please try again.';
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // ── Validate ──
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    // ── Authenticate if no format errors ──
    if (empty($errors)) {
        $stmt = $connect->prepare("SELECT id, username, email, password FROM admins WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin  = $result->fetch_assoc();
            $stmt->close();

            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful — set session
                ensure_session();
                $_SESSION['admin_id']        = $admin['id'];
                $_SESSION['admin_username']  = $admin['username'];
                $_SESSION['admin_email']     = $admin['email'];
                $_SESSION['admin_logged_in']  = true;
                $_SESSION['LAST_ACTIVITY']   = time();
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                http_response_code(302);
                header('Location: index.php');
                exit;
            } else {
                // Don't reveal whether email or password was wrong
                $errors['email']    = 'Invalid email or password.';
                $errors['password'] = 'Invalid email or password.';
            }
        } else {
            $errors['email'] = 'Database error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Login — Specora</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--gray-100);color:var(--black);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
a{text-decoration:none;color:inherit;}

/* ── AUTH CARD ── */
.auth-wrap{width:100%;max-width:420px;margin:0 auto;}
.auth-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:44px 36px;box-shadow:0 8px 32px rgba(0,0,0,.06);}
.auth-logo{display:block;text-align:center;margin-bottom:8px;}
.auth-logo-img{height:48px;width:auto;display:inline-block;}
.auth-title{font-family:var(--font-display);font-size:26px;font-weight:800;color:var(--black);text-align:center;letter-spacing:-.5px;margin-bottom:6px;}
.auth-sub{font-size:14px;color:var(--gray-400);text-align:center;margin-bottom:32px;line-height:1.5;}

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

/* ── SUBMIT ── */
.btn-primary{width:100%;font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:15px;cursor:pointer;transition:all .2s;margin-top:6px;}
.btn-primary:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-1px);}
.btn-primary:active{transform:translateY(0);}

/* ── FOOTER LINK ── */
.auth-footer{text-align:center;margin-top:20px;font-size:13px;color:var(--gray-400);}
.auth-footer a{color:var(--gold-dark);font-weight:600;transition:color .2s;}
.auth-footer a:hover{color:var(--gold-dark);text-decoration:underline;}

/* ── BACK LINK ── */
.back-link{display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:24px;font-size:12px;color:var(--gray-400);transition:color .2s;}
.back-link:hover{color:var(--gold-dark);}
</style>
</head>
<body>

<div class="auth-wrap">

  <div class="auth-card">

    <?php if ($loggedOutNotice): ?>
      <div style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:500;color:#065F46;text-align:center;">
        &check; You have been logged out successfully.
      </div>
    <?php endif; ?>

    <?php if ($timeoutNotice): ?>
      <div style="background:rgba(234,189,120,.1);border:1px solid rgba(234,189,120,.25);border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:500;color:var(--gold-dark);text-align:center;">
        Session expired due to inactivity. Please login again.
      </div>
    <?php endif; ?>

    <a href="../index.php" class="auth-logo">
      <img src="../img/specora-logo.png" alt="Specora" class="auth-logo-img" />
    </a>
    <h1 class="auth-title">Admin Login</h1>
    <p class="auth-sub">Sign in to manage Specora.</p>

    <!-- FORM -->
    <form method="POST" action="" novalidate>

      <!-- Email -->
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               placeholder="admin@specora.pk"
               class="<?= isset($errors['email']) ? 'input-err' : '' ?>"
               value="<?= old_value('email') ?>" />
        <?= field_error('email', $errors) ?>
      </div>

      <!-- Password -->
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="Enter your password"
               class="<?= isset($errors['password']) ? 'input-err' : '' ?>" />
        <?= field_error('password', $errors) ?>
      </div>

      <?= csrf_field() ?>
      <button type="submit" class="btn-primary">Login →</button>
    </form>

    <!-- <div class="auth-footer">
      Don't have an account? <a href="register.php">Register</a>
    </div> -->

  </div>
</div>

<script src="../js/admin-validation.js"></script>
</body>
</html>
