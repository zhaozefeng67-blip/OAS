<?php
    require 'connect.php';
    session_start();
    
    // Prevent caching
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // If logged in, redirect to appropriate page
    if (isset($_SESSION['username']) && isset($_SESSION['user_type'])) {
        if ($_SESSION['user_type'] == 'admin') {
            header("Location: admin.php");
            exit;
        } elseif ($_SESSION['user_type'] == 'operator') {
            header("Location: officer_dashboard.php");
            exit;
        } else {
            header("Location: index.html");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAS Login</title>
    <style>
    </style>
    <link rel="stylesheet" href="CSS/login.css">
</head>
<body>
    <div class="login-container">
        <h2 class="form-title">Online Application System</h2>
        <form id="loginForm" method="POST" action="login.php">
            <div class="form-group">
                <label><span class="required">*</span>Username :</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label><span class="required">*</span>Password :</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" required>
                </div>
            </div>

            <?php
                if(isset($_SESSION['error'])) {
                    echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
                    unset($_SESSION['error']); // Clear error message after displaying, $_SESSION['error'] will be gone after refresh
                }
            ?>

            <div class="remember-me">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="rg.php">Register now</a>
        </div>
    </div>
    <!--<script src="JS/login.js"></script>-->
</body>
</html>