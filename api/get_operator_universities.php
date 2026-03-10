<?php
require '../connect.php';
session_start();

// Check if logged in and is an officer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'operator') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
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

$operator_id = (int)$_SESSION['user_id'];

// Query universities managed by this officer
$sql = "SELECT s.sid, s.school_name
        FROM operator_school os
        INNER JOIN school s ON os.sid = s.sid
        WHERE os.ID = $operator_id
        ORDER BY s.school_name ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$universities = [];

while ($row = mysqli_fetch_assoc($result)) {
    $universities[] = [
        'id' => (int)$row['sid'],
        'name' => $row['school_name']
    ];
}

jsonResponse(true, 'Success', $universities);
?>

