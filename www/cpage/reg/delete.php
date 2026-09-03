<?php

require_once "cpage.php";

$number = $_POST['number'];

$sql = "Delete from bank_accounts where account_number = '".$number."'";

if(mysqli_query($conn,$sql))
{
    echo "Record deleted successfully";
}
else
{
    echo "No deleting record: " . mysqli_error($conn);
}

?>