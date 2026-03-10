<?php 
    require 'connect.php';

    session_start();

    $username = $_SESSION['username'];

    $uni = $_POST['under_university'];
    $gpa = $_POST['gpa'];   
    $major = $_POST['major'];

    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $ID = $user['ID'];

    $stmt = $conn->prepare("DELETE FROM undergraduate WHERE ID = ?");
    $stmt->bind_param("i" , $ID);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO undergraduate VALUES(? , ? , ? , ?)");
    $stmt->bind_param("issd" , $ID , $uni , $major , $gpa);
    $stmt->execute();

    header("Location: profile2.php");

?>