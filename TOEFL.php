<?php 
    // Enable output buffering
    ob_start();
    
    require 'connect.php';

    session_start();

    if (!isset($_SESSION['username'])) {
        ob_end_clean();
        header("Location: login_.php");
        exit();
    }

    $username = $_SESSION['username'];

    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        ob_end_clean();
        header("Location: login_.php");
        exit();
    }

    $ID = $user['ID'];

    // Validate and get input values
    $listening = isset($_POST['listening']) ? (float)$_POST['listening'] : 0;
    $speaking = isset($_POST['speaking']) ? (float)$_POST['speaking'] : 0;
    $reading = isset($_POST['reading']) ? (float)$_POST['reading'] : 0;
    $writing = isset($_POST['writing']) ? (float)$_POST['writing'] : 0;
    $type = "TOEFL";

    // Validate TOEFL score range (0-30)
    if ($listening < 0 || $listening > 30 || 
        $speaking < 0 || $speaking > 30 || 
        $reading < 0 || $reading > 30 || 
        $writing < 0 || $writing > 30) {
        $_SESSION['fail'] = 'TOEFL scores must be between 0 and 30 for each section.';
        ob_end_clean();
        header("Location: profile2.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("DELETE FROM language_grade WHERE ID = ? AND type = ?");
        $stmt->bind_param("is" , $ID , $type);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO language_grade VALUES(NULL , ? , ? , ? , ? , ? , ?)");
        $stmt->bind_param("idddds" , $ID , $listening , $speaking , $writing , $reading , $type);
        $stmt->execute();

        $_SESSION['success'] = 'TOEFL scores updated successfully.';
        ob_end_clean();
        header("Location: profile2.php");
        exit();
    } catch (mysqli_sql_exception $e) {
        $error_msg = $e->getMessage();
        if (strpos($error_msg, 'TOEFL scores must be between 0 and 30') !== false) {
            $_SESSION['fail'] = 'TOEFL scores must be between 0 and 30 for each section.';
        } else {
            $_SESSION['fail'] = 'Failed to update TOEFL scores: ' . $error_msg;
        }
        ob_end_clean();
        header("Location: profile2.php");
        exit();
    }

?>