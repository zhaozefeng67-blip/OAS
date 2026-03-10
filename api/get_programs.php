<?php
require 'config.php';
checkAdminOrOperatorAuth();

// Query program information, join with universities
// If it's an officer, only query programs from schools they manage
if ($_SESSION['user_type'] == 'operator') {
    $operator_id = (int)$_SESSION['user_id'];
    $sql = "SELECT p.sid, p.pid, p.pname, p.duration, p.ddl, p.gpa_requirement, 
                   p.language_requirement, p.category, p.degree_type,
                   s.school_name
            FROM program p
            INNER JOIN operator_school os ON p.sid = os.sid
            LEFT JOIN school s ON p.sid = s.sid
            WHERE os.ID = $operator_id
            ORDER BY s.school_name ASC, p.pname ASC";
} else {
    $sql = "SELECT p.sid, p.pid, p.pname, p.duration, p.ddl, p.gpa_requirement, 
                   p.language_requirement, p.category, p.degree_type,
                   s.school_name
            FROM program p
            LEFT JOIN school s ON p.sid = s.sid
            ORDER BY s.school_name ASC, p.pname ASC";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    jsonResponse(false, 'Query failed: ' . mysqli_error($conn));
}

$programs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $programs[] = [
        'id' => (int)$row['pid'],
        'universityId' => (int)$row['sid'],
        'name' => $row['pname'],
        'degree' => $row['degree_type'] ?: 'Master',
        'duration' => $row['duration'] ?: '',
        'deadline' => $row['ddl'] ?: '',
        'minGPA' => $row['gpa_requirement'] ? (float)$row['gpa_requirement'] : 0,
        'languageRequirement' => $row['language_requirement'] ?: '',
        'category' => $row['category'] ?: '',
        'universityName' => $row['school_name'] ?: ''
    ];
}

jsonResponse(true, 'Success', $programs);
?>

