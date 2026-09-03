<?php
require_once '../config/db.php';

$page_title = 'Add Customer';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $father = trim($_POST['father_name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $aadhaar = trim($_POST['aadhaar'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $opening = $_POST['opening_date'] ?? date('Y-m-d');
    $plan = $_POST['plan'] ?? 'Weekly';
    $amount = (float)($_POST['amount'] ?? 0);

    if ($plan !== 'Weekly' && $plan !== 'Monthly') {
        $plan = 'Weekly';
    }

    $total = $plan === 'Weekly' ? 52 : 12;
    $next = $plan === 'Weekly'
        ? date('Y-m-d', strtotime($opening . ' +7 days'))
        : date('Y-m-d', strtotime($opening . ' +1 month'));

    if ($name === '') {
        $error = 'Customer name required hai.';
    } elseif ($amount <= 0) {
        $error = 'Installment amount 0 se bada hona chahiye.';
    } elseif (!$opening) {
        $error = 'Opening date required hai.';
    } else {

        $stmt = $conn->prepare("
            INSERT INTO customers
            (
                name,
                father_name,
                mobile,
                aadhaar,
                address,
                opening_date,
                plan,
                amount,
                total_installments,
                paid_installments,
                next_due_date,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 'Active')
        ");

        if (!$stmt) {
            $error = $conn->error;
        } else {

            $stmt->bind_param(
                'sssssssdis',
                $name,
                $father,
                $mobile,
                $aadhaar,
                $address,
                $opening,
                $plan,
                $amount,
                $total,
                $next
            );

            if ($stmt->execute()) {
                header('Location: ../customers.php?created=1');
                exit;
            }

            $error = $stmt->error;
            $stmt->close();
        }
    }
}

include '../includes/header.php';
?>

<div class="cardx p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="muted small">GULABI ALIBROSE</div>
            <h3 class="fw-bold mb-1">Add New Customer</h3>
            <div class="muted">Create a Weekly or Monthly collection account.</div>
        </div>

        <div class="iconbox">
            <i class="bi bi-person-plus-fill"></i>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="row g-3">

        <div class="col-md-6">
            <label class="form-label">Customer Name</label>
            <input
                type="text"
                name="name"
                class="form-control"
                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                required
            >
        </div>

        <div class="col-md-6">
            <label class="form-label">Father / Husband Name</label>
            <input
                type="text"
                name="father_name"
                class="form-control"
                value="<?= htmlspecialchars($_POST['father_name'] ?? '') ?>"
            >
        </div>

        <div class="col-md-6">
            <label class="form-label">Mobile</label>
            <input
                type="text"
                name="mobile"
                class="form-control"
                value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>"
            >
        </div>

        <div class="col-md-6">
            <label class="form-label">Aadhaar</label>
            <input
                type="text"
                name="aadhaar"
                class="form-control"
                value="<?= htmlspecialchars($_POST['aadhaar'] ?? '') ?>"
            >
        </div>

        <div class="col-12">
            <label class="form-label">Address</label>
            <textarea
                name="address"
                class="form-control"
                rows="2"
            ><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
        </div>

        <div class="col-md-4">
            <label class="form-label">Opening Date</label>
            <input
                type="date"
                name="opening_date"
                class="form-control"
                value="<?= htmlspecialchars($_POST['opening_date'] ?? date('Y-m-d')) ?>"
                required
            >
        </div>

        <div class="col-md-4">
            <label class="form-label">Plan</label>
            <select name="plan" class="form-select">
                <option
                    value="Weekly"
                    <?= ($_POST['plan'] ?? 'Weekly') === 'Weekly' ? 'selected' : '' ?>
                >
                    Weekly — 52 Installments
                </option>

                <option
                    value="Monthly"
                    <?= ($_POST['plan'] ?? '') === 'Monthly' ? 'selected' : '' ?>
                >
                    Monthly — 12 Installments
                </option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Installment Amount</label>
            <input
                type="number"
                step="0.01"
                min="1"
                name="amount"
                class="form-control"
                value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>"
                required
            >
        </div>

        <div class="col-12 mt-3">
            <button class="btn btn-pink px-4">
                <i class="bi bi-person-plus-fill me-1"></i>
                Create Customer
            </button>

            <a
                href="../customers.php"
                class="btn btn-light px-4 ms-2"
            >
                Cancel
            </a>
        </div>

    </form>

</div>

<?php include '../includes/footer.php'; ?>