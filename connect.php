<?php
$severname = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// Enable mysqli exception mode
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = mysqli_connect($severname , $username , $password , $dbname);

if(!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn , "utf8mb4");
?>