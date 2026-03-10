<?php

    require 'connect.php';

    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM school WHERE sid = ?");
    $stmt->bind_param("i" , $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    ob_clean();
    header("Content-Type: image/jpg");
    echo $row['image'];
?>