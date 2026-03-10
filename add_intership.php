<?php 

    require 'connect.php';

    session_start();

    $username = $_SESSION['username'];

    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $ID = $user['ID'];

    $company = $_POST['name'];
    $position = $_POST['position'];
    $during = $_POST['during'];

    $stmt = $conn->prepare("INSERT INTO intership VALUES(NULL , ? , ? , ? , ?)");
    $stmt->bind_param("isss" , $ID , $company , $position , $during);
    $stmt->execute();

    header("Location: profile2.php");

?>