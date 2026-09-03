<?php

$server = "localhost";
$username = "root";
$password = "";
$dbname = "maruti";

$conn = mysqli_connect($server, $username, $password, $dbname);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// echo "Database Connected Successfully!";

?>