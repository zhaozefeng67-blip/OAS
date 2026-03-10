<?php

    require 'connect.php';

    session_start();
    
    $real_name = $_POST['real_name'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $country = $_POST['country'];
    $city = $_POST['city'];
    
    $username = $_SESSION['username'];

    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $ID = $user['ID'];

    $stmt = $conn->prepare("UPDATE profile SET real_name = ? WHERE ID = ?");
    $stmt->bind_param("si" , $real_name , $ID);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE profile SET date_of_birth = ? WHERE ID = ?");
    $stmt->bind_param("si" , $date_of_birth , $ID);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE profile SET email = ? WHERE ID = ?");
    $stmt->bind_param("si" , $email , $ID);
    $stmt->execute();   
    
    $stmt = $conn->prepare("SELECT * FROM Region WHERE country = ? AND city = ?");
    $stmt->bind_param("ss" , $country , $city);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(!$result->fetch_assoc()) {
        $stmt = $conn->prepare("INSERT INTO Region(country , city) VALUES(? , ?)");
        $stmt->bind_param("ss" , $country , $city);
        $stmt->execute();
    }

    $stmt = $conn->prepare("SELECT * FROM Region WHERE country = ? AND city = ?");
    $stmt->bind_param("ss" , $country , $city);
    $stmt->execute();
    $result = $stmt->get_result();
    $region = $result->fetch_assoc();
    
    $stmt = $conn->prepare("UPDATE student SET rid = ? WHERE ID = ?");
    $stmt->bind_param("is" , $region['rid'] , $ID);
    $stmt->execute();

    header("Location: profile2.php");


?>