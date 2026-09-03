<?php

require_once "connection.php";

if(isset($_POST['submit']))
{
    $admin_id  = $_POST['admin_id'];
    $full_name = $_POST['full_name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $password  = $_POST['password'];
    $role      = $_POST['role'];
    $status    = $_POST['status'];

    $sql = "INSERT INTO admin
    (admin_id, full_name, email, phone, password, role, status)
    VALUES
    ('$admin_id', '$full_name', '$email', '$phone', '$password', '$role', '$status')";

    if(mysqli_query($conn, $sql))
    {
        echo "<script>
                alert('Admin Registered Successfully');
                window.location='admin_register.php';
              </script>";
    }
    else
    {
        echo "Error : " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | Maruti Company</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body class="bg-slate-100">

<div class="max-w-3xl mx-auto py-10 px-5">

    <div class="bg-white shadow-xl rounded-xl overflow-hidden">

        <!-- Header -->
        <div class="bg-blue-700 p-6 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-white">
                    Maruti Company
                </h2>
                <p class="text-blue-100 mt-1">
                    Admin Registration Form
                </p>
            </div>

            <i class="ri-admin-fill text-5xl text-white"></i>
        </div>

        <!-- Form -->
        <form action="adminreg.php" method="POST" class="p-8">

            <div class="grid md:grid-cols-2 gap-6">

                <!-- Admin ID -->
                <div>
                    <label class="block font-semibold mb-2">Admin ID</label>
                    <input
                        type="text"
                        name="admin_id"
                        placeholder="Enter Admin ID"
                        required
                        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block font-semibold mb-2">Full Name</label>
                    <input
                        type="text"
                        name="full_name"
                        placeholder="Enter Full Name"
                        required
                        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <!-- Email -->
                <div>
                    <label class="block font-semibold mb-2">Email</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="Enter Email"
                        required
                        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block font-semibold mb-2">Phone Number</label>
                    <input
                        type="text"
                        name="phone"
                        placeholder="Enter Phone Number"
                        required
                        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <!-- Password -->
                <div>
                    <label class="block font-semibold mb-2">Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Enter Password"
                        required
                        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <!-- Role -->
                <div>
                    <label class="block font-semibold mb-2">Role</label>
                    <select
                        name="role"
                        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

                        <option value="Admin">Admin</option>
                        <option value="Super Admin">Super Admin</option>

                    </select>
                </div>

                <!-- Status -->
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2">Status</label>

                    <select
                        name="status"
                        class="w-full border rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>

                    </select>
                </div>

            </div>

            <!-- Buttons -->
            <div class="flex gap-4 mt-8">

                <button
                    type="submit"
                    name="submit"
                    class="bg-blue-700 hover:bg-blue-800 text-white px-8 py-3 rounded-lg font-semibold">

                    <i class="ri-user-add-fill"></i>
                    Register

                </button>

                <button
                    type="reset"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-8 py-3 rounded-lg font-semibold">

                    Reset

                </button>

                 <a href="hpage.html"
                   class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-semibold">

                    Back

                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>