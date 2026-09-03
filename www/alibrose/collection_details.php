<?php

date_default_timezone_set('Asia/Kolkata');

require_once 'includes/auth.php';
require_once 'config/db.php';

$page_title = 'Collection Details';

$plan = $_GET['plan'] ?? 'Monthly';

if ($plan !== 'Monthly' && $plan !== 'Weekly') {
    $plan = 'Monthly';
}

$currentMonth = date('Y-m');
$currentMonthName = date('F Y');

$periodStart = $plan === 'Weekly'
    ? date('Y-m-d', strtotime('monday this week'))
    : date('Y-m-01');

$periodEnd = $plan === 'Weekly'
    ? date('Y-m-d', strtotime('sunday this week'))
    : date('Y-m-t');

$expectedStmt = $conn->prepare("
    SELECT
        COUNT(*) AS scheduled_accounts,
        COALESCE(SUM(c.amount),0) AS expected_amount
    FROM customers c
    WHERE c.plan = ?
    AND c.status = 'Active'
    AND c.opening_date IS NOT NULL
    AND c.opening_date <= ?
    AND (
        (
            c.plan = 'Weekly'
            AND DATE_ADD(
                c.opening_date,
                INTERVAL
                (
                    FLOOR(
                        DATEDIFF(?, c.opening_date) / 7
                    ) * 7
                ) DAY
            ) BETWEEN ? AND ?
            AND
            (
                FLOOR(
                    DATEDIFF(?, c.opening_date) / 7
                ) + 1
            ) <= COALESCE(c.total_installments, 52)
        )
        OR
        (
            c.plan = 'Monthly'
            AND DATE_ADD(
                c.opening_date,
                INTERVAL
                (
                    PERIOD_DIFF(
                        DATE_FORMAT(?, '%Y%m'),
                        DATE_FORMAT(c.opening_date, '%Y%m')
                    )
                ) MONTH
            ) BETWEEN ? AND ?
            AND
            (
                PERIOD_DIFF(
                    DATE_FORMAT(?, '%Y%m'),
                    DATE_FORMAT(c.opening_date, '%Y%m')
                ) + 1
            ) <= COALESCE(c.total_installments, 12)
        )
    )
");

$expectedStmt->bind_param(
    "ssssssssss",
    $plan,
    $periodEnd,
    $periodEnd,
    $periodStart,
    $periodEnd,
    $periodEnd,
    $periodEnd,
    $periodStart,
    $periodEnd,
    $periodEnd
);

$expectedStmt->execute();

$expected = $expectedStmt
    ->get_result()
    ->fetch_assoc();

$expectedAmount = (float)($expected['expected_amount'] ?? 0);
$totalCustomers = (int)($expected['scheduled_accounts'] ?? 0);


$receivedStmt = $conn->prepare("
    SELECT
        COUNT(ip.id) AS total_payments,
        COALESCE(SUM(ip.amount),0) AS total_amount,
        COUNT(DISTINCT ip.customer_id) AS paid_customers
    FROM installment_payments ip
    INNER JOIN customers c
        ON c.id = ip.customer_id
    WHERE c.plan = ?
    AND ip.payment_date BETWEEN ? AND ?
");

$receivedStmt->bind_param(
    "sss",
    $plan,
    $periodStart,
    $periodEnd
);

$receivedStmt->execute();

$received = $receivedStmt
    ->get_result()
    ->fetch_assoc();

$totalPayments = (int)($received['total_payments'] ?? 0);
$totalCollection = (float)($received['total_amount'] ?? 0);
$paidCustomers = (int)($received['paid_customers'] ?? 0);

$dueAmount = max(
    0,
    $expectedAmount - $totalCollection
);



$detailsStmt = $conn->prepare("
    SELECT
        c.id,
        c.name,
        c.phone,
        c.aadhaar_no,
        c.opening_date,
        c.plan,
        c.amount AS installment_amount,
        c.total_installments,
        c.paid_installments,
        c.next_due_date,
        c.status,
        ip.installment_no,
        ip.amount AS payment_amount,
        ip.due_date,
        ip.payment_date
    FROM installment_payments ip
    INNER JOIN customers c
        ON c.id = ip.customer_id
    WHERE c.plan = ?
    AND DATE_FORMAT(ip.payment_date,'%Y-%m') = ?
    ORDER BY
        ip.payment_date DESC,
        c.name ASC,
        ip.installment_no ASC
");

$detailsStmt->bind_param(
    "ss",
    $plan,
    $currentMonth
);

$detailsStmt->execute();

$details = $detailsStmt->get_result();

$customersThisMonth = [];

while ($row = $details->fetch_assoc()) {

    $customersThisMonth[] = $row;
}

$details->data_seek(0);

include 'includes/header.php';

?>

<style>

.collection-page-title{
    font-size:29px;
    font-weight:850;
    letter-spacing:-.7px;
}

.collection-page-subtitle{
    color:#718078;
    font-size:13px;
}

.collection-summary{
    background:#fff;
    border:1px solid #e7ece9;
    border-radius:20px;
    padding:22px;
    height:100%;
    box-shadow:0 10px 30px rgba(9,18,12,.055);
    position:relative;
    overflow:hidden;
}

.collection-summary::after{
    content:"";
    position:absolute;
    width:100px;
    height:100px;
    border-radius:50%;
    right:-45px;
    top:-45px;
    background:#eaf8ef;
}

.summary-icon{
    width:48px;
    height:48px;
    border-radius:14px;
    background:#eaf8ef;
    color:#16a34a;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:21px;
    position:relative;
    z-index:1;
}

.summary-label{
    color:#718078;
    font-size:11px;
    font-weight:650;
}

.summary-value{
    font-size:25px;
    font-weight:850;
    margin-top:5px;
}

.plan-switch{
    background:#fff;
    border:1px solid #e7ece9;
    border-radius:18px;
    padding:17px 20px;
    box-shadow:0 8px 25px rgba(9,18,12,.045);
}

.plan-switch-title{
    font-size:15px;
    font-weight:800;
}

.plan-switch-subtitle{
    color:#718078;
    font-size:11px;
}

.plan-buttons{
    display:flex;
    gap:8px;
}

.plan-button{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:9px 17px;
    border-radius:11px;
    text-decoration:none;
    font-size:12px;
    font-weight:750;
    transition:.2s;
}

.plan-button.active{
    background:#16a34a;
    color:#fff;
}

.plan-button:not(.active){
    background:#f3f7f4;
    color:#53605a;
}

.plan-button:not(.active):hover{
    background:#dcfce7;
    color:#15803d;
}

.details-card{
    background:#fff;
    border:1px solid #e7ece9;
    border-radius:22px;
    box-shadow:0 10px 30px rgba(9,18,12,.055);
    overflow:hidden;
}

.details-header{
    padding:23px 25px;
    border-bottom:1px solid #edf1ee;
}

.details-title{
    font-size:17px;
    font-weight:850;
}

.details-subtitle{
    color:#718078;
    font-size:11px;
    margin-top:3px;
}

.payment-table{
    width:100%;
    margin:0;
}

.payment-table th{
    background:#f8faf9;
    color:#68746d;
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.3px;
    padding:14px 12px;
    white-space:nowrap;
    border-bottom:1px solid #e9eeeb;
}

.payment-table td{
    padding:15px 12px;
    font-size:12px;
    vertical-align:middle;
    border-bottom:1px solid #f0f3f1;
    white-space:nowrap;
}

.payment-table tbody tr{
    transition:.2s;
}

.payment-table tbody tr:hover{
    background:#fbfdfb;
}

.payment-table tbody tr:last-child td{
    border-bottom:0;
}

.customer-box{
    display:flex;
    align-items:center;
    gap:10px;
}

.customer-avatar{
    width:40px;
    height:40px;
    flex-shrink:0;
    border-radius:13px;
    background:#dcfce7;
    color:#15803d;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:850;
}

.customer-name{
    font-weight:800;
    color:#172019;
}

.customer-id{
    color:#87918b;
    font-size:9px;
    margin-top:2px;
}

.plan-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 10px;
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

.installment-badge{
    background:#f1f5f3;
    color:#53605a;
    padding:6px 9px;
    border-radius:9px;
    font-size:10px;
    font-weight:750;
}

.amount-value{
    color:#15803d;
    font-weight:850;
}

.date-main{
    font-weight:700;
    color:#26312a;
}

.date-small{
    color:#87918b;
    font-size:9px;
    margin-top:2px;
}

.status-badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 10px;
    border-radius:20px;
    font-size:10px;
    font-weight:750;
}

.status-active{
    background:#dcfce7;
    color:#15803d;
}

.status-completed{
    background:#dbeafe;
    color:#1d4ed8;
}

.empty-box{
    padding:70px 20px;
    text-align:center;
}

.empty-icon{
    width:70px;
    height:70px;
    margin:auto;
    border-radius:20px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f1f5f3;
    color:#87918b;
    font-size:30px;
}

.empty-title{
    font-size:17px;
    font-weight:800;
    margin-top:18px;
}

.empty-text{
    color:#718078;
    font-size:12px;
    margin-top:5px;
}

.total-footer{
    padding:18px 25px;
    background:#f8faf9;
    border-top:1px solid #e9eeeb;
}

.total-footer-label{
    color:#718078;
    font-size:11px;
}

.total-footer-value{
    color:#15803d;
    font-size:21px;
    font-weight:850;
}

@media(max-width:767px){

    .collection-page-title{
        font-size:24px;
    }

    .plan-switch{
        padding:15px;
    }

    .plan-buttons{
        width:100%;
        margin-top:12px;
    }

    .plan-button{
        flex:1;
        justify-content:center;
    }

}

</style>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <div class="collection-page-title">
            Collection Details
        </div>

        <div class="collection-page-subtitle">
            <?= htmlspecialchars($plan === 'Weekly' ? date('d M Y', strtotime($periodStart)) . ' - ' . date('d M Y', strtotime($periodEnd)) : $currentMonthName) ?>
            <?= htmlspecialchars($plan) ?>
            customer collection
        </div>

    </div>

    <a
        href="dashboard.php"
        class="btn btn-light"
    >
        <i class="bi bi-arrow-left me-2"></i>
        Dashboard
    </a>

</div>

<div class="row g-4 mb-4">

    <div class="col-md-4">

        <div class="collection-summary">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="summary-label">
                        <?= htmlspecialchars($plan) ?>
                        EXPECTED COLLECTION
                    </div>

                    <div class="summary-value">
                        ₹<?= number_format(
                            $expectedAmount,
                            2
                        ) ?>
                    </div>

                    <div
                        class="text-success"
                        style="font-size:10px;margin-top:4px;"
                    >
                        Active <?= htmlspecialchars(
                            $plan
                        ) ?> accounts
                    </div>

                </div>

                <div class="summary-icon">

                    <?php if ($plan === 'Monthly'): ?>

                        <i class="bi bi-calendar-month"></i>

                    <?php else: ?>

                        <i class="bi bi-calendar-week"></i>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="collection-summary">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="summary-label">
                        TOTAL PAYMENTS
                    </div>

                    <div class="summary-value">
                        <?= number_format(
                            $totalPayments
                        ) ?>
                    </div>

                    <div
                        class="text-muted"
                        style="font-size:10px;margin-top:4px;"
                    >
                        Payments received this month
                    </div>

                </div>

                <div class="summary-icon">

                    <i class="bi bi-receipt"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="collection-summary">

            <div class="d-flex justify-content-between">

                <div>

                    <div class="summary-label">
                        CUSTOMERS PAID
                    </div>

                    <div class="summary-value">
                        <?= number_format(
                            $totalCustomers
                        ) ?>
                    </div>

                    <div
                        class="text-muted"
                        style="font-size:10px;margin-top:4px;"
                    >
                        <?= htmlspecialchars($plan) ?>
                        customers
                    </div>

                </div>

                <div class="summary-icon">

                    <i class="bi bi-people-fill"></i>

                </div>

            </div>

        </div>

    </div>


</div>

<div class="row g-4 mb-4">

    <div class="col-md-4">
        <div class="collection-summary">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="summary-label">
                        EXPECTED COLLECTION
                    </div>
                    <div class="summary-value">
                        ₹<?= number_format($expectedAmount,2) ?>
                    </div>
                    <div class="text-muted" style="font-size:10px;margin-top:4px;">
                        Active <?= htmlspecialchars($plan) ?> customers
                    </div>
                </div>
                <div class="summary-icon">
                    <i class="bi bi-bullseye"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="collection-summary">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="summary-label">
                        RECEIVED
                    </div>
                    <div class="summary-value text-success">
                        ₹<?= number_format($totalCollection,2) ?>
                    </div>
                    <div class="text-muted" style="font-size:10px;margin-top:4px;">
                        <?= htmlspecialchars($currentMonthName) ?> received
                    </div>
                </div>
                <div class="summary-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="collection-summary">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="summary-label">
                        REMAINING DUE
                    </div>
                    <div class="summary-value" style="color:#dc2626;">
                        ₹<?= number_format($dueAmount,2) ?>
                    </div>
                    <div class="text-muted" style="font-size:10px;margin-top:4px;">
                        Expected minus received
                    </div>
                </div>
                <div class="summary-icon" style="background:#fee2e2;color:#dc2626;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="plan-switch mb-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap">

        <div>

            <div class="plan-switch-title">
                Collection Type
            </div>

            <div class="plan-switch-subtitle">
                Expected active accounts aur current month payments
            </div>

        </div>

        <div class="plan-buttons">

            <a
                href="collection_details.php?plan=Monthly"
                class="
                plan-button
                <?= $plan === 'Monthly'
                    ? 'active'
                    : ''
                ?>
                "
            >

                <i class="bi bi-calendar-month"></i>

                Monthly

            </a>

            <a
                href="collection_details.php?plan=Weekly"
                class="
                plan-button
                <?= $plan === 'Weekly'
                    ? 'active'
                    : ''
                ?>
                "
            >

                <i class="bi bi-calendar-week"></i>

                Weekly

            </a>

        </div>

    </div>

</div>

<div class="details-card">

    <div class="details-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <div class="details-title">

                    <?= htmlspecialchars($currentMonthName) ?>
                    Payment Details

                </div>

                <div class="details-subtitle">

                    <?= htmlspecialchars($plan) ?>
                    customers ki complete payment information

                </div>

            </div>

            <span class="badge text-bg-success">

                <?= $totalPayments ?>
                Payments

            </span>

        </div>

    </div>

    <?php if ($details->num_rows === 0): ?>

        <div class="empty-box">

            <div class="empty-icon">

                <i class="bi bi-receipt-cutoff"></i>

            </div>

            <div class="empty-title">
                No Payment Received
            </div>

            <div class="empty-text">

                <?= htmlspecialchars($currentMonthName) ?>
                mein abhi tak koi
                <?= htmlspecialchars($plan) ?>
                payment receive nahi hui.

            </div>

        </div>

    <?php else: ?>

        <div class="table-responsive">

            <table class="payment-table">

                <thead>

                    <tr>

                        <th>
                            Customer
                        </th>

                        <th>
                            Mobile
                        </th>

                        <th>
                            Aadhaar
                        </th>

                        <th>
                            Opening Date
                        </th>

                        <th>
                            Plan
                        </th>

                        <th>
                            Installment
                        </th>

                        <th>
                            Installment Amount
                        </th>

                        <th>
                            Due Date
                        </th>

                        <th>
                            Paid Date
                        </th>

                        <th>
                            Paid Amount
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php while ($row = $details->fetch_assoc()): ?>

                    <tr>

                        <td>

                            <div class="customer-box">

                                <div class="customer-avatar">

                                    <?= strtoupper(
                                        substr(
                                            $row['name'],
                                            0,
                                            1
                                        )
                                    ) ?>

                                </div>

                                <div>

                                    <div class="customer-name">

                                        <?= htmlspecialchars(
                                            $row['name']
                                        ) ?>

                                    </div>

                                    <div class="customer-id">

                                        Customer ID:
                                        #<?= (int)$row['id'] ?>

                                    </div>

                                </div>

                            </div>

                        </td>

                        <td>

                            <i
                                class="bi bi-phone me-1"
                                style="color:#16a34a;"
                            ></i>

                            <?= htmlspecialchars(
                                $row['phone']
                            ) ?>

                        </td>

                        <td>

                            <?= !empty($row['aadhaar_no'])
                                ? htmlspecialchars(
                                    $row['aadhaar_no']
                                )
                                : '—'
                            ?>

                        </td>

                        <td>

                            <?php if (
                                !empty(
                                    $row['opening_date']
                                )
                            ): ?>

                                <div class="date-main">

                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $row['opening_date']
                                        )
                                    ) ?>

                                </div>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if (
                                $row['plan'] === 'Monthly'
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

                            <span class="installment-badge">

                                #<?= (int)
                                $row[
                                    'installment_no'
                                ] ?>

                            </span>

                        </td>

                        <td>

                            <strong>

                                ₹<?= number_format(
                                    (float)
                                    $row[
                                        'installment_amount'
                                    ],
                                    2
                                ) ?>

                            </strong>

                        </td>

                        <td>

                            <?php if (
                                !empty(
                                    $row['due_date']
                                )
                            ): ?>

                                <div class="date-main">

                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $row['due_date']
                                        )
                                    ) ?>

                                </div>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if (
                                !empty(
                                    $row['payment_date']
                                )
                            ): ?>

                                <div class="date-main">

                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $row['payment_date']
                                        )
                                    ) ?>

                                </div>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                        <td>

                            <div class="amount-value">

                                ₹<?= number_format(
                                    (float)
                                    $row[
                                        'payment_amount'
                                    ],
                                    2
                                ) ?>

                            </div>

                        </td>

                        <td>

                            <?php if (
                                $row['status'] === 'Completed'
                            ): ?>

                                <span
                                    class="
                                    status-badge
                                    status-completed
                                    "
                                >

                                    <i class="bi bi-check-circle-fill"></i>

                                    Completed

                                </span>

                            <?php else: ?>

                                <span
                                    class="
                                    status-badge
                                    status-active
                                    "
                                >

                                    <i class="bi bi-check-circle-fill"></i>

                                    Active

                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

        <div class="total-footer">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <div class="total-footer-label">
                        <?= htmlspecialchars($plan) ?> Expected
                    </div>
                    <div class="total-footer-value">
                        ₹<?= number_format($expectedAmount,2) ?>
                    </div>
                </div>

                <div>
                    <div class="total-footer-label">
                        Received
                    </div>
                    <strong class="text-success">
                        ₹<?= number_format($totalCollection,2) ?>
                    </strong>
                </div>

                <div>
                    <div class="total-footer-label">
                        Remaining Due
                    </div>
                    <strong style="color:#dc2626;">
                        ₹<?= number_format($dueAmount,2) ?>
                    </strong>
                </div>

                <div class="text-end">
                    <div class="total-footer-label">
                        Total Payments
                    </div>
                    <strong>
                        <?= $totalPayments ?>
                    </strong>
                </div>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php

include 'includes/footer.php';

?>