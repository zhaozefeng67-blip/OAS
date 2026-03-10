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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized access');
}

$user_id = (int)$_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';

// If officer/admin, get student_id from request
$student_id = $user_id;
if (($user_type === 'operator' || $user_type === 'admin') && isset($_GET['student_id'])) {
    $student_id = (int)$_GET['student_id'];
}

// Query student files
$sql = "SELECT f.fid, f.dir_path, f.type, sf.ID as student_id
        FROM student_files sf
        INNER JOIN files f ON sf.fid = f.fid
        WHERE sf.ID = $student_id
        ORDER BY f.fid DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$files = [];
while ($row = mysqli_fetch_assoc($result)) {
    $file_path = '../' . $row['dir_path'];
    $file_name = basename($row['dir_path']);
    
    $files[] = [
        'fid' => (int)$row['fid'],
        'filename' => $file_name,
        'path' => $row['dir_path'],
        'type' => $row['type'],
        'size' => file_exists($file_path) ? filesize($file_path) : 0
    ];
}

jsonResponse(true, 'Success', $files);
?>
