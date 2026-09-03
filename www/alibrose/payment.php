<?php

date_default_timezone_set('Asia/Kolkata');

require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Customer Payment';

$error = '';
$success = '';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid customer ID.');
}

$stmt = $conn->prepare("
    SELECT *
    FROM customers
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    die('Customer not found.');
}

$plan = $customer['plan'];
$opening_date = $customer['opening_date'];
$amount = (float)$customer['amount'];

$total_installments = $plan === 'Monthly' ? 12 : 52;

function getInstallmentDueDate(
    string $openingDate,
    string $plan,
    int $installmentNo
): string {

    $opening = new DateTime(
        $openingDate,
        new DateTimeZone('Asia/Kolkata')
    );

    if ($plan === 'Monthly') {

        $day = (int)$opening->format('d');

        if ($day <= 10) {

            $firstDue = new DateTime(
                $opening->format('Y-m-10'),
                new DateTimeZone('Asia/Kolkata')
            );

        } else {

            $firstDue = new DateTime(
                $opening->format('Y-m-01'),
                new DateTimeZone('Asia/Kolkata')
            );

            $firstDue->modify('+1 month');

            $firstDue->setDate(
                (int)$firstDue->format('Y'),
                (int)$firstDue->format('m'),
                10
            );
        }

        if ($installmentNo > 1) {

            $months = $installmentNo - 1;

            $firstDue->modify("+{$months} months");
        }

        return $firstDue->format('Y-m-d');
    }

    $opening->modify('+7 days');

    if ($installmentNo > 1) {

        $days = ($installmentNo - 1) * 7;

        $opening->modify("+{$days} days");
    }

    return $opening->format('Y-m-d');
}

$historyStmt = $conn->prepare("
    SELECT
        installment_no,
        amount,
        payment_date,
        due_date,
        created_at
    FROM installment_payments
    WHERE customer_id = ?
    ORDER BY installment_no ASC
");

$historyStmt->bind_param("i", $id);
$historyStmt->execute();

$historyResult = $historyStmt->get_result();

$paidInstallments = [];

while ($row = $historyResult->fetch_assoc()) {

    $paidInstallments[
        (int)$row['installment_no']
    ] = $row;
}

$today = new DateTime(
    'today',
    new DateTimeZone('Asia/Kolkata')
);

$todayDate = $today->format('Y-m-d');

if (isset($_POST['make_payment'])) {

    $selectedInstallments =
        $_POST['installment_no'] ?? [];

    if (!is_array($selectedInstallments)) {
        $selectedInstallments = [
            $selectedInstallments
        ];
    }

    $selectedInstallments = array_map(
        'intval',
        $selectedInstallments
    );

    $selectedInstallments = array_unique(
        $selectedInstallments
    );

    sort($selectedInstallments);

    $paymentDate =
        trim(
            $_POST['payment_date'] ?? ''
        );

    if (empty($selectedInstallments)) {

        $error =
            'Please select at least one due installment.';

    } elseif (empty($paymentDate)) {

        $error =
            'Please select payment date.';

    } else {

        $paymentDateObj =
            DateTime::createFromFormat(
                '!Y-m-d',
                $paymentDate,
                new DateTimeZone('Asia/Kolkata')
            );

        $dateErrors =
            DateTime::getLastErrors();

        if (
            $dateErrors !== false &&
            (
                $dateErrors['warning_count'] > 0 ||
                $dateErrors['error_count'] > 0
            )
        ) {
            $paymentDateObj = false;
        }

        if (!$paymentDateObj) {

            $error =
                'Invalid payment date.';

        } else {

            $openingObj =
                new DateTime(
                    $opening_date,
                    new DateTimeZone('Asia/Kolkata')
                );

            if ($paymentDateObj < $openingObj) {

                $error =
                    'Payment date cannot be before opening date.';

            } elseif (
                $paymentDateObj->format('Y-m-d') >
                $todayDate
            ) {

                $error =
                    'Payment date cannot be in the future.';

            } else {

                $validInstallments = [];

                foreach (
                    $selectedInstallments
                    as $installmentNo
                ) {

                    if (
                        $installmentNo < 1 ||
                        $installmentNo > $total_installments
                    ) {
                        continue;
                    }

                    if (
                        isset(
                            $paidInstallments[
                                $installmentNo
                            ]
                        )
                    ) {
                        continue;
                    }

                    $dueDate =
                        getInstallmentDueDate(
                            $opening_date,
                            $plan,
                            $installmentNo
                        );

                    if ($dueDate > $todayDate) {
                        continue;
                    }

                    $validInstallments[] =
                        $installmentNo;
                }

                if (empty($validInstallments)) {

                    $error =
                        'No unpaid due installment selected.';

                } else {

                    $conn->begin_transaction();

                    try {

                        $insert =
                            $conn->prepare("
                                INSERT INTO installment_payments
                                (
                                    customer_id,
                                    installment_no,
                                    amount,
                                    payment_date,
                                    due_date
                                )
                                VALUES (?, ?, ?, ?, ?)
                            ");

                        foreach (
                            $validInstallments
                            as $installmentNo
                        ) {

                            $dueDate =
                                getInstallmentDueDate(
                                    $opening_date,
                                    $plan,
                                    $installmentNo
                                );

                            $insert->bind_param(
                                "iidss",
                                $id,
                                $installmentNo,
                                $amount,
                                $paymentDate,
                                $dueDate
                            );

                            if (!$insert->execute()) {

                                throw new Exception(
                                    'Payment could not be saved.'
                                );
                            }
                        }

                        $countStmt =
                            $conn->prepare("
                                SELECT
                                    COUNT(DISTINCT installment_no) AS paid
                                FROM installment_payments
                                WHERE customer_id = ?
                            ");

                        $countStmt->bind_param(
                            "i",
                            $id
                        );

                        $countStmt->execute();

                        $countResult =
                            $countStmt->get_result();

                        $countRow =
                            $countResult->fetch_assoc();

                        $paidCount =
                            (int)$countRow['paid'];

                        $remaining =
                            max(
                                0,
                                $total_installments -
                                $paidCount
                            );

                        $nextDueDate = null;

                        if ($remaining > 0) {

                            for (
                                $x = 1;
                                $x <= $total_installments;
                                $x++
                            ) {

                                if (
                                    isset(
                                        $paidInstallments[$x]
                                    ) ||
                                    in_array(
                                        $x,
                                        $validInstallments,
                                        true
                                    )
                                ) {
                                    continue;
                                }

                                $nextDueDate =
                                    getInstallmentDueDate(
                                        $opening_date,
                                        $plan,
                                        $x
                                    );

                                break;
                            }
                        }

                        if ($remaining <= 0) {

                            $update =
                                $conn->prepare("
                                    UPDATE customers
                                    SET
                                        total_installments = ?,
                                        paid_installments = ?,
                                        next_due_date = NULL,
                                        status = 'Completed'
                                    WHERE id = ?
                                ");

                            $update->bind_param(
                                "iii",
                                $total_installments,
                                $paidCount,
                                $id
                            );

                            $update->execute();

                        } else {

                            $update =
                                $conn->prepare("
                                    UPDATE customers
                                    SET
                                        total_installments = ?,
                                        paid_installments = ?,
                                        next_due_date = ?,
                                        status = 'Active'
                                    WHERE id = ?
                                ");

                            $update->bind_param(
                                "iisi",
                                $total_installments,
                                $paidCount,
                                $nextDueDate,
                                $id
                            );

                            $update->execute();
                        }

                        $conn->commit();

                        header(
                            'Location: payment.php?id=' .
                            $id .
                            '&success=1'
                        );

                        exit;

                    } catch (Exception $e) {

                        $conn->rollback();

                        $error =
                            'Payment failed: ' .
                            $e->getMessage();
                    }
                }
            }
        }
    }
}

if (isset($_GET['success'])) {

    $success =
        'Payment successfully recorded.';
}

$historyStmt = $conn->prepare("
    SELECT
        installment_no,
        amount,
        payment_date,
        due_date,
        created_at
    FROM installment_payments
    WHERE customer_id = ?
    ORDER BY installment_no ASC
");

$historyStmt->bind_param(
    "i",
    $id
);

$historyStmt->execute();

$historyResult =
    $historyStmt->get_result();

$paidInstallments = [];

while ($row = $historyResult->fetch_assoc()) {

    $paidInstallments[
        (int)$row['installment_no']
    ] = $row;
}

$paidCount =
    count($paidInstallments);

$remainingCount =
    max(
        $total_installments -
        $paidCount,
        0
    );

$progress =
    $total_installments > 0
    ? (
        $paidCount /
        $total_installments
    ) * 100
    : 0;

$dueCount = 0;
$dueAmount = 0;
$upcomingCount = 0;
$upcomingAmount = 0;

for (
    $i = 1;
    $i <= $total_installments;
    $i++
) {

    if (isset($paidInstallments[$i])) {
        continue;
    }

    $dueDate =
        getInstallmentDueDate(
            $opening_date,
            $plan,
            $i
        );

    if ($dueDate <= $todayDate) {

        $dueCount++;
        $dueAmount += $amount;

    } else {

        $upcomingCount++;
        $upcomingAmount += $amount;
    }
}

$totalPlanAmount =
    $amount * $total_installments;

$totalPaidAmount = 0;

foreach ($paidInstallments as $payment) {

    $totalPaidAmount +=
        (float)$payment['amount'];
}

$totalRemainingAmount =
    max(
        0,
        $totalPlanAmount -
        $totalPaidAmount
    );

$customerStatus =
    $remainingCount <= 0
    ? 'Completed'
    : 'Active';

include 'includes/header.php';

?>

<style>

.payment-title{
    font-size:28px;
    font-weight:850;
    letter-spacing:-.7px;
}

.payment-subtitle{
    color:#6b756e;
    font-size:13px;
}

.payment-card{
    background:#fff;
    border:1px solid #e6ece8;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(9,18,12,.055);
}

.customer-profile{
    background:linear-gradient(
        135deg,
        #050806,
        #0b1810,
        #166534
    );
    color:#fff;
    border-radius:22px;
    padding:25px;
    box-shadow:0 15px 35px rgba(5,20,10,.13);
}

.profile-avatar{
    width:62px;
    height:62px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#22c55e;
    color:#041007;
    font-size:25px;
    font-weight:850;
    flex-shrink:0;
}

.profile-name{
    font-size:21px;
    font-weight:850;
    line-height:1.2;
}

.profile-phone{
    color:#a8c2af;
    font-size:12px;
    margin-top:4px;
}

.customer-info-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:10px;
    margin-top:22px;
}

.customer-info{
    padding:14px;
    border-radius:14px;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.08);
}

.customer-info-label{
    color:#91aa99;
    font-size:9px;
    font-weight:650;
    text-transform:uppercase;
    letter-spacing:.5px;
}

.customer-info-value{
    color:#fff;
    font-size:13px;
    font-weight:750;
    margin-top:5px;
    word-break:break-word;
}

.customer-info-value.green{
    color:#4ade80;
}

.customer-info-value.yellow{
    color:#facc15;
}

.customer-info-value.red{
    color:#fca5a5;
}

.customer-info-full{
    grid-column:1 / -1;
}

.summary-card{
    position:sticky;
    top:90px;
    background:linear-gradient(
        135deg,
        #07100a,
        #0d2015
    );
    color:#fff;
    border-radius:20px;
    padding:24px;
    box-shadow:0 15px 35px rgba(5,20,10,.12);
}

.summary-title{
    font-size:17px;
    font-weight:800;
}

.summary-label{
    color:#91aa99;
    font-size:11px;
}

.summary-value{
    font-size:26px;
    font-weight:850;
}

.summary-number{
    font-size:19px;
    font-weight:800;
}

.due-highlight{
    background:rgba(34,197,94,.10);
    border:1px solid rgba(34,197,94,.18);
    border-radius:15px;
    padding:15px;
}

.installment-card{
    border:1px solid #e8eeea;
    border-radius:15px;
    background:#fff;
    padding:16px;
    transition:.2s;
}

.installment-card:hover{
    border-color:#86efac;
    box-shadow:0 8px 25px rgba(9,18,12,.06);
}

.installment-card.due{
    border-left:4px solid #ef4444;
}

.installment-card.today{
    border-left:4px solid #f59e0b;
}

.installment-card.paid{
    border-left:4px solid #22c55e;
    background:#f7fcf8;
}

.installment-card.upcoming{
    border-left:4px solid #cbd5d1;
}

.installment-number{
    width:38px;
    height:38px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#eaf8ef;
    color:#15803d;
    font-weight:800;
}

.status-badge{
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:6px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:750;
}

.status-paid{
    background:#dcfce7;
    color:#15803d;
}

.status-due{
    background:#fee2e2;
    color:#dc2626;
}

.status-today{
    background:#fef3c7;
    color:#b45309;
}

.status-upcoming{
    background:#f1f5f3;
    color:#64706a;
}

.add-btn{
    width:36px;
    height:36px;
    border-radius:11px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.add-btn.selected{
    background:#16a34a;
    border-color:#16a34a;
    color:#fff;
}

.progress{
    background:#e8efea;
}

.progress-bar{
    background:#16a34a;
}

.history-table th{
    color:#718078;
    font-size:11px;
    border-bottom:1px solid #edf1ee;
}

.history-table td{
    font-size:12px;
    padding:13px 9px;
}

@media(max-width:991px){

    .summary-card{
        position:relative;
        top:auto;
    }

}

@media(max-width:575px){

    .customer-info-grid{
        grid-template-columns:1fr;
    }

    .customer-info-full{
        grid-column:auto;
    }

    .payment-title{
        font-size:23px;
    }

}

</style>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <div class="payment-title">
            Customer Payment
        </div>

        <div class="payment-subtitle">
            Manage installments and collection
        </div>

    </div>

    <a
        href="customers.php"
        class="btn btn-light"
    >
        <i class="bi bi-arrow-left me-2"></i>
        Back
    </a>

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

<form
    method="POST"
    id="paymentForm"
>

<div class="row g-4">

<div class="col-xl-4">

    <div class="customer-profile mb-4">

        <div class="d-flex align-items-center">

            <div class="profile-avatar me-3">

                <?= strtoupper(
                    substr(
                        $customer['name'],
                        0,
                        1
                    )
                ) ?>

            </div>

            <div>

                <div class="profile-name">
                    <?= htmlspecialchars(
                        $customer['name']
                    ) ?>
                </div>

                <div class="profile-phone">

                    <i class="bi bi-phone me-1"></i>

                    <?= htmlspecialchars(
                        $customer['phone']
                    ) ?>

                </div>

            </div>

        </div>

        <div class="customer-info-grid">

            <div class="customer-info">

                <div class="customer-info-label">
                    Aadhaar Number
                </div>

                <div class="customer-info-value">
                    <?= !empty($customer['aadhaar_no'])
                        ? htmlspecialchars(
                            $customer['aadhaar_no']
                        )
                        : 'Not Available'
                    ?>
                </div>

            </div>

            <div class="customer-info">

                <div class="customer-info-label">
                    Opening Date
                </div>

                <div class="customer-info-value">

                    <?= !empty($opening_date)
                        ? date(
                            'd M Y',
                            strtotime(
                                $opening_date
                            )
                        )
                        : 'Not Available'
                    ?>

                </div>

            </div>

            <div class="customer-info">

                <div class="customer-info-label">
                    Plan
                </div>

                <div class="customer-info-value green">
                    <?= htmlspecialchars($plan) ?>
                </div>

            </div>

            <div class="customer-info">

                <div class="customer-info-label">
                    Installment
                </div>

                <div class="customer-info-value green">
                    ₹<?= number_format(
                        $amount,
                        2
                    ) ?>
                </div>

            </div>

            <div class="customer-info">

                <div class="customer-info-label">
                    Total Plan
                </div>

                <div class="customer-info-value">
                    ₹<?= number_format(
                        $totalPlanAmount,
                        2
                    ) ?>
                </div>

            </div>

            <div class="customer-info">

                <div class="customer-info-label">
                    Paid Amount
                </div>

                <div class="customer-info-value green">
                    ₹<?= number_format(
                        $totalPaidAmount,
                        2
                    ) ?>
                </div>

            </div>

            <div class="customer-info">

                <div class="customer-info-label">
                    Remaining Amount
                </div>

                <div class="customer-info-value yellow">
                    ₹<?= number_format(
                        $totalRemainingAmount,
                        2
                    ) ?>
                </div>

            </div>

            <div class="customer-info">

                <div class="customer-info-label">
                    Status
                </div>

                <div class="customer-info-value">

                    <?php if (
                        $customerStatus === 'Completed'
                    ): ?>

                        <span
                            style="
                            color:#4ade80;
                            "
                        >
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Completed
                        </span>

                    <?php else: ?>

                        <span
                            style="
                            color:#facc15;
                            "
                        >
                            <i class="bi bi-clock-fill me-1"></i>
                            Active
                        </span>

                    <?php endif; ?>

                </div>

            </div>

            <?php if (
                !empty($customer['email'])
            ): ?>

            <div class="customer-info customer-info-full">

                <div class="customer-info-label">
                    Email
                </div>

                <div class="customer-info-value">
                    <?= htmlspecialchars(
                        $customer['email']
                    ) ?>
                </div>

            </div>

            <?php endif; ?>

            <?php if (
                !empty($customer['address'])
            ): ?>

            <div class="customer-info customer-info-full">

                <div class="customer-info-label">
                    Address
                </div>

                <div class="customer-info-value">
                    <?= htmlspecialchars(
                        $customer['address']
                    ) ?>
                </div>

            </div>

            <?php endif; ?>

        </div>

    </div>

    <div class="summary-card">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <div class="summary-title">
                    Payment Summary
                </div>

                <div class="summary-label mt-1">
                    Today:
                    <?= date('d M Y') ?>
                </div>

            </div>

            <i
                class="bi bi-wallet2"
                style="
                font-size:25px;
                color:#4ade80;
                "
            ></i>

        </div>

        <div class="due-highlight mt-4">

            <div class="summary-label">
                DUE TILL TODAY
            </div>

            <div class="summary-value mt-1">
                ₹<?= number_format(
                    $dueAmount,
                    2
                ) ?>
            </div>

            <div class="summary-label mt-1">
                <?= $dueCount ?>
                <?= $plan === 'Monthly'
                    ? 'months'
                    : 'weeks'
                ?>
                due
            </div>

        </div>

        <div class="d-flex justify-content-between mt-4">

            <div>

                <div class="summary-label">
                    Paid
                </div>

                <div class="summary-number">
                    <?= $paidCount ?>
                    /
                    <?= $total_installments ?>
                </div>

            </div>

            <div class="text-end">

                <div class="summary-label">
                    Remaining
                </div>

                <div class="summary-number">
                    <?= $remainingCount ?>
                </div>

            </div>

        </div>

        <div class="progress mt-3" style="height:9px">

            <div
                class="progress-bar"
                style="width:<?= $progress ?>%"
            ></div>

        </div>

        <div class="d-flex justify-content-between mt-2">

            <small style="color:#4ade80">
                <?= round($progress) ?>% Paid
            </small>

            <small class="summary-label">
                <?= $remainingCount ?>
                Remaining
            </small>

        </div>

        <hr
            style="
            border-color:
            rgba(255,255,255,.08);
            "
        >

        <div class="mb-3">

            <label
                class="form-label"
                style="color:#a8bcae"
            >
                Payment Date
            </label>

            <input
                type="date"
                name="payment_date"
                class="form-control"
                value="<?= date('Y-m-d') ?>"
                min="<?= htmlspecialchars(
                    $opening_date
                ) ?>"
                max="<?= date('Y-m-d') ?>"
                required
            >

        </div>

        <div class="d-flex justify-content-between mb-2">

            <span class="summary-label">
                Selected
            </span>

            <strong id="selectedCount">
                0
            </strong>

        </div>

        <div class="d-flex justify-content-between">

            <span class="summary-label">
                Selected Amount
            </span>

            <strong id="totalPayment">
                ₹0.00
            </strong>

        </div>

        <button
            type="button"
            id="payAllDue"
            class="btn btn-success w-100 mt-4 py-2"
            <?= $dueCount === 0 ? 'disabled' : '' ?>
        >
            <i class="bi bi-check2-all me-2"></i>
            Select All Due
        </button>

        <button
            type="submit"
            name="make_payment"
            id="payButton"
            class="btn btn-light w-100 mt-2 py-2"
            disabled
        >
            <i class="bi bi-credit-card me-2"></i>
            Pay Selected
        </button>

        <button
            type="button"
            id="clearSelection"
            class="btn btn-outline-light w-100 mt-2 py-2"
        >
            Clear Selection
        </button>

    </div>

</div>

<div class="col-xl-8">

    <div class="payment-card p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h5 class="fw-bold mb-1">
                    Installments
                </h5>

                <small class="muted">
                    Due installments can be selected for payment
                </small>

            </div>

            <?php if (
                $customerStatus === 'Completed'
            ): ?>

                <span class="badge text-bg-success">
                    <i class="bi bi-check-circle me-1"></i>
                    Completed
                </span>

            <?php endif; ?>

        </div>

        <div class="row g-3">

        <?php for (
            $i = 1;
            $i <= $total_installments;
            $i++
        ): ?>

            <?php

            $dueDate =
                getInstallmentDueDate(
                    $opening_date,
                    $plan,
                    $i
                );

            $isPaid =
                isset(
                    $paidInstallments[$i]
                );

            $isDue =
                !$isPaid &&
                $dueDate <= $todayDate;

            $isToday =
                !$isPaid &&
                $dueDate === $todayDate;

            ?>

            <div class="col-md-6">

                <div
                    class="
                    installment-card
                    <?=
                        $isPaid
                        ? 'paid'
                        : (
                            $isToday
                            ? 'today'
                            : (
                                $isDue
                                ? 'due'
                                : 'upcoming'
                            )
                        )
                    ?>
                    "
                >

                    <div class="d-flex align-items-center">

                        <div class="installment-number me-3">

                            #<?= $i ?>

                        </div>

                        <div class="flex-grow-1">

                            <div class="fw-bold">

                                <?= date(
                                    'd M Y',
                                    strtotime(
                                        $dueDate
                                    )
                                ) ?>

                            </div>

                            <div class="small muted">

                                ₹<?= number_format(
                                    $amount,
                                    2
                                ) ?>

                            </div>

                        </div>

                        <div>

                            <?php if ($isPaid): ?>

                                <span class="status-badge status-paid">

                                    <i class="bi bi-check-lg"></i>

                                    Paid

                                </span>

                            <?php elseif ($isToday): ?>

                                <span class="status-badge status-today">

                                    <i class="bi bi-clock"></i>

                                    Due Today

                                </span>

                            <?php elseif ($isDue): ?>

                                <span class="status-badge status-due">

                                    <i class="bi bi-exclamation-circle"></i>

                                    Due

                                </span>

                            <?php else: ?>

                                <span class="status-badge status-upcoming">

                                    Upcoming

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">

                        <div class="small muted">

                            <?php if ($isPaid): ?>

                                Paid on

                                <?= date(
                                    'd M Y',
                                    strtotime(
                                        $paidInstallments[
                                            $i
                                        ]['payment_date']
                                    )
                                ) ?>

                            <?php elseif ($isDue): ?>

                                Payment pending

                            <?php else: ?>

                                Not due yet

                            <?php endif; ?>

                        </div>

                        <div>

                            <?php if ($isPaid): ?>

                                <span class="text-success">

                                    <i class="bi bi-check-circle-fill"></i>

                                </span>

                            <?php elseif ($isDue): ?>

                                <button
                                    type="button"
                                    class="
                                    btn
                                    btn-sm
                                    btn-outline-success
                                    add-payment
                                    add-btn
                                    "
                                    data-id="<?= $i ?>"
                                    data-amount="<?= $amount ?>"
                                >

                                    <i class="bi bi-plus-lg"></i>

                                </button>

                                <input
                                    type="checkbox"
                                    name="installment_no[]"
                                    value="<?= $i ?>"
                                    class="d-none installment-checkbox"
                                    data-id="<?= $i ?>"
                                    data-amount="<?= $amount ?>"
                                >

                            <?php else: ?>

                                <span class="text-muted">

                                    <i class="bi bi-lock"></i>

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

        <?php endfor; ?>

        </div>

    </div>

    <?php if ($paidCount > 0): ?>

    <div class="payment-card p-4 mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>

                <h5 class="fw-bold mb-1">
                    Payment History
                </h5>

                <small class="muted">
                    Complete installment payment details
                </small>

            </div>

            <span class="badge text-bg-success">
                <?= $paidCount ?> Paid
            </span>

        </div>

        <div class="table-responsive">

            <table class="table history-table align-middle">

                <thead>

                    <tr>

                        <th>
                            Installment
                        </th>

                        <th>
                            Due Date
                        </th>

                        <th>
                            Paid On
                        </th>

                        <th>
                            Amount
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach (
                    $paidInstallments
                    as $payment
                ): ?>

                    <tr>

                        <td>

                            <span class="badge text-bg-success">

                                #<?= (int)
                                $payment[
                                    'installment_no'
                                ] ?>

                            </span>

                        </td>

                        <td>

                            <?= date(
                                'd M Y',
                                strtotime(
                                    $payment[
                                        'due_date'
                                    ]
                                )
                            ) ?>

                        </td>

                        <td>

                            <?= date(
                                'd M Y',
                                strtotime(
                                    $payment[
                                        'payment_date'
                                    ]
                                )
                            ) ?>

                        </td>

                        <td class="fw-semibold text-success">

                            ₹<?= number_format(
                                (float)
                                $payment['amount'],
                                2
                            ) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <?php endif; ?>

</div>

</div>

</form>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function(){

        const buttons =
            document.querySelectorAll(
                '.add-payment'
            );

        const checkboxes =
            document.querySelectorAll(
                '.installment-checkbox'
            );

        const selectedCount =
            document.getElementById(
                'selectedCount'
            );

        const totalPayment =
            document.getElementById(
                'totalPayment'
            );

        const payButton =
            document.getElementById(
                'payButton'
            );

        const clearButton =
            document.getElementById(
                'clearSelection'
            );

        const payAllDue =
            document.getElementById(
                'payAllDue'
            );

        function updateSummary(){

            let count = 0;
            let total = 0;

            checkboxes.forEach(
                function(checkbox){

                    if(checkbox.checked){

                        count++;

                        total +=
                            parseFloat(
                                checkbox.dataset.amount
                            );
                    }
                }
            );

            selectedCount.textContent =
                count;

            totalPayment.textContent =
                '₹' +
                total.toLocaleString(
                    'en-IN',
                    {
                        minimumFractionDigits:2,
                        maximumFractionDigits:2
                    }
                );

            payButton.disabled =
                count === 0;

            if(count > 0){

                payButton.innerHTML =
                    '<i class="bi bi-check-circle-fill me-2"></i>' +
                    'Pay ₹' +
                    total.toLocaleString(
                        'en-IN',
                        {
                            minimumFractionDigits:2,
                            maximumFractionDigits:2
                        }
                    );

            }else{

                payButton.innerHTML =
                    '<i class="bi bi-credit-card me-2"></i>' +
                    'Pay Selected';
            }
        }

        buttons.forEach(
            function(button){

                button.addEventListener(
                    'click',
                    function(){

                        const id =
                            this.dataset.id;

                        const checkbox =
                            document.querySelector(
                                '.installment-checkbox[data-id="' +
                                id +
                                '"]'
                            );

                        if(!checkbox){
                            return;
                        }

                        checkbox.checked =
                            !checkbox.checked;

                        if(checkbox.checked){

                            this.classList.remove(
                                'btn-outline-success'
                            );

                            this.classList.add(
                                'btn-success',
                                'selected'
                            );

                            this.innerHTML =
                                '<i class="bi bi-check-lg"></i>';

                        }else{

                            this.classList.remove(
                                'btn-success',
                                'selected'
                            );

                            this.classList.add(
                                'btn-outline-success'
                            );

                            this.innerHTML =
                                '<i class="bi bi-plus-lg"></i>';
                        }

                        updateSummary();
                    }
                );
            }
        );

        payAllDue.addEventListener(
            'click',
            function(){

                checkboxes.forEach(
                    function(checkbox){

                        checkbox.checked =
                            true;

                    }
                );

                buttons.forEach(
                    function(button){

                        button.classList.remove(
                            'btn-outline-success'
                        );

                        button.classList.add(
                            'btn-success',
                            'selected'
                        );

                        button.innerHTML =
                            '<i class="bi bi-check-lg"></i>';
                    }
                );

                updateSummary();
            }
        );

        clearButton.addEventListener(
            'click',
            function(){

                checkboxes.forEach(
                    function(checkbox){

                        checkbox.checked =
                            false;

                    }
                );

                buttons.forEach(
                    function(button){

                        button.classList.remove(
                            'btn-success',
                            'selected'
                        );

                        button.classList.add(
                            'btn-outline-success'
                        );

                        button.innerHTML =
                            '<i class="bi bi-plus-lg"></i>';
                    }
                );

                updateSummary();
            }
        );

        document
            .getElementById('paymentForm')
            .addEventListener(
                'submit',
                function(event){

                    const selected =
                        document.querySelectorAll(
                            '.installment-checkbox:checked'
                        );

                    if(
                        selected.length === 0
                    ){

                        event.preventDefault();

                        alert(
                            'Please select at least one due installment.'
                        );

                        return;
                    }

                    let amount = 0;

                    selected.forEach(
                        function(item){

                            amount +=
                                parseFloat(
                                    item.dataset.amount
                                );
                        }
                    );

                    const confirmed =
                        confirm(
                            'Confirm payment of ₹' +
                            amount.toLocaleString(
                                'en-IN',
                                {
                                    minimumFractionDigits:2,
                                    maximumFractionDigits:2
                                }
                            ) +
                            ' for ' +
                            selected.length +
                            ' installment(s)?'
                        );

                    if(!confirmed){

                        event.preventDefault();
                    }
                }
            );

    }
);

</script>

<?php

include 'includes/footer.php';

?>