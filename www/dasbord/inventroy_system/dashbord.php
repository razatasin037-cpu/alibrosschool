<?php session_start(); if (!isset($_SESSION['id'])) { header("Location: ../login.php"); exit(); } ?>



<?php
require_once "connection.php";

// Total Products
$product = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
$totalProduct = mysqli_fetch_assoc($product)['total'];

// Total Categories
$category = mysqli_query($conn, "SELECT COUNT(*) AS total FROM category");
$totalCategory = mysqli_fetch_assoc($category)['total'];

// Total Suppliers
$supplier = mysqli_query($conn, "SELECT COUNT(*) AS total FROM supplier");
$totalSupplier = mysqli_fetch_assoc($supplier)['total'];

// Stock In
$stockIn = mysqli_query($conn, "SELECT SUM(quantity) AS total FROM stock_in");
$totalStockIn = mysqli_fetch_assoc($stockIn)['total'];
if($totalStockIn=="") $totalStockIn=0;

// Stock Out
$stockOut = mysqli_query($conn, "SELECT SUM(quantity) AS total FROM stock_out");
$totalStockOut = mysqli_fetch_assoc($stockOut)['total'];
if($totalStockOut=="") $totalStockOut=0;

// Low Stock
$lowStock = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE quantity<=minimum_stock");
$totalLowStock = mysqli_fetch_assoc($lowStock)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard - Professional</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Remix Icons CDN -->
    <link href="https://jsdelivr.net" rel="stylesheet"/>
    <!-- Theme Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-transition { transition: all 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <!-- App Container -->
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white border-r border-gray-100 flex flex-col h-full hidden md:flex shrink-0">
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center px-6 border-b border-gray-100 gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                    <i class="ri-archive-stack-fill text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold text-gray-900 tracking-tight leading-none">StockMaster</h1>
                    <span class="text-[11px] text-gray-400 font-medium">Inventory System</span>
                </div>
            </div>

            <!-- Sidebar Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl bg-brand-50 text-brand-500 transition-all">
                    <i class="ri-dashboard-3-fill text-lg"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-box-3-line text-lg"></i>
                    <span>Products</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-folder-open-line text-lg"></i>
                    <span>Categories</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-user-shared-line text-lg"></i>
                    <span>Suppliers</span>
                </a>

                <!-- Inventory Section -->
                <div class="pt-4 my-2 border-t border-gray-100">
                    <span class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Inventory Logic</span>
                </div>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-arrow-left-down-line text-lg text-emerald-500"></i>
                    <span>Stock In</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-arrow-right-up-line text-lg text-rose-500"></i>
                    <span>Stock Out</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-bar-chart-box-line text-lg"></i>
                    <span>Reports</span>
                </a>

                <!-- System Section -->
                <div class="pt-4 my-2 border-t border-gray-100">
                    <span class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">System</span>
                </div>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-user-settings-line text-lg"></i>
                    <span>Users</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-500 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all">
                    <i class="ri-settings-3-line text-lg"></i>
                    <span>Settings</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-100">
                <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-rose-600 rounded-xl hover:bg-rose-50 transition-all">
                    <i class="ri-logout-box-r-line text-lg"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            
            <!-- Top Header Navbar -->
            <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 z-10 shrink-0">
                <!-- Search bar & Mobile Menu Trigger -->
                <div class="flex items-center gap-4 flex-1 max-w-lg">
                    <button class="md:hidden text-gray-500 hover:text-brand-500 transition-colors focus:outline-none" aria-label="Toggle Menu">
                        <i class="ri-menu-2-line text-2xl"></i>
                    </button>
                    <div class="relative w-full hidden sm:flex items-center gap-3">
                        <div class="relative w-full">
                            <i class="ri-search-2-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-base"></i>
                            <input type="search" id="navbarSearch" placeholder="Search product code, batches, or active suppliers..." class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 transition-all placeholder:text-gray-400" >
                        </div>
                    </div>
                </div>

                <!-- Notifications & Profile Menu -->
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button class="w-10 h-10 rounded-xl border border-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-all relative focus:outline-none" id="notificationBtn">
                            <i class="ri-notification-3-line text-xl"></i>
                            <span class="absolute top-2.5 right-2.5 w-2.5 h-2.5 bg-brand-500 rounded-full ring-2 ring-white animate-pulse"></span>
                        </button>
                    </div>
                    <div class="h-6 w-px bg-gray-200"></div>
                    <div class="flex items-center gap-3 group cursor-pointer">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-gray-900 leading-tight group-hover:text-brand-500 transition-colors">Welcome, Admin</p>
                            <span class="text-[11px] text-gray-400 font-medium tracking-wide">Super Administrator</span>
                        </div>
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-orange-400 text-white font-bold text-sm flex items-center justify-center shadow-sm tracking-wider border border-brand-100 transform group-hover:scale-105 transition-transform"> AD </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full ring-2 ring-white"></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Frame -->
            <main class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Welcome Dashboard Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                    <h2 class="text-3xl font-bold text-gray-900">
    Welcome Back, <?php echo $_SESSION['name']; ?> 👋
</h2>

<p class="text-gray-500 mt-1">
    Manage your Inventory Management System from one place.
</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-lg shadow-brand-500/10 flex items-center gap-2 transition-all">
                            <i class="ri-add-line font-bold"></i> Add Product
                        </button>
                    </div>
                </div>
          
    <!-- Total Products -->
    <div class="bg-white rounded-2xl shadow-lg p-6 flex justify-between items-center hover:shadow-2xl transition duration-300 border-l-4 border-orange-500">
        <div>
            <p class="text-gray-500 font-medium">Total Products</p>
            <h2 class="text-5xl font-bold text-gray-800 mt-2"><?php echo $totalProduct; ?></h2>
            <span class="text-green-500 text-sm">Available Products</span>
        </div>
        <div class="w-16 h-16 rounded-2xl bg-orange-100 flex items-center justify-center">
            <i class="ri-box-3-line text-3xl text-orange-600"></i>
        </div>
    </div>

 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Total Categories -->
    <div class="bg-white rounded-2xl shadow-lg p-6 flex justify-between items-center hover:shadow-2xl transition duration-300 border-l-4 border-blue-500">
        <div>
            <p class="text-gray-500 font-medium">Total Categories</p>
            <h2 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $totalCategory; ?></h2>
            <span class="text-blue-500 text-sm">All Categories</span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center shrink-0">
            <i class="ri-apps-2-line text-2xl text-blue-600"></i>
        </div>
    </div>

    <!-- Total Suppliers -->
    <div class="bg-white rounded-2xl shadow-lg p-6 flex justify-between items-center hover:shadow-2xl transition duration-300 border-l-4 border-green-500">
        <div>
            <p class="text-gray-500 font-medium">Total Suppliers</p>
            <h2 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $totalSupplier; ?></h2>
            <span class="text-green-500 text-sm">Registered Suppliers</span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center shrink-0">
            <i class="ri-truck-line text-2xl text-green-600"></i>
        </div>
    </div>

    <!-- Stock In -->
    <div class="bg-white rounded-2xl shadow-lg p-6 flex justify-between items-center hover:shadow-2xl transition duration-300 border-l-4 border-cyan-500">
        <div>
            <p class="text-gray-500 font-medium">Stock In</p>
            <h2 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $totalStockIn; ?></h2>
            <span class="text-cyan-500 text-sm">Items Received</span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-cyan-100 flex items-center justify-center shrink-0">
            <i class="ri-download-cloud-2-line text-2xl text-cyan-600"></i>
        </div>
    </div>

    <!-- Stock Out -->
    <div class="bg-white rounded-2xl shadow-lg p-6 flex justify-between items-center hover:shadow-2xl transition duration-300 border-l-4 border-red-500">
        <div>
            <p class="text-gray-500 font-medium">Stock Out</p>
            <h2 class="text-3xl font-bold text-gray-800 mt-2"><?php echo $totalStockOut; ?></h2>
            <span class="text-red-500 text-sm">Items Sold</span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center shrink-0">
            <i class="ri-upload-cloud-2-line text-2xl text-red-600"></i>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="bg-white rounded-2xl shadow-lg p-6 flex justify-between items-center hover:shadow-2xl transition duration-300 border-l-4 border-yellow-500">
        <div>
            <p class="text-gray-500 font-medium">Low Stock Alert</p>
            <h2 class="text-3xl font-bold text-red-600 mt-2"><?php echo $totalLowStock; ?></h2>
            <span class="text-yellow-600 text-sm">Need Restock</span>
        </div>
        <div class="w-14 h-14 rounded-2xl bg-yellow-100 flex items-center justify-center shrink-0">
            <i class="ri-alarm-warning-line text-2xl text-yellow-600"></i>
        </div>
    </div>

</div>
    