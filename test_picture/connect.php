<?php

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "test";

    $conn = mysqli_connect($servername , $username , $password , $dbname);

    if(!$conn) {
        die("failed : " . $conn->connect_error);
    } 

?>