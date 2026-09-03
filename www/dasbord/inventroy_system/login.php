<?php
session_start();
require_once "connection.php";

$error = "";

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' AND status='Active'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == "Admin") {
                header("Location:dashbord.php");
                exit();
            } else {
                header("Location:dashbord.php");
                exit();
            }

        } else {
            $error = "Invalid Password!";
        }

    } else {
        $error = "Email not found or account inactive!";
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inventory Management System | Login</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css"
      rel="stylesheet"
    />
  </head>

  <body class="bg-slate-100">
    <div class="min-h-screen flex items-center justify-center p-5">
      <div
        class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden grid lg:grid-cols-2"
      >
        <!-- Left Side -->

        <div
          class="hidden lg:flex font-semibold text-white bg-gradient-to-r from-red-600 to-black text-white flex-col justify-center items-center p-12"
        >
          <div
            class="w-24 h-24 rounded-full bg-white flex items-center justify-center font-semibold text-white bg-gradient-to-r from-red-600 to-black text-5xl mb-6"
          >
            <i class="ri-store-3-line"></i>
          </div>

          <h1 class="text-4xl font-bold mb-4">Inventory System</h1>

          <p class="text-center text-white-100 leading-7">
            Manage Products, Categories, Suppliers, Stock In, Stock Out and
            Reports from one professional dashboard.
          </p>
        </div>

        <!-- Right Side -->

        <div class="p-10 lg:p-14">
          <div class="text-center mb-8">
            <div
              class="w-20 h-20 mx-auto rounded-full bg-blue-600 flex items-center justify-center text-white text-4xl mb-4 lg:hidden"
            >
              <i class="ri-store-3-line"></i>
            </div>

            <h2 class="text-3xl font-bold text-slate-800">Welcome Back</h2>

            <p class="text-gray-500 mt-2">Login to continue</p>
          </div>

          <?php if($error != "") { ?>
    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
        <?php echo $error; ?>
    </div>
<?php } ?>

          
          <form action="login.php" method="POST" class="space-y-6">
            <!-- Email -->

            <div>
              <label class="block mb-2 text-gray-700 font-medium">
                Email Address
              </label>

              <div class="relative">
                <i
                  class="ri-mail-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                ></i>

                <input
                  type="email"
                  name="email"
                  placeholder="Enter your email"
                  class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                />
              </div>
            </div>

            <!-- Password -->

            <div>
              <label class="block mb-2 text-gray-700 font-medium">
                Password
              </label>

              <div class="relative">
                <i
                  class="ri-lock-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                ></i>

                <input
                  id="password"
                  type="password"
                  name="password"
                  placeholder="Enter your password"
                  class="w-full pl-12 pr-12 py-3 rounded-xl border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                />

                <button
                  type="button"
                  onclick="togglePassword()"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-xl text-gray-500"
                >
                  <i id="eyeIcon" class="ri-eye-line"></i>
                </button>
              </div>
            </div>

            <!-- Remember -->

            <div class="flex justify-between items-center">
              <label class="flex items-center gap-2 text-gray-600">
                <input type="checkbox" />

                Remember Me
              </label>

              <a href="#" class="text-blue-600 hover:underline">
                Forgot Password?
              </a>
            </div>

            <!-- Login Button -->

            <button
              type="submit"
              name="login"
              class="w-full py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-red-600 to-black"
            >
              Login
            </button>
          </form>
        </div>
      </div>
    </div>

    <script>
      function togglePassword() {
        let password = document.getElementById("password");
        let icon = document.getElementById("eyeIcon");

        if (password.type === "password") {
          password.type = "text";
          icon.className = "ri-eye-off-line";
        } else {
          password.type = "password";
          icon.className = "ri-eye-line";
        }
      }
    </script>
  </body>
</html>
