<?php

    require 'connect.php';
    
    session_start();
    
    $username = $_SESSION['username'];
    $cid = $_POST['cid'];

    $stmt = $conn->prepare("DELETE FROM competition_grade WHERE cid = ?");
    $stmt->bind_param("i" , $cid);
    $stmt->execute();

    header("Location: profile2.php");

?>