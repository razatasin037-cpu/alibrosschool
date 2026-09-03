<?php

date_default_timezone_set('Asia/Kolkata');

require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Dashboard';

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

$today = new DateTime(
    'today',
    new DateTimeZone('Asia/Kolkata')
);

$todayDate = $today->format('Y-m-d');
$currentMonth = $today->format('Y-m');
$currentWeek = $today->format('o-W');

$customers = [];
$payments = [];

$customerResult = $conn->query("
    SELECT
        id,
        name,
        phone,
        opening_date,
        plan,
        amount,
        paid_installments,
        total_installments,
        status
    FROM customers
    ORDER BY id DESC
");

while ($row = $customerResult->fetch_assoc()) {
    $customers[] = $row;
}

$paymentResult = $conn->query("
    SELECT
        id,
        customer_id,
        installment_no,
        amount,
        payment_date,
        due_date
    FROM installment_payments
    ORDER BY id ASC
");

while ($row = $paymentResult->fetch_assoc()) {
    $customerId = (int)$row['customer_id'];
    $installmentNo = (int)$row['installment_no'];

    $payments[$customerId][$installmentNo] = $row;
}

$totalCustomers = count($customers);

$monthlyCustomers = 0;
$weeklyCustomers = 0;

$monthlyAllTimeCollection = 0;
$weeklyAllTimeCollection = 0;

$todayMonthlyCollection = 0;
$todayWeeklyCollection = 0;

$thisMonthMonthlyCollection = 0;
$thisWeekWeeklyCollection = 0;

$monthlyReceived = 0;
$weeklyReceived = 0;

$monthlyDue = 0;
$weeklyDue = 0;

$monthlyExpected = 0;
$weeklyExpected = 0;

$monthlyDueInstallments = 0;
$weeklyDueInstallments = 0;

$monthlyPaidInstallments = 0;
$weeklyPaidInstallments = 0;

$monthlyTotalInstallments = 0;
$weeklyTotalInstallments = 0;

$monthlyDuePayments = [];
$weeklyDuePayments = [];

foreach ($customers as $customer) {

    $customerId = (int)$customer['id'];
    $plan = $customer['plan'];
    $amount = (float)$customer['amount'];
    $openingDate = $customer['opening_date'];

    $totalInstallments =
        $plan === 'Monthly'
        ? 12
        : 52;

    if ($plan === 'Monthly') {
        $monthlyCustomers++;
        $monthlyTotalInstallments += $totalInstallments;
    } else {
        $weeklyCustomers++;
        $weeklyTotalInstallments += $totalInstallments;
    }

    if (isset($payments[$customerId])) {

        foreach ($payments[$customerId] as $payment) {

            $paymentAmount = (float)$payment['amount'];
            $paymentDate = $payment['payment_date'];

            if ($plan === 'Monthly') {

                $monthlyAllTimeCollection += $paymentAmount;
                $monthlyPaidInstallments++;

                if ($paymentDate === $todayDate) {
                    $todayMonthlyCollection += $paymentAmount;
                }

            } else {

                $weeklyAllTimeCollection += $paymentAmount;
                $weeklyPaidInstallments++;

                if ($paymentDate === $todayDate) {
                    $todayWeeklyCollection += $paymentAmount;
                }
            }
        }
    }

    if (empty($openingDate)) {
        continue;
    }

    $currentInstallmentNo = null;
    $currentDueDate = null;

    for (
        $installmentNo = 1;
        $installmentNo <= $totalInstallments;
        $installmentNo++
    ) {

        $dueDate = getInstallmentDueDate(
            $openingDate,
            $plan,
            $installmentNo
        );

        if ($plan === 'Monthly') {

            if (
                date('Y-m', strtotime($dueDate))
                === $currentMonth
            ) {
                $currentInstallmentNo = $installmentNo;
                $currentDueDate = $dueDate;
                break;
            }

        } else {

            if (
                date('o-W', strtotime($dueDate))
                === $currentWeek
            ) {
                $currentInstallmentNo = $installmentNo;
                $currentDueDate = $dueDate;
                break;
            }
        }
    }

    if (
        $currentInstallmentNo === null ||
        $currentDueDate === null
    ) {
        continue;
    }

    $currentPayment =
        $payments[$customerId][$currentInstallmentNo]
        ?? null;

    if ($plan === 'Monthly') {

        $monthlyExpected += $amount;

        if ($currentPayment !== null) {

            $monthlyReceived +=
                (float)$currentPayment['amount'];

        } else {

            $monthlyDue += $amount;
            $monthlyDueInstallments++;

            $monthlyDuePayments[] = [
                'customer_id' => $customerId,
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'plan' => $plan,
                'installment_no' => $currentInstallmentNo,
                'amount' => $amount,
                'due_date' => $currentDueDate
            ];
        }

    } else {

        $weeklyExpected += $amount;

        if ($currentPayment !== null) {

            $weeklyReceived +=
                (float)$currentPayment['amount'];

        } else {

            $weeklyDue += $amount;
            $weeklyDueInstallments++;

            $weeklyDuePayments[] = [
                'customer_id' => $customerId,
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'plan' => $plan,
                'installment_no' => $currentInstallmentNo,
                'amount' => $amount,
                'due_date' => $currentDueDate
            ];
        }
    }
}

$thisMonthMonthlyCollection = $monthlyReceived;
$thisWeekWeeklyCollection = $weeklyReceived;

$monthlyProgress = 0;

if ($monthlyExpected > 0) {
    $monthlyProgress =
        ($monthlyReceived / $monthlyExpected) * 100;
}

$monthlyProgress =
    min(
        100,
        max(0, $monthlyProgress)
    );

$weeklyProgress = 0;

if ($weeklyExpected > 0) {
    $weeklyProgress =
        ($weeklyReceived / $weeklyExpected) * 100;
}

$weeklyProgress =
    min(
        100,
        max(0, $weeklyProgress)
    );

$todayTotalCollection =
    $todayMonthlyCollection +
    $todayWeeklyCollection;

$recentPayments = $conn->query("
    SELECT
        ip.id,
        ip.amount,
        ip.installment_no,
        ip.payment_date,
        ip.due_date,
        c.name,
        c.phone,
        c.plan
    FROM installment_payments ip
    INNER JOIN customers c
        ON c.id = ip.customer_id
    ORDER BY ip.id DESC
    LIMIT 10
");

$paymentSuccess = '';
$paymentError = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['dashboard_pay_multiple'])
) {

    $selectedDue =
        $_POST['selected_due'] ?? [];

    if (!is_array($selectedDue)) {
        $selectedDue = [];
    }

    $selectedDue = array_values(
        array_unique($selectedDue)
    );

    if (empty($selectedDue)) {

        $paymentError =
            'Kam se kam ek due select karein.';

    } else {

        $successCount = 0;
        $successAmount = 0;

        $conn->begin_transaction();

        try {

            foreach ($selectedDue as $selected) {

                $parts =
                    explode(
                        ':',
                        (string)$selected
                    );

                $customerId =
                    (int)($parts[0] ?? 0);

                $installmentNo =
                    (int)($parts[1] ?? 0);

                if (
                    $customerId <= 0 ||
                    $installmentNo <= 0
                ) {
                    continue;
                }

                $customerStmt =
                    $conn->prepare("
                        SELECT
                            id,
                            name,
                            opening_date,
                            plan,
                            amount
                        FROM customers
                        WHERE id = ?
                        LIMIT 1
                    ");

                $customerStmt->bind_param(
                    'i',
                    $customerId
                );

                $customerStmt->execute();

                $customer =
                    $customerStmt
                    ->get_result()
                    ->fetch_assoc();

                if (!$customer) {
                    continue;
                }

                $totalInstallments =
                    $customer['plan'] === 'Monthly'
                    ? 12
                    : 52;

                if (
                    $installmentNo < 1 ||
                    $installmentNo > $totalInstallments
                ) {
                    continue;
                }

                $dueDate =
                    getInstallmentDueDate(
                        $customer['opening_date'],
                        $customer['plan'],
                        $installmentNo
                    );

                if ($dueDate > $todayDate) {
                    continue;
                }

                if (
                    $customer['plan'] === 'Monthly' &&
                    date('Y-m', strtotime($dueDate))
                    !== $currentMonth
                ) {
                    continue;
                }

                if (
                    $customer['plan'] === 'Weekly' &&
                    date('o-W', strtotime($dueDate))
                    !== $currentWeek
                ) {
                    continue;
                }

                $check =
                    $conn->prepare("
                        SELECT id
                        FROM installment_payments
                        WHERE customer_id = ?
                        AND installment_no = ?
                        LIMIT 1
                    ");

                $check->bind_param(
                    'ii',
                    $customerId,
                    $installmentNo
                );

                $check->execute();

                if (
                    $check
                    ->get_result()
                    ->num_rows > 0
                ) {
                    continue;
                }

                $amount =
                    (float)$customer['amount'];

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

                $insert->bind_param(
                    'iidss',
                    $customerId,
                    $installmentNo,
                    $amount,
                    $todayDate,
                    $dueDate
                );

                if (!$insert->execute()) {
                    throw new Exception(
                        'Payment save nahi ho saka.'
                    );
                }

                $countStmt =
                    $conn->prepare("
                        SELECT
                            COUNT(DISTINCT installment_no) AS paid
                        FROM installment_payments
                        WHERE customer_id = ?
                    ");

                $countStmt->bind_param(
                    'i',
                    $customerId
                );

                $countStmt->execute();

                $paidCount =
                    (int)$countStmt
                    ->get_result()
                    ->fetch_assoc()['paid'];

                $nextDueDate = null;

                for (
                    $next = 1;
                    $next <= $totalInstallments;
                    $next++
                ) {

                    $nextCheck =
                        $conn->prepare("
                            SELECT id
                            FROM installment_payments
                            WHERE customer_id = ?
                            AND installment_no = ?
                            LIMIT 1
                        ");

                    $nextCheck->bind_param(
                        'ii',
                        $customerId,
                        $next
                    );

                    $nextCheck->execute();

                    if (
                        $nextCheck
                        ->get_result()
                        ->num_rows === 0
                    ) {

                        $nextDueDate =
                            getInstallmentDueDate(
                                $customer['opening_date'],
                                $customer['plan'],
                                $next
                            );

                        break;
                    }
                }

                if (
                    $paidCount >=
                    $totalInstallments
                ) {

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
                        'iii',
                        $totalInstallments,
                        $paidCount,
                        $customerId
                    );

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
                        'iisi',
                        $totalInstallments,
                        $paidCount,
                        $nextDueDate,
                        $customerId
                    );
                }

                if (!$update->execute()) {
                    throw new Exception(
                        'Customer progress update nahi ho saka.'
                    );
                }

                $successCount++;
                $successAmount += $amount;
            }

            $conn->commit();

            if ($successCount > 0) {

                $paymentSuccess =
                    $successCount .
                    ' payment(s) receive ho gaya. Total ₹' .
                    number_format(
                        $successAmount,
                        2
                    ) .
                    '.';

            } else {

                $paymentError =
                    'Selected due already paid hai ya current period ka due nahi hai.';
            }

        } catch (Throwable $e) {

            $conn->rollback();

            $paymentError =
                $e->getMessage();
        }
    }

    header(
        'Location: dashboard.php?payment_status=' .
        (
            $paymentSuccess !== ''
            ? 'success'
            : 'error'
        ) .
        '&msg=' .
        urlencode(
            $paymentSuccess !== ''
            ? $paymentSuccess
            : $paymentError
        )
    );

    exit;
}

if (isset($_GET['payment_status'])) {

    if ($_GET['payment_status'] === 'success') {
        $paymentSuccess =
            $_GET['msg'] ?? 'Payment successful.';
    } else {
        $paymentError =
            $_GET['msg'] ?? 'Payment failed.';
    }
}

include 'includes/header.php';

?>
<style>

.dashboard-title{
    font-size:29px;
    font-weight:850;
    letter-spacing:-.8px;
}

.dashboard-subtitle{
    color:#6b756e;
    font-size:13px;
}

.stat-card{
    background:#fff;
    border:1px solid #e7ece9;
    border-radius:20px;
    padding:22px;
    height:100%;
    box-shadow:0 10px 30px rgba(9,18,12,.055);
    position:relative;
    overflow:hidden;
    transition:.25s;
}

.stat-card:hover{
    transform:translateY(-3px);
    box-shadow:0 16px 38px rgba(9,18,12,.09);
}

.stat-card:after{
    content:"";
    position:absolute;
    width:120px;
    height:120px;
    border-radius:50%;
    right:-55px;
    top:-55px;
    background:#eaf8ef;
}

.stat-icon{
    width:50px;
    height:50px;
    border-radius:15px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#eaf8ef;
    color:#16a34a;
    font-size:22px;
    position:relative;
    z-index:1;
}

.stat-label{
    color:#6f7a73;
    font-size:11px;
    font-weight:650;
}

.stat-value{
    font-size:25px;
    font-weight:850;
    margin-top:6px;
}

.stat-note{
    color:#87918b;
    font-size:10px;
    margin-top:4px;
}

.today-card{
    background:
        linear-gradient(
            135deg,
            #050806,
            #0d2015,
            #166534
        );
    color:#fff;
    border:0;
}

.today-card:after{
    background:rgba(34,197,94,.10);
}

.today-card .stat-label{
    color:#a9c3b0;
}

.today-card .stat-note{
    color:#8fa99a;
}

.today-card .stat-value{
    color:#fff;
}

.today-card .stat-icon{
    background:rgba(255,255,255,.09);
    color:#4ade80;
}

.collection-card{
    background:#fff;
    border:1px solid #e7ece9;
    border-radius:22px;
    padding:25px;
    height:100%;
    box-shadow:0 10px 30px rgba(9,18,12,.055);
}

.collection-card-dark{
    background:
        linear-gradient(
            135deg,
            #050806,
            #0b1810,
            #14532d
        );
    border:0;
    color:#fff;
    box-shadow:0 15px 35px rgba(5,20,10,.13);
}

.collection-title{
    font-size:18px;
    font-weight:850;
}

.collection-subtitle{
    color:#78837c;
    font-size:11px;
}

.collection-card-dark
.collection-subtitle{
    color:#9db5a5;
}

.big-amount{
    font-size:31px;
    font-weight:900;
    margin-top:18px;
}

.green-amount{
    color:#15803d;
}

.light-green{
    color:#4ade80;
}

.info-box{
    background:#f7faf8;
    border:1px solid #edf2ee;
    border-radius:15px;
    padding:15px;
}

.collection-card-dark
.info-box{
    background:rgba(255,255,255,.06);
    border-color:rgba(255,255,255,.08);
}

.info-label{
    color:#738078;
    font-size:10px;
}

.collection-card-dark
.info-label{
    color:#98afa0;
}

.info-value{
    font-size:18px;
    font-weight:800;
    margin-top:5px;
}

.info-value.due{
    color:#dc2626;
}

.collection-card-dark
.info-value.due{
    color:#fca5a5;
}

.info-value.success{
    color:#15803d;
}

.collection-card-dark
.info-value.success{
    color:#4ade80;
}

.progress-wrap{
    height:8px;
    background:#e8efea;
    border-radius:20px;
    overflow:hidden;
}

.collection-card-dark
.progress-wrap{
    background:rgba(255,255,255,.10);
}

.progress-fill{
    height:100%;
    border-radius:20px;
    background:
        linear-gradient(
            90deg,
            #15803d,
            #22c55e
        );
}

.collection-card-dark
.progress-fill{
    background:#4ade80;
}

.section-card{
    background:#fff;
    border:1px solid #e7ece9;
    border-radius:22px;
    padding:25px;
    box-shadow:0 10px 30px rgba(9,18,12,.05);
}

.section-title{
    font-size:17px;
    font-weight:850;
}

.section-subtitle{
    color:#78837c;
    font-size:11px;
}

.payment-table{
    width:100%;
}

.payment-table th{
    color:#738078;
    font-size:11px;
    font-weight:750;
    padding:13px 10px;
    border-bottom:1px solid #edf1ee;
}

.payment-table td{
    padding:14px 10px;
    font-size:12px;
    border-bottom:1px solid #f0f3f1;
}

.payment-table tr:last-child td{
    border-bottom:0;
}

.avatar-small{
    width:36px;
    height:36px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:#dcfce7;
    color:#15803d;
    font-weight:800;
    margin-right:8px;
}

.plan-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:5px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:750;
}

.monthly-badge{
    background:#dcfce7;
    color:#15803d;
}

.weekly-badge{
    background:#d1fae5;
    color:#047857;
}


.collection-clickable{
    cursor:pointer;
    transition:.25s ease;
}

.collection-clickable:hover{
    transform:translateY(-4px);
    box-shadow:0 18px 40px rgba(9,18,12,.10);
}

.due-modal .modal-content{
    border:0;
    border-radius:22px;
    overflow:hidden;
}

.due-modal .modal-header{
    background:linear-gradient(135deg,#050806,#102318,#166534);
    color:#fff;
    border:0;
    padding:20px 22px;
}

.due-modal .modal-title{
    font-weight:900;
}

.due-modal .modal-subtitle{
    color:#a8b9ad;
    font-size:10px;
}

.bulk-bar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:12px;
    background:#f6faf7;
    border:1px solid #e5eee8;
    border-radius:14px;
    margin-bottom:12px;
}

.bulk-left{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:10px;
    font-weight:800;
}

.bulk-left input,
.due-check input{
    width:18px;
    height:18px;
    accent-color:#16a34a;
}

.bulk-summary{
    display:flex;
    align-items:center;
    gap:12px;
    font-size:9px;
    color:#66736b;
}

.bulk-summary strong{
    color:#dc2626;
    font-size:13px;
}

.bulk-pay{
    border:0;
    background:#16a34a;
    color:#fff;
    border-radius:10px;
    padding:9px 13px;
    font-size:9px;
    font-weight:850;
}

.bulk-pay:disabled{
    opacity:.45;
}

.due-row{
    display:grid;
    grid-template-columns:30px minmax(180px,1.7fr) 1fr 1fr auto;
    gap:12px;
    align-items:center;
    padding:12px;
    border:1px solid #e8eee9;
    border-radius:14px;
    margin-bottom:9px;
    background:#fff;
}

.due-row.selected{
    background:#f0fdf4;
    border-color:#86efac;
}

.due-customer{
    display:flex;
    align-items:center;
    gap:9px;
}

.due-avatar{
    width:38px;
    height:38px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#dcfce7;
    color:#15803d;
    font-weight:900;
}

.due-name{
    font-size:11px;
    font-weight:900;
}

.due-meta,
.due-label{
    color:#87918b;
    font-size:8px;
}

.due-value{
    font-size:10px;
    font-weight:800;
}

.due-amount{
    color:#dc2626;
    font-size:13px;
    font-weight:900;
}

.single-pay{
    border:0;
    background:#15803d;
    color:#fff;
    border-radius:9px;
    padding:9px 12px;
    font-size:9px;
    font-weight:850;
    white-space:nowrap;
}

.due-empty{
    padding:45px 20px;
    text-align:center;
    color:#6b756e;
}

.due-empty i{
    display:block;
    color:#16a34a;
    font-size:32px;
    margin-bottom:8px;
}

@media(max-width:767px){
    .due-row{
        grid-template-columns:30px 1fr;
    }

    .due-customer{
        grid-column:2;
    }

    .due-row .due-box{
        grid-column:2;
    }

    .due-row .single-pay{
        grid-column:2;
        width:100%;
    }

    .bulk-bar{
        flex-wrap:wrap;
    }

    .bulk-summary{
        width:100%;
        justify-content:space-between;
        order:3;
    }
}

@media(max-width:767px){

    .dashboard-title{
        font-size:24px;
    }

    .big-amount{
        font-size:25px;
    }

}

</style>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <div class="dashboard-title">
            Dashboard
        </div>

        <div class="dashboard-subtitle">
            Monthly & Weekly collection overview
        </div>

    </div>

    <div class="text-end">

        <div class="small muted">
            Today
        </div>

        <strong>
            <?= date('d M Y') ?>
        </strong>

    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        TOTAL CUSTOMERS
                    </div>

                    <div class="stat-value">
                        <?= number_format(
                            $totalCustomers
                        ) ?>
                    </div>

                    <div class="stat-note">
                        All customers
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        MONTHLY CUSTOMERS
                    </div>

                    <div class="stat-value">
                        <?= number_format(
                            $monthlyCustomers
                        ) ?>
                    </div>

                    <div class="stat-note">
                        Monthly plan only
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-calendar-month-fill"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        WEEKLY CUSTOMERS
                    </div>

                    <div class="stat-value">
                        <?= number_format(
                            $weeklyCustomers
                        ) ?>
                    </div>

                    <div class="stat-note">
                        Weekly plan only
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-calendar-week-fill"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        TODAY TOTAL
                    </div>

                    <div class="stat-value">
                        ₹<?= number_format(
                            $todayTotalCollection,
                            2
                        ) ?>
                    </div>

                    <div class="stat-note">
                        <?= date('d M Y') ?>
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

            </div>

        </div>

    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-xl-3 col-md-6">

        <div class="stat-card today-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        TODAY MONTHLY
                    </div>

                    <div class="stat-value">
                        ₹<?= number_format(
                            $todayMonthlyCollection,
                            2
                        ) ?>
                    </div>

                    <div class="stat-note">
                        Monthly payments today
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-calendar-month-fill"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="stat-card today-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        TODAY WEEKLY
                    </div>

                    <div class="stat-value">
                        ₹<?= number_format(
                            $todayWeeklyCollection,
                            2
                        ) ?>
                    </div>

                    <div class="stat-note">
                        Weekly payments today
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-calendar-week-fill"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        THIS MONTH MONTHLY
                    </div>

                    <div class="stat-value">
                        ₹<?= number_format(
                            $thisMonthMonthlyCollection,
                            2
                        ) ?>
                    </div>

                    <div class="stat-note">
                        <?= date('F Y') ?>
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        THIS WEEK WEEKLY
                    </div>

                    <div class="stat-value">
                        ₹<?= number_format(
                            $thisWeekWeeklyCollection,
                            2
                        ) ?>
                    </div>

                    <div class="stat-note">
                        Current week
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>

            </div>

        </div>

    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-xl-6">

        <div class="collection-card collection-clickable" onclick="openDueModal('Monthly')" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){openDueModal('Monthly');}">

            <div class="d-flex justify-content-between align-items-start">

                <div>

                    <div class="collection-title">
                        Monthly Collection
                    </div>

                    <div class="collection-subtitle">
                        Only Monthly customers · <?= date('F Y') ?>
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-calendar-month-fill"></i>
                </div>

            </div>

            <div class="big-amount green-amount">

                ₹<?= number_format(
                    $thisMonthMonthlyCollection,
                    2
                ) ?>

            </div>

            <div class="collection-subtitle">
                Current month received collection
            </div>

            <div class="row g-3 mt-3">

                <div class="col-4">

                    <div class="info-box">

                        <div class="info-label">
                            EXPECTED
                        </div>

                        <div class="info-value">

                            ₹<?= number_format(
                                $monthlyExpected,
                                2
                            ) ?>

                        </div>

                    </div>

                </div>

                <div class="col-4">

                    <div class="info-box">

                        <div class="info-label">
                            RECEIVED
                        </div>

                        <div class="info-value success">

                            ₹<?= number_format(
                                $monthlyReceived,
                                2
                            ) ?>

                        </div>

                    </div>

                </div>

                <div class="col-4">

                    <div class="info-box">

                        <div class="info-label">
                            DUE
                        </div>

                        <div class="info-value due">

                            ₹<?= number_format(
                                $monthlyDue,
                                2
                            ) ?>

                        </div>

                    </div>

                </div>

            </div>

            <div class="mt-4">

                <div class="d-flex justify-content-between mb-2">

                    <small class="muted">
                        Monthly Progress
                    </small>

                    <small class="fw-bold">

                        <?= number_format(
                            $monthlyProgress,
                            1
                        ) ?>%

                    </small>

                </div>

                <div class="progress-wrap">

                    <div
                        class="progress-fill"
                        style="width:<?= $monthlyProgress ?>%"
                    ></div>

                </div>

            </div>

            <div class="d-flex justify-content-between mt-3">

                <small class="muted">
                    Due Installments
                </small>

                <strong>
                    <?= $monthlyDueInstallments ?>
                </strong>

            </div>

        </div>

    </div>

    <div class="col-xl-6">

        <div class="collection-card collection-card-dark collection-clickable" onclick="openDueModal('Weekly')" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){openDueModal('Weekly');}">

            <div class="d-flex justify-content-between align-items-start">

                <div>

                    <div class="collection-title">
                        Weekly Collection
                    </div>

                    <div class="collection-subtitle">
                        Only Weekly customers · Current week
                    </div>

                </div>

                <div
                    class="stat-icon"
                    style="
                    background:rgba(255,255,255,.08);
                    color:#4ade80;
                    "
                >
                    <i class="bi bi-calendar-week-fill"></i>
                </div>

            </div>

            <div class="big-amount light-green">

                ₹<?= number_format(
                    $thisWeekWeeklyCollection,
                    2
                ) ?>

            </div>

            <div class="collection-subtitle">
                Current week received collection
            </div>

            <div class="row g-3 mt-3">

                <div class="col-4">

                    <div class="info-box">

                        <div class="info-label">
                            EXPECTED
                        </div>

                        <div class="info-value">

                            ₹<?= number_format(
                                $weeklyExpected,
                                2
                            ) ?>

                        </div>

                    </div>

                </div>

                <div class="col-4">

                    <div class="info-box">

                        <div class="info-label">
                            RECEIVED
                        </div>

                        <div class="info-value success">

                            ₹<?= number_format(
                                $weeklyReceived,
                                2
                            ) ?>

                        </div>

                    </div>

                </div>

                <div class="col-4">

                    <div class="info-box">

                        <div class="info-label">
                            DUE
                        </div>

                        <div class="info-value due">

                            ₹<?= number_format(
                                $weeklyDue,
                                2
                            ) ?>

                        </div>

                    </div>

                </div>

            </div>

            <div class="mt-4">

                <div class="d-flex justify-content-between mb-2">

                    <small class="collection-subtitle">
                        Weekly Progress
                    </small>

                    <small
                        style="
                        color:#fff;
                        font-weight:700;
                        "
                    >
                        <?= number_format(
                            $weeklyProgress,
                            1
                        ) ?>%
                    </small>

                </div>

                <div class="progress-wrap">

                    <div
                        class="progress-fill"
                        style="width:<?= $weeklyProgress ?>%"
                    ></div>

                </div>

            </div>

            <div class="d-flex justify-content-between mt-3">

                <small class="collection-subtitle">
                    Due Installments
                </small>

                <strong>
                    <?= $weeklyDueInstallments ?>
                </strong>

            </div>

        </div>

    </div>

</div>

<div class="row g-4 mb-4">

    <div class="col-xl-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        MONTHLY PAID INSTALLMENTS
                    </div>

                    <div class="stat-value">

                        <?= $monthlyPaidInstallments ?>

                        /

                        <?= $monthlyTotalInstallments ?>

                    </div>

                    <div class="stat-note">
                        Monthly payment records
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-check2-circle"></i>
                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-6">

        <div class="stat-card">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="stat-label">
                        WEEKLY PAID INSTALLMENTS
                    </div>

                    <div class="stat-value">

                        <?= $weeklyPaidInstallments ?>

                        /

                        <?= $weeklyTotalInstallments ?>

                    </div>

                    <div class="stat-note">
                        Weekly payment records
                    </div>

                </div>

                <div class="stat-icon">
                    <i class="bi bi-check2-circle"></i>
                </div>

            </div>

        </div>

    </div>

</div>


<div
    class="modal fade due-modal"
    id="dueCollectionModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <div class="small text-uppercase fw-bold" style="color:#4ade80;letter-spacing:1.4px;">
                        Collection Due
                    </div>
                    <h5 class="modal-title mt-1" id="dueModalTitle">
                        Weekly Due Payments
                    </h5>
                    <div class="modal-subtitle" id="dueModalSubtitle">
                        Current week ke unpaid dues
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                ></button>
            </div>

            <div class="modal-body">

                <div
                    id="weeklyDueContent"
                    class="due-content"
                >

                    <?php if (!empty($weeklyDuePayments)): ?>

                        <form method="POST">

                            <div class="bulk-bar">

                                <label class="bulk-left">
                                    <input
                                        type="checkbox"
                                        id="selectAllWeekly"
                                        onchange="toggleAllDue('Weekly',this.checked)"
                                    >
                                    Select All
                                </label>

                                <div class="bulk-summary">
                                    <span id="weeklySelectedCount">
                                        0 selected
                                    </span>

                                    <strong id="weeklySelectedAmount">
                                        ₹0.00
                                    </strong>

                                    <button
                                        type="submit"
                                        name="dashboard_pay_multiple"
                                        value="1"
                                        class="bulk-pay"
                                        id="weeklyBulkPay"
                                        disabled
                                        onclick="return confirmBulk('Weekly')"
                                    >
                                        <i class="bi bi-check2-all me-1"></i>
                                        Pay Selected
                                    </button>
                                </div>

                            </div>

                            <?php foreach ($weeklyDuePayments as $due): ?>

                                <div class="due-row">

                                    <label class="due-check">
                                        <input
                                            type="checkbox"
                                            name="selected_due[]"
                                            value="<?= (int)$due['customer_id'] ?>:<?= (int)$due['installment_no'] ?>"
                                            class="weekly-due"
                                            data-amount="<?= (float)$due['amount'] ?>"
                                            onchange="updateDueSummary('Weekly')"
                                        >
                                    </label>

                                    <div class="due-customer">

                                        <div class="due-avatar">
                                            <?= strtoupper(
                                                substr(
                                                    $due['name'],
                                                    0,
                                                    1
                                                )
                                            ) ?>
                                        </div>

                                        <div>
                                            <div class="due-name">
                                                <?= htmlspecialchars($due['name']) ?>
                                            </div>

                                            <div class="due-meta">
                                                <?= htmlspecialchars($due['phone']) ?>
                                                · Installment #<?= (int)$due['installment_no'] ?>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="due-box">
                                        <div class="due-label">
                                            DUE DATE
                                        </div>
                                        <div class="due-value">
                                            <?= date(
                                                'd M Y',
                                                strtotime($due['due_date'])
                                            ) ?>
                                        </div>
                                    </div>

                                    <div class="due-box">
                                        <div class="due-label">
                                            DUE AMOUNT
                                        </div>
                                        <div class="due-amount">
                                            ₹<?= number_format(
                                                $due['amount'],
                                                2
                                            ) ?>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="single-pay"
                                        onclick="payOneDue(
                                            <?= (int)$due['customer_id'] ?>,
                                            <?= (int)$due['installment_no'] ?>,
                                            '<?= htmlspecialchars($due['name'],ENT_QUOTES) ?>',
                                            <?= (float)$due['amount'] ?>
                                        )"
                                    >
                                        Pay Now
                                    </button>

                                </div>

                            <?php endforeach; ?>

                        </form>

                    <?php else: ?>

                        <div class="due-empty">
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>Weekly Due Complete</strong>
                            <div class="small mt-1">
                                Is week ka koi unpaid due nahi hai.
                            </div>
                        </div>

                    <?php endif; ?>

                </div>

                <div
                    id="monthlyDueContent"
                    class="due-content d-none"
                >

                    <?php if (!empty($monthlyDuePayments)): ?>

                        <form method="POST">

                            <div class="bulk-bar">

                                <label class="bulk-left">
                                    <input
                                        type="checkbox"
                                        id="selectAllMonthly"
                                        onchange="toggleAllDue('Monthly',this.checked)"
                                    >
                                    Select All
                                </label>

                                <div class="bulk-summary">
                                    <span id="monthlySelectedCount">
                                        0 selected
                                    </span>

                                    <strong id="monthlySelectedAmount">
                                        ₹0.00
                                    </strong>

                                    <button
                                        type="submit"
                                        name="dashboard_pay_multiple"
                                        value="1"
                                        class="bulk-pay"
                                        id="monthlyBulkPay"
                                        disabled
                                        onclick="return confirmBulk('Monthly')"
                                    >
                                        <i class="bi bi-check2-all me-1"></i>
                                        Pay Selected
                                    </button>
                                </div>

                            </div>

                            <?php foreach ($monthlyDuePayments as $due): ?>

                                <div class="due-row">

                                    <label class="due-check">
                                        <input
                                            type="checkbox"
                                            name="selected_due[]"
                                            value="<?= (int)$due['customer_id'] ?>:<?= (int)$due['installment_no'] ?>"
                                            class="monthly-due"
                                            data-amount="<?= (float)$due['amount'] ?>"
                                            onchange="updateDueSummary('Monthly')"
                                        >
                                    </label>

                                    <div class="due-customer">

                                        <div class="due-avatar">
                                            <?= strtoupper(
                                                substr(
                                                    $due['name'],
                                                    0,
                                                    1
                                                )
                                            ) ?>
                                        </div>

                                        <div>
                                            <div class="due-name">
                                                <?= htmlspecialchars($due['name']) ?>
                                            </div>

                                            <div class="due-meta">
                                                <?= htmlspecialchars($due['phone']) ?>
                                                · Installment #<?= (int)$due['installment_no'] ?>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="due-box">
                                        <div class="due-label">
                                            DUE DATE
                                        </div>
                                        <div class="due-value">
                                            <?= date(
                                                'd M Y',
                                                strtotime($due['due_date'])
                                            ) ?>
                                        </div>
                                    </div>

                                    <div class="due-box">
                                        <div class="due-label">
                                            DUE AMOUNT
                                        </div>
                                        <div class="due-amount">
                                            ₹<?= number_format(
                                                $due['amount'],
                                                2
                                            ) ?>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="single-pay"
                                        onclick="payOneDue(
                                            <?= (int)$due['customer_id'] ?>,
                                            <?= (int)$due['installment_no'] ?>,
                                            '<?= htmlspecialchars($due['name'],ENT_QUOTES) ?>',
                                            <?= (float)$due['amount'] ?>
                                        )"
                                    >
                                        Pay Now
                                    </button>

                                </div>

                            <?php endforeach; ?>

                        </form>

                    <?php else: ?>

                        <div class="due-empty">
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>Monthly Due Complete</strong>
                            <div class="small mt-1">
                                Is month ka koi unpaid due nahi hai.
                            </div>
                        </div>

                    <?php endif; ?>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
function openDueModal(plan){

    const weekly =
        document.getElementById('weeklyDueContent');

    const monthly =
        document.getElementById('monthlyDueContent');

    const title =
        document.getElementById('dueModalTitle');

    const subtitle =
        document.getElementById('dueModalSubtitle');

    if(plan === 'Weekly'){

        weekly.classList.remove('d-none');
        monthly.classList.add('d-none');

        title.textContent =
            'Weekly Due Payments';

        subtitle.textContent =
            'Current week ke unpaid dues';

    }else{

        weekly.classList.add('d-none');
        monthly.classList.remove('d-none');

        title.textContent =
            'Monthly Due Payments';

        subtitle.textContent =
            'Current month ke unpaid dues';
    }

    bootstrap.Modal
        .getOrCreateInstance(
            document.getElementById(
                'dueCollectionModal'
            )
        )
        .show();
}

function updateDueSummary(plan){

    const selector =
        plan === 'Weekly'
        ? '.weekly-due'
        : '.monthly-due';

    const prefix =
        plan === 'Weekly'
        ? 'weekly'
        : 'monthly';

    const boxes =
        [...document.querySelectorAll(selector)];

    const selected =
        boxes.filter(
            item => item.checked
        );

    let total = 0;

    selected.forEach(
        item => {
            total +=
                parseFloat(
                    item.dataset.amount
                ) || 0;

            item.closest('.due-row')
                .classList.add('selected');
        }
    );

    boxes.forEach(
        item => {
            if(!item.checked){
                item.closest('.due-row')
                    .classList.remove('selected');
            }
        }
    );

    document.getElementById(
        prefix + 'SelectedCount'
    ).textContent =
        selected.length + ' selected';

    document.getElementById(
        prefix + 'SelectedAmount'
    ).textContent =
        '₹' +
        total.toLocaleString(
            'en-IN',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        );

    document.getElementById(
        prefix + 'BulkPay'
    ).disabled =
        selected.length === 0;

    document.getElementById(
        plan === 'Weekly'
        ? 'selectAllWeekly'
        : 'selectAllMonthly'
    ).checked =
        boxes.length > 0 &&
        selected.length === boxes.length;
}

function toggleAllDue(plan,checked){

    const selector =
        plan === 'Weekly'
        ? '.weekly-due'
        : '.monthly-due';

    document.querySelectorAll(selector)
        .forEach(
            item => {
                item.checked = checked;
            }
        );

    updateDueSummary(plan);
}

function confirmBulk(plan){

    const selector =
        plan === 'Weekly'
        ? '.weekly-due'
        : '.monthly-due';

    const selected =
        [...document.querySelectorAll(selector)]
        .filter(
            item => item.checked
        );

    if(selected.length === 0){
        return false;
    }

    let total = 0;

    selected.forEach(
        item => {
            total +=
                parseFloat(
                    item.dataset.amount
                ) || 0;
        }
    );

    return confirm(
        selected.length +
        ' payment(s) receive karein?\n\nTotal: ₹' +
        total.toLocaleString(
            'en-IN',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        )
    );
}

function payOneDue(
    customerId,
    installmentNo,
    name,
    amount
){

    if(!confirm(
        'Customer: ' +
        name +
        '\nAmount: ₹' +
        amount.toLocaleString(
            'en-IN',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        ) +
        '\n\nPayment receive karein?'
    )){
        return;
    }

    const form =
        document.createElement('form');

    form.method = 'POST';

    const fields = {
        dashboard_pay_multiple: '1',
        'selected_due[]':
            customerId + ':' + installmentNo
    };

    Object.keys(fields).forEach(
        key => {

            const input =
                document.createElement('input');

            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];

            form.appendChild(input);
        }
    );

    document.body.appendChild(form);
    form.submit();
}
</script>

<div class="section-card">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <div class="section-title">
                Recent Payments
            </div>

            <div class="section-subtitle">
                Latest Monthly and Weekly payments
            </div>

        </div>

        <a
            href="customers.php"
            class="btn btn-sm btn-outline-success"
        >
            Customers
        </a>

    </div>

    <div class="table-responsive">

        <table class="payment-table">

            <thead>

                <tr>

                    <th>
                        Customer
                    </th>

                    <th>
                        Plan
                    </th>

                    <th>
                        Installment
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Payment Date
                    </th>

                    <th>
                        Due Date
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php if (
                $recentPayments &&
                $recentPayments->num_rows > 0
            ): ?>

                <?php while (
                    $payment =
                    $recentPayments->fetch_assoc()
                ): ?>

                    <tr>

                        <td>

                            <span class="avatar-small">

                                <?= strtoupper(
                                    substr(
                                        $payment['name'],
                                        0,
                                        1
                                    )
                                ) ?>

                            </span>

                            <?= htmlspecialchars(
                                $payment['name']
                            ) ?>

                        </td>

                        <td>

                            <?php if (
                                $payment['plan'] === 'Monthly'
                            ): ?>

                                <span
                                    class="
                                    plan-badge
                                    monthly-badge
                                    "
                                >

                                    <i class="bi bi-calendar-month"></i>

                                    Monthly

                                </span>

                            <?php else: ?>

                                <span
                                    class="
                                    plan-badge
                                    weekly-badge
                                    "
                                >

                                    <i class="bi bi-calendar-week"></i>

                                    Weekly

                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            #<?= (int)
                            $payment[
                                'installment_no'
                            ] ?>

                        </td>

                        <td>

                            <strong
                                style="color:#15803d"
                            >

                                ₹<?= number_format(
                                    (float)
                                    $payment['amount'],
                                    2
                                ) ?>

                            </strong>

                        </td>

                        <td class="muted">

                            <?= !empty(
                                $payment['payment_date']
                            )
                                ? date(
                                    'd M Y',
                                    strtotime(
                                        $payment[
                                            'payment_date'
                                        ]
                                    )
                                )
                                : '-'
                            ?>

                        </td>

                        <td class="muted">

                            <?= !empty(
                                $payment['due_date']
                            )
                                ? date(
                                    'd M Y',
                                    strtotime(
                                        $payment[
                                            'due_date'
                                        ]
                                    )
                                )
                                : '-'
                            ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="6"
                        class="text-center py-5"
                    >

                        <i
                            class="bi bi-receipt"
                            style="
                            font-size:30px;
                            color:#b8c2bb;
                            "
                        ></i>

                        <div class="muted mt-2">
                            No payment found
                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php

include 'includes/footer.php';

?>