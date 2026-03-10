<?php
session_start();
require 'connect.php';

$username = $_POST['username'];
$password = $_POST['password']; 

$stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
$stmt->bind_param("s" , $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if(!$user) {
    $_SESSION['error'] = "The user does not exist";
    header("Location: login_.php");
    exit;
    } else {
        if($user['password'] !== $password) {
            $_SESSION['error'] = "The password is incorrect";
            header("Location: login_.php");
            exit;    
        } else {
            // Check if officer is in pending approval status
            if ($user['type'] == 'operator' && isset($user['status']) && $user['status'] == 'pending') {
                $_SESSION['error'] = "Your account is pending approval. Please wait for admin approval.";
                header("Location: login_.php");
                exit;
            }
            
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['type'];
            if ($user['type'] == 'admin') {
                header("Location: admin.php"); 
            } elseif ($user['type'] == 'operator') {
                header("Location: officer_dashboard.php"); 
            } else {
                header("Location: index.html");
            }
            exit;
        }
    }
?>