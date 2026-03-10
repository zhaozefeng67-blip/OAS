<?php
require 'config.php';
checkAdminOrOperatorAuth();

// Disable error display to ensure only JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['universityId'])) {
    jsonResponse(false, 'Invalid data or missing ID/universityId');
}

$sid = (int)$data['universityId'];
$pid = (int)$data['id'];

// Check if officer has permission to manage this university
if (!checkOperatorSchool($sid)) {
    jsonResponse(false, 'You do not have permission to manage programs for this university');
}

// Start transaction
mysqli_begin_transaction($conn);

try {

    $delete_apply_sql = "DELETE FROM apply WHERE sid = $sid AND pid = $pid";
    if (!mysqli_query($conn, $delete_apply_sql)) {
        throw new Exception('Failed to delete related applications: ' . mysqli_error($conn));
    }

    $check_table_sql = "SHOW TABLES LIKE 'program_course'";
    $table_exists = mysqli_query($conn, $check_table_sql);
    if ($table_exists && mysqli_num_rows($table_exists) > 0) {
        $delete_program_course_sql = "DELETE FROM program_course WHERE sid = $sid AND pid = $pid";
        if (!mysqli_query($conn, $delete_program_course_sql)) {
            throw new Exception('Failed to delete related program courses: ' . mysqli_error($conn));
        }
    }


    $delete_program_sql = "DELETE FROM program WHERE sid = $sid AND pid = $pid";
    if (!mysqli_query($conn, $delete_program_sql)) {
        throw new Exception('Failed to delete program: ' . mysqli_error($conn));
    }

    mysqli_commit($conn);
    jsonResponse(true, 'Program deleted successfully!');

} catch (Exception $e) {

    mysqli_rollback($conn);
    jsonResponse(false, 'Failed to delete program: ' . $e->getMessage());
}
?>

