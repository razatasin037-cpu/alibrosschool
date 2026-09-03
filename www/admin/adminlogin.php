<?php
session_start();
require_once "connection.php";

$error = "";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM admin WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        $admin = mysqli_fetch_assoc($result);

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];

        header("Location: admin.php");
        exit();

    } else {
        $error = "Invalid Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-slate-100 flex justify-center items-center min-h-screen">

<div class="bg-white w-full max-w-md rounded-xl shadow-lg p-8">

    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-blue-600">
            Admin Login
        </h1>
        <p class="text-gray-500 mt-2">
            Login to continue
        </p>
    </div>

    <?php if($error!=""){ ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST">

        <div class="mb-4">
            <label class="block mb-2 font-medium">
                Email
            </label>

            <input
                type="email"
                name="email"
                required
                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter Email">
        </div>

        <div class="mb-6">
            <label class="block mb-2 font-medium">
                Password
            </label>

            <input
                type="password"
                name="password"
                required
                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter Password">
        </div>

        <button
            type="submit"
            name="login"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">

            Login

        </button>

    </form>
    <div class="mt-6 text-center">

    <p class="text-gray-500 mb-3">
        Don't have an admin account?
    </p>

    <a href="admininsert.php"
       class="block w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition">
        Register
    </a>

</div>

</div>

</body>
</html>