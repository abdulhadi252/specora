<?php
// ── Only require files if PHPMailer is installed ──
$hasMailer = file_exists(__DIR__ . '/PHPMailer-master/PHPMailer-master/src/PHPMailer.php');
if ($hasMailer) {
    require __DIR__ . '/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer-master/PHPMailer-master/src/SMTP.php';
    require __DIR__ . '/PHPMailer-master/PHPMailer-master/src/Exception.php';
}

/**
 * feedback_process.php — Specora User Feedback Handler
 * 
 * Receives AJAX POST requests from contact.php feedback form,
 * validates the data, saves to user_feedback table,
 * handles optional image upload, and returns a JSON response.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
if (!validate_db_connection($connect)) { http_response_code(503); echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable.']); exit; }
require_once __DIR__ . '/includes/loadenv.php';
require_once __DIR__ . '/includes/check_tables.php';
check_specora_tables();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Get and sanitize input
$name         = trim($_POST['name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$rating       = (int)($_POST['rating'] ?? 5);
$feedbackText = trim($_POST['feedback'] ?? '');
$city         = trim($_POST['city'] ?? '');
$photoUrl     = '';

// Validation
$errors = [];

if (empty($name)) {
    $errors['name'] = 'Please enter your name.';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($rating < 1 || $rating > 5) {
    $rating = 5;
}

if (empty($feedbackText)) {
    $errors['feedback'] = 'Please write your feedback.';
}

if (strlen($feedbackText) > 1000) {
    $errors['feedback'] = 'Feedback must not exceed 1000 characters.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please fix the highlighted fields and try again.', 'errors' => $errors]);
    exit;
}

// Get client IP
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

// Insert into database
$stmt = $connect->prepare(
    "INSERT INTO user_feedback (name, email, rating, feedback_text, photo_url, city, ip_address) 
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
    exit;
}

$stmt->bind_param('ssissss', $name, $email, $rating, $feedbackText, $photoUrl, $city, $ip_address);

// ── Try sending confirmation email first — so user gets feedback about email status ──
$emailSent = false;
$emailWarning = '';
if ($email && $hasMailer && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME') ?: '';
        $mail->Password   = getenv('SMTP_PASSWORD') ?: '';
        $mail->SMTPSecure = getenv('SMTP_ENCRYPTION') ?: 'tls';
        $mail->Port       = (int)(getenv('SMTP_PORT') ?: 587);

        $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: 'noreply@specora.pk', getenv('SMTP_FROM_NAME') ?: 'Specora');
        $mail->addAddress($email);
        $mail->Subject = "Thanks for giving us reviews – Specora";
        $mail->Body = "Your feedback has been received and will help us improve!\n\n"
            . "Thank you for choosing specora.\n\n"
            . "Best Regards,\n"
            . "Team Specora.pk";

        $mail->send();
        $emailSent = true;
    } catch (\Exception $e) {
        specora_log('Feedback email failed: ' . $e->getMessage(), 4); // ERROR
        $emailWarning = ' Your feedback was saved, but we could not send a confirmation email. It will still be reviewed.';
    }
}

if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message' => 'Thank you for your feedback! It will be reviewed and published soon.'
    ];
    if ($emailWarning) {
        $response['message'] .= $emailWarning;
    } elseif (!$emailSent && $email) {
        $response['message'] .= ' Note: Email confirmation could not be sent (mail server not configured).';
    }
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save your feedback. Please try again.']);
}

$stmt->close();
