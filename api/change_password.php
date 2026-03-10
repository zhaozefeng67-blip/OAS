<?php
require '../connect.php';
session_start();

function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Check if logged in
if (!isset($_SESSION['username'])) {
    jsonResponse(false, 'Please login first');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['current']) || !isset($data['new']) || !isset($data['confirm'])) {
    jsonResponse(false, 'Invalid data or missing required fields');
}

$username = $_SESSION['username'];
$current = trim($data['current']);
$new = trim($data['new']);
$confirm = trim($data['confirm']);

// Validate required fields
if (empty($current) || empty($new) || empty($confirm)) {
    jsonResponse(false, 'Please fill all password fields');
}

// Validate new password and confirm password match
if ($new !== $confirm) {
    jsonResponse(false, 'New password and confirm password do not match');
}

// Validate new password length
if (strlen($new) < 6) {
    jsonResponse(false, 'New password must be at least 6 characters');
}

// Validate new password is different from current password
if ($current === $new) {
    jsonResponse(false, 'New password cannot be the same as current password');
}

// Query current user
$stmt = $conn->prepare("SELECT password FROM profile WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    jsonResponse(false, 'User not found');
}

// Validate current password
if ($row['password'] !== $current) {
    jsonResponse(false, 'Current password is incorrect');
}

// Update password
$update_stmt = $conn->prepare("UPDATE profile SET password = ? WHERE username = ?");
$update_stmt->bind_param("ss", $new, $username);

if (!$update_stmt->execute()) {
    jsonResponse(false, 'Failed to update password: ' . mysqli_error($conn));
}

jsonResponse(true, 'Password changed successfully!');
?>

