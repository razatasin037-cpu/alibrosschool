<?php
session_start();
header('Location: '.(isset($_SESSION['admin']) ? 'dashboard.php' : 'login.php'));
exit;
