<?php
require_once "../config/db.php";


// Total Customers
$customersQuery = $conn->query("SELECT COUNT(*) AS total FROM customers");
$customers = $customersQuery->fetch_assoc()['total'];


// Total Plans
$plansQuery = $conn->query("SELECT COUNT(*) AS total FROM plans");
$plans = $plansQuery->fetch_assoc()['total'];


// Active Customers
$activeQuery = $conn->query("SELECT COUNT(*) AS total FROM customers WHERE status = 'Active'");
$active = $activeQuery->fetch_assoc()['total'];


include "../includes/header.php";
include "../includes/sidebar.php";
?>


<main class="md:ml-64 p-5 md:p-8">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-8">

        <div>
            <h2 class="text-3xl font-bold text-slate-800">
                Dashboard
            </h2>

            <p class="text-slate-500">
                Bank Management System
            </p>
        </div>


        <a href="add_customer.php"
           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg">

            <i class="fa-solid fa-user-plus mr-2"></i>

            Add Customer

        </a>

    </div>


    <!-- Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">


        <!-- Total Customers -->
        <div class="bg-white rounded-2xl shadow-sm p-6">

            <div class="flex justify-between items-center">

                <div>

                    <p class="text-slate-500">
                        Total Customers
                    </p>

                    <h3 class="text-3xl font-bold mt-2">
                        <?= $customers ?>
                    </h3>

                </div>


                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">

                    <i class="fa-solid fa-users text-xl"></i>

                </div>

            </div>

        </div>



        <!-- Active Customers -->
        <div class="bg-white rounded-2xl shadow-sm p-6">

            <div class="flex justify-between items-center">

                <div>

                    <p class="text-slate-500">
                        Active Customers
                    </p>

                    <h3 class="text-3xl font-bold mt-2">
                        <?= $active ?>
                    </h3>

                </div>


                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center">

                    <i class="fa-solid fa-user-check text-xl"></i>

                </div>

            </div>

        </div>



        <!-- Total Plans -->
        <div class="bg-white rounded-2xl shadow-sm p-6">

            <div class="flex justify-between items-center">

                <div>

                    <p class="text-slate-500">
                        Total Plans
                    </p>

                    <h3 class="text-3xl font-bold mt-2">
                        <?= $plans ?>
                    </h3>

                </div>


                <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">

                    <i class="fa-solid fa-layer-group text-xl"></i>

                </div>

            </div>

        </div>

    </div>



    <!-- Quick Actions -->
    <div class="mt-8">

        <h3 class="text-xl font-bold mb-5">
            Quick Actions
        </h3>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">


            <!-- Customers -->
            <a href="customers.php"
               class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md">

                <i class="fa-solid fa-users text-3xl text-blue-600"></i>

                <h4 class="text-lg font-bold mt-4">
                    Customers
                </h4>

                <p class="text-slate-500 mt-1">
                    View all customers
                </p>

            </a>



            <!-- Add Customer -->
            <a href="add_customer.php"
               class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md">

                <i class="fa-solid fa-user-plus text-3xl text-green-600"></i>

                <h4 class="text-lg font-bold mt-4">
                    Add Customer
                </h4>

                <p class="text-slate-500 mt-1">
                    Add new customer
                </p>

            </a>



            <!-- Plans -->
            <a href="plans.php"
               class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md">

                <i class="fa-solid fa-layer-group text-3xl text-purple-600"></i>

                <h4 class="text-lg font-bold mt-4">
                    Bank Plans
                </h4>

                <p class="text-slate-500 mt-1">
                    Manage bank plans
                </p>

            </a>

        </div>

    </div>


</main>


<?php include "../includes/footer.php"; ?>