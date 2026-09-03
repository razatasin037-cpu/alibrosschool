<?php
require_once 'cpage.php';

$make = $_POST['make'];
$model = $_POST['model'];
$year = $_POST['year'];
$color = $_POST['color'];
$price = $_POST['price'];
$mileage = $_POST['mileage'];
$transmission = $_POST['transmission'];

$sql = "INSERT INTO cars(make, model, year, color, price, mileage, transmission)
VALUES (
    '".$make."',
    '".$model."',
    '".$year."',
    '".$color."',
    '".$price."',
    '".$mileage."',
    '".$transmission."'
)";

if (mysqli_query($conn, $sql)) {
    echo "<script>
            alert('Record Added Successfully');
            window.location.href='cars.html';
          </script>";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>