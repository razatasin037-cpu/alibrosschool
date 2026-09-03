<?php

date_default_timezone_set('Asia/Kolkata');

require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Edit Account';

$error = '';

function getInstallmentDueDate($openingDate, $plan, $installmentNo)
{
    $opening = new DateTime(
        $openingDate,
        new DateTimeZone('Asia/Kolkata')
    );

    if ($plan === 'Monthly') {

        $day = (int)$opening->format('d');

        if ($day <= 10) {
            $due = new DateTime(
                $opening->format('Y-m-10'),
                new DateTimeZone('Asia/Kolkata')
            );
        } else {
            $due = new DateTime(
                $opening->format('Y-m-01'),
                new DateTimeZone('Asia/Kolkata')
            );

            $due->modify('+1 month');

            $due->setDate(
                (int)$due->format('Y'),
                (int)$due->format('m'),
                10
            );
        }

        if ($installmentNo > 1) {
            $due->modify(
                '+' . ($installmentNo - 1) . ' months'
            );
        }

        return $due->format('Y-m-d');
    }

    $opening->modify('+7 days');

    if ($installmentNo > 1) {
        $opening->modify(
            '+' . (($installmentNo - 1) * 7) . ' days'
        );
    }

    return $opening->format('Y-m-d');
}


$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: customers.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        phone,
        aadhaar_no,
        opening_date,
        plan,
        amount,
        total_installments,
        paid_installments,
        next_due_date,
        status
    FROM customers
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    'i',
    $id
);

$stmt->execute();

$result = $stmt->get_result();

$customer = $result->fetch_assoc();

if (!$customer) {
    header('Location: customers.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $aadhaar = trim($_POST['aadhaar_no'] ?? '');
    $openingDate = trim($_POST['opening_date'] ?? '');
    $plan = trim($_POST['plan'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);

    if ($name === '') {

        $error = 'Customer name is required.';

    } elseif ($phone === '') {

        $error = 'Mobile number is required.';

    } elseif ($openingDate === '') {

        $error = 'Opening date is required.';

    } elseif (
        $plan !== 'Monthly' &&
        $plan !== 'Weekly'
    ) {

        $error = 'Please select a valid payment plan.';

    } elseif ($amount <= 0) {

        $error = 'Amount must be greater than 0.';

    } else {

        $openingObj = DateTime::createFromFormat(
            '!Y-m-d',
            $openingDate,
            new DateTimeZone('Asia/Kolkata')
        );

        $dateErrors =
            DateTime::getLastErrors();

        if (
            $openingObj === false ||
            (
                is_array($dateErrors) &&
                (
                    $dateErrors['warning_count'] > 0 ||
                    $dateErrors['error_count'] > 0
                )
            )
        ) {

            $error = 'Invalid opening date.';

        } else {

            $openingDate =
                $openingObj->format('Y-m-d');

            $today = new DateTime(
                'today',
                new DateTimeZone('Asia/Kolkata')
            );

            if ($openingObj > $today) {

                $error =
                    'Opening date cannot be in the future.';

            } else {

                if ($plan === 'Weekly') {

                    $totalInstallments = 52;

                } else {

                    $totalInstallments = 12;
                }

                $paymentStmt = $conn->prepare("
                    SELECT
                        installment_no
                    FROM installment_payments
                    WHERE customer_id = ?
                    ORDER BY installment_no ASC
                ");

                $paymentStmt->bind_param(
                    'i',
                    $id
                );

                $paymentStmt->execute();

                $paymentResult =
                    $paymentStmt->get_result();

                $paidNumbers = [];

                while (
                    $paymentRow =
                    $paymentResult->fetch_assoc()
                ) {

                    $number =
                        (int)(
                            $paymentRow[
                                'installment_no'
                            ]
                        );

                    if ($number > 0) {

                        $paidNumbers[] =
                            $number;
                    }
                }

                $paidNumbers =
                    array_values(
                        array_unique(
                            $paidNumbers
                        )
                    );

                sort($paidNumbers);

                $paidCount =
                    count($paidNumbers);

                $paidCount =
                    min(
                        $paidCount,
                        $totalInstallments
                    );

                $remaining =
                    max(
                        0,
                        $totalInstallments -
                        $paidCount
                    );

                $nextDueDate = null;

                if ($remaining > 0) {

                    $paidLookup = [];

                    foreach ($paidNumbers as $paidNumber) {
                        $paidLookup[$paidNumber] = true;
                    }

                    for (
                        $nextInstallment = 1;
                        $nextInstallment <= $totalInstallments;
                        $nextInstallment++
                    ) {

                        if (
                            isset(
                                $paidLookup[
                                    $nextInstallment
                                ]
                            )
                        ) {
                            continue;
                        }

                        $nextDueDate =
                            getInstallmentDueDate(
                                $openingDate,
                                $plan,
                                $nextInstallment
                            );

                        break;
                    }

                    $status = 'Active';

                } else {

                    $status = 'Completed';
                }

                $update = $conn->prepare("
                    UPDATE customers
                    SET
                        name = ?,
                        phone = ?,
                        aadhaar_no = ?,
                        opening_date = ?,
                        plan = ?,
                        amount = ?,
                        total_installments = ?,
                        paid_installments = ?,
                        next_due_date = ?,
                        status = ?
                    WHERE id = ?
                ");

                $update->bind_param(
                    'sssssdiiisi',
                    $name,
                    $phone,
                    $aadhaar,
                    $openingDate,
                    $plan,
                    $amount,
                    $totalInstallments,
                    $paidCount,
                    $nextDueDate,
                    $status,
                    $id
                );

                if ($update->execute()) {

                    header(
                        'Location: customers.php?updated=1'
                    );

                    exit;

                } else {

                    $error =
                        'Account update nahi ho saka: ' .
                        $update->error;
                }
            }
        }
    }

    $customer['name'] =
        $name;

    $customer['phone'] =
        $phone;

    $customer['aadhaar_no'] =
        $aadhaar;

    $customer['opening_date'] =
        $openingDate;

    $customer['plan'] =
        $plan;

    $customer['amount'] =
        $amount;
}

$totalInstallments =
    (int)(
        $customer['total_installments']
        ??
        (
            $customer['plan'] === 'Weekly'
            ? 52
            : 12
        )
    );

$paidInstallments =
    (int)(
        $customer['paid_installments']
        ?? 0
    );

$remaining =
    max(
        0,
        $totalInstallments -
        $paidInstallments
    );

$progress =
    $totalInstallments > 0
    ? (
        $paidInstallments /
        $totalInstallments
    ) * 100
    : 0;

$progress =
    min(
        100,
        max(
            0,
            $progress
        )
    );

$isCompleted =
    $paidInstallments >=
    $totalInstallments;

include 'includes/header.php';

?>

<style>

.edit-page{
    width:100%;
    max-width:1150px;
    margin:0 auto;
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:24px;
}

.kicker{
    color:#16a34a;
    font-size:10px;
    font-weight:900;
    letter-spacing:1.4px;
    text-transform:uppercase;
}

.page-title{
    font-size:29px;
    font-weight:900;
    margin:4px 0 0;
}

.page-subtitle{
    color:#7b867f;
    font-size:11px;
    margin-top:4px;
}

.back-btn{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:10px 15px;
    border-radius:11px;
    border:1px solid #e1e8e3;
    background:#fff;
    color:#34423a;
    text-decoration:none;
    font-size:10px;
    font-weight:800;
}

.back-btn:hover{
    color:#15803d;
    border-color:#86efac;
}

.alert-box{
    display:flex;
    align-items:center;
    gap:11px;
    padding:14px 16px;
    border-radius:14px;
    margin-bottom:18px;
    font-size:11px;
}

.alert-error{
    background:#fff1f2;
    border:1px solid #fecdd3;
    color:#991b1b;
}

.account-hero{
    background:
        linear-gradient(
            135deg,
            #050806,
            #102318 60%,
            #166534
        );
    color:#fff;
    border-radius:23px;
    padding:24px;
    position:relative;
    overflow:hidden;
    margin-bottom:20px;
    box-shadow:
        0 15px 40px
        rgba(5,15,8,.16);
}

.account-hero:after{
    content:"";
    position:absolute;
    width:240px;
    height:240px;
    border-radius:50%;
    right:-100px;
    top:-130px;
    background:rgba(34,197,94,.11);
}

.hero-content{
    position:relative;
    z-index:2;
}

.customer-profile{
    display:flex;
    align-items:center;
    gap:13px;
}

.avatar{
    width:58px;
    height:58px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#22c55e;
    color:#052e16;
    font-size:21px;
    font-weight:900;
}

.customer-name{
    font-size:20px;
    font-weight:900;
}

.customer-info{
    color:#a7bcae;
    font-size:10px;
    margin-top:3px;
}

.hero-status{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:7px 11px;
    border-radius:20px;
    font-size:9px;
    font-weight:850;
    margin-top:10px;
}

.status-active{
    background:#fef3c7;
    color:#92400e;
}

.status-completed{
    background:#dbeafe;
    color:#1d4ed8;
}

.form-card{
    background:#fff;
    border:1px solid #e4ebe6;
    border-radius:22px;
    padding:23px;
    box-shadow:
        0 10px 30px
        rgba(9,18,12,.045);
    margin-bottom:20px;
}

.card-title{
    font-size:16px;
    font-weight:900;
}

.card-subtitle{
    color:#7d8881;
    font-size:10px;
    margin-top:3px;
}

.form-grid{
    display:grid;
    grid-template-columns:
        repeat(2,1fr);
    gap:16px;
    margin-top:21px;
}

.field label{
    display:block;
    font-size:10px;
    font-weight:850;
    margin-bottom:6px;
}

.input{
    width:100%;
    height:46px;
    border:1px solid #dfe7e2;
    border-radius:11px;
    padding:0 12px;
    outline:none;
    font-size:11px;
    background:#fff;
}

.input:focus{
    border-color:#4ade80;
    box-shadow:
        0 0 0 4px
        rgba(34,197,94,.08);
}

.plan-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:9px;
}

.plan-label{
    cursor:pointer;
}

.plan-radio{
    display:none;
}

.plan-card{
    border:1px solid #dfe7e2;
    border-radius:12px;
    padding:12px;
}

.plan-radio:checked + .plan-card{
    border-color:#16a34a;
    background:#f0fdf4;
    box-shadow:
        0 0 0 3px
        rgba(34,197,94,.07);
}

.plan-name{
    font-size:11px;
    font-weight:850;
}

.plan-description{
    color:#849089;
    font-size:8px;
    margin-top:3px;
}

.stats-grid{
    display:grid;
    grid-template-columns:
        repeat(4,1fr);
    gap:10px;
    margin-top:20px;
}

.stat-box{
    background:#f8faf9;
    border:1px solid #edf1ee;
    border-radius:13px;
    padding:13px;
}

.stat-label{
    color:#89938d;
    font-size:8px;
    font-weight:850;
    text-transform:uppercase;
}

.stat-value{
    font-size:17px;
    font-weight:900;
    margin-top:4px;
}

.green{
    color:#15803d;
}

.red{
    color:#dc2626;
}

.progress-area{
    margin-top:20px;
}

.progress-head{
    display:flex;
    justify-content:space-between;
    margin-bottom:7px;
}

.progress-label{
    color:#7d8781;
    font-size:9px;
    font-weight:750;
}

.progress-percent{
    color:#15803d;
    font-size:10px;
    font-weight:900;
}

.progress{
    height:8px;
    background:#e8efea;
    border-radius:20px;
    overflow:hidden;
}

.progress-bar-custom{
    height:100%;
    border-radius:20px;
    background:
        linear-gradient(
            90deg,
            #15803d,
            #22c55e
        );
}

.completed-progress{
    background:
        linear-gradient(
            90deg,
            #2563eb,
            #60a5fa
        );
}

.button-row{
    display:flex;
    justify-content:flex-end;
    gap:9px;
    margin-top:22px;
    padding-top:17px;
    border-top:1px solid #edf1ee;
}

.cancel-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:10px 18px;
    border-radius:10px;
    border:1px solid #dfe7e2;
    background:#fff;
    color:#46534b;
    text-decoration:none;
    font-size:10px;
    font-weight:800;
}

.save-btn{
    border:0;
    padding:10px 20px;
    border-radius:10px;
    color:#fff;
    background:
        linear-gradient(
            135deg,
            #15803d,
            #22c55e
        );
    font-size:10px;
    font-weight:850;
}

@media(max-width:700px){

    .page-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .stats-grid{
        grid-template-columns:
            repeat(2,1fr);
    }

    .account-hero{
        padding:18px;
    }

}

</style>

<div class="edit-page">

    <div class="page-header">

        <div>

            <div class="kicker">
                <i class="bi bi-pencil-square"></i>
                ACCOUNT MANAGEMENT
            </div>

            <div class="page-title">
                Edit Account
            </div>

            <div class="page-subtitle">
                Customer aur account information update karo
            </div>

        </div>

        <a
            href="customers.php"
            class="back-btn"
        >

            <i class="bi bi-arrow-left"></i>

            Back to Customers

        </a>

    </div>

    <?php if ($error !== ''): ?>

        <div class="alert-box alert-error">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>
                <?= htmlspecialchars($error) ?>
            </div>

        </div>

    <?php endif; ?>

    <div class="account-hero">

        <div class="hero-content">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div class="customer-profile">

                    <div class="avatar">

                        <?= strtoupper(
                            substr(
                                $customer['name'],
                                0,
                                1
                            )
                        ) ?>

                    </div>

                    <div>

                        <div class="customer-name">

                            <?= htmlspecialchars(
                                $customer['name']
                            ) ?>

                        </div>

                        <div class="customer-info">

                            <i class="bi bi-telephone-fill me-1"></i>

                            <?= htmlspecialchars(
                                $customer['phone']
                            ) ?>

                            <span class="mx-2">•</span>

                            Account #<?= $id ?>

                        </div>

                        <?php if ($isCompleted): ?>

                            <div class="hero-status status-completed">

                                <i class="bi bi-check-circle-fill"></i>

                                Completed

                            </div>

                        <?php else: ?>

                            <div class="hero-status status-active">

                                <i class="bi bi-clock-history"></i>

                                Upcoming Payment

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

                <div>

                    <div
                        style="
                        color:#8fa699;
                        font-size:9px;
                        text-align:right;
                        "
                    >
                        CURRENT PLAN
                    </div>

                    <div
                        style="
                        font-size:17px;
                        font-weight:900;
                        margin-top:3px;
                        "
                    >

                        <?= htmlspecialchars(
                            $customer['plan']
                        ) ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="form-card">

        <div class="card-title">

            <i
                class="bi bi-person-vcard-fill me-1"
                style="color:#16a34a"
            ></i>

            Customer Information

        </div>

        <div class="card-subtitle">

            Customer ki basic details

        </div>

        <form
            method="POST"
            autocomplete="off"
        >

            <input
                type="hidden"
                name="id"
                value="<?= $id ?>"
            >

            <div class="form-grid">

                <div class="field">

                    <label>
                        Customer Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="input"
                        value="<?= htmlspecialchars(
                            $customer['name']
                        ) ?>"
                        required
                    >

                </div>

                <div class="field">

                    <label>
                        Mobile Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        class="input"
                        value="<?= htmlspecialchars(
                            $customer['phone']
                        ) ?>"
                        required
                    >

                </div>

                <div class="field">

                    <label>
                        Aadhaar Number
                    </label>

                    <input
                        type="text"
                        name="aadhaar_no"
                        class="input"
                        value="<?= htmlspecialchars(
                            $customer['aadhaar_no'] ?? ''
                        ) ?>"
                    >

                </div>

                <div class="field">

                    <label>
                        Opening Date
                    </label>

                    <input
                        type="date"
                        name="opening_date"
                        class="input"
                        value="<?= htmlspecialchars(
                            $customer['opening_date']
                        ) ?>"
                        max="<?= date('Y-m-d') ?>"
                        required
                    >

                </div>

                <div class="field">

                    <label>
                        Payment Plan
                    </label>

                    <div class="plan-grid">

                        <label class="plan-label">

                            <input
                                type="radio"
                                name="plan"
                                value="Monthly"
                                class="plan-radio"
                                <?= $customer['plan'] === 'Monthly'
                                    ? 'checked'
                                    : '' ?>
                            >

                            <div class="plan-card">

                                <div class="plan-name">

                                    <i
                                        class="bi bi-calendar-month-fill text-success me-1"
                                    ></i>

                                    Monthly

                                </div>

                                <div class="plan-description">

                                    12 installments

                                </div>

                            </div>

                        </label>

                        <label class="plan-label">

                            <input
                                type="radio"
                                name="plan"
                                value="Weekly"
                                class="plan-radio"
                                <?= $customer['plan'] === 'Weekly'
                                    ? 'checked'
                                    : '' ?>
                            >

                            <div class="plan-card">

                                <div class="plan-name">

                                    <i
                                        class="bi bi-calendar-week-fill text-success me-1"
                                    ></i>

                                    Weekly

                                </div>

                                <div class="plan-description">

                                    52 installments

                                </div>

                            </div>

                        </label>

                    </div>

                </div>

                <div class="field">

                    <label>
                        Installment Amount
                    </label>

                    <input
                        type="number"
                        name="amount"
                        class="input"
                        min="1"
                        step="0.01"
                        value="<?= htmlspecialchars(
                            $customer['amount']
                        ) ?>"
                        required
                    >

                </div>

            </div>

            <div class="button-row">

                <a
                    href="customers.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="save-btn"
                >

                    <i class="bi bi-check-circle-fill me-1"></i>

                    Save Changes

                </button>

            </div>

        </form>

    </div>

    <div class="form-card">

        <div class="card-title">

            <i
                class="bi bi-bar-chart-fill me-1"
                style="color:#16a34a"
            ></i>

            Account Details

        </div>

        <div class="card-subtitle">

            Current collection progress

        </div>

        <div class="stats-grid">

            <div class="stat-box">

                <div class="stat-label">
                    Plan
                </div>

                <div class="stat-value">
                    <?= htmlspecialchars(
                        $customer['plan']
                    ) ?>
                </div>

            </div>

            <div class="stat-box">

                <div class="stat-label">
                    Paid
                </div>

                <div class="stat-value green">
                    <?= $paidInstallments ?>
                </div>

            </div>

            <div class="stat-box">

                <div class="stat-label">
                    Remaining
                </div>

                <div class="stat-value red">
                    <?= $remaining ?>
                </div>

            </div>

            <div class="stat-box">

                <div class="stat-label">
                    Total
                </div>

                <div class="stat-value">
                    <?= $totalInstallments ?>
                </div>

            </div>

        </div>

        <div class="progress-area">

            <div class="progress-head">

                <div class="progress-label">
                    Collection Progress
                </div>

                <div class="progress-percent">
                    <?= number_format(
                        $progress,
                        0
                    ) ?>%
                </div>

            </div>

            <div class="progress">

                <div
                    class="
                    progress-bar-custom
                    <?= $isCompleted
                        ? 'completed-progress'
                        : '' ?>
                    "
                    style="width:<?= $progress ?>%"
                ></div>

            </div>

        </div>

    </div>

</div>

<?php

include 'includes/footer.php';

?>