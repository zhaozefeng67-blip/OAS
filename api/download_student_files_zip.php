<?php
require '../connect.php';
session_start();

// Check if user is logged in and is an officer or admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(403);
    die('Unauthorized access');
}

$user_type = $_SESSION['user_type'];
if ($user_type !== 'operator' && $user_type !== 'admin') {
    http_response_code(403);
    die('Unauthorized access');
}

if (!isset($_GET['student_id'])) {
    http_response_code(400);
    die('Student ID is required');
}

$student_id = (int)$_GET['student_id'];

// Get student information
$student_sql = "SELECT username, real_name FROM profile WHERE ID = $student_id";
$student_result = mysqli_query($conn, $student_sql);
if (!$student_result || mysqli_num_rows($student_result) === 0) {
    http_response_code(404);
    die('Student not found');
}
$student = mysqli_fetch_assoc($student_result);
$student_name = $student['real_name'] ?: $student['username'];

// Get all files for the student
$files_sql = "SELECT f.fid, f.dir_path, f.type
              FROM student_files sf
              INNER JOIN files f ON sf.fid = f.fid
              WHERE sf.ID = $student_id";

$files_result = mysqli_query($conn, $files_sql);

if (!$files_result || mysqli_num_rows($files_result) === 0) {
    http_response_code(404);
    die('No files found for this student');
}

// Create a temporary zip file
$zip_filename = tempnam(sys_get_temp_dir(), 'student_files_');
$zip = new ZipArchive();

if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    http_response_code(500);
    die('Failed to create zip file');
}

// Add files to zip
while ($file = mysqli_fetch_assoc($files_result)) {
    $file_path = '../' . $file['dir_path'];
    if (file_exists($file_path)) {
        $file_name_in_zip = basename($file['dir_path']);
        $zip->addFile($file_path, $file_name_in_zip);
    }
}

$zip->close();

// Set headers for zip download
$zip_download_name = $student_name . '_documents_' . date('Y-m-d') . '.zip';
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_download_name . '"');
header('Content-Length: ' . filesize($zip_filename));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output zip file
readfile($zip_filename);

// Clean up
unlink($zip_filename);
exit;
?>
