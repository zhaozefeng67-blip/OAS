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

    $c_name = $_POST['c_name'];
    $prize = $_POST['prize'];
    $during = $_POST['during'];

    $stmt = $conn->prepare("INSERT INTO competition_grade VALUES(NULL , ? , ? , ? , ?)");
    $stmt->bind_param("isss" , $ID , $c_name , $prize , $during);
    $stmt->execute();

    header("Location: profile2.php");

?>