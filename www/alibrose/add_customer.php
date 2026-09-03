<?php

date_default_timezone_set('Asia/Kolkata');

require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Add Account';

$error = '';

$customers = [];

$customerResult = $conn->query("
    SELECT
        id,
        name,
        phone,
        aadhaar_no
    FROM customers
    ORDER BY name ASC, id DESC
");

if ($customerResult) {

    while ($customer = $customerResult->fetch_assoc()) {
        $customers[] = $customer;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer_mode = $_POST['customer_mode'] ?? 'new';

    $customer_id = (int)($_POST['customer_id'] ?? 0);

    $name = trim($_POST['name'] ?? '');

    $phone = trim($_POST['phone'] ?? '');

    $aadhaar = trim($_POST['aadhaar_no'] ?? '');

    $opening_date = trim($_POST['opening_date'] ?? '');

    $plan = trim($_POST['plan'] ?? '');

    $amount = (float)($_POST['amount'] ?? 0);

    if ($customer_mode === 'existing') {

        if ($customer_id <= 0) {

            $error = 'Please select an existing customer.';

        } else {

            $customerStmt = $conn->prepare("
                SELECT
                    name,
                    phone,
                    aadhaar_no
                FROM customers
                WHERE id = ?
                LIMIT 1
            ");

            $customerStmt->bind_param(
                'i',
                $customer_id
            );

            $customerStmt->execute();

            $customerResult =
                $customerStmt->get_result();

            $existingCustomer =
                $customerResult->fetch_assoc();

            if (!$existingCustomer) {

                $error = 'Selected customer not found.';

            } else {

                $name =
                    $existingCustomer['name'];

                $phone =
                    $existingCustomer['phone'];

                if ($aadhaar === '') {

                    $aadhaar =
                        $existingCustomer['aadhaar_no'];
                }
            }
        }
    }

    if ($error === '') {

        if ($name === '') {

            $error =
                'Customer name is required.';

        } elseif ($phone === '') {

            $error =
                'Mobile number is required.';

        } elseif ($opening_date === '') {

            $error =
                'Opening date is required.';

        } elseif (
            $plan !== 'Monthly' &&
            $plan !== 'Weekly'
        ) {

            $error =
                'Please select a valid payment plan.';

        } elseif ($amount <= 0) {

            $error =
                'Amount must be greater than 0.';

        } else {

            $openingObj =
                DateTime::createFromFormat(
                    '!Y-m-d',
                    $opening_date,
                    new DateTimeZone('Asia/Kolkata')
                );

            if (!$openingObj) {

                $error =
                    'Invalid opening date.';

            } else {

                $today =
                    new DateTime(
                        'today',
                        new DateTimeZone('Asia/Kolkata')
                    );

                if ($openingObj > $today) {

                    $error =
                        'Opening date cannot be in the future.';

                } else {

                    if ($plan === 'Monthly') {

                        $totalInstallments = 12;

                        $day =
                            (int)$openingObj->format('d');

                        if ($day <= 10) {

                            $nextDue =
                                new DateTime(
                                    $openingObj->format('Y-m-10'),
                                    new DateTimeZone(
                                        'Asia/Kolkata'
                                    )
                                );

                        } else {

                            $nextDue =
                                new DateTime(
                                    $openingObj->format('Y-m-01'),
                                    new DateTimeZone(
                                        'Asia/Kolkata'
                                    )
                                );

                            $nextDue->modify('+1 month');

                            $nextDue->setDate(
                                (int)$nextDue->format('Y'),
                                (int)$nextDue->format('m'),
                                10
                            );
                        }

                    } else {

                        $totalInstallments = 52;

                        $nextDue =
                            clone $openingObj;

                        $nextDue->modify('+7 days');
                    }

                    $nextDueDate =
                        $nextDue->format('Y-m-d');

                    $stmt = $conn->prepare("
                        INSERT INTO customers
                        (
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
                        )
                        VALUES
                        (
                            ?, ?, ?, ?, ?, ?,
                            ?, 0, ?, 'Active'
                        )
                    ");

                    $stmt->bind_param(
                        "sssssdis",
                        $name,
                        $phone,
                        $aadhaar,
                        $opening_date,
                        $plan,
                        $amount,
                        $totalInstallments,
                        $nextDueDate
                    );

                    if ($stmt->execute()) {

                        $newAccountId =
                            $conn->insert_id;

                        header(
                            'Location: payment.php?id=' .
                            $newAccountId .
                            '&new=1'
                        );

                        exit;

                    } else {

                        $error =
                            'Account could not be created.';
                    }
                }
            }
        }
    }
}

include 'includes/header.php';

?>

<style>

.add-page{
    width:100%;
    max-width:1250px;
    margin:0 auto;
}

.page-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:25px;
}

.kicker{
    color:#16a34a;
    font-size:10px;
    font-weight:900;
    letter-spacing:1.5px;
    text-transform:uppercase;
}

.page-title{
    font-size:29px;
    font-weight:900;
    margin:4px 0 0;
}

.page-subtitle{
    color:#748078;
    font-size:12px;
    margin-top:5px;
}

.back-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:11px 15px;
    border-radius:12px;
    background:#fff;
    border:1px solid #e2e9e4;
    color:#34423a;
    text-decoration:none;
    font-size:12px;
    font-weight:750;
}

.back-btn:hover{
    color:#15803d;
    border-color:#86efac;
    background:#f0fdf4;
}

.alert-box{
    display:flex;
    align-items:center;
    gap:12px;
    background:#fff1f2;
    border:1px solid #fecdd3;
    color:#991b1b;
    padding:13px 15px;
    border-radius:14px;
    margin-bottom:20px;
}

.main-grid{
    display:grid;
    grid-template-columns:minmax(0,1fr) 350px;
    gap:22px;
}

.form-card{
    background:#fff;
    border:1px solid #e4ebe6;
    border-radius:23px;
    padding:27px;
    box-shadow:0 12px 35px rgba(9,18,12,.055);
}

.card-head{
    display:flex;
    align-items:center;
    gap:13px;
    padding-bottom:20px;
    margin-bottom:23px;
    border-bottom:1px solid #edf1ee;
}

.card-icon{
    width:47px;
    height:47px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#dcfce7;
    color:#15803d;
    font-size:20px;
}

.card-head h5{
    margin:0;
    font-size:17px;
    font-weight:850;
}

.card-head p{
    margin:4px 0 0;
    color:#7b867f;
    font-size:11px;
}

.mode-box{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    margin-bottom:25px;
}

.mode-label{
    cursor:pointer;
}

.mode-radio{
    display:none;
}

.mode-card{
    border:1px solid #e0e7e2;
    border-radius:15px;
    padding:15px;
    background:#fff;
    transition:.2s;
}

.mode-card:hover{
    border-color:#86efac;
}

.mode-radio:checked + .mode-card{
    border-color:#16a34a;
    background:#f0fdf4;
    box-shadow:0 0 0 3px rgba(34,197,94,.08);
}

.mode-icon{
    width:39px;
    height:39px;
    border-radius:11px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#ecfdf3;
    color:#15803d;
    margin-bottom:9px;
}

.mode-title{
    font-size:13px;
    font-weight:850;
}

.mode-text{
    color:#7c8780;
    font-size:10px;
    margin-top:3px;
}

.field{
    margin-bottom:17px;
}

.field label{
    display:block;
    color:#26332b;
    font-size:11px;
    font-weight:800;
    margin-bottom:7px;
}

.required{
    color:#dc2626;
}

.input{
    width:100%;
    height:47px;
    border:1px solid #dfe6e1;
    border-radius:11px;
    padding:0 13px;
    outline:none;
    font-size:12px;
    background:#fff;
}

.input:focus{
    border-color:#4ade80;
    box-shadow:0 0 0 4px rgba(34,197,94,.08);
}

.input:disabled{
    background:#f6f8f7;
    color:#7b867f;
}

.two-col{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.section-title{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:12px;
    font-weight:850;
    margin:25px 0 15px;
}

.section-number{
    width:26px;
    height:26px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#07100a;
    color:#fff;
    border-radius:8px;
    font-size:9px;
}

.plan-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:13px;
}

.plan-label{
    cursor:pointer;
}

.plan-radio{
    display:none;
}

.plan-card{
    border:1px solid #dfe6e1;
    border-radius:16px;
    padding:17px;
    background:#fff;
    transition:.2s;
}

.plan-card:hover{
    border-color:#86efac;
    transform:translateY(-2px);
}

.plan-radio:checked + .plan-card{
    border-color:#16a34a;
    background:#f0fdf4;
    box-shadow:0 0 0 3px rgba(34,197,94,.08);
}

.plan-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.plan-icon{
    width:41px;
    height:41px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
}

.monthly-icon{
    background:#dcfce7;
    color:#15803d;
}

.weekly-icon{
    background:#d1fae5;
    color:#047857;
}

.check{
    width:21px;
    height:21px;
    border-radius:50%;
    border:1px solid #ccd5cf;
    display:flex;
    align-items:center;
    justify-content:center;
    color:transparent;
}

.plan-radio:checked + .plan-card .check{
    background:#16a34a;
    border-color:#16a34a;
    color:#fff;
}

.plan-card h6{
    margin:13px 0 4px;
    font-size:14px;
    font-weight:850;
}

.plan-card p{
    margin:0;
    color:#7b867f;
    font-size:10px;
}

.plan-duration{
    display:flex;
    justify-content:space-between;
    border-top:1px solid #edf1ee;
    margin-top:13px;
    padding-top:11px;
    font-size:10px;
}

.plan-duration span{
    color:#7b867f;
}

.plan-duration strong{
    color:#15803d;
}

.amount-box{
    padding:17px;
    border-radius:16px;
    background:#f7faf8;
    border:1px solid #e7ede9;
}

.amount-input{
    height:55px;
    display:flex;
    align-items:center;
    background:#fff;
    border:1px solid #dfe6e1;
    border-radius:12px;
    overflow:hidden;
}

.amount-symbol{
    width:53px;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    border-right:1px solid #edf1ee;
    color:#15803d;
    font-size:20px;
    font-weight:900;
}

.amount-input input{
    width:100%;
    height:100%;
    border:0;
    outline:0;
    padding:0 14px;
    font-size:19px;
    font-weight:750;
}

.form-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    padding-top:21px;
    margin-top:24px;
    border-top:1px solid #edf1ee;
}

.cancel{
    padding:11px 20px;
    border-radius:11px;
    border:1px solid #dfe6e1;
    background:#fff;
    color:#46534b;
    text-decoration:none;
    font-size:12px;
    font-weight:750;
}

.save{
    padding:11px 21px;
    border:0;
    border-radius:11px;
    background:linear-gradient(135deg,#15803d,#22c55e);
    color:#fff;
    font-size:12px;
    font-weight:800;
    box-shadow:0 8px 18px rgba(22,163,74,.18);
}

.side{
    display:flex;
    flex-direction:column;
    gap:15px;
}

.preview{
    background:linear-gradient(145deg,#050806,#102318);
    color:#fff;
    border-radius:22px;
    padding:22px;
    overflow:hidden;
    position:relative;
    box-shadow:0 15px 35px rgba(5,8,6,.17);
}

.preview:after{
    content:"";
    position:absolute;
    width:200px;
    height:200px;
    border-radius:50%;
    right:-100px;
    top:-100px;
    background:rgba(34,197,94,.11);
}

.preview-label{
    color:#86efac;
    font-size:9px;
    font-weight:850;
    letter-spacing:1.3px;
}

.preview h5{
    margin:4px 0 0;
    font-size:17px;
    font-weight:850;
}

.preview-user{
    display:flex;
    align-items:center;
    gap:11px;
    padding:14px;
    margin-top:19px;
    background:rgba(255,255,255,.055);
    border:1px solid rgba(255,255,255,.07);
    border-radius:15px;
    position:relative;
    z-index:1;
}

.preview-avatar{
    width:42px;
    height:42px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#16a34a;
    font-weight:900;
}

.preview-user small{
    color:#94a3b8;
    font-size:9px;
}

.preview-user strong{
    display:block;
    margin-top:2px;
    font-size:13px;
}

.preview-row{
    display:flex;
    justify-content:space-between;
    padding:14px 0;
    border-bottom:1px solid rgba(255,255,255,.07);
    position:relative;
    z-index:1;
}

.preview-row span{
    color:#94a3b8;
    font-size:11px;
}

.preview-row strong{
    font-size:12px;
}

.preview-amount{
    color:#4ade80!important;
    font-size:18px!important;
}

.info-card{
    background:#fff;
    border:1px solid #e5ebe7;
    border-radius:18px;
    padding:18px;
}

.info-title{
    font-size:13px;
    font-weight:850;
    margin-bottom:12px;
}

.info-item{
    display:flex;
    justify-content:space-between;
    padding:9px 0;
    border-bottom:1px solid #edf1ee;
}

.info-item:last-child{
    border-bottom:0;
}

.info-item span{
    color:#7b867f;
    font-size:10px;
}

.info-item strong{
    font-size:10px;
    color:#25312a;
}

@media(max-width:1000px){

    .main-grid{
        grid-template-columns:1fr;
    }

}

@media(max-width:700px){

    .page-head{
        align-items:flex-start;
        flex-direction:column;
    }

    .two-col,
    .plan-grid,
    .mode-box{
        grid-template-columns:1fr;
    }

    .form-card{
        padding:20px;
    }

    .form-actions{
        flex-direction:column-reverse;
    }

    .save,
    .cancel{
        width:100%;
        text-align:center;
    }

}

</style>

<div class="add-page">

    <div class="page-head">

        <div>

            <div class="kicker">
                <i class="bi bi-bank2"></i>
                ALIBROSE BANK
            </div>

            <h1 class="page-title">
                Add Account
            </h1>

            <div class="page-subtitle">
                Create a new customer or add another account to an existing customer.
            </div>

        </div>

        <a
            href="customers.php"
            class="back-btn"
        >
            <i class="bi bi-arrow-left"></i>
            Customers
        </a>

    </div>

    <?php if ($error !== ''): ?>

        <div class="alert-box">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>

                <strong>
                    Unable to create account
                </strong>

                <div style="font-size:11px;margin-top:2px">
                    <?= htmlspecialchars($error) ?>
                </div>

            </div>

        </div>

    <?php endif; ?>

    <div class="main-grid">

        <div class="form-card">

            <div class="card-head">

                <div class="card-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>

                    <h5>
                        Account Information
                    </h5>

                    <p>
                        Select customer type and create an account.
                    </p>

                </div>

            </div>

            <form
                method="POST"
                id="accountForm"
            >

                <div class="section-title">

                    <span class="section-number">
                        01
                    </span>

                    Customer Type

                </div>

                <div class="mode-box">

                    <label class="mode-label">

                        <input
                            type="radio"
                            name="customer_mode"
                            value="new"
                            class="mode-radio"
                            id="newCustomer"
                            <?= (
                                ($_POST['customer_mode'] ?? 'new')
                                === 'new'
                            ) ? 'checked' : '' ?>
                        >

                        <div class="mode-card">

                            <div class="mode-icon">

                                <i class="bi bi-person-plus-fill"></i>

                            </div>

                            <div class="mode-title">
                                New Customer
                            </div>

                            <div class="mode-text">
                                Create a new customer profile.
                            </div>

                        </div>

                    </label>

                    <label class="mode-label">

                        <input
                            type="radio"
                            name="customer_mode"
                            value="existing"
                            class="mode-radio"
                            id="existingCustomer"
                            <?= (
                                ($_POST['customer_mode'] ?? '')
                                === 'existing'
                            ) ? 'checked' : '' ?>
                        >

                        <div class="mode-card">

                            <div class="mode-icon">

                                <i class="bi bi-person-check-fill"></i>

                            </div>

                            <div class="mode-title">
                                Existing Customer
                            </div>

                            <div class="mode-text">
                                Add another account to a customer.
                            </div>

                        </div>

                    </label>

                </div>

                <div
                    id="existingCustomerBox"
                    style="display:none"
                >

                    <div class="field">

                        <label>
                            Select Existing Customer
                            <span class="required">*</span>
                        </label>

                        <select
                            name="customer_id"
                            id="customer_id"
                            class="input"
                        >

                            <option value="">
                                Select customer
                            </option>

                            <?php foreach (
                                $customers
                                as $customer
                            ): ?>

                                <option
                                    value="<?= (int)$customer['id'] ?>"
                                    data-name="<?= htmlspecialchars(
                                        $customer['name']
                                    ) ?>"
                                    data-phone="<?= htmlspecialchars(
                                        $customer['phone']
                                    ) ?>"
                                    data-aadhaar="<?= htmlspecialchars(
                                        $customer['aadhaar_no'] ?? ''
                                    ) ?>"
                                    <?= (
                                        (int)(
                                            $_POST['customer_id']
                                            ?? 0
                                        )
                                        ===
                                        (int)$customer['id']
                                    )
                                    ? 'selected'
                                    : '' ?>
                                >

                                    <?= htmlspecialchars(
                                        $customer['name']
                                    ) ?>

                                    —
                                    <?= htmlspecialchars(
                                        $customer['phone']
                                    ) ?>

                                    —
                                    Account #<?= (int)$customer['id'] ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <div class="section-title">

                    <span class="section-number">
                        02
                    </span>

                    Customer Details

                </div>

                <div class="field">

                    <label>
                        Customer Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="input"
                        placeholder="Enter full name"
                        value="<?= htmlspecialchars(
                            $_POST['name'] ?? ''
                        ) ?>"
                        required
                    >

                </div>

                <div class="two-col">

                    <div class="field">

                        <label>
                            Mobile Number
                            <span class="required">*</span>
                        </label>

                        <input
                            type="tel"
                            name="phone"
                            id="phone"
                            class="input"
                            placeholder="10 digit mobile"
                            maxlength="20"
                            value="<?= htmlspecialchars(
                                $_POST['phone'] ?? ''
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
                            id="aadhaar"
                            class="input"
                            placeholder="Aadhaar number"
                            maxlength="20"
                            value="<?= htmlspecialchars(
                                $_POST['aadhaar_no'] ?? ''
                            ) ?>"
                        >

                    </div>

                </div>

                <div class="section-title">

                    <span class="section-number">
                        03
                    </span>

                    Account Details

                </div>

                <div class="field">

                    <label>
                        Opening Date
                        <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="opening_date"
                        class="input"
                        value="<?= htmlspecialchars(
                            $_POST['opening_date']
                            ?? date('Y-m-d')
                        ) ?>"
                        max="<?= date('Y-m-d') ?>"
                        required
                    >

                </div>

                <div class="section-title">

                    <span class="section-number">
                        04
                    </span>

                    Payment Plan

                </div>

                <div class="plan-grid">

                    <label class="plan-label">

                        <input
                            type="radio"
                            name="plan"
                            value="Monthly"
                            class="plan-radio"
                            <?= (
                                ($_POST['plan']
                                ?? 'Monthly')
                                === 'Monthly'
                            )
                            ? 'checked'
                            : '' ?>
                        >

                        <div class="plan-card">

                            <div class="plan-top">

                                <div class="plan-icon monthly-icon">

                                    <i class="bi bi-calendar-month-fill"></i>

                                </div>

                                <div class="check">

                                    <i class="bi bi-check"></i>

                                </div>

                            </div>

                            <h6>
                                Monthly
                            </h6>

                            <p>
                                Customer pays every month.
                            </p>

                            <div class="plan-duration">

                                <span>
                                    Duration
                                </span>

                                <strong>
                                    12 Months
                                </strong>

                            </div>

                        </div>

                    </label>

                    <label class="plan-label">

                        <input
                            type="radio"
                            name="plan"
                            value="Weekly"
                            class="plan-radio"
                            <?= (
                                ($_POST['plan']
                                ?? '')
                                === 'Weekly'
                            )
                            ? 'checked'
                            : '' ?>
                        >

                        <div class="plan-card">

                            <div class="plan-top">

                                <div class="plan-icon weekly-icon">

                                    <i class="bi bi-calendar-week-fill"></i>

                                </div>

                                <div class="check">

                                    <i class="bi bi-check"></i>

                                </div>

                            </div>

                            <h6>
                                Weekly
                            </h6>

                            <p>
                                Customer pays every week.
                            </p>

                            <div class="plan-duration">

                                <span>
                                    Duration
                                </span>

                                <strong>
                                    52 Weeks
                                </strong>

                            </div>

                        </div>

                    </label>

                </div>

                <div class="section-title">

                    <span class="section-number">
                        05
                    </span>

                    Installment Amount

                </div>

                <div class="amount-box">

                    <div class="field" style="margin:0">

                        <label id="amountLabel">
                            Monthly Amount
                            <span class="required">*</span>
                        </label>

                        <div class="amount-input">

                            <div class="amount-symbol">
                                ₹
                            </div>

                            <input
                                type="number"
                                name="amount"
                                id="amount"
                                min="1"
                                step="0.01"
                                placeholder="0.00"
                                value="<?= htmlspecialchars(
                                    $_POST['amount'] ?? ''
                                ) ?>"
                                required
                            >

                        </div>

                    </div>

                </div>

                <div class="form-actions">

                    <a
                        href="customers.php"
                        class="cancel"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="save"
                    >

                        <i class="bi bi-plus-circle-fill me-1"></i>

                        Create Account

                    </button>

                </div>

            </form>

        </div>

        <div class="side">

            <div class="preview">

                <div class="preview-label">
                    ACCOUNT PREVIEW
                </div>

                <h5>
                    New Account
                </h5>

                <div class="preview-user">

                    <div
                        class="preview-avatar"
                        id="previewAvatar"
                    >
                        ?
                    </div>

                    <div>

                        <small>
                            Customer
                        </small>

                        <strong id="previewName">
                            New Customer
                        </strong>

                    </div>

                </div>

                <div class="preview-row">

                    <span>
                        Plan
                    </span>

                    <strong id="previewPlan">
                        Monthly
                    </strong>

                </div>

                <div class="preview-row">

                    <span>
                        Duration
                    </span>

                    <strong id="previewDuration">
                        12 Months
                    </strong>

                </div>

                <div class="preview-row">

                    <span>
                        Installment
                    </span>

                    <strong
                        id="previewAmount"
                        class="preview-amount"
                    >
                        ₹0.00
                    </strong>

                </div>

                <div class="preview-row">

                    <span>
                        Frequency
                    </span>

                    <strong id="previewFrequency">
                        Monthly
                    </strong>

                </div>

            </div>

            <div class="info-card">

                <div class="info-title">

                    <i
                        class="bi bi-lightning-charge-fill"
                        style="color:#16a34a"
                    ></i>

                    Quick Information

                </div>

                <div class="info-item">

                    <span>
                        Monthly Account
                    </span>

                    <strong>
                        12 Payments
                    </strong>

                </div>

                <div class="info-item">

                    <span>
                        Weekly Account
                    </span>

                    <strong>
                        52 Payments
                    </strong>

                </div>

                <div class="info-item">

                    <span>
                        Multiple Accounts
                    </span>

                    <strong style="color:#15803d">
                        Allowed
                    </strong>

                </div>

                <div class="info-item">

                    <span>
                        Same Customer
                    </span>

                    <strong style="color:#15803d">
                        Yes
                    </strong>

                </div>

            </div>

            <div class="info-card">

                <div class="info-title">

                    <i
                        class="bi bi-info-circle-fill"
                        style="color:#16a34a"
                    ></i>

                    Important

                </div>

                <div
                    style="
                    color:#78837c;
                    font-size:10px;
                    line-height:1.7;
                    "
                >

                    Existing customer select karke
                    <strong>Monthly</strong> ya
                    <strong>Weekly</strong> account
                    create kar sakte ho.

                    <br><br>

                    Ek customer ke
                    <strong>2 Weekly accounts</strong>
                    bhi allowed hain.

                </div>

            </div>

        </div>

    </div>

</div>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function(){

        const modeRadios =
            document.querySelectorAll(
                'input[name="customer_mode"]'
            );

        const existingBox =
            document.getElementById(
                'existingCustomerBox'
            );

        const customerSelect =
            document.getElementById(
                'customer_id'
            );

        const nameInput =
            document.getElementById(
                'name'
            );

        const phoneInput =
            document.getElementById(
                'phone'
            );

        const aadhaarInput =
            document.getElementById(
                'aadhaar'
            );

        const previewName =
            document.getElementById(
                'previewName'
            );

        const previewAvatar =
            document.getElementById(
                'previewAvatar'
            );

        const amountInput =
            document.getElementById(
                'amount'
            );

        const previewAmount =
            document.getElementById(
                'previewAmount'
            );

        const previewPlan =
            document.getElementById(
                'previewPlan'
            );

        const previewDuration =
            document.getElementById(
                'previewDuration'
            );

        const previewFrequency =
            document.getElementById(
                'previewFrequency'
            );

        const amountLabel =
            document.getElementById(
                'amountLabel'
            );

        function updateMode(){

            const selected =
                document.querySelector(
                    'input[name="customer_mode"]:checked'
                );

            if(!selected){

                return;
            }

            if(
                selected.value ===
                'existing'
            ){

                existingBox.style.display =
                    'block';

                nameInput.readOnly =
                    true;

                phoneInput.readOnly =
                    true;

                aadhaarInput.readOnly =
                    true;

                nameInput.style.background =
                    '#f6f8f7';

                phoneInput.style.background =
                    '#f6f8f7';

                aadhaarInput.style.background =
                    '#f6f8f7';

                loadCustomer();

            }else{

                existingBox.style.display =
                    'none';

                nameInput.readOnly =
                    false;

                phoneInput.readOnly =
                    false;

                aadhaarInput.readOnly =
                    false;

                nameInput.style.background =
                    '#fff';

                phoneInput.style.background =
                    '#fff';

                aadhaarInput.style.background =
                    '#fff';

            }

            updatePreview();
        }

        function loadCustomer(){

            const option =
                customerSelect.options[
                    customerSelect.selectedIndex
                ];

            if(
                !option ||
                !option.value
            ){

                return;
            }

            nameInput.value =
                option.dataset.name || '';

            phoneInput.value =
                option.dataset.phone || '';

            aadhaarInput.value =
                option.dataset.aadhaar || '';

            updatePreview();
        }

        function updatePlan(){

            const selected =
                document.querySelector(
                    'input[name="plan"]:checked'
                );

            if(!selected){

                return;
            }

            if(
                selected.value ===
                'Weekly'
            ){

                amountLabel.innerHTML =
                    'Weekly Amount <span class="required">*</span>';

                previewPlan.textContent =
                    'Weekly';

                previewDuration.textContent =
                    '52 Weeks';

                previewFrequency.textContent =
                    'Weekly';

            }else{

                amountLabel.innerHTML =
                    'Monthly Amount <span class="required">*</span>';

                previewPlan.textContent =
                    'Monthly';

                previewDuration.textContent =
                    '12 Months';

                previewFrequency.textContent =
                    'Monthly';
            }

            updateAmount();
        }

        function updateAmount(){

            const value =
                parseFloat(
                    amountInput.value
                ) || 0;

            previewAmount.textContent =
                '₹' +
                value.toLocaleString(
                    'en-IN',
                    {
                        minimumFractionDigits:2,
                        maximumFractionDigits:2
                    }
                );
        }

        function updatePreview(){

            const name =
                nameInput.value.trim();

            if(name){

                previewName.textContent =
                    name;

                previewAvatar.textContent =
                    name
                    .charAt(0)
                    .toUpperCase();

            }else{

                previewName.textContent =
                    'New Customer';

                previewAvatar.textContent =
                    '?';
            }

            updatePlan();
            updateAmount();
        }

        modeRadios.forEach(
            function(radio){

                radio.addEventListener(
                    'change',
                    updateMode
                );
            }
        );

        customerSelect.addEventListener(
            'change',
            loadCustomer
        );

        nameInput.addEventListener(
            'input',
            updatePreview
        );

        amountInput.addEventListener(
            'input',
            updateAmount
        );

        document
            .querySelectorAll(
                'input[name="plan"]'
            )
            .forEach(
                function(radio){

                    radio.addEventListener(
                        'change',
                        updatePlan
                    );
                }
            );

        updateMode();
        updatePlan();
        updateAmount();
        updatePreview();

    }
);

</script>

<?php

include 'includes/footer.php';

?>