<?php
// Enable output buffering to ensure no unexpected output
ob_start();

// Disable error display to ensure only JSON output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require 'config.php';

// #region agent log
$log_file = __DIR__ . '/../.cursor/debug.log';
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'A',
    'location' => 'api/update_program.php:10',
    'message' => 'Before auth check',
    'data' => [
        'user_id' => $_SESSION['user_id'] ?? 'not_set',
        'user_type' => $_SESSION['user_type'] ?? 'not_set'
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

checkAdminOrOperatorAuth();

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'A',
    'location' => 'api/update_program.php:22',
    'message' => 'Auth check passed',
    'data' => [],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

// Get POST data
$raw_input = file_get_contents('php://input');
$data = json_decode($raw_input, true);

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'D',
    'location' => 'api/update_program.php:30',
    'message' => 'POST data received',
    'data' => [
        'raw_input_length' => strlen($raw_input),
        'data_decoded' => $data !== null,
        'has_id' => isset($data['id']),
        'has_universityId' => isset($data['universityId']),
        'data_keys' => $data ? array_keys($data) : []
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

if (!$data || !isset($data['id']) || !isset($data['universityId'])) {
    jsonResponse(false, 'Invalid data or missing ID/universityId');
}

// Validate required fields
if (empty($data['name'])) {
    jsonResponse(false, 'You MUST input program name!');
}

$sid = (int)$data['universityId'];
$pid = (int)$data['id'];
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
// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'E',
    'location' => 'api/update_program.php:55',
    'message' => 'Before permission check',
    'data' => [
        'sid' => $sid,
        'user_type' => $_SESSION['user_type'] ?? 'not_set',
        'user_id' => $_SESSION['user_id'] ?? 'not_set'
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

$permission_result = checkOperatorSchool($sid);

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'E',
    'location' => 'api/update_program.php:68',
    'message' => 'Permission check result',
    'data' => ['has_permission' => $permission_result],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

if (!checkOperatorSchool($sid)) {
    jsonResponse(false, 'You do not have permission to manage programs for this university');
}

// Update program (only update fields that actually exist)
// Note: If deadline is a past date, the trigger will prevent the update
// But if deadline is empty, set it to NULL
$update_program_sql = "UPDATE program SET 
                      pname = '$name',
                      degree_type = '$degree',
                      duration = '$duration',
                      ddl = " . ($deadline ? "'$deadline'" : "NULL") . ",
                      gpa_requirement = $minGPA,
                      language_requirement = '$languageRequirement',
                      category = '$category'
                      WHERE sid = $sid AND pid = $pid";

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'C',
    'location' => 'api/update_program.php:80',
    'message' => 'Before UPDATE query',
    'data' => ['sql' => $update_program_sql],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

$update_result = mysqli_query($conn, $update_program_sql);

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'C',
    'location' => 'api/update_program.php:90',
    'message' => 'UPDATE query result',
    'data' => [
        'success' => $update_result !== false,
        'error' => $update_result ? '' : mysqli_error($conn),
        'affected_rows' => $update_result ? mysqli_affected_rows($conn) : 0
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

if (!$update_result) {
    $error_msg = mysqli_error($conn);
    // Check if it's a trigger error
    if (strpos($error_msg, 'program.ddl cannot be in the past') !== false) {
        jsonResponse(false, 'Deadline cannot be in the past. Please enter a future date.');
    } elseif (strpos($error_msg, 'program.gpa_requirement') !== false) {
        jsonResponse(false, 'GPA requirement must be between 0 and 4.0.');
    } elseif (strpos($error_msg, 'program.degree_type') !== false) {
        jsonResponse(false, 'Degree type must be Master, PhD, or Bachelor.');
    } else {
        jsonResponse(false, 'Failed Updating Program: ' . $error_msg);
    }
}

jsonResponse(true, 'Successfully updated!', ['id' => $pid, 'universityId' => $sid]);
?>

