<?php
require_once 'auth.php';
require_once 'config/db.php';

$page_title = 'Dashboard';

function getPaidInstallmentsForDashboard(mysqli $conn, int $customerId): array
{
    $paid = [];

    $stmt = $conn->prepare("
        SELECT installment_no, amount, payment_date
        FROM installment_payments
        WHERE customer_id = ?
        ORDER BY installment_no ASC
    ");

    if (!$stmt) {
        return $paid;
    }

    $stmt->bind_param('i', $customerId);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $no = (int)($row['installment_no'] ?? 0);

        if ($no > 0) {
            $paid[$no] = [
                'amount' => (float)($row['amount'] ?? 0),
                'payment_date' => $row['payment_date'] ?? null
            ];
        }
    }

    $stmt->close();

    return $paid;
}


function getDashboardDueDate(
    string $openingDate,
    string $plan,
    int $installmentNo
): ?string {

    if (
        empty($openingDate) ||
        $openingDate === '0000-00-00' ||
        $installmentNo < 1
    ) {
        return null;
    }

    try {

        $opening = new DateTimeImmutable($openingDate);

        if ($plan === 'Monthly') {

            $firstMonth = new DateTimeImmutable(
                $opening->format('Y-m-10')
            );

            if ($opening->format('d') > 10) {
                $firstMonth =
                    $firstMonth->modify('+1 month');
            }

            if ($installmentNo === 1) {
                return $firstMonth->format('Y-m-d');
            }

            return $firstMonth
                ->modify('+' . ($installmentNo - 1) . ' months')
                ->format('Y-m-d');
        }

        $firstMonday = $opening;

        if ($firstMonday->format('N') !== '1') {
            $firstMonday =
                $opening->modify('next monday');
        }

        if ($installmentNo === 1) {
            return $firstMonday->format('Y-m-d');
        }

        return $firstMonday
            ->modify('+' . (($installmentNo - 1) * 7) . ' days')
            ->format('Y-m-d');

    } catch (Exception $e) {
        return null;
    }
}


function calculateCustomerPeriod(
    mysqli $conn,
    array $customer
): array {

    $plan =
        $customer['plan'] ?? 'Weekly';

    $total =
        max(
            0,
            (int)($customer['total_installments'] ?? 0)
        );

    $amount =
        (float)($customer['amount'] ?? 0);

    $openingDate =
        $customer['opening_date'] ?? null;

    $storedStatus =
        $customer['status'] ?? 'Active';

    $today =
        date('Y-m-d');

    $customerId =
        (int)($customer['id'] ?? 0);

    $paid =
        getPaidInstallmentsForDashboard(
            $conn,
            $customerId
        );

    $paidCount = count($paid);

    $currentInstallment = 0;
    $currentDueDate = null;
    $nextPayment = null;

    for (
        $i = 1;
        $i <= $total;
        $i++
    ) {

        $dueDate =
            getDashboardDueDate(
                $openingDate,
                $plan,
                $i
            );

        if ($dueDate === null) {
            continue;
        }

        if (isset($paid[$i])) {
            $currentInstallment = $i;
            continue;
        }

        if ($dueDate <= $today) {
            $currentInstallment = $i;
            $currentDueDate = $dueDate;
            break;
        }

        $nextPayment = $dueDate;
        break;
    }

    if (
        $paidCount >= $total &&
        $total > 0
    ) {
        $status = 'Completed';
        $currentInstallment = $total;
        $currentDueDate = null;
        $nextPayment = null;

    } elseif ($storedStatus === 'Closed') {

        $status = 'Closed';

    } elseif ($currentDueDate !== null) {

        $status = 'Active';

    } else {

        $status = 'Active';
    }

    $remaining =
        max(
            0,
            $total - $paidCount
        );

    $progress =
        $total > 0
            ? min(
                100,
                ($paidCount / $total) * 100
            )
            : 0;

    return [
        'current' =>
            $currentInstallment,

        'paid_count' =>
            $paidCount,

        'total' =>
            $total,

        'remaining' =>
            $remaining,

        'progress' =>
            $progress,

        'amount' =>
            $amount,

        'current_due_date' =>
            $currentDueDate,

        'next_payment' =>
            $nextPayment,

        'status' =>
            $status,

        'paid' =>
            $paid
    ];
}

function getAllCustomersForDashboard(mysqli $conn): array
{
    $result = $conn->query("
        SELECT
            id,
            opening_date,
            plan,
            amount,
            total_installments,
            status
        FROM customers
    ");

    if (!$result) {
        return [];
    }

    $customers = [];

    while ($row = $result->fetch_assoc()) {
        $row['_calc'] = calculateCustomerPeriod($conn, $row);
        $customers[] = $row;
    }

    return $customers;
}

function getPlanCountFromCustomers(array $customers, string $plan): int
{
    $count = 0;

    foreach ($customers as $customer) {
        if (($customer['plan'] ?? '') === $plan) {
            $count++;
        }
    }

    return $count;
}

function getStatusCountFromCustomers(array $customers, string $status): int
{
    $count = 0;

    foreach ($customers as $customer) {
        if (($customer['_calc']['status'] ?? '') === $status) {
            $count++;
        }
    }

    return $count;
}

function getPlanProgressFromCustomers(array $customers, string $plan): array
{
    $completed = 0;
    $total = 0;

    foreach ($customers as $customer) {
        if (($customer['plan'] ?? '') !== $plan) {
            continue;
        }

        $completed += (int)($customer['_calc']['paid_count'] ?? 0);
        $total += (int)($customer['_calc']['total'] ?? 0);
    }

    $percentage = $total > 0
        ? min(100, ($completed / $total) * 100)
        : 0;

    return [
        'paid' => $completed,
        'total' => $total,
        'percentage' => $percentage
    ];
}

function getCurrentPeriodCollection(
    mysqli $conn,
    string $plan
): array {

    $today = new DateTimeImmutable(date('Y-m-d'));

    $expected = 0.0;
    $due = 0.0;

    $result = $conn->query("
        SELECT
            id,
            opening_date,
            amount,
            total_installments,
            status
        FROM customers
        WHERE plan = '" . $conn->real_escape_string($plan) . "'
    ");

    if (!$result) {
        return [
            'expected' => 0.0,
            'due' => 0.0
        ];
    }

    while ($customer = $result->fetch_assoc()) {

        if (($customer['status'] ?? 'Active') !== 'Active') {
            continue;
        }

        $customerId = (int)$customer['id'];
        $amount = (float)$customer['amount'];
        $total = (int)$customer['total_installments'];
        $openingDate = $customer['opening_date'] ?? '';

        if ($total <= 0 || $amount <= 0 || empty($openingDate)) {
            continue;
        }

        $paid = getPaidInstallmentsForDashboard(
            $conn,
            $customerId
        );

        $paidCount = count($paid);

        if ($paidCount >= $total) {
            continue;
        }

        /*
         * Only the FIRST unpaid installment is considered.
         * Future installments are never expected/due.
         */
        $firstUnpaid = $paidCount + 1;

        $dueDate = getDashboardDueDate(
            $openingDate,
            $plan,
            $firstUnpaid
        );

        if ($dueDate === null) {
            continue;
        }

        $dueDateObj = new DateTimeImmutable($dueDate);

        if ($dueDateObj <= $today) {
            $expected += $amount;
            $due += $amount;
        }
    }

    return [
        'expected' => $expected,
        'due' => $due
    ];
}

function getCurrentPeriodReceived(
    mysqli $conn,
    string $plan
): float {

    $periodStart =
        $plan === 'Weekly'
            ? date('Y-m-d', strtotime('monday this week'))
            : date('Y-m-01');

    $periodEnd =
        $plan === 'Weekly'
            ? date('Y-m-d', strtotime('sunday this week'))
            : date('Y-m-t');

    $result = $conn->query("
        SELECT
            id,
            opening_date,
            total_installments,
            status
        FROM customers
        WHERE plan = '" .
        $conn->real_escape_string($plan) . "'
    ");

    if (!$result) {
        return 0.0;
    }

    $received = 0.0;

    while ($customer = $result->fetch_assoc()) {

        if (($customer['status'] ?? 'Active') === 'Closed') {
            continue;
        }

        $customerId =
            (int)$customer['id'];

        $paid =
            getPaidInstallmentsForDashboard(
                $conn,
                $customerId
            );

        foreach ($paid as $installmentNo => $payment) {

            $dueDate =
                getDashboardDueDate(
                    $customer['opening_date'] ?? '',
                    $plan,
                    (int)$installmentNo
                );

            if (
                $dueDate !== null &&
                $dueDate >= $periodStart &&
                $dueDate <= $periodEnd
            ) {
                $received +=
                    (float)($payment['amount'] ?? 0);
            }
        }
    }

    return $received;
}

function getDueFromCustomers(array $customers, string $plan): float
{
    $today = date('Y-m-d');
    $due = 0;

    foreach ($customers as $customer) {

        if (($customer['plan'] ?? '') !== $plan) {
            continue;
        }

        if (($customer['_calc']['status'] ?? '') !== 'Active') {
            continue;
        }

        $currentDueDate =
            $customer['_calc']['current_due_date'] ?? null;

        $paidCount =
            (int)($customer['_calc']['paid_count'] ?? 0);

        $total =
            (int)($customer['_calc']['total'] ?? 0);

        if (
            $currentDueDate !== null &&
            $currentDueDate <= $today &&
            $paidCount < $total
        ) {
            $due += (float)($customer['amount'] ?? 0);
        }
    }

    return $due;
}

$dashboardCustomers = getAllCustomersForDashboard($conn);

$weeklyCustomers = getPlanCountFromCustomers(
    $dashboardCustomers,
    'Weekly'
);

$monthlyCustomers = getPlanCountFromCustomers(
    $dashboardCustomers,
    'Monthly'
);

$activeCustomers = getStatusCountFromCustomers(
    $dashboardCustomers,
    'Active'
);

$completedCustomers = getStatusCountFromCustomers(
    $dashboardCustomers,
    'Completed'
);

$weeklyProgress = getPlanProgressFromCustomers(
    $dashboardCustomers,
    'Weekly'
);

$monthlyProgress = getPlanProgressFromCustomers(
    $dashboardCustomers,
    'Monthly'
);

$weekStart = date(
    'Y-m-d',
    strtotime('monday this week')
);

$weekEnd = date(
    'Y-m-d',
    strtotime('sunday this week')
);

$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');

$weeklyCollection =
    getCurrentPeriodCollection(
        $conn,
        'Weekly'
    );

$monthlyCollection =
    getCurrentPeriodCollection(
        $conn,
        'Monthly'
    );

$weeklyExpected =
    $weeklyCollection['expected'];

$weeklyDue =
    $weeklyCollection['due'];

$monthlyExpected =
    $monthlyCollection['expected'];

$monthlyDue =
    $monthlyCollection['due'];

$weeklyReceived =
    getCurrentPeriodReceived(
        $conn,
        'Weekly'
    );

$monthlyReceived =
    getCurrentPeriodReceived(
        $conn,
        'Monthly'
    );



$weeklyCollectionProgress =
    $weeklyExpected > 0
        ? min(
            100,
            ($weeklyReceived / $weeklyExpected) * 100
        )
        : 100;

$monthlyCollectionProgress =
    $monthlyExpected > 0
        ? min(
            100,
            ($monthlyReceived / $monthlyExpected) * 100
        )
        : 100;

include 'includes/header.php';

?>





<style>

.dashboard-page {
    animation: pageIn 1.2s ease both;
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

.dashboard-card {
    display: block;
    height: 100%;
    text-decoration: none;
    color: inherit;
}

.dashboard-card:hover {
    color: inherit;
}

.dashboard-card .cardx {
    height: 100%;
    transition:
        transform .6s ease,
        box-shadow .6s ease;
}

.dashboard-card:hover .cardx {
    transform: translateY(-8px);
    box-shadow: 0 25px 55px rgba(0,0,0,.11);
}

.dashboard-card .iconbox {
    transition:
        transform .7s ease;
}

.dashboard-card:hover .iconbox {
    transform: scale(1.1) rotate(5deg);
}

.stat-card {
    opacity: 0;
    animation: statIn 1.2s ease forwards;
}

.stat-card:nth-child(1) {
    animation-delay: .15s;
}

.stat-card:nth-child(2) {
    animation-delay: .35s;
}

.stat-card:nth-child(3) {
    animation-delay: .55s;
}

@keyframes statIn {

    from {
        opacity: 0;
        transform: translateY(35px) scale(.96);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

}

.completion-card {
    position: relative;
    overflow: hidden;
    min-height: 315px;
    padding: 32px;
    border-radius: 28px;
    background: #fff;
    border: 1px solid #edf0ee;
    box-shadow: 0 12px 40px rgba(0,0,0,.055);
    transition:
        transform .7s ease,
        box-shadow .7s ease;
    animation: completionIn 1.5s ease both;
}

.completion-card:hover {
    transform: translateY(-9px);
    box-shadow: 0 28px 65px rgba(0,0,0,.12);
}

.completion-card::before {
    content: "";
    position: absolute;
    width: 280px;
    height: 280px;
    right: -145px;
    bottom: -145px;
    border-radius: 50%;
    background: #fff2f8;
    transition: transform 1.2s ease;
}

.completion-card:hover::before {
    transform: scale(1.25);
}

@keyframes completionIn {

    from {
        opacity: 0;
        transform: translateY(45px) scale(.96);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

}

.completion-top {
    position: relative;
    z-index: 3;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.completion-small {
    color: #d81b72;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 2.5px;
}

.completion-title {
    margin-top: 7px;
    color: #172019;
    font-size: 25px;
    font-weight: 900;
}

.completion-plan {
    margin-top: 4px;
    color: #9aa39d;
    font-size: 10px;
}

.completion-icon {
    width: 57px;
    height: 57px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff1f7;
    color: #d81b72;
    font-size: 22px;
    transition: transform .9s ease;
}

.completion-card:hover .completion-icon {
    transform: rotate(360deg) scale(1.08);
}

.completion-center {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    gap: 40px;
    margin-top: 28px;
}

.circle-progress {
    position: relative;
    width: 145px;
    height: 145px;
    flex-shrink: 0;
}

.circle-progress svg {
    width: 145px;
    height: 145px;
    transform: rotate(-90deg);
}

.circle-bg,
.circle-fill {
    fill: none;
    stroke-width: 8;
}

.circle-bg {
    stroke: #edf1ee;
}

.circle-fill {
    stroke: #d81b72;
    stroke-linecap: round;
    stroke-dasharray: 314.159;
    stroke-dashoffset: 314.159;
    transition:
        stroke-dashoffset 3.2s cubic-bezier(.22,1,.36,1);
}

.circle-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.circle-text strong {
    color: #172019;
    font-size: 29px;
    font-weight: 900;
}

.circle-text span {
    color: #89938d;
    font-size: 13px;
    font-weight: 900;
}

.completion-number {
    color: #172019;
    font-size: 46px;
    line-height: 1;
    font-weight: 900;
}

.completion-total {
    color: #9aa39d;
    font-size: 19px;
    font-weight: 700;
}

.completion-unit {
    margin-top: 10px;
    color: #89938d;
    font-size: 11px;
}

.collection-card {
    position: relative;
    overflow: hidden;
    min-height: 300px;
    border-radius: 25px;
    transition:
        transform .6s ease,
        box-shadow .6s ease;
}

.collection-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 25px 55px rgba(0,0,0,.10);
}

.collection-card::before {
    content: "";
    position: absolute;
    width: 200px;
    height: 200px;
    right: -100px;
    top: -100px;
    border-radius: 50%;
    background: rgba(216,27,114,.04);
}

.collection-progress {
    height: 8px;
    overflow: hidden;
    border-radius: 50px;
    background: #edf1ee;
}

.collection-progress .progress-bar {
    width: 0;
    background: #d81b72;
    transition:
        width 2.8s cubic-bezier(.22,1,.36,1);
}

.collection-percent {
    color: #d81b72;
}

.amount-green {
    color: #159957;
    font-weight: 900;
}

.amount-red {
    color: #d81b72;
    font-weight: 900;
}

@media(max-width:768px) {

    .completion-card {
        padding: 25px;
        min-height: 285px;
    }

    .completion-center {
        gap: 25px;
    }

    .circle-progress {
        width: 120px;
        height: 120px;
    }

    .circle-progress svg {
        width: 120px;
        height: 120px;
    }

    .completion-number {
        font-size: 35px;
    }

}

@media(max-width:480px) {

    .completion-center {
        gap: 18px;
    }

    .circle-progress {
        width: 105px;
        height: 105px;
    }

    .circle-progress svg {
        width: 105px;
        height: 105px;
    }

    .completion-number {
        font-size: 30px;
    }

    .completion-title {
        font-size: 20px;
    }

}

</style>


<div class="dashboard-page">

    <div class="mb-4">

        <div class="muted small">
            GULABI ALIBROSE
        </div>

        <h2 class="fw-bold mb-1">
            Collection Dashboard
        </h2>

        <div class="muted">
            Weekly and Monthly collection overview
        </div>

    </div>


    <div class="row g-4 mb-4">


        <div class="col-md-4 stat-card">

            <a
                href="customers.php?plan=Weekly"
                class="dashboard-card"
            >

                <div class="cardx p-4">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="muted small">
                                WEEKLY CUSTOMERS
                            </div>

                            <div
                                class="stat-value mt-2 animated-number"
                                data-number="<?= $weeklyCustomers ?>"
                            >
                                0
                            </div>

                            <small class="muted">
                                Weekly collection customers
                            </small>

                        </div>

                        <div class="iconbox">

                            <i class="bi bi-calendar-week"></i>

                        </div>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-md-4 stat-card">

            <a
                href="customers.php?plan=Monthly"
                class="dashboard-card"
            >

                <div class="cardx p-4">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="muted small">
                                MONTHLY CUSTOMERS
                            </div>

                            <div
                                class="stat-value mt-2 animated-number"
                                data-number="<?= $monthlyCustomers ?>"
                            >
                                0
                            </div>

                            <small class="muted">
                                Monthly collection customers
                            </small>

                        </div>

                        <div class="iconbox">

                            <i class="bi bi-calendar-month"></i>

                        </div>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-md-4 stat-card">

            <a
                href="customers.php?status=Active"
                class="dashboard-card"
            >

                <div class="cardx p-4">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="muted small">
                                ACTIVE CUSTOMERS
                            </div>

                            <div
                                class="stat-value mt-2 animated-number"
                                data-number="<?= $activeCustomers ?>"
                            >
                                0
                            </div>

                            <small class="muted">
                                Currently active customers
                            </small>

                        </div>

                        <div class="iconbox">

                            <i class="bi bi-person-check-fill"></i>

                        </div>

                    </div>

                </div>

            </a>

        </div>


    </div>


    <div class="row g-4 mb-4">


        <div class="col-lg-6">

            <div class="completion-card">

                <div class="completion-top">

                    <div>

                        <div class="completion-small">
                            WEEKLY
                        </div>

                        <div class="completion-title">
                            Weekly Completed
                        </div>

                        <div class="completion-plan">
                            52 Weeks Plan
                        </div>

                    </div>

                    <div class="completion-icon">

                        <i class="bi bi-calendar-week"></i>

                    </div>

                </div>


                <div class="completion-center">

                    <div class="circle-progress">

                        <svg viewBox="0 0 120 120">

                            <circle
                                class="circle-bg"
                                cx="60"
                                cy="60"
                                r="50"
                            ></circle>

                            <circle
                                class="circle-fill weekly-circle"
                                cx="60"
                                cy="60"
                                r="50"
                            ></circle>

                        </svg>


                        <div class="circle-text">

                            <strong
                                class="completion-percent"
                                data-value="<?= $weeklyProgress['percentage'] ?>"
                            >
                                0
                            </strong>

                            <span>
                                %
                            </span>

                        </div>

                    </div>


                    <div>

                        <div>

                            <span
                                class="completion-number completion-count"
                                data-value="<?= $weeklyProgress['paid'] ?>"
                            >
                                0
                            </span>

                            <span class="completion-total">
                                / <?= $weeklyProgress['total'] ?>
                            </span>

                        </div>

                        <div class="completion-unit">
                            Weeks Completed
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="completion-card">

                <div class="completion-top">

                    <div>

                        <div class="completion-small">
                            MONTHLY
                        </div>

                        <div class="completion-title">
                            Monthly Completed
                        </div>

                        <div class="completion-plan">
                            12 Months Plan
                        </div>

                    </div>

                    <div class="completion-icon">

                        <i class="bi bi-calendar-month"></i>

                    </div>

                </div>


                <div class="completion-center">

                    <div class="circle-progress">

                        <svg viewBox="0 0 120 120">

                            <circle
                                class="circle-bg"
                                cx="60"
                                cy="60"
                                r="50"
                            ></circle>

                            <circle
                                class="circle-fill monthly-circle"
                                cx="60"
                                cy="60"
                                r="50"
                            ></circle>

                        </svg>


                        <div class="circle-text">

                            <strong
                                class="completion-percent"
                                data-value="<?= $monthlyProgress['percentage'] ?>"
                            >
                                0
                            </strong>

                            <span>
                                %
                            </span>

                        </div>

                    </div>


                    <div>

                        <div>

                            <span
                                class="completion-number completion-count"
                                data-value="<?= $monthlyProgress['paid'] ?>"
                            >
                                0
                            </span>

                            <span class="completion-total">
                                / <?= $monthlyProgress['total'] ?>
                            </span>

                        </div>

                        <div class="completion-unit">
                            Months Completed
                        </div>

                    </div>

                </div>

            </div>

        </div>


    </div>


    <div class="row g-4">


        <div class="col-lg-6">

            <div class="cardx p-4 collection-card">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="muted small">
                            THIS WEEK
                        </div>

                        <div class="summary-number">
                            ₹<?= number_format(
                                $weeklyExpected,
                                2
                            ) ?>
                        </div>

                        <div class="muted">
                            Expected collection
                        </div>

                    </div>

                    <div class="iconbox">

                        <i class="bi bi-calendar-week"></i>

                    </div>

                </div>


                <div class="row mt-4">

                    <div class="col-6">

                        <small class="muted">
                            Received
                        </small>

                        <h4 class="amount-green">
                            ₹<?= number_format(
                                $weeklyReceived,
                                2
                            ) ?>
                        </h4>

                    </div>


                    <div class="col-6">

                        <small class="muted">
                            Due
                        </small>

                        <h4 class="amount-red">
                            ₹<?= number_format(
                                $weeklyDue,
                                2
                            ) ?>
                        </h4>

                    </div>

                </div>


                <div class="collection-progress mt-3">

                    <div
                        class="progress-bar"
                        data-progress="<?= $weeklyCollectionProgress ?>"
                    ></div>

                </div>


                <div class="d-flex justify-content-between mt-2">

                    <small class="muted">
                        Collection progress
                    </small>

                    <strong>

                        <span
                            class="collection-percent"
                            data-progress="<?= $weeklyCollectionProgress ?>"
                        >
                            0
                        </span>%

                    </strong>

                </div>


                <a
                    href="collection/collection_details.php?plan=Weekly"
                    class="btn btn-pink w-100 mt-3"
                >

                    <i class="bi bi-arrow-right-circle me-1"></i>

                    Weekly Collection

                </a>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="cardx p-4 collection-card">

                <div class="d-flex justify-content-between">

                    <div>

                        <div class="muted small">
                            THIS MONTH
                        </div>

                        <div class="summary-number">
                            ₹<?= number_format(
                                $monthlyExpected,
                                2
                            ) ?>
                        </div>

                        <div class="muted">
                            Expected collection
                        </div>

                    </div>

                    <div class="iconbox">

                        <i class="bi bi-calendar-month"></i>

                    </div>

                </div>


                <div class="row mt-4">

                    <div class="col-6">

                        <small class="muted">
                            Received
                        </small>

                        <h4 class="amount-green">
                            ₹<?= number_format(
                                $monthlyReceived,
                                2
                            ) ?>
                        </h4>

                    </div>


                    <div class="col-6">

                        <small class="muted">
                            Due
                        </small>

                        <h4 class="amount-red">
                            ₹<?= number_format(
                                $monthlyDue,
                                2
                            ) ?>
                        </h4>

                    </div>

                </div>


                <div class="collection-progress mt-3">

                    <div
                        class="progress-bar"
                        data-progress="<?= $monthlyCollectionProgress ?>"
                    ></div>

                </div>


                <div class="d-flex justify-content-between mt-2">

                    <small class="muted">
                        Collection progress
                    </small>

                    <strong>

                        <span
                            class="collection-percent"
                            data-progress="<?= $monthlyCollectionProgress ?>"
                        >
                            0
                        </span>%

                    </strong>

                </div>


                <a
                    href="collection/collection_details.php?plan=Monthly"
                    class="btn btn-pink w-100 mt-3"
                >

                    <i class="bi bi-arrow-right-circle me-1"></i>

                    Monthly Collection

                </a>

            </div>

        </div>


    </div>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        function animateNumber(
            element,
            target,
            duration
        ) {

            const start =
                performance.now();

            function update(now) {

                const elapsed =
                    now - start;

                const progress =
                    Math.min(
                        elapsed / duration,
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
                        target * eased
                    );

                element.textContent =
                    value.toLocaleString(
                        'en-IN'
                    );

                if (progress < 1) {

                    requestAnimationFrame(
                        update
                    );

                } else {

                    element.textContent =
                        target.toLocaleString(
                            'en-IN'
                        );

                }

            }

            requestAnimationFrame(
                update
            );

        }


        function animatePercent(
            element,
            target,
            duration
        ) {

            const start =
                performance.now();

            function update(now) {

                const elapsed =
                    now - start;

                const progress =
                    Math.min(
                        elapsed / duration,
                        1
                    );

                const eased =
                    1 -
                    Math.pow(
                        1 - progress,
                        3
                    );

                const value =
                    target * eased;

                element.textContent =
                    value.toFixed(1);

                if (progress < 1) {

                    requestAnimationFrame(
                        update
                    );

                } else {

                    element.textContent =
                        target.toFixed(1);

                }

            }

            requestAnimationFrame(
                update
            );

        }


        document
            .querySelectorAll(
                '.animated-number'
            )
            .forEach(
                function (element) {

                    const target =
                        Number(
                            element.dataset.number
                        ) || 0;

                    animateNumber(
                        element,
                        target,
                        2200
                    );

                }
            );


        document
            .querySelectorAll(
                '.completion-count'
            )
            .forEach(
                function (element) {

                    const target =
                        Number(
                            element.dataset.value
                        ) || 0;

                    animateNumber(
                        element,
                        target,
                        2800
                    );

                }
            );


        document
            .querySelectorAll(
                '.completion-percent'
            )
            .forEach(
                function (element) {

                    const target =
                        Number(
                            element.dataset.value
                        ) || 0;

                    animatePercent(
                        element,
                        target,
                        3000
                    );

                }
            );


        function animateCircle(
            selector,
            percentage,
            delay
        ) {

            const circle =
                document.querySelector(
                    selector
                );

            if (!circle) {
                return;
            }

            const circumference =
                2 * Math.PI * 50;

            circle.style.strokeDasharray =
                circumference;

            circle.style.strokeDashoffset =
                circumference;

            setTimeout(
                function () {

                    const safePercentage =
                        Math.max(
                            0,
                            Math.min(
                                100,
                                percentage
                            )
                        );

                    const offset =
                        circumference -
                        (
                            circumference *
                            safePercentage /
                            100
                        );

                    circle.style.strokeDashoffset =
                        offset;

                },
                delay
            );

        }


        animateCircle(
            '.weekly-circle',
            <?= $weeklyProgress['percentage'] ?>,
            600
        );


        animateCircle(
            '.monthly-circle',
            <?= $monthlyProgress['percentage'] ?>,
            900
        );


        document
            .querySelectorAll(
                '.collection-progress .progress-bar'
            )
            .forEach(
                function (bar) {

                    const target =
                        Number(
                            bar.dataset.progress
                        ) || 0;

                    setTimeout(
                        function () {

                            bar.style.width =
                                Math.min(
                                    100,
                                    target
                                ) + '%';

                        },
                        700
                    );

                }
            );


        document
            .querySelectorAll(
                '.collection-percent'
            )
            .forEach(
                function (element) {

                    const target =
                        Number(
                            element.dataset.progress
                        ) || 0;

                    animatePercent(
                        element,
                        target,
                        2600
                    );

                }
            );

    }
);

</script>


<?php include 'includes/footer.php'; ?>