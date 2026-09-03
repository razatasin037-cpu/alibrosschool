<?php
require_once "../config/db.php";
$msg = "";

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['plan_name']);
    $code = trim($_POST['plan_code']);
    $rate = (float)$_POST['interest_rate'];
    $duration = (int)$_POST['duration_months'];
    $min = (float)$_POST['min_amount'];
    $desc = trim($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO plans (plan_name,plan_code,interest_rate,duration_months,min_amount,description) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssdids", $name,$code,$rate,$duration,$min,$desc);
    $msg = $stmt->execute() ? "Plan added successfully." : "Error: ".$stmt->error;
    $stmt->close();
}
$plans = $conn->query("SELECT * FROM plans ORDER BY id DESC");
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<main class="md:ml-64 p-5 md:p-8">
    <h2 class="text-3xl font-bold mb-6">Plans</h2>
    <?php if($msg): ?><div class="bg-blue-50 p-4 rounded-lg mb-5"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <form method="POST" class="bg-white p-6 rounded-2xl shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <input name="plan_name" placeholder="Plan Name" class="border rounded-lg p-3" required>
        <input name="plan_code" placeholder="Plan Code" class="border rounded-lg p-3" required>
        <input name="interest_rate" type="number" step="0.01" placeholder="Interest %" class="border rounded-lg p-3" required>
        <input name="duration_months" type="number" placeholder="Duration Months" class="border rounded-lg p-3" required>
        <input name="min_amount" type="number" step="0.01" placeholder="Minimum Amount" class="border rounded-lg p-3" required>
        <input name="description" placeholder="Description" class="border rounded-lg p-3">
        <button class="md:col-span-3 bg-blue-600 text-white py-3 rounded-lg">Add Plan</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <?php while($p=$plans->fetch_assoc()): ?>
        <div class="bg-white p-6 rounded-2xl shadow-sm">
            <h3 class="text-xl font-bold"><?= htmlspecialchars($p['plan_name']) ?></h3>
            <p class="text-slate-500"><?= htmlspecialchars($p['plan_code']) ?></p>
            <div class="mt-4 text-2xl font-bold"><?= $p['interest_rate'] ?>% <span class="text-sm font-normal">interest</span></div>
            <p class="mt-2">Duration: <?= $p['duration_months'] ?> months</p>
            <p>Minimum: ₹<?= number_format($p['min_amount'],2) ?></p>
        </div>
    <?php endwhile; ?>
    </div>
</main>
<?php include "../includes/footer.php"; ?>
