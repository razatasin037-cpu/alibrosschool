<?php
require_once "../config/db.php";
$user = $conn->query("SELECT * FROM users ORDER BY id ASC LIMIT 1")->fetch_assoc();
include "../includes/header.php";
?>
<div class="min-h-screen flex items-center justify-center p-5">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-8">
        <div class="text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-4xl">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2 class="text-2xl font-bold mt-4"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></h2>
            <p class="text-slate-500"><?= htmlspecialchars($user['role'] ?? 'customer') ?></p>
        </div>
        <div class="mt-8 space-y-4">
            <div class="border rounded-lg p-4"><b>Email:</b> <?= htmlspecialchars($user['email'] ?? '') ?></div>
            <div class="border rounded-lg p-4"><b>Phone:</b> <?= htmlspecialchars($user['phone'] ?? '') ?></div>
            <div class="border rounded-lg p-4"><b>Joined:</b> <?= htmlspecialchars($user['created_at'] ?? '') ?></div>
        </div>
        <a href="../admin/dashboard.php" class="block text-center mt-6 bg-blue-600 text-white py-3 rounded-lg">Back to Dashboard</a>
    </div>
</div>
<?php include "../includes/footer.php"; ?>
