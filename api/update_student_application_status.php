<?php
require 'config.php';
checkAdminAuth();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['studentId']) || !isset($data['sid']) || !isset($data['pid']) || !isset($data['status'])) {
    jsonResponse(false, 'Invalid data or missing required fields.');
}

$student_id = (int)$data['studentId'];
$sid = (int)$data['sid'];
$pid = (int)$data['pid'];
$status = $conn->real_escape_string($data['status']);

// Validate status value
if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
    jsonResponse(false, 'Invalid status');
}

// Update application status
$update_sql = "UPDATE apply SET status = '$status' WHERE ID = $student_id AND sid = $sid AND pid = $pid";

if (!mysqli_query($conn, $update_sql)) {
    jsonResponse(false, 'Failed to update application status: ' . mysqli_error($conn));
}

// Check if record was actually updated
if (mysqli_affected_rows($conn) == 0) {
    jsonResponse(false, 'Application not found.');
}

jsonResponse(true, 'Application status updated successfully!');
?>

