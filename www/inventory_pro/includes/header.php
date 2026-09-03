<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/helpers.php';
require_login();
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($pageTitle)?> · Inventory Pro</title>
<link rel="stylesheet" href="/inventory_pro/assets/css/app.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="app">
<aside class="sidebar" id="sidebar">
  <div class="brand"><span class="brand-mark">IP</span><span>Inventory Pro</span></div>
  <nav>
    <a href="/inventory_pro/index.php">Dashboard</a>
    <div class="nav-label">Master</div>
    <a href="/inventory_pro/products/index.php">Products</a>
    <a href="/inventory_pro/categories/index.php">Categories</a>
    <a href="/inventory_pro/suppliers/index.php">Suppliers</a>
    <a href="/inventory_pro/customers/index.php">Customers</a>
    <div class="nav-label">Operations</div>
    <a href="/inventory_pro/purchases/index.php">Purchases</a>
    <a href="/inventory_pro/sales/index.php">Sales</a>
    <a href="/inventory_pro/stock/index.php">Stock Ledger</a>
    <a href="/inventory_pro/expenses/index.php">Expenses</a>
    <div class="nav-label">Analytics</div>
    <a href="/inventory_pro/reports/index.php">Reports</a>
    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
    <div class="nav-label">Administration</div>
    <a href="/inventory_pro/users/index.php">Users</a>
    <a href="/inventory_pro/settings.php">Settings</a>
    <?php endif; ?>
  </nav>
</aside>
<main class="main">
<header class="topbar">
  <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
  <div>
    <strong><?=e($pageTitle)?></strong>
    <div class="muted"><?=date('d M Y')?></div>
  </div>
  <div class="top-actions">
    <span class="user-pill"><?=e($_SESSION['user']['name'])?> · <?=e($_SESSION['user']['role'])?></span>
    <a class="btn btn-danger btn-sm" href="/inventory_pro/logout.php">Logout</a>
  </div>
</header>
<section class="content">
<?php show_flash(); ?>
