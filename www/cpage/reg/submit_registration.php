<?php
require_once 'cpage.php';

$name = $_POST['name'];
$contact = $_POST['contact'];
$gender = $_POST['gender'];
$age = $_POST['age'];
$email = $_POST['email'];
$password = $_POST['password'];

$sql = "INSERT INTO users(name, contact, gender, age, email, password)
VALUES ('".$name."','".$contact."','".$gender."','".$age."','".$email."','".$password."')";
    

if (mysqli_query($conn, $sql))
    
{
   // echo "<script>alert('Registration Successful');</script>";
        header("Location:user.html");            
          
}

else 
{
   echo "Error: " . mysqli_error($conn);
        //header("Location:user.html");
}

mysqli_close($conn);
?>

