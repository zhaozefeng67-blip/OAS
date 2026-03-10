<?php
session_start();
// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (isset($_SESSION['username']) && isset($_SESSION['user_type'])) {
    // If logged in, redirect to appropriate page based on user type
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['user_type'] == 'operator') {
        header("Location: officer_dashboard.php");
    } else {
        header("Location: index_student.php");
    }
} else {
    // If not logged in, redirect to login page
    header("Location: login_.php");
}
exit;
?>
