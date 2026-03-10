<?php

    require 'connect.php';
    
    session_start();
    
    $username = $_SESSION['username'];
    $iid = $_POST['iid'];

    $stmt = $conn->prepare("DELETE FROM intership WHERE iid = ?");
    $stmt->bind_param("i" , $iid);
    $stmt->execute();

    header("Location: profile2.php");

?>