<?php
// ── Only require files if PHPMailer is installed ──
$hasMailer = file_exists(__DIR__ . '/PHPMailer-master/PHPMailer-master/src/PHPMailer.php');
if ($hasMailer) {
    require __DIR__ . '/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer-master/PHPMailer-master/src/SMTP.php';
    require __DIR__ . '/PHPMailer-master/PHPMailer-master/src/Exception.php';
}


/**
 * contact_process.php — Specora Contact Form Handler
 * 
 * Receives AJAX POST requests from contact.php,
 * validates the data, saves to contact_messages table,
 * and returns a JSON response.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
if (!validate_db_connection($connect)) { http_response_code(503); echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable.']); exit; }
require_once __DIR__ . '/includes/check_tables.php';
check_specora_tables();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Get and sanitize input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$category = trim($_POST['category'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors['name'] = 'Please enter your name.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if (empty($message)) {
    $errors['message'] = 'Please write a message.';
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
    "INSERT INTO contact_messages (name, email, subject, category, message, ip_address, status) 
     VALUES (?, ?, ?, ?, ?, ?, 'pending')"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
    exit;
}

$stmt->bind_param('ssssss', $name, $email, $subject, $category, $message, $ip_address);

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
        $mail->Subject = "Thanks for contacting us. – Specora";
        $mail->Body = "Your message has been successfully delivered to the admin. He will analyze your message and\n\n"
            . " reply in 24 hours. " . "\n"
            . "Thank you for choosing specora.\n\n"
            . "Best Regards,\n"
            . "Team Specora.pk";

        $mail->send();
        $emailSent = true;
    } catch (\Exception $e) {
        specora_log('Contact email failed: ' . $e->getMessage(), 4); // ERROR
        $emailWarning = ' Your message was saved, but we could not send a confirmation email. Our team will still review your inquiry.';
    }
}

if ($stmt->execute()) {
    $msg = 'Thank you for reaching out! Our team will review your message and get back to you within 24 hours.';
    if ($emailWarning) {
        $msg .= $emailWarning;
    } elseif (!$emailSent && $email) {
        $msg .= ' Note: Email confirmation could not be sent (mail server not configured).';
    }
    echo json_encode([
        'success' => true,
        'message' => $msg
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save your message. Please try again.']);
}

$stmt->close();
