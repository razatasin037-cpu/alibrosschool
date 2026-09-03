<?php
require_once 'cpage.php';

$name = $_POST['NAME'];
$class = $_POST['CLASS'];
$roll_no = $_POST['ROLL_NO'];
$section = $_POST['SECTION'];
$password = $_POST['PASSWORD'];

{
$sql = "insert into class_detail(NAME,CLASS,ROLL_NO,SECTION,PASSWORD)
values ('".$name."','".$class."','".$roll_no."','".$section."','".$password."')";
}

{
    if(mysqli_query($conn,$sql))
    {
        echo "<script>alert('rechord add sucessfull'); window.location.href='form.html';</script>";

        header("location:class.html");
    }
    else {
        echo "error:" . mysqli_error($conn);
    }
}
?>
