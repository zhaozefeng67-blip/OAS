<?php 

    require 'connect.php'; 

    session_start();

    $ID = $_POST['ID'];
    $sid = $_POST['sid'];
    $pid = $_POST['pid'];

    $result = $conn->query("DELETE FROM apply WHERE ID = '$ID' AND sid = '$sid' AND pid = '$pid'");

    $_SESSION['success'] = 'Successfully! ';
    header("Location: My_applications.php");

?>