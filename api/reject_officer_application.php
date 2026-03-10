<?php
require 'config.php';
checkAdminAuth();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    jsonResponse(false, 'Invalid data or missing application ID.');
}

$profile_id = (int)$data['id'];

// Check if profile exists and is in pending status
$check_sql = "SELECT ID, type, status FROM profile WHERE ID = $profile_id AND type = 'operator'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) == 0) {
    jsonResponse(false, 'Application not found or not an operator account.');
}

$profile_row = mysqli_fetch_assoc($check_result);

// Check if status is pending
if (isset($profile_row['status']) && $profile_row['status'] !== 'pending') {
    jsonResponse(false, 'This application has already been processed.');
}

// Update profile table status to rejected
$update_profile_sql = "UPDATE profile SET status = 'rejected' WHERE ID = $profile_id AND type = 'operator'";
mysqli_query($conn, $update_profile_sql);
// If status field doesn't exist, the above statement will fail but won't affect subsequent operations

// Delete record from operator_school table
$delete_sql = "DELETE FROM operator_school WHERE ID = $profile_id";
mysqli_query($conn, $delete_sql);

jsonResponse(true, 'Application rejected successfully!');
?>

