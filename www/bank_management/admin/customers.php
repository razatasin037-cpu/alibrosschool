<?php
require_once "../config/db.php";
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM customers WHERE id=$id");
    header("Location: customers.php");
    exit;
}
$result = $conn->query("SELECT c.*, p.plan_name FROM customers c LEFT JOIN plans p ON c.plan_id=p.id ORDER BY c.id DESC");
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<main class="md:ml-64 p-5 md:p-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">Customers</h2>
        <a href="add_customer.php" class="bg-blue-600 text-white px-5 py-3 rounded-lg">Add Customer</a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-900 text-white">
                <tr><th class="p-4">Code</th><th class="p-4">Name</th><th class="p-4">Phone</th><th class="p-4">Plan</th><th class="p-4">Balance</th><th class="p-4">Action</th></tr>
            </thead>
            <tbody>
            <?php while($row=$result->fetch_assoc()): ?>
                <tr class="border-b">
                    <td class="p-4"><?= htmlspecialchars($row['customer_code']) ?></td>
                    <td class="p-4 font-semibold"><?= htmlspecialchars($row['full_name']) ?></td>
                    <td class="p-4"><?= htmlspecialchars($row['phone']) ?></td>
                    <td class="p-4"><?= htmlspecialchars($row['plan_name'] ?? 'No Plan') ?></td>
                    <td class="p-4">₹<?= number_format($row['opening_balance'],2) ?></td>
                    <td class="p-4">
                        <a onclick="return confirm('Delete customer?')" href="?delete=<?= $row['id'] ?>" class="text-red-600">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include "../includes/footer.php"; ?>
