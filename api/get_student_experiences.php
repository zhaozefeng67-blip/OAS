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

if (!isset($_GET['student_id'])) {
    jsonResponse(false, 'Student ID is required');
}

$student_id = (int)$_GET['student_id'];
$operator_id = (int)$_SESSION['user_id'];

// Verify if the officer has permission to view this student's information (by checking if the student has applied to schools managed by this officer)
$check_sql = "SELECT COUNT(*) as count 
              FROM operator_school os
              INNER JOIN apply a ON os.sid = a.sid
              WHERE os.ID = $operator_id AND a.ID = $student_id";
$check_result = mysqli_query($conn, $check_sql);
$check_row = mysqli_fetch_assoc($check_result);

if ($check_row['count'] == 0) {
    jsonResponse(false, 'Unauthorized: You do not have permission to view this student\'s information');
}

// Get competition experience
$competitions = [];
$comp_sql = "SELECT cid, c_name, prize, duration 
             FROM competition_grade 
             WHERE ID = $student_id 
             ORDER BY duration DESC, cid DESC";
$comp_result = mysqli_query($conn, $comp_sql);

if ($comp_result) {
    while ($row = mysqli_fetch_assoc($comp_result)) {
        $competitions[] = [
            'cid' => (int)$row['cid'],
            'name' => $row['c_name'],
            'prize' => $row['prize'],
            'duration' => $row['duration']
        ];
    }
}

// Get internship experience
$internships = [];
$int_sql = "SELECT iid, company, position, duration 
            FROM intership 
            WHERE ID = $student_id 
            ORDER BY duration DESC, iid DESC";
$int_result = mysqli_query($conn, $int_sql);

if ($int_result) {
    while ($row = mysqli_fetch_assoc($int_result)) {
        $internships[] = [
            'iid' => (int)$row['iid'],
            'company' => $row['company'],
            'position' => $row['position'],
            'duration' => $row['duration']
        ];
    }
}

jsonResponse(true, 'Success', [
    'competitions' => $competitions,
    'internships' => $internships
]);
?>



