<?php

date_default_timezone_set('Asia/Kolkata');

require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Customers';

$search = trim($_GET['search'] ?? '');

$plan = $_GET['plan'] ?? 'All';

if (!in_array($plan, ['All', 'Monthly', 'Weekly'], true)) {
    $plan = 'All';
}

$where = [];
$params = [];
$types = '';

if ($plan !== 'All') {
    $where[] = 'c.plan = ?';
    $params[] = $plan;
    $types .= 's';
}

if ($search !== '') {
    $where[] = "
        (
            c.name LIKE ?
            OR c.phone LIKE ?
            OR c.aadhaar_no LIKE ?
        )
    ";

    $searchLike = '%' . $search . '%';

    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;

    $types .= 'sss';
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT
        c.*,
        COALESCE(
            (
                SELECT SUM(ip.amount)
                FROM installment_payments ip
                WHERE ip.customer_id = c.id
            ),
            0
        ) AS total_paid,
        COALESCE(
            (
                SELECT COUNT(DISTINCT ip.installment_no)
                FROM installment_payments ip
                WHERE ip.customer_id = c.id
            ),
            0
        ) AS actual_paid_installments
    FROM customers c
    $whereSql
    ORDER BY c.name ASC, c.phone ASC, c.id DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$accounts = [];

while ($row = $result->fetch_assoc()) {
    $accounts[] = $row;
}

$totalAccounts = (int)$conn->query("
    SELECT COUNT(*) AS total
    FROM customers
")->fetch_assoc()['total'];

$activeAccounts = (int)$conn->query("
    SELECT COUNT(*) AS total
    FROM customers
    WHERE status = 'Active'
")->fetch_assoc()['total'];

$completedAccounts = (int)$conn->query("
    SELECT COUNT(*) AS total
    FROM customers
    WHERE status = 'Completed'
")->fetch_assoc()['total'];

$monthlyAccounts = (int)$conn->query("
    SELECT COUNT(*) AS total
    FROM customers
    WHERE plan = 'Monthly'
")->fetch_assoc()['total'];

$weeklyAccounts = (int)$conn->query("
    SELECT COUNT(*) AS total
    FROM customers
    WHERE plan = 'Weekly'
")->fetch_assoc()['total'];

$groupedCustomers = [];

foreach ($accounts as $row) {

    $groupKey =
        strtolower(trim($row['name'])) .
        '|' .
        trim($row['phone']);

    if (!isset($groupedCustomers[$groupKey])) {

        $groupedCustomers[$groupKey] = [
            'name' => $row['name'],
            'phone' => $row['phone'],
            'aadhaar' => $row['aadhaar_no'] ?? '',
            'accounts' => []
        ];
    }

    $groupedCustomers[$groupKey]['accounts'][] = $row;
}

include 'includes/header.php';

?>

<style>

.page-head{
    margin-bottom:28px;
}

.page-kicker{
    color:#16a34a;
    font-size:11px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:1.2px;
    margin-bottom:5px;
}

.page-title{
    font-size:30px;
    font-weight:900;
    letter-spacing:-1px;
    margin:0;
    color:#101713;
}

.page-subtitle{
    color:#748078;
    font-size:13px;
    margin-top:5px;
}

.add-btn{
    border:0;
    border-radius:13px;
    padding:11px 18px;
    background:linear-gradient(
        135deg,
        #15803d,
        #22c55e
    );
    box-shadow:
        0 8px 20px
        rgba(22,163,74,.22);
    font-weight:750;
}

.add-btn:hover{
    background:linear-gradient(
        135deg,
        #166534,
        #16a34a
    );
}

.summary-card{
    position:relative;
    overflow:hidden;
    background:#fff;
    border:1px solid #e6ece8;
    border-radius:20px;
    padding:21px;
    box-shadow:
        0 8px 28px
        rgba(9,18,12,.045);
    height:100%;
    transition:.25s;
}

.summary-card:hover{
    transform:translateY(-3px);
    box-shadow:
        0 14px 35px
        rgba(9,18,12,.08);
}

.summary-card::after{
    content:"";
    position:absolute;
    width:90px;
    height:90px;
    border-radius:50%;
    right:-35px;
    top:-35px;
    background:#ecfdf3;
}

.summary-icon{
    width:48px;
    height:48px;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#ecfdf3;
    color:#16a34a;
    font-size:20px;
    position:relative;
    z-index:1;
}

.summary-label{
    color:#7a857f;
    font-size:10px;
    font-weight:800;
    letter-spacing:.4px;
}

.summary-number{
    font-size:27px;
    line-height:1;
    font-weight:900;
    margin-top:8px;
}

.summary-note{
    color:#929b96;
    font-size:10px;
    margin-top:7px;
}

.filter-box{
    background:#fff;
    border:1px solid #e5ebe7;
    border-radius:20px;
    padding:16px;
    box-shadow:
        0 8px 25px
        rgba(9,18,12,.04);
}

.search-input{
    border-radius:12px!important;
    padding:11px 14px!important;
    border-color:#e1e8e3!important;
}

.search-input:focus{
    border-color:#86efac!important;
    box-shadow:
        0 0 0 .2rem
        rgba(34,197,94,.10)!important;
}

.filter-select{
    border-radius:12px!important;
    padding:11px 14px!important;
    border-color:#e1e8e3!important;
}

.filter-btn{
    border-radius:12px;
    padding:11px 18px;
    font-weight:750;
}

.clear-btn{
    border-radius:12px;
    padding:11px 15px;
}

.customer-wrapper{
    background:#fff;
    border:1px solid #e4ebe6;
    border-radius:24px;
    overflow:hidden;
    margin-bottom:18px;
    box-shadow:
        0 10px 32px
        rgba(9,18,12,.05);
    transition:.25s;
}

.customer-wrapper:hover{
    box-shadow:
        0 17px 40px
        rgba(9,18,12,.075);
}

.customer-top{
    padding:20px 22px;
    background:
        linear-gradient(
            135deg,
            #ffffff,
            #f7fbf8
        );
    border-bottom:1px solid #edf1ee;
}

.customer-profile{
    display:flex;
    align-items:center;
    gap:13px;
}

.customer-avatar{
    width:52px;
    height:52px;
    flex-shrink:0;
    border-radius:17px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:
        linear-gradient(
            135deg,
            #dcfce7,
            #bbf7d0
        );
    color:#15803d;
    font-size:19px;
    font-weight:900;
}

.customer-name{
    font-size:17px;
    font-weight:900;
    color:#172019;
}

.customer-phone{
    color:#78837c;
    font-size:11px;
    margin-top:3px;
}

.customer-aadhaar{
    color:#929b96;
    font-size:10px;
    margin-top:2px;
}

.account-count{
    background:#ecfdf3;
    color:#15803d;
    padding:7px 11px;
    border-radius:20px;
    font-size:10px;
    font-weight:800;
}

.accounts-area{
    padding:18px;
    background:#fbfcfb;
}

.account-card{
    background:#fff;
    border:1px solid #e6ece8;
    border-radius:18px;
    padding:18px;
    height:100%;
    transition:.22s;
    position:relative;
}

.account-card:hover{
    border-color:#bbf7d0;
    box-shadow:
        0 8px 25px
        rgba(9,18,12,.055);
}

.account-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:10px;
}

.account-id{
    color:#89938d;
    font-size:9px;
    font-weight:800;
    letter-spacing:.4px;
    text-transform:uppercase;
}

.account-plan{
    font-size:15px;
    font-weight:900;
    margin-top:3px;
}

.plan-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:800;
    white-space:nowrap;
}

.monthly-badge{
    background:#dcfce7;
    color:#15803d;
}

.weekly-badge{
    background:#d1fae5;
    color:#047857;
}

.status-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:5px 9px;
    border-radius:20px;
    font-size:9px;
    font-weight:800;
}

.active-badge{
    background:#fef3c7;
    color:#92400e;
}

.completed-badge{
    background:#dbeafe;
    color:#1d4ed8;
}

.account-amount{
    font-size:25px;
    font-weight:900;
    color:#15803d;
    margin-top:17px;
}

.account-frequency{
    color:#8a948e;
    font-size:10px;
    margin-top:1px;
}

.account-stats{
    display:grid;
    grid-template-columns:
        repeat(3,1fr);
    gap:8px;
    margin-top:18px;
}

.account-stat{
    background:#f7faf8;
    border:1px solid #edf1ee;
    border-radius:12px;
    padding:10px;
}

.account-stat-label{
    color:#87918b;
    font-size:8px;
    font-weight:800;
    text-transform:uppercase;
}

.account-stat-value{
    color:#25312a;
    font-size:12px;
    font-weight:850;
    margin-top:4px;
}

.account-stat-value.green{
    color:#15803d;
}

.account-stat-value.red{
    color:#dc2626;
}

.progress-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:18px;
    margin-bottom:7px;
}

.progress-text{
    color:#79847d;
    font-size:9px;
    font-weight:750;
}

.progress-percent{
    color:#15803d;
    font-size:10px;
    font-weight:850;
}

.account-progress{
    height:7px;
    background:#e8efea;
    border-radius:20px;
    overflow:hidden;
}

.account-progress-bar{
    height:100%;
    border-radius:20px;
    background:
        linear-gradient(
            90deg,
            #15803d,
            #22c55e
        );
}

.account-info{
    display:grid;
    grid-template-columns:
        repeat(2,1fr);
    gap:8px;
    margin-top:16px;
}

.info-item{
    padding:9px 10px;
    border-radius:11px;
    background:#fafcfb;
}

.info-label{
    color:#929b96;
    font-size:8px;
    font-weight:800;
    text-transform:uppercase;
}

.info-value{
    color:#303a34;
    font-size:10px;
    font-weight:750;
    margin-top:3px;
}

.account-actions{
    display:flex;
    gap:7px;
    margin-top:17px;
    padding-top:14px;
    border-top:1px solid #edf1ee;
}

.action-btn{
    height:35px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    font-size:10px;
    font-weight:750;
}

.payment-action{
    flex:1;
}

.icon-action{
    width:36px;
    flex-shrink:0;
}

.empty-box{
    background:#fff;
    border:1px solid #e5ebe7;
    border-radius:22px;
    padding:75px 20px;
    text-align:center;
    box-shadow:
        0 8px 25px
        rgba(9,18,12,.04);
}

.empty-icon{
    width:75px;
    height:75px;
    margin:auto;
    border-radius:22px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f0f5f2;
    color:#9aa59e;
    font-size:30px;
}

.empty-title{
    font-size:18px;
    font-weight:850;
    margin-top:17px;
}

.empty-text{
    color:#7d8781;
    font-size:12px;
    margin-top:5px;
}

.result-text{
    color:#7b867f;
    font-size:11px;
}

.filter-active{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#ecfdf3;
    color:#15803d;
    border:1px solid #bbf7d0;
    border-radius:20px;
    padding:7px 12px;
    font-size:10px;
    font-weight:800;
}

@media(max-width:767px){

    .page-title{
        font-size:25px;
    }

    .account-stats{
        grid-template-columns:
            repeat(3,1fr);
    }

    .customer-top{
        padding:17px;
    }

    .accounts-area{
        padding:12px;
    }

    .account-card{
        padding:15px;
    }

}

</style>

<div class="page-head">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>

            <div class="page-kicker">
                AliBrose Bank
            </div>

            <h1 class="page-title">
                Customers
            </h1>

            <div class="page-subtitle">
                Manage customers, accounts and collections
            </div>

        </div>

        <a
            href="add_customer.php"
            class="btn btn-success add-btn"
        >

            <i class="bi bi-person-plus-fill me-2"></i>

            Add Customer

        </a>

    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-6 col-xl-3">

        <div class="summary-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="summary-label">
                        TOTAL ACCOUNTS
                    </div>

                    <div class="summary-number">
                        <?= number_format($totalAccounts) ?>
                    </div>

                    <div class="summary-note">
                        All accounts
                    </div>

                </div>

                <div class="summary-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-6 col-xl-3">

        <div class="summary-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="summary-label">
                        ACTIVE
                    </div>

                    <div class="summary-number">
                        <?= number_format($activeAccounts) ?>
                    </div>

                    <div class="summary-note">
                        Running accounts
                    </div>

                </div>

                <div class="summary-icon">
                    <i class="bi bi-activity"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-6 col-xl-3">

        <div class="summary-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="summary-label">
                        MONTHLY
                    </div>

                    <div class="summary-number">
                        <?= number_format($monthlyAccounts) ?>
                    </div>

                    <div class="summary-note">
                        12 installments
                    </div>

                </div>

                <div class="summary-icon">
                    <i class="bi bi-calendar-month"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-6 col-xl-3">

        <div class="summary-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="summary-label">
                        WEEKLY
                    </div>

                    <div class="summary-number">
                        <?= number_format($weeklyAccounts) ?>
                    </div>

                    <div class="summary-note">
                        52 installments
                    </div>

                </div>

                <div class="summary-icon">
                    <i class="bi bi-calendar-week"></i>
                </div>

            </div>

        </div>

    </div>

</div>

<?php if (isset($_GET['deleted'])): ?>

<div class="alert alert-success alert-dismissible fade show">

    <i class="bi bi-check-circle-fill me-2"></i>

    Account aur uski collection successfully delete ho gayi.

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert"
    ></button>

</div>

<?php endif; ?>

<?php if (isset($_GET['updated'])): ?>

<div class="alert alert-success alert-dismissible fade show">

    <i class="bi bi-check-circle-fill me-2"></i>

    Account successfully update ho gaya.

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert"
    ></button>

</div>

<?php endif; ?>

<div class="filter-box mb-4">

    <form
        method="GET"
        class="row g-2 align-items-center"
    >

        <div class="col-lg-6">

            <div class="input-group">

                <span class="input-group-text bg-white border-end-0">

                    <i
                        class="bi bi-search"
                        style="color:#16a34a"
                    ></i>

                </span>

                <input
                    type="text"
                    name="search"
                    class="form-control search-input border-start-0"
                    placeholder="Search customer, mobile or Aadhaar..."
                    value="<?= htmlspecialchars($search) ?>"
                >

            </div>

        </div>

        <div class="col-lg-3">

            <select
                name="plan"
                class="form-select filter-select"
            >

                <option
                    value="All"
                    <?= $plan === 'All'
                        ? 'selected'
                        : '' ?>
                >
                    All Accounts
                </option>

                <option
                    value="Monthly"
                    <?= $plan === 'Monthly'
                        ? 'selected'
                        : '' ?>
                >
                    Monthly Accounts
                </option>

                <option
                    value="Weekly"
                    <?= $plan === 'Weekly'
                        ? 'selected'
                        : '' ?>
                >
                    Weekly Accounts
                </option>

            </select>

        </div>

        <div class="col-lg-2">

            <button
                type="submit"
                class="btn btn-success filter-btn w-100"
            >

                <i class="bi bi-funnel-fill me-1"></i>

                Filter

            </button>

        </div>

        <div class="col-lg-1">

            <a
                href="customers.php"
                class="btn btn-light clear-btn w-100"
                title="Clear Filter"
            >

                <i class="bi bi-x-lg"></i>

            </a>

        </div>

    </form>

</div>

<div class="d-flex justify-content-between align-items-center mb-3">

    <div class="result-text">

        <strong>
            <?= count($groupedCustomers) ?>
        </strong>

        customer(s)

        <span class="mx-1">•</span>

        <strong>
            <?= count($accounts) ?>
        </strong>

        account(s)

    </div>

    <?php if ($plan !== 'All'): ?>

        <div class="filter-active">

            <?php if ($plan === 'Monthly'): ?>

                <i class="bi bi-calendar-month"></i>

            <?php else: ?>

                <i class="bi bi-calendar-week"></i>

            <?php endif; ?>

            <?= htmlspecialchars($plan) ?>

        </div>

    <?php endif; ?>

</div>

<?php if (empty($groupedCustomers)): ?>

<div class="empty-box">

    <div class="empty-icon">

        <i class="bi bi-people"></i>

    </div>

    <div class="empty-title">
        No Customer Found
    </div>

    <div class="empty-text">

        Search ya selected filter ke according
        koi customer nahi mila.

    </div>

    <a
        href="add_customer.php"
        class="btn btn-success mt-4"
    >

        <i class="bi bi-person-plus-fill me-2"></i>

        Add Customer

    </a>

</div>

<?php else: ?>

<?php foreach (
    $groupedCustomers
    as $customer
): ?>

<div class="customer-wrapper">

    <div class="customer-top">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div class="customer-profile">

                <div class="customer-avatar">

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

                    <div class="customer-phone">

                        <i class="bi bi-telephone-fill me-1"></i>

                        <?= htmlspecialchars(
                            $customer['phone']
                        ) ?>

                    </div>

                    <?php if (
                        !empty(
                            $customer['aadhaar']
                        )
                    ): ?>

                        <div class="customer-aadhaar">

                            <i class="bi bi-person-vcard me-1"></i>

                            <?= htmlspecialchars(
                                $customer['aadhaar']
                            ) ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <div class="account-count">

                <i class="bi bi-wallet2 me-1"></i>

                <?= count(
                    $customer['accounts']
                ) ?>

                Account<?= count(
                    $customer['accounts']
                ) > 1 ? 's' : '' ?>

            </div>

        </div>

    </div>

    <div class="accounts-area">

        <div class="row g-3">

            <?php foreach (
                $customer['accounts']
                as $account
            ): ?>

            <?php

            $totalInstallments =
                (int)(
                    $account[
                        'total_installments'
                    ]
                    ??
                    (
                        $account['plan']
                        === 'Weekly'
                        ? 52
                        : 12
                    )
                );

            $paidInstallments =
                (int)(
                    $account[
                        'actual_paid_installments'
                    ]
                    ??
                    $account[
                        'paid_installments'
                    ]
                    ??
                    0
                );

            $paidInstallments =
                min(
                    $paidInstallments,
                    $totalInstallments
                );

            $remaining =
                max(
                    0,
                    $totalInstallments
                    -
                    $paidInstallments
                );

            $progress = 0;

            if (
                $totalInstallments > 0
            ) {

                $progress =
                    (
                        $paidInstallments /
                        $totalInstallments
                    ) * 100;
            }

            $progress =
                min(
                    100,
                    max(
                        0,
                        $progress
                    )
                );

            $isMonthly =
                $account['plan']
                ===
                'Monthly';

            $planClass =
                $isMonthly
                ? 'monthly-badge'
                : 'weekly-badge';

            $planIcon =
                $isMonthly
                ? 'bi-calendar-month-fill'
                : 'bi-calendar-week-fill';

            $frequency =
                $isMonthly
                ? 'per month'
                : 'per week';

            $statusCompleted =
                $paidInstallments >=
                $totalInstallments;

            ?>

            <div class="col-xl-4 col-md-6">

                <div class="account-card">

                    <div class="account-top">

                        <div>

                            <div class="account-id">

                                Account ID
                                #<?= (int)$account['id'] ?>

                            </div>

                            <div class="account-plan">

                                <?= htmlspecialchars(
                                    $account['plan']
                                ) ?>

                                Account

                            </div>

                        </div>

                        <span
                            class="
                            plan-badge
                            <?= $planClass ?>
                            "
                        >

                            <i
                                class="
                                bi
                                <?= $planIcon ?>
                                "
                            ></i>

                            <?= htmlspecialchars(
                                $account['plan']
                            ) ?>

                        </span>

                    </div>

                    <div class="account-amount">

                        ₹<?= number_format(
                            (float)$account['amount'],
                            2
                        ) ?>

                    </div>

                    <div class="account-frequency">

                        Installment <?= $frequency ?>

                    </div>

                    <div class="account-stats">

                        <div class="account-stat">

                            <div class="account-stat-label">
                                Paid
                            </div>

                            <div class="account-stat-value green">

                                <?= $paidInstallments ?>

                            </div>

                        </div>

                        <div class="account-stat">

                            <div class="account-stat-label">
                                Remaining
                            </div>

                            <div class="account-stat-value red">

                                <?= $remaining ?>

                            </div>

                        </div>

                        <div class="account-stat">

                            <div class="account-stat-label">
                                Total
                            </div>

                            <div class="account-stat-value">

                                <?= $totalInstallments ?>

                            </div>

                        </div>

                    </div>

                    <div class="progress-head">

                        <div class="progress-text">
                            Account Progress
                        </div>

                        <div class="progress-percent">

                            <?= number_format(
                                $progress,
                                0
                            ) ?>%

                        </div>

                    </div>

                    <div class="account-progress">

                        <div
                            class="account-progress-bar"
                            style="
                            width:<?= $progress ?>%
                            "
                        ></div>

                    </div>

                    <div class="account-info">

                        <div class="info-item">

                            <div class="info-label">
                                Opening Date
                            </div>

                            <div class="info-value">

                                <?php if (
                                    !empty(
                                        $account[
                                            'opening_date'
                                        ]
                                    )
                                ): ?>

                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $account[
                                                'opening_date'
                                            ]
                                        )
                                    ) ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </div>

                        </div>

                        <div class="info-item">

                            <div class="info-label">
                                Next Due
                            </div>

                            <div class="info-value">

                                <?php if (
                                    !empty(
                                        $account[
                                            'next_due_date'
                                        ]
                                    )
                                ): ?>

                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $account[
                                                'next_due_date'
                                            ]
                                        )
                                    ) ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">

                        <?php if (
                            $statusCompleted
                        ): ?>

                            <span
                                class="
                                status-badge
                                completed-badge
                                "
                            >

                                <i class="bi bi-check-circle-fill"></i>

                                Completed

                            </span>

                        <?php else: ?>

                            <span
                                class="
                                status-badge
                                active-badge
                                "
                            >

                                <i class="bi bi-clock-fill"></i>

                                Active

                            </span>

                        <?php endif; ?>

                        <small
                            class="text-muted"
                            style="font-size:9px"
                        >

                            <?= htmlspecialchars(
                                $account['phone']
                            ) ?>

                        </small>

                    </div>

                    <div class="account-actions">

                        <a
                            href="payment.php?id=<?= (int)$account['id'] ?>"
                            class="
                            btn
                            btn-success
                            action-btn
                            payment-action
                            "
                        >

                            <i class="bi bi-cash-stack"></i>

                            Payment

                        </a>

                        <a
                            href="edit_customer.php?id=<?= (int)$account['id'] ?>"
                            class="
                            btn
                            btn-outline-primary
                            action-btn
                            icon-action
                            "
                            title="Edit Account"
                        >

                            <i class="bi bi-pencil-fill"></i>

                        </a>

                        <a
                            href="delete_customer.php?id=<?= (int)$account['id'] ?>"
                            class="
                            btn
                            btn-outline-danger
                            action-btn
                            icon-action
                            "
                            title="Delete Account"
                            onclick="return confirm(
                                '<?= htmlspecialchars(
                                    addslashes(
                                        $account['name']
                                    )
                                ) ?>\\n\\n' +
                                'Account #<?= (int)$account['id'] ?>\\n' +
                                'Plan: <?= htmlspecialchars(
                                    $account['plan']
                                ) ?>\\n\\n' +
                                'Is account ki saari payment aur collection bhi delete ho jayegi.\\n\\n' +
                                'Kya aap sure hain?'
                            );"
                        >

                            <i class="bi bi-trash3-fill"></i>

                        </a>

                    </div>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<?php endforeach; ?>

<?php endif; ?>

<?php

include 'includes/footer.php';

?>