<?php
require '../connect.php';
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'student') {
    jsonResponse(false, 'Unauthorized access');
}

function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['fid'])) {
    jsonResponse(false, 'File ID is required');
}

$fid = (int)$data['fid'];

// Verify file belongs to student
$check_sql = "SELECT f.dir_path FROM files f
              INNER JOIN student_files sf ON f.fid = sf.fid
              WHERE f.fid = $fid AND sf.ID = $student_id";

$check_result = mysqli_query($conn, $check_sql);

if (!$check_result || mysqli_num_rows($check_result) === 0) {
    jsonResponse(false, 'File not found or access denied');
}

$file = mysqli_fetch_assoc($check_result);
$file_path = '../' . $file['dir_path'];

// Delete file from filesystem
if (file_exists($file_path)) {
    unlink($file_path);
}

// Delete from student_files (cascade will delete from files)
$delete_sql = "DELETE FROM student_files WHERE fid = $fid AND ID = $student_id";

if (!mysqli_query($conn, $delete_sql)) {
    jsonResponse(false, 'Failed to delete file record: ' . mysqli_error($conn));
}

jsonResponse(true, 'File deleted successfully');
?>
