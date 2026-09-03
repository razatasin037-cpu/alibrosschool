<?php
require_once '../config/db.php';

$page_title = 'Accounts';
$error = '';
$success = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: accounts.php?deleted=1");
        exit;
    }

    $error = $stmt->error;
}

if (isset($_GET['deleted'])) {
    $success = 'Account successfully deleted.';
}

$editAccount = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("
        SELECT a.*, c.name AS customer_name
        FROM accounts a
        LEFT JOIN customers c ON c.id = a.customer_id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $editAccount = $stmt->get_result()->fetch_assoc();

    if (!$editAccount) {
        $error = 'Account not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'save') {

        $id = (int)($_POST['id'] ?? 0);
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $accountNumber = trim($_POST['account_number'] ?? '');
        $accountType = trim($_POST['account_type'] ?? 'Savings');
        $balance = (float)($_POST['balance'] ?? 0);
        $status = ($_POST['status'] ?? 'Active') === 'Closed' ? 'Closed' : 'Active';

        if ($accountNumber === '') {
            $error = 'Account number is required.';
        } else {

            if ($id > 0) {

                $stmt = $conn->prepare("
                    UPDATE accounts
                    SET customer_id = ?,
                        account_number = ?,
                        account_type = ?,
                        balance = ?,
                        status = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "issdsi",
                    $customerId,
                    $accountNumber,
                    $accountType,
                    $balance,
                    $status,
                    $id
                );

                if ($stmt->execute()) {
                    header("Location: accounts.php?updated=1");
                    exit;
                }

                $error = $stmt->error;

            } else {

                $stmt = $conn->prepare("
                    INSERT INTO accounts
                    (customer_id, account_number, account_type, balance, status)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "issds",
                    $customerId,
                    $accountNumber,
                    $accountType,
                    $balance,
                    $status
                );

                if ($stmt->execute()) {
                    header("Location: accounts.php?created=1");
                    exit;
                }

                $error = $stmt->error;
            }
        }
    }
}

if (isset($_GET['created'])) {
    $success = 'New account successfully created.';
}

if (isset($_GET['updated'])) {
    $success = 'Account successfully updated.';
}

$customers = $conn->query("
    SELECT id, name, mobile, aadhaar
    FROM customers
    ORDER BY name ASC
");

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $like = '%' . $search . '%';

    $stmt = $conn->prepare("
        SELECT
            a.id,
            a.account_number,
            a.account_type,
            a.balance,
            a.status,
            a.created_at,
            c.id AS customer_id,
            c.name AS customer_name,
            c.mobile,
            c.aadhaar,
            c.plan,
            c.amount,
            c.next_due_date
        FROM accounts a
        LEFT JOIN customers c ON c.id = a.customer_id
        WHERE
            a.account_number LIKE ?
            OR a.account_type LIKE ?
            OR c.name LIKE ?
            OR c.mobile LIKE ?
            OR c.aadhaar LIKE ?
        ORDER BY a.id DESC
    ");

    $stmt->bind_param(
        "sssss",
        $like,
        $like,
        $like,
        $like,
        $like
    );

    $stmt->execute();
    $accounts = $stmt->get_result();

} else {

    $accounts = $conn->query("
        SELECT
            a.id,
            a.account_number,
            a.account_type,
            a.balance,
            a.status,
            a.created_at,
            c.id AS customer_id,
            c.name AS customer_name,
            c.mobile,
            c.aadhaar,
            c.plan,
            c.amount,
            c.next_due_date
        FROM accounts a
        LEFT JOIN customers c ON c.id = a.customer_id
        ORDER BY a.id DESC
    ");
}

$totalAccounts = (int)$conn->query("
    SELECT COUNT(*) AS total
    FROM accounts
")->fetch_assoc()['total'];

$activeAccounts = (int)$conn->query("
    SELECT COUNT(*) AS total
    FROM accounts
    WHERE status = 'Active'
")->fetch_assoc()['total'];

$totalBalance = (float)$conn->query("
    SELECT COALESCE(SUM(balance),0) AS total
    FROM accounts
")->fetch_assoc()['total'];

include '../includes/header.php';
?>

<style>
.account-hero{
    background:linear-gradient(135deg,#7b123f,#d81b72,#f04a9b);
    color:#fff;
    border-radius:26px;
    padding:28px;
    box-shadow:0 20px 50px #d81b7225;
}

.account-stat{
    background:#fff;
    border:1px solid #f1d8e5;
    border-radius:20px;
    padding:20px;
    height:100%;
    box-shadow:0 10px 30px #7b123f0c;
}

.account-stat-icon{
    width:46px;
    height:46px;
    border-radius:15px;
    background:#fff0f7;
    color:#d81b72;
    display:grid;
    place-items:center;
    font-size:20px;
}

.account-number{
    font-weight:800;
    color:#7b123f;
    letter-spacing:.3px;
}

.customer-name{
    font-weight:800;
    color:#351a29;
}

.customer-meta{
    font-size:11px;
    color:#947786;
}

.account-actions{
    display:flex;
    gap:6px;
    flex-wrap:wrap;
}

.account-actions a{
    width:35px;
    height:35px;
    border-radius:10px;
    display:grid;
    place-items:center;
}

.form-section-title{
    font-size:11px;
    font-weight:800;
    color:#b16b8c;
    letter-spacing:1.2px;
    margin-bottom:12px;
}
</style>

<div class="account-hero mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="small opacity-75">GULABI ALIBROSE</div>
            <h2 class="fw-bold mb-1">Account Management</h2>
            <div class="opacity-75">
                Multiple accounts ek customer ke liye bhi create kar sakte ho.
            </div>
        </div>

        <div class="text-end">
            <div class="small opacity-75">TOTAL BALANCE</div>
            <div class="fs-3 fw-bold">
                ₹<?= number_format($totalBalance, 2) ?>
            </div>
        </div>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success border-0 shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger border-0 shadow-sm">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">

    <div class="col-md-4">
        <div class="account-stat">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="muted small">TOTAL ACCOUNTS</div>
                    <div class="stat-value mt-2">
                        <?= $totalAccounts ?>
                    </div>
                </div>

                <div class="account-stat-icon">
                    <i class="bi bi-bank2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="account-stat">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="muted small">ACTIVE ACCOUNTS</div>
                    <div class="stat-value mt-2">
                        <?= $activeAccounts ?>
                    </div>
                </div>

                <div class="account-stat-icon">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="account-stat">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="muted small">TOTAL BALANCE</div>
                    <div class="stat-value mt-2">
                        ₹<?= number_format($totalBalance, 2) ?>
                    </div>
                </div>

                <div class="account-stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row g-4">

    <div class="col-xl-4">

        <div class="cardx p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h4 class="fw-bold mb-1">
                        <?= $editAccount ? 'Edit Account' : 'Add Account' ?>
                    </h4>

                    <div class="muted small">
                        <?= $editAccount
                            ? 'Account details update karein.'
                            : 'New account create karein.'
                        ?>
                    </div>
                </div>

                <div class="iconbox">
                    <i class="bi <?= $editAccount ? 'bi-pencil-square' : 'bi-plus-lg' ?>"></i>
                </div>

            </div>

            <form method="post">

                <input type="hidden"
                       name="action"
                       value="save">

                <input type="hidden"
                       name="id"
                       value="<?= (int)($editAccount['id'] ?? 0) ?>">

                <div class="form-section-title">
                    CUSTOMER
                </div>

                <label class="form-label">
                    Select Customer
                </label>

                <select name="customer_id"
                        class="form-select mb-3"
                        required>

                    <option value="0">
                        Select Customer
                    </option>

                    <?php while ($customer = $customers->fetch_assoc()): ?>

                        <option
                            value="<?= (int)$customer['id'] ?>"
                            <?= isset($editAccount['customer_id'])
                                && (int)$editAccount['customer_id'] === (int)$customer['id']
                                ? 'selected'
                                : ''
                            ?>
                        >
                            <?= htmlspecialchars($customer['name']) ?>
                            <?php if (!empty($customer['mobile'])): ?>
                                — <?= htmlspecialchars($customer['mobile']) ?>
                            <?php endif; ?>
                        </option>

                    <?php endwhile; ?>

                </select>

                <div class="form-section-title mt-3">
                    ACCOUNT DETAILS
                </div>

                <label class="form-label">
                    Account Number
                </label>

                <input
                    type="text"
                    name="account_number"
                    class="form-control mb-3"
                    value="<?= htmlspecialchars($editAccount['account_number'] ?? '') ?>"
                    placeholder="Example: GA-10001"
                    required
                >

                <label class="form-label">
                    Account Type
                </label>

                <select name="account_type"
                        class="form-select mb-3">

                    <?php
                    $types = [
                        'Savings',
                        'Current',
                        'Business',
                        'Collection',
                        'Investment'
                    ];
                    ?>

                    <?php foreach ($types as $type): ?>

                        <option
                            value="<?= htmlspecialchars($type) ?>"
                            <?= ($editAccount['account_type'] ?? 'Savings') === $type
                                ? 'selected'
                                : ''
                            ?>
                        >
                            <?= htmlspecialchars($type) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <label class="form-label">
                    Opening Balance
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="balance"
                    class="form-control mb-3"
                    value="<?= htmlspecialchars($editAccount['balance'] ?? '0') ?>"
                >

                <label class="form-label">
                    Status
                </label>

                <select name="status"
                        class="form-select mb-4">

                    <option
                        value="Active"
                        <?= ($editAccount['status'] ?? 'Active') === 'Active'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Active
                    </option>

                    <option
                        value="Closed"
                        <?= ($editAccount['status'] ?? '') === 'Closed'
                            ? 'selected'
                            : ''
                        ?>
                    >
                        Closed
                    </option>

                </select>

                <button class="btn btn-pink w-100">

                    <i class="bi <?= $editAccount
                        ? 'bi-check2-circle'
                        : 'bi-plus-circle'
                    ?> me-1"></i>

                    <?= $editAccount
                        ? 'Update Account'
                        : 'Create Account'
                    ?>

                </button>

                <?php if ($editAccount): ?>

                    <a href="accounts.php"
                       class="btn btn-light w-100 mt-2">
                        Cancel Edit
                    </a>

                <?php endif; ?>

            </form>

        </div>

    </div>

    <div class="col-xl-8">

        <div class="cardx p-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

                <div>
                    <h4 class="fw-bold mb-1">
                        All Accounts
                    </h4>

                    <div class="muted small">
                        Customer-wise complete account details
                    </div>
                </div>

                <form method="get"
                      class="d-flex gap-2">

                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        class="form-control"
                        placeholder="Search account/customer"
                    >

                    <button class="btn btn-pink">
                        <i class="bi bi-search"></i>
                    </button>

                    <?php if ($search !== ''): ?>

                        <a href="accounts.php"
                           class="btn btn-light">
                            <i class="bi bi-x-lg"></i>
                        </a>

                    <?php endif; ?>

                </form>

            </div>

            <div class="table-wrap">

                <table class="table align-middle">

                    <thead>

                    <tr>
                        <th>Account</th>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Installment</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>

                    </thead>

                    <tbody>

                    <?php if ($accounts->num_rows === 0): ?>

                        <tr>

                            <td colspan="7"
                                class="text-center py-5">

                                <div class="iconbox mx-auto mb-3">
                                    <i class="bi bi-bank"></i>
                                </div>

                                <strong>No account found</strong>

                                <div class="muted small mt-1">
                                    Abhi koi account create nahi hua.
                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    <?php while ($account = $accounts->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <div class="account-number">
                                    <?= htmlspecialchars($account['account_number']) ?>
                                </div>

                                <small class="customer-meta">
                                    <?= htmlspecialchars($account['account_type']) ?>
                                </small>

                            </td>

                            <td>

                                <?php if ($account['customer_name']): ?>

                                    <div class="customer-name">
                                        <?= htmlspecialchars($account['customer_name']) ?>
                                    </div>

                                    <div class="customer-meta">
                                        <?= htmlspecialchars($account['mobile'] ?? '') ?>
                                    </div>

                                    <?php if (!empty($account['aadhaar'])): ?>

                                        <div class="customer-meta">
                                            Aadhaar:
                                            <?= htmlspecialchars($account['aadhaar']) ?>
                                        </div>

                                    <?php endif; ?>

                                <?php else: ?>

                                    <span class="muted">
                                        No customer
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if ($account['plan']): ?>

                                    <span class="badge text-bg-light">
                                        <?= htmlspecialchars($account['plan']) ?>
                                    </span>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if ($account['amount'] !== null): ?>

                                    ₹<?= number_format(
                                        (float)$account['amount'],
                                        2
                                    ) ?>

                                    <?php if ($account['next_due_date']): ?>

                                        <div class="customer-meta mt-1">
                                            Due:
                                            <?= htmlspecialchars($account['next_due_date']) ?>
                                        </div>

                                    <?php endif; ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>

                            <td>

                                <strong>
                                    ₹<?= number_format(
                                        (float)$account['balance'],
                                        2
                                    ) ?>
                                </strong>

                            </td>

                            <td>

                                <span class="badge <?= $account['status'] === 'Active'
                                    ? 'badge-active'
                                    : 'badge-due'
                                ?>">

                                    <?= htmlspecialchars($account['status']) ?>

                                </span>

                            </td>

                            <td>

                                <div class="account-actions">

                                    <a
                                        href="?edit=<?= (int)$account['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Edit"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a
                                        href="?delete=<?= (int)$account['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Delete"
                                        onclick="return confirm('Ye account permanently delete karna hai?')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>