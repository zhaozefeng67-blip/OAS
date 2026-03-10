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

// Query all programs from schools managed by this officer
$sql = "SELECT pr.sid, pr.pid, pr.pname, pr.degree_type, pr.duration, pr.ddl,
               s.school_name,
               COUNT(DISTINCT a.ID) as applicant_count
        FROM operator_school os
        INNER JOIN program pr ON os.sid = pr.sid
        INNER JOIN school s ON pr.sid = s.sid
        LEFT JOIN apply a ON pr.sid = a.sid AND pr.pid = a.pid
        WHERE os.ID = $operator_id
        GROUP BY pr.sid, pr.pid, pr.pname, pr.degree_type, pr.duration, pr.ddl, s.school_name
        ORDER BY s.school_name ASC, pr.pname ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$programs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $programs[] = [
        'id' => (int)$row['pid'],
        'sid' => (int)$row['sid'],
        'name' => $row['pname'],
        'degree' => $row['degree_type'] ?: 'Master',
        'duration' => $row['duration'] ?: '',
        'deadline' => $row['ddl'] ?: '',
        'university' => $row['school_name'],
        'applicants' => (int)$row['applicant_count']
    ];
}

jsonResponse(true, 'Success', $programs);
?>

