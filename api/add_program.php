<?php
require 'config.php';
checkAdminOrOperatorAuth();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    jsonResponse(false, 'Invalid data');
}

// Validate required fields
if (empty($data['name']) || empty($data['universityId'])) {
    jsonResponse(false, 'You MUST input program name and select university!');
}

$sid = (int)$data['universityId'];
$name = $conn->real_escape_string($data['name']);

// Normalize degree to satisfy DB constraint (allowed: Master, PhD, Bachelor)
$degreeRaw = isset($data['degree']) ? $data['degree'] : 'Master';
$degreeLower = strtolower($degreeRaw);
if (in_array($degreeLower, ['phd', 'p.h.d', 'doctor', 'doctorate'])) {
    $degree = 'PhD';
} elseif (in_array($degreeLower, ['bachelor', 'bachelors', 'undergraduate', 'ba', 'bsc', 'bs'])) {
    $degree = 'Bachelor';
} else {
    // Treat Master/MBA/others as Master to pass constraint
    $degree = 'Master';
}
$degree = $conn->real_escape_string($degree);
$duration = isset($data['duration']) ? $conn->real_escape_string($data['duration']) : '';
$deadline = isset($data['deadline']) && $data['deadline'] ? $conn->real_escape_string($data['deadline']) : null;
$minGPA = isset($data['minGPA']) ? (float)$data['minGPA'] : 0;
$languageRequirement = isset($data['languageRequirement']) ? $conn->real_escape_string($data['languageRequirement']) : '';
$category = isset($data['category']) ? $conn->real_escape_string($data['category']) : '';

// Check if officer has permission to manage this university
if (!checkOperatorSchool($sid)) {
    jsonResponse(false, 'You do not have permission to manage programs for this university');
}

// Get next pid
$pid_sql = "SELECT COALESCE(MAX(pid), 0) + 1 as next_pid FROM program WHERE sid = $sid";
$pid_result = mysqli_query($conn, $pid_sql);
$pid_row = mysqli_fetch_assoc($pid_result);
$pid = (int)$pid_row['next_pid'];

// Insert program (only use fields that actually exist)
$insert_program_sql = "INSERT INTO program (sid, pid, pname, degree_type, duration, ddl, gpa_requirement, language_requirement, category) 
                       VALUES ($sid, $pid, '$name', '$degree', '$duration', " . 
                       ($deadline ? "'$deadline'" : "NULL") . ", $minGPA, '$languageRequirement', '$category')";

if (!mysqli_query($conn, $insert_program_sql)) {
    jsonResponse(false, 'Failed Inserting Program: ' . mysqli_error($conn));
}

jsonResponse(true, 'Successfully added!', ['id' => $pid, 'universityId' => $sid]);
?>

