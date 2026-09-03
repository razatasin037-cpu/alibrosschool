<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli(
    '127.0.0.1',
    'root',
    '',
    'gulabi_alibrose',
    3306
);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>