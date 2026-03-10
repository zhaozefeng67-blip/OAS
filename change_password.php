<?php 
    
    require 'connect.php';

    session_start();

    $current = $_POST['current'];
    $New = $_POST['New'];
    $confirm = $_POST['confirm'];

    $username = $_SESSION['username'];
    
    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $password = $row['password'];

    if($password != $current) {
        $_SESSION['error'] = "Original password is incorrect! "; 
        header("Location: profile2.php");
    } else {
        if($New != $confirm) {
            $_SESSION['error'] = "New password should be equal to confirm password! "; 
            header("Location: profile2.php");
        } else {
            $stmt = $conn->prepare("UPDATE profile SET password = ? WHERE username = ?");
            $stmt->bind_param("ss" , $New , $username); 
            $stmt->execute();
            $_SESSION['password'] = $New;
            header("Location: profile2.php");
        }
    }
?>