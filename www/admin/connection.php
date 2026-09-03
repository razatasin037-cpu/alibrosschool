<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "admin";  

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Character set
mysqli_set_charset($conn, "utf8");

?>