<?php
require 'config.php';
checkAdminAuth();

// Query student application information, join profile, school and program
$sql = "SELECT a.ID as student_id, a.sid, a.pid, a.status, a.apply_date,
               p.username, p.real_name, p.email,
               s.school_name,
               pr.pname as program_name
        FROM apply a
        INNER JOIN profile p ON a.ID = p.ID
        INNER JOIN program pr ON a.sid = pr.sid AND a.pid = pr.pid
        INNER JOIN school s ON a.sid = s.sid
        WHERE a.status = 'Pending'
        ORDER BY a.apply_date DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$applications = [];

while ($row = mysqli_fetch_assoc($result)) {
    $applications[] = [
        'id' => (int)$row['student_id'],
        'universityId' => (int)$row['sid'],
        'programId' => (int)$row['pid'],
        'name' => $row['real_name'] ?: $row['username'],
        'email' => $row['email'] ?: '',
        'appliedAt' => $row['apply_date'] ?: '',
        'status' => $row['status'] ?: 'Pending',
        'universityName' => $row['school_name'] ?: '',
        'programName' => $row['program_name'] ?: ''
    ];
}

jsonResponse(true, 'Success', $applications);
?>

