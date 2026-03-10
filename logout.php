<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Clear all session variables
$_SESSION = array();

// If using cookie to save session ID, delete cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login_.php");
exit;
?>

