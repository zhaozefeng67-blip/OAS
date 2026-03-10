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
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : 'all';

// Query applications for schools managed by this officer
$sql = "SELECT a.ID as student_id, a.sid, a.pid, a.status, a.apply_date,
               p.username, p.real_name, p.email,
               pr.pname as program_name,
               s.school_name,
               u.gpa,
               lg_toefl.listening as toefl_listening, lg_toefl.speaking as toefl_speaking,
               lg_toefl.reading as toefl_reading, lg_toefl.writing as toefl_writing,
               lg_ielts.listening as ielts_listening, lg_ielts.speaking as ielts_speaking,
               lg_ielts.reading as ielts_reading, lg_ielts.writing as ielts_writing,
               ug.under_university, ug.major
        FROM operator_school os
        INNER JOIN apply a ON os.sid = a.sid
        INNER JOIN profile p ON a.ID = p.ID
        INNER JOIN program pr ON a.sid = pr.sid AND a.pid = pr.pid
        INNER JOIN school s ON a.sid = s.sid
        LEFT JOIN undergraduate u ON a.ID = u.ID
        LEFT JOIN language_grade lg_toefl ON a.ID = lg_toefl.ID AND lg_toefl.type = 'TOEFL'
        LEFT JOIN language_grade lg_ielts ON a.ID = lg_ielts.ID AND lg_ielts.type = 'IELTS'
        LEFT JOIN undergraduate ug ON a.ID = ug.ID
        WHERE os.ID = $operator_id";

if ($status !== 'all') {
    $sql .= " AND a.status = '$status'";
}

$sql .= " ORDER BY a.apply_date DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$applications = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Calculate TOEFL total score
    $toefl_total = null;
    if ($row['toefl_listening'] !== null) {
        $toefl_total = (float)$row['toefl_listening'] + (float)$row['toefl_speaking'] + 
                       (float)$row['toefl_reading'] + (float)$row['toefl_writing'];
    }
    
    // Calculate IELTS average score
    $ielts_avg = null;
    if ($row['ielts_listening'] !== null) {
        $ielts_avg = ((float)$row['ielts_listening'] + (float)$row['ielts_speaking'] + 
                     (float)$row['ielts_reading'] + (float)$row['ielts_writing']) / 4.0;
        // Round to 0.5
        $ielts_avg = round($ielts_avg * 2) / 2;
    }
    
    $applications[] = [
        'id' => (int)$row['student_id'],
        'sid' => (int)$row['sid'],
        'pid' => (int)$row['pid'],
        'name' => $row['real_name'] ?: $row['username'],
        'email' => $row['email'],
        'program' => $row['program_name'],
        'university' => $row['school_name'],
        'gpa' => $row['gpa'] ? (float)$row['gpa'] : null,
        'toefl' => $toefl_total ? (int)$toefl_total : null,
        'ielts' => $ielts_avg ? (float)$ielts_avg : null,
        'status' => $row['status'],
        'submitTime' => $row['apply_date'],
        'background' => ($row['under_university'] ?: '') . ($row['major'] ? ' - ' . $row['major'] : '')
    ];
}

jsonResponse(true, 'Success', $applications);
?>

