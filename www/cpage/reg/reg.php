<?php
require_once 'cpage.php';

/*varibles from input from for sql*/
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$dob = $_POST['dob'];
$gender = $_POST['gender'];
$password = $_POST['password'];
{

$sql = "insert into users(name,email,phone,dob,gender,password)
values ('".$name."','".$email."','".$phone."','".$dob."','".$gender."','".$password."')";
}
{
    if(mysqli_query($conn,$sql))
        {
    echo"<script> aleart('rechord add sucessfull')";
    header ("location:reg.html");
    }
    else {
        echo "error:";
    }
    
}
?>