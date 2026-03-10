<?php
require 'config.php';
checkAdminAuth();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['universityId'])) {
    jsonResponse(false, 'Invalid data or missing application ID or university ID.');
}

$profile_id = (int)$data['id'];
$university_id = (int)$data['universityId'];

// Get pending officer information from profile table
$profile_sql = "SELECT ID, username, password, real_name, email, type, status FROM profile WHERE ID = $profile_id AND type = 'operator'";
$profile_result = mysqli_query($conn, $profile_sql);

if (mysqli_num_rows($profile_result) == 0) {
    jsonResponse(false, 'Profile not found or not an operator account');
}

$profile_row = mysqli_fetch_assoc($profile_result);

// Check if status is pending (if status field exists)
if (isset($profile_row['status']) && $profile_row['status'] !== 'pending') {
    jsonResponse(false, 'This application has already been processed');
}

$operator_id = (int)$profile_row['ID'];
$username = $profile_row['username'];
$password = $profile_row['password'];

// Check if record already exists in operator_school table
$check_sql = "SELECT sid FROM operator_school WHERE ID = $operator_id";
$check_result = mysqli_query($conn, $check_sql);
$existing_record = mysqli_fetch_assoc($check_result);

if ($existing_record) {
    // If record exists, update school ID and status (admin may have modified assigned university)
    $update_sql = "UPDATE operator_school SET sid = $university_id, status = 'approved' WHERE ID = $operator_id";
    if (!mysqli_query($conn, $update_sql)) {
        $error_msg = mysqli_error($conn);
        // If status field doesn't exist, try updating without status (backward compatibility)
        if (strpos($error_msg, 'Unknown column') !== false && strpos($error_msg, 'status') !== false) {
    $update_sql = "UPDATE operator_school SET sid = $university_id WHERE ID = $operator_id";
    if (!mysqli_query($conn, $update_sql)) {
        jsonResponse(false, 'Failed updating school assignment: ' . mysqli_error($conn));
            }
        } else {
            jsonResponse(false, 'Failed updating school assignment: ' . $error_msg);
        }
    }
} else {
    // If no record exists, insert new record (this shouldn't happen as it's inserted during registration)
    $insert_sql = "INSERT INTO operator_school (ID, sid, status) VALUES ($operator_id, $university_id, 'approved')";
    if (!mysqli_query($conn, $insert_sql)) {
        $error_msg = mysqli_error($conn);
        // If status field doesn't exist, try inserting without status (backward compatibility)
        if (strpos($error_msg, 'Unknown column') !== false && strpos($error_msg, 'status') !== false) {
    $insert_sql = "INSERT INTO operator_school (ID, sid) VALUES ($operator_id, $university_id)";
    if (!mysqli_query($conn, $insert_sql)) {
        jsonResponse(false, 'Failed assigning school: ' . mysqli_error($conn));
            }
        } else {
            jsonResponse(false, 'Failed assigning school: ' . $error_msg);
        }
    }
}

// Update profile table status (from pending to approved)
$update_profile_sql = "UPDATE profile SET status = 'approved' WHERE ID = $operator_id";
if (!mysqli_query($conn, $update_profile_sql)) {
    $error_msg = mysqli_error($conn);
    // If status field doesn't exist, try not updating status (backward compatibility)
    if (strpos($error_msg, 'Unknown column') !== false && strpos($error_msg, 'status') !== false) {
        // status field doesn't exist, this is acceptable (backward compatibility)
        error_log("Warning: status column does not exist in profile table");
    } else {
        // Other errors, return failure
        jsonResponse(false, 'Failed to update profile status: ' . $error_msg);
    }
}

jsonResponse(true, 'Operator account created and assigned successfully', [
    'operatorId' => $operator_id,
    'username' => $username,
    'password' => $password
]);
?>

