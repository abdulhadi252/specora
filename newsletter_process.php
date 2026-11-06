<?php
/**
 * newsletter_process.php — Specora Newsletter Subscription Handler
 *
 * AJAX endpoint: accepts POST with email, validates it, checks for
 * duplicates, and saves to newsletter_subscribers table.
 *
 * Response: JSON { success: bool, message: string }
 */

// ── CORS & Content-Type headers (AJAX) ──
header('Content-Type: application/json');

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── Load dependencies ──
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/check_tables.php';

// ── Ensure tables exist ──
check_specora_tables();

// ── Validate database connection ──
if (!isset($connect) || !$connect instanceof mysqli || $connect->connect_error) {
    specora_log('Newsletter: Database connection failed', 5); // CRITICAL
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable. Please try again later.']);
    exit;
}

// ── Get and validate email ──
$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter your email address.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($email) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email address is too long.']);
    exit;
}

// ── Get client IP ──
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
$ipAddress = substr($ipAddress, 0, 45);

// ── Check for duplicate ──
$checkStmt = $connect->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
if (!$checkStmt) {
    specora_log('Newsletter: Prepare failed (SELECT) - ' . $connect->error, 4); // ERROR
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    exit;
}

$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    // Subscriber already exists
    $checkStmt->bind_result($existingId, $existingStatus);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($existingStatus === 'active') {
        echo json_encode(['success' => true, 'message' => 'You\'re already subscribed! Stay tuned for updates.']);
        exit;
    } else {
        // Re-subscribe: update status back to active
        $reStmt = $connect->prepare("UPDATE newsletter_subscribers SET status = 'active', ip_address = ? WHERE id = ?");
        if ($reStmt) {
            $reStmt->bind_param('si', $ipAddress, $existingId);
            $reStmt->execute();
            $reStmt->close();
        }
        echo json_encode(['success' => true, 'message' => 'Welcome back! You\'ve been re-subscribed.']);
        exit;
    }
}
$checkStmt->close();

// ── Insert new subscriber ──
$insertStmt = $connect->prepare("INSERT INTO newsletter_subscribers (email, ip_address) VALUES (?, ?)");
if (!$insertStmt) {
    specora_log('Newsletter: Prepare failed (INSERT) - ' . $connect->error, 4); // ERROR
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    exit;
}

$insertStmt->bind_param('ss', $email, $ipAddress);

if ($insertStmt->execute()) {
    $insertStmt->close();
    specora_log("Newsletter: New subscriber - $email", 2); // INFO
    echo json_encode(['success' => true, 'message' => '🎉 You\'re subscribed! Thanks for joining.']);
    exit;
} else {
    $error = $insertStmt->error;
    $insertStmt->close();

    // Handle duplicate entry race condition
    if (strpos($error, 'Duplicate') !== false) {
        echo json_encode(['success' => true, 'message' => 'You\'re already subscribed!']);
        exit;
    }

    specora_log('Newsletter: Insert failed - ' . $error, 4); // ERROR
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    exit;
}
