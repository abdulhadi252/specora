<?php
/**
 * testimonial_process.php — Specora Testimonial Submission Handler
 * 
 * Receives AJAX POST requests from contact.php testimonial form,
 * validates the data, saves to testimonials table with status='pending',
 * and returns a JSON response.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
if (!validate_db_connection($connect)) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Service temporarily unavailable.']);
    exit;
}
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
$city = trim($_POST['city'] ?? '');
$feedback = trim($_POST['feedback'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors['name'] = 'Please enter your name.';
}

if (empty($feedback)) {
    $errors['feedback'] = 'Please write your testimonial.';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please fix the highlighted fields and try again.', 'errors' => $errors]);
    exit;
}

// Insert into database with status='pending'
$stmt = $connect->prepare(
    "INSERT INTO testimonials (name, email, city, feedback, status) 
     VALUES (?, ?, ?, ?, 'pending')"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
    exit;
}

$stmt->bind_param('ssss', $name, $email, $city, $feedback);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your feedback! It will be visible after admin approval.'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save your testimonial. Please try again.']);
}

$stmt->close();
