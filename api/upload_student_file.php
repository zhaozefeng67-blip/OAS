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

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'student') {
    jsonResponse(false, 'Unauthorized access');
}

$student_id = (int)$_SESSION['user_id'];
$upload_dir = '../uploads/students/' . $student_id . '/';

// Create directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, 'File upload failed');
}

$file = $_FILES['file'];
$file_size = $file['size'];
$max_size = 50 * 1024 * 1024; // 50MB

if ($file_size > $max_size) {
    jsonResponse(false, 'File size exceeds 50MB limit');
}

$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$allowed_exts = ['pdf', 'doc', 'docx', 'zip'];

if (!in_array($file_ext, $allowed_exts)) {
    jsonResponse(false, 'Invalid file type. Only PDF, DOC, DOCX, and ZIP files are allowed');
}

// Generate unique filename
$unique_name = time() . '_' . uniqid() . '.' . $file_ext;
$file_path = $upload_dir . $unique_name;

if (!move_uploaded_file($file_tmp, $file_path)) {
    jsonResponse(false, 'Failed to save file');
}

// Determine file type
$file_type = 'document';
if ($file_ext === 'zip') {
    $file_type = 'zip';
} elseif (in_array($file_ext, ['pdf'])) {
    $file_type = 'certificate';
}

// Insert file record into database
$relative_path = 'uploads/students/' . $student_id . '/' . $unique_name;
$insert_file_sql = "INSERT INTO files (dir_path, type) VALUES ('$relative_path', '$file_type')";
if (!mysqli_query($conn, $insert_file_sql)) {
    unlink($file_path); // Delete file if database insert fails
    jsonResponse(false, 'Failed to save file record: ' . mysqli_error($conn));
}

$fid = mysqli_insert_id($conn);

// Link file to student
$link_sql = "INSERT INTO student_files (ID, fid) VALUES ($student_id, $fid) 
             ON DUPLICATE KEY UPDATE ID = $student_id, fid = $fid";
if (!mysqli_query($conn, $link_sql)) {
    jsonResponse(false, 'Failed to link file to student: ' . mysqli_error($conn));
}

jsonResponse(true, 'File uploaded successfully', [
    'fid' => $fid,
    'filename' => $file_name,
    'path' => $relative_path,
    'type' => $file_type
]);
?>
