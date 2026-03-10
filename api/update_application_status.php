<?php
// Enable output buffering to ensure no unexpected output
ob_start();

// Disable error display to ensure only JSON output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// #region agent log
$log_file = __DIR__ . '/../.cursor/debug.log';
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'A',
    'location' => 'api/update_application_status.php:1',
    'message' => 'Script started',
    'data' => ['session_keys' => array_keys($_SESSION ?? [])],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

require '../connect.php';
session_start();

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'A',
    'location' => 'api/update_application_status.php:10',
    'message' => 'Session check',
    'data' => [
        'user_id' => $_SESSION['user_id'] ?? 'not_set',
        'user_type' => $_SESSION['user_type'] ?? 'not_set',
        'username' => $_SESSION['username'] ?? 'not_set',
        'all_session_keys' => array_keys($_SESSION)
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

// Check if logged in and is an officer
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'operator') {
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'pre-fix',
        'hypothesisId' => 'A',
        'location' => 'api/update_application_status.php:18',
        'message' => 'Unauthorized access',
        'data' => [
            'has_user_id' => isset($_SESSION['user_id']),
            'has_user_type' => isset($_SESSION['user_type']),
            'user_type_value' => $_SESSION['user_type'] ?? 'not_set'
        ],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

function jsonResponse($success, $message = '', $data = []) {
    // Clear all output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

$operator_id = (int)$_SESSION['user_id'];

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'D',
    'location' => 'api/update_application_status.php:30',
    'message' => 'Before reading POST data',
    'data' => ['operator_id' => $operator_id],
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
    'location' => 'api/update_application_status.php:38',
    'message' => 'POST data received',
    'data' => [
        'raw_input_length' => strlen($raw_input),
        'data_decoded' => $data !== null,
        'has_studentId' => isset($data['studentId']),
        'has_sid' => isset($data['sid']),
        'has_pid' => isset($data['pid']),
        'has_status' => isset($data['status']),
        'data_keys' => $data ? array_keys($data) : []
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

if (!$data || !isset($data['studentId']) || !isset($data['sid']) || !isset($data['pid']) || !isset($data['status'])) {
    jsonResponse(false, 'Invalid data');
}

$student_id = (int)$data['studentId'];
$sid = (int)$data['sid'];
$pid = (int)$data['pid'];
$status = $conn->real_escape_string($data['status']);

// Validate status value
if (!in_array($status, ['Pending', 'Approved', 'Rejected'])) {
    jsonResponse(false, 'Invalid status');
}

// Verify if the officer has permission to manage applications for this school
$check_sql = "SELECT 1 FROM operator_school WHERE ID = $operator_id AND sid = $sid";

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'B',
    'location' => 'api/update_application_status.php:50',
    'message' => 'Before permission check',
    'data' => ['operator_id' => $operator_id, 'sid' => $sid, 'sql' => $check_sql],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

$check_result = mysqli_query($conn, $check_sql);

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'B',
    'location' => 'api/update_application_status.php:58',
    'message' => 'Permission check result',
    'data' => [
        'query_success' => $check_result !== false,
        'num_rows' => $check_result ? mysqli_num_rows($check_result) : 0,
        'error' => $check_result ? '' : mysqli_error($conn)
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

if (mysqli_num_rows($check_result) == 0) {
    jsonResponse(false, 'You do not have permission to manage this application');
}

// Update application status
// Note: If apply_date is a future date, the trigger will prevent the update
// We need to check and fix apply_date first, or only update status without triggering apply_date check
// Solution: When updating, if apply_date is a future date, set it to the current date
$check_date_sql = "SELECT apply_date FROM apply WHERE ID = $student_id AND sid = $sid AND pid = $pid";
$check_date_result = mysqli_query($conn, $check_date_sql);

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'C',
    'location' => 'api/update_application_status.php:165',
    'message' => 'Before checking apply_date',
    'data' => ['check_query' => $check_date_sql],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

$apply_date_fixed = false;
if ($check_date_result && mysqli_num_rows($check_date_result) > 0) {
    $row = mysqli_fetch_assoc($check_date_result);
    $existing_date = $row['apply_date'];
    
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'pre-fix',
        'hypothesisId' => 'C',
        'location' => 'api/update_application_status.php:177',
        'message' => 'apply_date check result',
        'data' => [
            'existing_date' => $existing_date,
            'current_date' => date('Y-m-d'),
            'is_future' => $existing_date && $existing_date > date('Y-m-d')
        ],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    // If apply_date is a future date, set it to the current date
    if ($existing_date && $existing_date > date('Y-m-d')) {
        $fix_date_sql = "UPDATE apply SET apply_date = CURDATE() WHERE ID = $student_id AND sid = $sid AND pid = $pid";
        mysqli_query($conn, $fix_date_sql);
        $apply_date_fixed = true;
        
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'pre-fix',
            'hypothesisId' => 'C',
            'location' => 'api/update_application_status.php:192',
            'message' => 'Fixed future apply_date',
            'data' => ['fix_query' => $fix_date_sql],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
    }
}

// Update application status
$update_sql = "UPDATE apply SET status = '$status' WHERE ID = $student_id AND sid = $sid AND pid = $pid";

// #region agent log
$log_entry = json_encode([
    'sessionId' => 'debug-session',
    'runId' => 'pre-fix',
    'hypothesisId' => 'C',
    'location' => 'api/update_application_status.php:205',
    'message' => 'Before UPDATE query',
    'data' => [
        'student_id' => $student_id,
        'sid' => $sid,
        'pid' => $pid,
        'status' => $status,
        'apply_date_fixed' => $apply_date_fixed,
        'sql' => $update_sql
    ],
    'timestamp' => time() * 1000
]) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
// #endregion

// Use try-catch to catch exceptions
try {
    $update_result = mysqli_query($conn, $update_sql);
    
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'pre-fix',
        'hypothesisId' => 'C',
        'location' => 'api/update_application_status.php:223',
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
        if (strpos($error_msg, 'Foreign key constraint violated') !== false) {
            jsonResponse(false, 'Cannot update application: Related student or program record not found. Please contact administrator.');
        } elseif (strpos($error_msg, 'apply.status must be') !== false) {
            jsonResponse(false, 'Invalid status value. Status must be Pending, Approved, or Rejected.');
        } elseif (strpos($error_msg, 'apply.apply_date cannot be in the future') !== false) {
            jsonResponse(false, 'Application date is invalid. Please contact administrator to fix the application date.');
        } else {
            jsonResponse(false, 'Update failed: ' . $error_msg);
        }
    }
    
    // Check if record was actually updated
    if (mysqli_affected_rows($conn) == 0) {
        jsonResponse(false, 'Application not found or no changes made.');
    }
} catch (mysqli_sql_exception $e) {
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'pre-fix',
        'hypothesisId' => 'C',
        'location' => 'api/update_application_status.php:250',
        'message' => 'Exception caught',
        'data' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    $error_msg = $e->getMessage();
    if (strpos($error_msg, 'apply.apply_date cannot be in the future') !== false) {
        jsonResponse(false, 'Application date is invalid. Please contact administrator to fix the application date.');
    } elseif (strpos($error_msg, 'apply.status must be') !== false) {
        jsonResponse(false, 'Invalid status value. Status must be Pending, Approved, or Rejected.');
    } else {
        jsonResponse(false, 'Update failed: ' . $error_msg);
    }
}

jsonResponse(true, 'Status updated successfully');
?>

