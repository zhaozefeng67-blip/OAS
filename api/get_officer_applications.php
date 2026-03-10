<?php
require 'config.php';
checkAdminAuth();

// Query officer application information, get pending officers from profile table
// Use LEFT JOIN to associate operator_school and school tables, so applications can be displayed even if there's no record in operator_school
$sql = "SELECT p.ID as profile_id, p.username, p.real_name, p.email, p.status as profile_status,
               os.sid as university_id,
               s.school_name
        FROM profile p
        LEFT JOIN operator_school os ON p.ID = os.ID
        LEFT JOIN school s ON os.sid = s.sid
        WHERE p.type = 'operator' 
          AND p.status = 'pending'
        ORDER BY p.ID DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$applications = [];

while ($row = mysqli_fetch_assoc($result)) {
    $applications[] = [
        'id' => (int)$row['profile_id'],
        'universityId' => (int)$row['university_id'] ?: 0,
        'programId' => 0, // program_id is no longer used
        'name' => $row['real_name'] ?: $row['username'] ?: '',
        'email' => $row['email'] ?: '',
        'appliedAt' => '', // Application time is no longer stored
        'status' => 'pending',
        'universityName' => $row['school_name'] ?: 'N/A',
        'programName' => '-' // program is no longer used
    ];
}

jsonResponse(true, 'Success', $applications);
?>
