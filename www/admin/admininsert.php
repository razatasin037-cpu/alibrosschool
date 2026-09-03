<?php
require_once "connection.php";

$message = "";

if (isset($_POST['register'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check email already exists
    $check = mysqli_query($conn, "SELECT * FROM admin WHERE email='$email'");

    if (mysqli_num_rows($check) > 0) {

        $message = "Email already exists!";

    } else {

        $sql = "INSERT INTO admin(email, password)
                VALUES('$email', '$password')";

        if (mysqli_query($conn, $sql)) {

            header("Location: login.php");
            exit();

        } else {

            $message = "Registration Failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Registration</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

<div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">

    <h2 class="text-3xl font-bold text-center text-blue-600 mb-6">
        Admin Registration
    </h2>

    <?php if($message!=""){ ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
    <?php } ?>

    <form method="POST">

        <div class="mb-4">
            <label class="block mb-2 font-medium">Email</label>

            <input
                type="email"
                name="email"
                required
                placeholder="Enter Email"
                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-6">
            <label class="block mb-2 font-medium">Password</label>

            <input
                type="password"
                name="password"
                required
                placeholder="Enter Password"
                class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button
            type="submit"
            name="register"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold">
            Register
        </button>

    </form>

    <div class="text-center mt-6">
        <p class="text-gray-600">
            Already have an account?
        </p>

        <a href="login.php"
           class="text-blue-600 font-semibold hover:underline">
            Login Here
        </a>
    </div>

</div>

</body>
</html>