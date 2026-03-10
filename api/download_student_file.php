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

if (!isset($_GET['fid'])) {
    http_response_code(400);
    die('File ID is required');
}

$fid = (int)$_GET['fid'];

// Get file information
$sql = "SELECT f.dir_path, f.type, sf.ID as student_id, p.username
        FROM files f
        INNER JOIN student_files sf ON f.fid = sf.fid
        INNER JOIN profile p ON sf.ID = p.ID
        WHERE f.fid = $fid";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    die('File not found');
}

$file = mysqli_fetch_assoc($result);
$file_path = '../' . $file['dir_path'];

if (!file_exists($file_path)) {
    http_response_code(404);
    die('File not found on server');
}

$file_name = basename($file_path);
$file_size = filesize($file_path);

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output file
readfile($file_path);
exit;
?>
