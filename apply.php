<?php 
    
    require 'connect.php';

    session_start();

    $sid = $_POST['sid'];
    $pid = $_POST['pid'];
    
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT * FROM profile WHERE username = ?");
    $stmt->bind_param("s" , $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $ID = $user['ID'];

    $result = $conn->query("SELECT * FROM apply WHERE ID = '$ID' AND sid = '$sid' AND pid = '$pid'");

    if($result->fetch_assoc()) {
        $_SESSION['error'] = "You have applied! Please wait! ";
        header("Location: program.php?sid=$sid");
    } else {
        $_SESSION['success'] = "Successfully! ";
        $apply_date = date('Y-m-d');
        $result = $conn->query("INSERT INTO apply VALUES('$ID' , '$sid' , '$pid' , 'Pending' , '$apply_date')");
        header("Location: program.php?sid=$sid");
    }
?>