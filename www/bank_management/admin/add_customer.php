<?php
require_once "../config/db.php";
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $father_name = trim($_POST['father_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $dob = $_POST['dob'] ?: null;
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $aadhaar = trim($_POST['aadhaar']);
    $account = trim($_POST['account_number']);
    $plan_id = (int)$_POST['plan_id'];
    $opening = (float)$_POST['opening_balance'];

    $code = "CUS" . date("ymdHis") . rand(10,99);

    $stmt = $conn->prepare("INSERT INTO customers
        (customer_code, full_name, father_name, phone, email, dob, gender, address, city, aadhaar, account_number, plan_id, opening_balance)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssssssid", $code, $full_name, $father_name, $phone, $email, $dob, $gender, $address, $city, $aadhaar, $account, $plan_id, $opening);

    if ($stmt->execute()) {
        $msg = "Customer added successfully.";
    } else {
        $msg = "Error: " . $stmt->error;
    }
}
$plans = $conn->query("SELECT id, plan_name FROM plans WHERE status='Active' ORDER BY plan_name");
include "../includes/header.php";
include "../includes/sidebar.php";
?>
<main class="md:ml-64 p-5 md:p-8">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold mb-2">Add Customer</h2>
        <p class="text-slate-500 mb-6">Create a new bank customer.</p>

        <?php if ($msg): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-lg mb-5"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-6 rounded-2xl shadow-sm grid grid-cols-1 md:grid-cols-2 gap-5">
            <?php
            $fields = [
                ['full_name','Full Name','text'], ['father_name','Father Name','text'],
                ['phone','Phone','text'], ['email','Email','email'],
                ['dob','Date of Birth','date'], ['city','City','text'],
                ['aadhaar','Aadhaar','text'], ['account_number','Account Number','text'],
                ['opening_balance','Opening Balance','number']
            ];
            foreach ($fields as $f):
            ?>
            <div>
                <label class="block mb-2 font-medium"><?= $f[1] ?></label>
                <input name="<?= $f[0] ?>" type="<?= $f[2] ?>" step="0.01" class="w-full border rounded-lg px-4 py-3" required>
            </div>
            <?php endforeach; ?>

            <div>
                <label class="block mb-2 font-medium">Gender</label>
                <select name="gender" class="w-full border rounded-lg px-4 py-3">
                    <option>Male</option><option>Female</option><option>Other</option>
                </select>
            </div>

            <div>
                <label class="block mb-2 font-medium">Plan</label>
                <select name="plan_id" class="w-full border rounded-lg px-4 py-3" required>
                    <?php while($p = $plans->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['plan_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block mb-2 font-medium">Address</label>
                <textarea name="address" class="w-full border rounded-lg px-4 py-3" rows="3"></textarea>
            </div>

            <button class="md:col-span-2 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                Save Customer
            </button>
        </form>
    </div>
</main>
<?php include "../includes/footer.php"; ?>
