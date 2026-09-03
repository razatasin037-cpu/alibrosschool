<?php
require_once 'cpage.php';

  $account_number = $_POST['number'];
  $customer_name = $_POST['name'];
  $account_type = $_POST['type'];
  $balance = $_POST['balance'];
  $phone_number = $_POST['phone'];
  $email = $_POST['email'];
  $opening_date = $_POST['date'];


  {
    $sql = "INSERT INTO bank_accounts (account_number, customer_name, account_type, balance, phone_number, email, opening_date)
    VALUES ('".$account_number."', '".$customer_name."', '".$account_type."', '".$balance."', '".$phone_number."', '".$email."', '".$opening_date."')";     
  }

    {
        if(mysqli_query($conn, $sql)) {
            echo "<script>alert('Record added successfully'); window.location.href='create.html';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
?>