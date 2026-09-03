<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/db.php';

$page_title = 'Customers';

$q = trim($_GET['q'] ?? '');

$plan = $_GET['plan'] ?? 'All';

$status = $_GET['status'] ?? 'All';

if (!in_array($plan, ['All', 'Weekly', 'Monthly'], true)) {
    $plan = 'All';
}

if (!in_array($status, ['All', 'Active', 'Completed', 'Closed'], true)) {
    $status = 'All';
}

if (isset($_GET['delete'])) {

    $id = (int)($_GET['delete'] ?? 0);

    if ($id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM customers
            WHERE id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                'i',
                $id
            );

            $stmt->execute();

            $stmt->close();
        }
    }

    header('Location: customers.php');

    exit;
}

function getCustomerCalculation($customer)
{
    $openingDate = $customer['opening_date'] ?? null;
    $plan = $customer['plan'] ?? 'Weekly';
    $total = max(0, (int)($customer['total_installments'] ?? 0));
    $paid = max(0, min(
        (int)($customer['paid_installments'] ?? 0),
        $total
    ));

    if ($total <= 0) {
        return [
            'current' => 0,
            'completed' => 0,
            'remaining' => 0,
            'progress' => 0,
            'next_payment' => null,
            'status' => 'Active'
        ];
    }

    if ($paid >= $total) {
        return [
            'current' => $total,
            'completed' => $paid,
            'remaining' => 0,
            'progress' => 100,
            'next_payment' => null,
            'status' => 'Completed'
        ];
    }

    $nextNo = $paid + 1;
    $nextPayment = null;

    try {
        $opening = new DateTimeImmutable($openingDate);

        if ($plan === 'Monthly') {
            $first = new DateTimeImmutable(
                $opening->format('Y-m-10')
            );

            if ((int)$opening->format('d') > 10) {
                $first = $first->modify('+1 month');
            }

            $nextPayment = $first
                ->modify('+' . ($nextNo - 1) . ' months')
                ->format('Y-m-d');

        } else {
            $first = $opening;

            if ((int)$first->format('N') !== 1) {
                $first = $first->modify('next monday');
            }

            $nextPayment = $first
                ->modify('+' . (($nextNo - 1) * 7) . ' days')
                ->format('Y-m-d');
        }
    } catch (Exception $e) {
        $nextPayment = null;
    }

    return [
        'current' => $nextNo,
        'completed' => $paid,
        'remaining' => max(0, $total - $paid),
        'progress' => min(100, ($paid / $total) * 100),
        'next_payment' => $nextPayment,
        'status' => 'Active'
    ];
}

$where = [];

$params = [];

$types = '';

if ($plan !== 'All') {

    $where[] = "plan = ?";

    $params[] = $plan;

    $types .= 's';
}

if ($q !== '') {

    $where[] = "
        (
            name LIKE ?
            OR father_name LIKE ?
            OR mobile LIKE ?
            OR aadhaar LIKE ?
            OR address LIKE ?
        )
    ";

    $like = "%{$q}%";

    for ($i = 0; $i < 5; $i++) {

        $params[] = $like;

        $types .= 's';
    }
}

$sql = "
    SELECT
        id,
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
        absent_installments,
        next_due_date,
        status,
        created_at
    FROM customers
";

if (!empty($where)) {

    $sql .=
        " WHERE " .
        implode(
            " AND ",
            $where
        );
}

$sql .= "
    ORDER BY id DESC
";

if (!empty($params)) {

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        die(
            "Customer query error: " .
            htmlspecialchars(
                $conn->error
            )
        );
    }

    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $rows =
        $stmt->get_result();

} else {

    $rows =
        $conn->query($sql);
}

if (!$rows) {

    die(
        "Customer list error: " .
        htmlspecialchars(
            $conn->error
        )
    );
}

$customers = [];

while (
    $customer =
    $rows->fetch_assoc()
) {

    $calculation =
        getCustomerCalculation(
            $customer
        );

    $customer['_calculation'] =
        $calculation;

    if (
        $status !== 'All' &&
        $status !==
        $calculation['status']
    ) {

        continue;
    }

    $customers[] =
        $customer;
}

$resultCount =
    count($customers);

$weeklyCustomers = 0;

$monthlyCustomers = 0;

$activeCustomers = 0;

$completedCustomers = 0;

$countResult =
    $conn->query("
        SELECT
            plan,
            COUNT(*) AS total
        FROM customers
        GROUP BY plan
    ");

if ($countResult) {

    while (
        $countRow =
        $countResult->fetch_assoc()
    ) {

        if (
            $countRow['plan'] ===
            'Weekly'
        ) {

            $weeklyCustomers =
                (int)$countRow['total'];
        }

        if (
            $countRow['plan'] ===
            'Monthly'
        ) {

            $monthlyCustomers =
                (int)$countRow['total'];
        }
    }
}

$allCustomersResult =
    $conn->query("
        SELECT
            id,
            opening_date,
            plan,
            total_installments
        FROM customers
    ");

if ($allCustomersResult) {

    while (
        $countCustomer =
        $allCustomersResult->fetch_assoc()
    ) {

        $calculation =
            getCustomerCalculation(
                $countCustomer
            );

        if (
            $calculation['status'] ===
            'Completed'
        ) {

            $completedCustomers++;

        } else {

            $activeCustomers++;
        }
    }
}

if ($status === 'Completed') {

    $pageHeading =
        'Completed Customers';

} elseif (
    $status === 'Active'
) {

    $pageHeading =
        'Active Customers';

} elseif (
    $status === 'Closed'
) {

    $pageHeading =
        'Closed Customers';

} elseif (
    $plan === 'Weekly'
) {

    $pageHeading =
        'Weekly Customers';

} elseif (
    $plan === 'Monthly'
) {

    $pageHeading =
        'Monthly Customers';

} else {

    $pageHeading =
        'Customers';
}

include 'includes/header.php';

?>

<style>

.customer-page {
    animation: pageIn 1s ease both;
}

@keyframes pageIn {

    from {
        opacity: 0;
        transform: translateY(25px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.customer-header {
    animation: headerIn 1s ease both;
}

@keyframes headerIn {

    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.customer-stat {
    position: relative;
    overflow: hidden;
    background: #fff;
    border: 1px solid #e8eee9;
    border-radius: 22px;
    padding: 22px;
    height: 100%;
    box-shadow:
        0 9px 28px
        rgba(0,0,0,.045);
    transition:
        transform .6s ease,
        box-shadow .6s ease;
    animation:
        cardIn 1s ease both;
}

.customer-stat:hover {
    transform: translateY(-8px);
    box-shadow:
        0 22px 45px
        rgba(0,0,0,.10);
}

.customer-stat::before {
    content: "";
    position: absolute;
    width: 140px;
    height: 140px;
    right: -70px;
    bottom: -70px;
    border-radius: 50%;
    background: #fff2f8;
    transition:
        transform 1s ease;
}

.customer-stat:hover::before {
    transform: scale(1.4);
}

.customer-stat-label {
    color: #7a867f;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .7px;
    text-transform: uppercase;
}

.customer-stat-value {
    color: #172019;
    font-size: 32px;
    line-height: 1;
    font-weight: 900;
    margin-top: 9px;
}

.customer-stat-icon {
    position: relative;
    z-index: 2;
    width: 50px;
    height: 50px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff0f7;
    color: #d81b72;
    font-size: 20px;
    transition:
        transform .8s ease;
}

.customer-stat:hover
.customer-stat-icon {
    transform:
        rotate(360deg)
        scale(1.08);
}

.customer-tools {
    background: #fff;
    border: 1px solid #e8eee9;
    border-radius: 22px;
    padding: 18px;
    box-shadow:
        0 9px 28px
        rgba(0,0,0,.045);
    animation:
        toolsIn 1.1s ease .2s both;
}

@keyframes toolsIn {

    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    height: 48px;
    padding: 5px 6px 5px 15px;
    border: 1px solid #dfe7e2;
    border-radius: 14px;
    background: #fff;
    transition: .4s ease;
}

.search-box:focus-within {
    border-color: #d81b72;
    box-shadow:
        0 0 0 4px
        rgba(216,27,114,.08);
    transform:
        translateY(-2px);
}

.search-box i {
    color: #d81b72;
    font-size: 18px;
}

.search-box input {
    flex: 1;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
    font-size: 12px;
}

.search-box button {
    border: 0;
    border-radius: 10px;
    padding: 9px 17px;
    background: #d81b72;
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    transition: .35s ease;
}

.search-box button:hover {
    background: #b8145f;
    transform:
        translateY(-2px);
}

.filter-area {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
}

.filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    padding: 9px 14px;
    border-radius: 11px;
    font-size: 10px;
    font-weight: 850;
    background: #f1f5f3;
    color: #59655e;
    transition: .35s ease;
}

.filter-btn:hover {
    background: #fff0f7;
    color: #d81b72;
    transform:
        translateY(-2px);
}

.filter-btn.active {
    background: #d81b72;
    color: #fff;
    box-shadow:
        0 7px 18px
        rgba(216,27,114,.20);
}

.customer-table-card {
    overflow: hidden;
    background: #fff;
    border: 1px solid #e8eee9;
    border-radius: 23px;
    box-shadow:
        0 9px 28px
        rgba(0,0,0,.045);
    animation:
        tableIn 1.2s ease .25s both;
}

@keyframes tableIn {

    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.customer-table-header {
    padding: 19px 20px;
    border-bottom:
        1px solid #edf1ee;
}

.customer-table-title {
    color: #172019;
    font-size: 15px;
    font-weight: 900;
}

.customer-table-subtitle {
    color: #87918b;
    font-size: 10px;
    margin-top: 3px;
}

.customer-table {
    width: 100%;
    margin: 0;
}

.customer-table thead th {
    background: #fafbfa;
    color: #69756e;
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 14px 12px;
    white-space: nowrap;
    border-bottom:
        1px solid #e9efeb;
}

.customer-table tbody td {
    padding: 15px 12px;
    font-size: 11px;
    vertical-align: middle;
    border-bottom:
        1px solid #eef2ef;
    white-space: nowrap;
}

.customer-table tbody tr {
    transition: .35s ease;
    animation:
        rowIn .7s ease both;
}

.customer-table tbody tr:hover {
    background: #fff8fb;
    transform:
        scale(1.002);
}

@keyframes rowIn {

    from {
        opacity: 0;
        transform: translateX(-10px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.customer-avatar {
    width: 41px;
    height: 41px;
    min-width: 41px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff0f7;
    color: #d81b72;
    font-weight: 900;
    font-size: 13px;
}

.customer-name {
    color: #172019;
    font-weight: 850;
    font-size: 11px;
}

.customer-meta {
    color: #8a948e;
    font-size: 9px;
    margin-top: 3px;
}

.plan-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 850;
}

.plan-weekly {
    background: #ecfdf5;
    color: #047857;
}

.plan-monthly {
    background: #eff6ff;
    color: #1d4ed8;
}

.progress-badge {
    display: inline-flex;
    padding: 6px 9px;
    border-radius: 9px;
    background: #f1f5f3;
    color: #53605a;
    font-size: 9px;
    font-weight: 850;
}

.period-badge {
    display: inline-flex;
    padding: 6px 9px;
    border-radius: 9px;
    background: #fff0f7;
    color: #b8145f;
    font-size: 9px;
    font-weight: 900;
}

.status-badge {
    display: inline-flex;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 850;
}

.status-active {
    background: #dcfce7;
    color: #15803d;
}

.status-completed {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-closed {
    background: #f1f5f3;
    color: #53605a;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: .35s ease;
}

.action-btn:hover {
    transform:
        translateY(-3px)
        scale(1.06);
}

.view-btn {
    color: #2563eb;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
}

.view-btn:hover {
    color: #fff;
    background: #2563eb;
    border-color: #2563eb;
}

.delete-btn {
    color: #dc2626;
    border: 1px solid #fecaca;
    background: #fef2f2;
}

.delete-btn:hover {
    color: #fff;
    background: #dc2626;
    border-color: #dc2626;
}

.active-filter {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 12px;
    border-radius: 11px;
    background: #fff2f8;
    color: #d81b72;
    font-size: 10px;
    font-weight: 850;
}

.clear-filter {
    color: #d81b72;
    text-decoration: none;
}

.empty-box {
    text-align: center;
    padding: 80px 20px;
}

.empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 22px;
    background: #fff2f8;
    color: #d81b72;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    font-size: 29px;
}

.empty-title {
    margin-top: 17px;
    color: #172019;
    font-size: 18px;
    font-weight: 900;
}

.empty-text {
    color: #7d8881;
    font-size: 11px;
    margin-top: 6px;
}

@media(max-width:768px) {

    .customer-stat-value {
        font-size: 26px;
    }

    .customer-tools {
        padding: 14px;
    }

    .search-box {
        margin-bottom: 12px;
    }

    .customer-table-card {
        border-radius: 16px;
    }
}

@media(max-width:500px) {

    .search-box button {
        padding: 9px 12px;
    }

    .filter-area {
        width: 100%;
    }

    .filter-btn {
        flex: 1;
        justify-content: center;
    }
}

</style>


<div class="customer-page">


    <div class="customer-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <div class="muted small">
                GULABI ALIBROSE
            </div>

            <h3 class="fw-bold mb-1">
                <?= htmlspecialchars($pageHeading) ?>
            </h3>

            <div class="muted">
                Search, filter and manage customers.
            </div>

        </div>


        <a
            href="customers/add_customer.php"
            class="btn btn-pink"
        >

            <i class="bi bi-person-plus-fill me-1"></i>

            Add Customer

        </a>

    </div>


    <div class="row g-3 mb-4">


        <div class="col-xl-3 col-md-6">

            <a
                href="customers.php?plan=Weekly"
                style="text-decoration:none;color:inherit"
            >

                <div class="customer-stat">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="customer-stat-label">
                                Weekly Customers
                            </div>

                            <div
                                class="customer-stat-value animated-number"
                                data-value="<?= $weeklyCustomers ?>"
                            >
                                0
                            </div>

                        </div>

                        <div class="customer-stat-icon">

                            <i class="bi bi-calendar-week"></i>

                        </div>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-xl-3 col-md-6">

            <a
                href="customers.php?plan=Monthly"
                style="text-decoration:none;color:inherit"
            >

                <div class="customer-stat">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="customer-stat-label">
                                Monthly Customers
                            </div>

                            <div
                                class="customer-stat-value animated-number"
                                data-value="<?= $monthlyCustomers ?>"
                            >
                                0
                            </div>

                        </div>

                        <div class="customer-stat-icon">

                            <i class="bi bi-calendar-month"></i>

                        </div>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-xl-3 col-md-6">

            <a
                href="customers.php?status=Active"
                style="text-decoration:none;color:inherit"
            >

                <div class="customer-stat">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="customer-stat-label">
                                Active Customers
                            </div>

                            <div
                                class="customer-stat-value animated-number"
                                data-value="<?= $activeCustomers ?>"
                            >
                                0
                            </div>

                        </div>

                        <div class="customer-stat-icon">

                            <i class="bi bi-person-check-fill"></i>

                        </div>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-xl-3 col-md-6">

            <a
                href="customers.php?status=Completed"
                style="text-decoration:none;color:inherit"
            >

                <div class="customer-stat">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="customer-stat-label">
                                Completed Customers
                            </div>

                            <div
                                class="customer-stat-value animated-number"
                                data-value="<?= $completedCustomers ?>"
                            >
                                0
                            </div>

                        </div>

                        <div class="customer-stat-icon">

                            <i class="bi bi-check-circle-fill"></i>

                        </div>

                    </div>

                </div>

            </a>

        </div>


    </div>


    <div class="customer-tools mb-4">

        <form method="GET">

            <?php if ($status !== 'All'): ?>

                <input
                    type="hidden"
                    name="status"
                    value="<?= htmlspecialchars($status) ?>"
                >

            <?php endif; ?>


            <?php if ($plan !== 'All'): ?>

                <input
                    type="hidden"
                    name="plan"
                    value="<?= htmlspecialchars($plan) ?>"
                >

            <?php endif; ?>


            <div class="row g-3 align-items-center">


                <div class="col-xl-6 col-lg-5">

                    <div class="search-box">

                        <i class="bi bi-search"></i>

                        <input
                            type="text"
                            name="q"
                            value="<?= htmlspecialchars($q) ?>"
                            placeholder="Search name, father name, mobile, Aadhaar..."
                            autocomplete="off"
                        >

                        <?php if ($q !== ''): ?>

                            <a
                                href="customers.php"
                                class="clear-filter"
                            >

                                <i class="bi bi-x-circle"></i>

                            </a>

                        <?php endif; ?>


                        <button type="submit">

                            <i class="bi bi-search me-1"></i>

                            Search

                        </button>

                    </div>

                </div>


                <div class="col-xl-6 col-lg-7">

                    <div class="filter-area">


                        <a
                            href="customers.php"
                            class="filter-btn <?= (
                                $plan === 'All' &&
                                $status === 'All'
                            ) ? 'active' : '' ?>"
                        >

                            <i class="bi bi-grid"></i>

                            All

                        </a>


                        <a
                            href="customers.php?plan=Weekly"
                            class="filter-btn <?= $plan === 'Weekly'
                                ? 'active'
                                : '' ?>"
                        >

                            <i class="bi bi-calendar-week"></i>

                            Weekly

                        </a>


                        <a
                            href="customers.php?plan=Monthly"
                            class="filter-btn <?= $plan === 'Monthly'
                                ? 'active'
                                : '' ?>"
                        >

                            <i class="bi bi-calendar-month"></i>

                            Monthly

                        </a>


                        <a
                            href="customers.php?status=Active"
                            class="filter-btn <?= $status === 'Active'
                                ? 'active'
                                : '' ?>"
                        >

                            <i class="bi bi-person-check"></i>

                            Active

                        </a>


                        <a
                            href="customers.php?status=Completed"
                            class="filter-btn <?= $status === 'Completed'
                                ? 'active'
                                : '' ?>"
                        >

                            <i class="bi bi-check-circle"></i>

                            Completed

                        </a>


                        <a
                            href="customers.php?status=Closed"
                            class="filter-btn <?= $status === 'Closed'
                                ? 'active'
                                : '' ?>"
                        >

                            <i class="bi bi-x-circle"></i>

                            Closed

                        </a>


                    </div>

                </div>


            </div>

        </form>

    </div>


    <?php if (
        $q !== '' ||
        $plan !== 'All' ||
        $status !== 'All'
    ): ?>


        <div class="mb-3">


            <?php if ($q !== ''): ?>

                <span class="active-filter">

                    <i class="bi bi-search"></i>

                    <?= htmlspecialchars($q) ?>

                    <a
                        href="customers.php"
                        class="clear-filter"
                    >

                        <i class="bi bi-x-circle"></i>

                    </a>

                </span>

            <?php endif; ?>


            <?php if ($plan !== 'All'): ?>

                <span class="active-filter ms-1">

                    <?= htmlspecialchars($plan) ?>

                    <a
                        href="customers.php"
                        class="clear-filter"
                    >

                        <i class="bi bi-x-circle"></i>

                    </a>

                </span>

            <?php endif; ?>


            <?php if ($status !== 'All'): ?>

                <span class="active-filter ms-1">

                    <?= htmlspecialchars($status) ?>

                    <a
                        href="customers.php"
                        class="clear-filter"
                    >

                        <i class="bi bi-x-circle"></i>

                    </a>

                </span>

            <?php endif; ?>


        </div>


    <?php endif; ?>


    <div class="customer-table-card">


        <div class="customer-table-header d-flex justify-content-between align-items-center">

            <div>

                <div class="customer-table-title">

                    <?= htmlspecialchars(
                        $pageHeading
                    ) ?>

                </div>

                <div class="customer-table-subtitle">

                    Showing
                    <?= $resultCount ?>
                    customers

                </div>

            </div>


            <span class="badge text-bg-success">

                <?= $resultCount ?>

            </span>

        </div>


        <?php if (
            $resultCount === 0
        ): ?>


            <div class="empty-box">

                <div class="empty-icon">

                    <i class="bi bi-person-x"></i>

                </div>


                <div class="empty-title">

                    No Customers Found

                </div>


                <div class="empty-text">

                    Is filter ya search ke andar
                    koi customer nahi mila.

                </div>

            </div>


        <?php else: ?>


            <div class="table-responsive">

                <table class="table customer-table align-middle">


                    <thead>

                        <tr>

                            <th>
                                Customer
                            </th>

                            <th>
                                Mobile
                            </th>

                            <th>
                                Plan
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Progress
                            </th>

                            <th>
                                Period
                            </th>

                            <th>
                                Next Payment
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $customers
                        as $r
                    ): ?>


                        <?php

                        $id =
                            (int)(
                                $r['id'] ?? 0
                            );

                        $name =
                            trim(
                                $r['name'] ?? ''
                            );

                        $father =
                            trim(
                                $r['father_name'] ?? ''
                            );

                        $mobile =
                            trim(
                                $r['mobile'] ?? ''
                            );

                        $planValue =
                            $r['plan'] ?? '';

                        $amount =
                            (float)(
                                $r['amount'] ?? 0
                            );

                        $total =
                            (int)(
                                $r[
                                    'total_installments'
                                ] ?? 0
                            );

                        $calc =
                            $r['_calculation'];

                        $current =
                            $calc['current'];

                        $remaining =
                            $calc['remaining'];

                        $progress =
                            $calc['progress'];

                        $nextPayment =
                            $calc['next_payment'];

                        $customerStatus =
                            $calc['status'];

                        $initial = '?';

                        if ($name !== '') {

                            $initial =
                                strtoupper(
                                    mb_substr(
                                        $name,
                                        0,
                                        1,
                                        'UTF-8'
                                    )
                                );
                        }

                        $statusClass =
                            $customerStatus ===
                            'Completed'
                                ? 'status-completed'
                                : 'status-active';

                        ?>


                        <tr>


                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    <div class="customer-avatar">

                                        <?= htmlspecialchars(
                                            $initial
                                        ) ?>

                                    </div>


                                    <div>

                                        <div class="customer-name">

                                            <?= htmlspecialchars(
                                                $name
                                            ) ?>

                                        </div>


                                        <div class="customer-meta">

                                            #<?= $id ?>

                                            <?php if (
                                                $father !== ''
                                            ): ?>

                                                ·

                                                <?= htmlspecialchars(
                                                    $father
                                                ) ?>

                                            <?php endif; ?>

                                        </div>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <?= $mobile !== ''
                                    ? htmlspecialchars(
                                        $mobile
                                    )
                                    : '—'
                                ?>

                            </td>


                            <td>

                                <?php if (
                                    $planValue ===
                                    'Weekly'
                                ): ?>

                                    <span
                                        class="plan-badge plan-weekly"
                                    >

                                        <i class="bi bi-calendar-week"></i>

                                        Weekly

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="plan-badge plan-monthly"
                                    >

                                        <i class="bi bi-calendar-month"></i>

                                        Monthly

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <strong>

                                    ₹<?= number_format(
                                        $amount,
                                        2
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <span
                                    class="progress-badge"
                                >

                                    <?= $current ?>

                                    /

                                    <?= $total ?>

                                </span>

                                <div
                                    class="customer-meta"
                                >

                                    <?= number_format(
                                        $progress,
                                        1
                                    ) ?>%

                                </div>

                            </td>


                            <td>

                                <span class="period-badge">

                                    <i
                                        class="bi <?= $planValue === 'Weekly'
                                            ? 'bi-calendar-week'
                                            : 'bi-calendar-month'
                                        ?>"
                                    ></i>

                                    <?= $planValue === 'Weekly'
                                        ? 'Week '
                                        : 'Month '
                                    ?>

                                    <?= $current ?>

                                </span>

                            </td>


                            <td>

                                <?php if (
                                    $nextPayment
                                ): ?>

                                    <?= htmlspecialchars(
                                        date(
                                            'd M Y',
                                            strtotime(
                                                $nextPayment
                                            )
                                        )
                                    ) ?>

                                <?php else: ?>

                                    <span
                                        class="status-badge status-completed"
                                    >

                                        <i
                                            class="bi bi-check-circle-fill me-1"
                                        ></i>

                                        Completed

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <span
                                    class="status-badge <?= $statusClass ?>"
                                >

                                    <?= htmlspecialchars(
                                        $customerStatus
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <div class="d-flex gap-1">


                                    <a
                                        href="customer_details.php?id=<?= $id ?>"
                                        class="action-btn view-btn"
                                        title="View Customer Details"
                                    >

                                        <i
                                            class="bi bi-eye-fill"
                                        ></i>

                                    </a>


                                    <a
                                        href="?delete=<?= $id ?>"
                                        class="action-btn delete-btn"
                                        title="Delete Customer"
                                        onclick="return confirm('Customer delete karein?')"
                                    >

                                        <i
                                            class="bi bi-trash3-fill"
                                        ></i>

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


            <div class="p-3 border-top small muted">

                Showing

                <strong>
                    <?= $resultCount ?>
                </strong>

                customers

            </div>


        <?php endif; ?>


    </div>


</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        document
            .querySelectorAll(
                '.animated-number'
            )
            .forEach(
                function(element)
                {

                    const target =
                        Number(
                            element.dataset.value
                        ) || 0;

                    const duration =
                        2200;

                    const start =
                        performance.now();

                    function animate(now)
                    {

                        const progress =
                            Math.min(
                                (
                                    now - start
                                ) /
                                duration,
                                1
                            );

                        const eased =
                            1 -
                            Math.pow(
                                1 - progress,
                                3
                            );

                        const value =
                            Math.floor(
                                target *
                                eased
                            );

                        element.textContent =
                            value.toLocaleString(
                                'en-IN'
                            );

                        if (
                            progress < 1
                        ) {

                            requestAnimationFrame(
                                animate
                            );

                        } else {

                            element.textContent =
                                target.toLocaleString(
                                    'en-IN'
                                );
                        }
                    }

                    requestAnimationFrame(
                        animate
                    );

                }
            );

    }
);

</script>


<?php include 'includes/footer.php'; ?>