<?php
// api/config.php
require '../connect.php';
session_start();

function checkAdminAuth() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid visit! Please login first!'
        ]);
        exit();
    }
}

function checkAdminOrOperatorAuth() {
    if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] != 'admin' && $_SESSION['user_type'] != 'operator')) {
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid visit! Please login first!'
        ]);
        exit();
    }
}

function checkOperatorSchool($sid) {
    global $conn;
    if ($_SESSION['user_type'] == 'admin') {
        return true; // Admin can manage all schools
    }
    if ($_SESSION['user_type'] == 'operator') {
        $operator_id = (int)$_SESSION['user_id'];
        $check_sql = "SELECT 1 FROM operator_school WHERE ID = $operator_id AND sid = $sid";
        $result = mysqli_query($conn, $check_sql);
        return mysqli_num_rows($result) > 0;
    }
    return false;
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
?>