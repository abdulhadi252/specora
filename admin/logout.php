<?php
/**
 * admin/logout.php — Specora Admin Logout
 * 
 * Destroys the admin session and redirects to login page.
 * Requires a valid CSRF token to prevent logout CSRF attacks.
 */

require_once __DIR__ . '/../includes/admin_functions.php';

ensure_session();

// ── CSRF protection ──
// Validate the CSRF token from the URL query parameter
if (!validate_csrf_token($_GET['csrf_token'] ?? null)) {
    // Invalid or missing token — redirect to dashboard with error
    http_response_code(302);
    header('Location: index.php?error=invalid_logout');
    exit;
}

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login with success notice
http_response_code(302);
header('Location: login.php?logged_out=1');
exit;
