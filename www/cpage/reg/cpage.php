<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "curd";

$conn = mysqli_connect ($servername,$username,$password,$dbname);

if(!$conn)
    {
die("connect failde:" .mysqli_connect_error());
}
echo " coonect" ;
?> 