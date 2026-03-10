<?php
require 'config.php';
checkAdminAuth();

// Query officer information, join operator_school, profile, school
$sql = "SELECT os.ID, os.sid,
               p.username, p.real_name, p.email,
               s.school_name,
               COUNT(DISTINCT pr.pid) as program_count,
               COUNT(DISTINCT a.ID) as application_count
        FROM operator_school os
        LEFT JOIN profile p ON os.ID = p.ID
        LEFT JOIN school s ON os.sid = s.sid
        LEFT JOIN program pr ON os.sid = pr.sid
        LEFT JOIN apply a ON os.sid = a.sid
        WHERE p.type = 'operator'
        GROUP BY os.ID, os.sid, p.username, p.real_name, p.email, s.school_name
        ORDER BY s.school_name ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$officers = [];

while ($row = mysqli_fetch_assoc($result)) {
    $officers[] = [
        'id' => (int)$row['ID'],
        'universityId' => (int)$row['sid'],
        'name' => $row['real_name'] ?: $row['username'],
        'email' => $row['email'] ?: '',
        'universityName' => $row['school_name'] ?: '',
        'programCount' => (int)$row['program_count'],
        'applicationCount' => (int)$row['application_count']
    ];
}

jsonResponse(true, 'Success', $officers);
?>

