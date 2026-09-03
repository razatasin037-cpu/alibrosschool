<?php

require_once 'config/db.php';

$page_title = 'Customer Details';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: customers.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        father_name,
        mobile,
        aadhaar,
        address,
        gulabi_khatoon,
        alibrose_agent,
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
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die('Customer query error: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param('i', $id);
$stmt->execute();

$result = $stmt->get_result();
$customer = $result->fetch_assoc();

$stmt->close();

if (!$customer) {
    header('Location: customers.php');
    exit;
}

function safeDate($date)
{
    if (
        empty($date) ||
        $date === '0000-00-00'
    ) {
        return null;
    }

    $time = strtotime($date);

    if (!$time) {
        return null;
    }

    return date('d M Y', $time);
}

$plan = $customer['plan'] ?? 'Weekly';

$openingDate = $customer['opening_date'];

$totalInstallments = (int)($customer['total_installments'] ?? 0);

$amount = (float)($customer['amount'] ?? 0);

$today = new DateTimeImmutable(date('Y-m-d'));

$paidInstallments = max(
    0,
    min(
        (int)($customer['paid_installments'] ?? 0),
        $totalInstallments
    )
);

$completedInstallments = $paidInstallments;
$currentInstallment = $completedInstallments + 1;
$remainingInstallments = max(
    0,
    $totalInstallments - $completedInstallments
);

$progress = $totalInstallments > 0
    ? min(100, ($completedInstallments / $totalInstallments) * 100)
    : 0;

$nextPaymentDate = null;

if ($currentInstallment <= $totalInstallments) {
    try {
        if ($plan === 'Monthly') {
            $first = new DateTimeImmutable(
                $openingDate->format('Y-m-10')
            );

            if ((int)$openingDate->format('d') > 10) {
                $first = $first->modify('+1 month');
            }

            $nextPaymentDate = $first
                ->modify('+' . ($currentInstallment - 1) . ' months')
                ->format('Y-m-d');

            $periodName = 'Month';
            $periodPlural = 'Months';
            $periodIcon = 'bi-calendar-month';

        } else {
            $first = $openingDate;

            if ((int)$first->format('N') !== 1) {
                $first = $first->modify('next monday');
            }

            $nextPaymentDate = $first
                ->modify('+' . (($currentInstallment - 1) * 7) . ' days')
                ->format('Y-m-d');

            $periodName = 'Week';
            $periodPlural = 'Weeks';
            $periodIcon = 'bi-calendar-week';
        }
    } catch (Exception $e) {
        $nextPaymentDate = null;
    }
}

$customerStatus =
    $completedInstallments >= $totalInstallments &&
    $totalInstallments > 0
        ? 'Completed'
        : 'Active';

include 'includes/header.php';

?>

<style>

.details-page {
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

.customer-profile {
    position: relative;
    overflow: hidden;
    background: linear-gradient(
        135deg,
        #681033,
        #d81b72,
        #f04a9b
    );
    color: #fff;
    border-radius: 28px;
    padding: 30px;
    box-shadow:
        0 22px 60px
        rgba(216,27,114,.22);
}

.customer-profile::before {
    content: "";
    position: absolute;
    width: 320px;
    height: 320px;
    right: -140px;
    top: -190px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
    animation: floatCircle 7s ease-in-out infinite;
}

.customer-profile::after {
    content: "";
    position: absolute;
    width: 190px;
    height: 190px;
    left: -90px;
    bottom: -100px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}

.customer-profile > * {
    position: relative;
    z-index: 2;
}

@keyframes floatCircle {

    0%,
    100% {
        transform: translate(0,0) scale(1);
    }

    50% {
        transform: translate(-25px,25px) scale(1.12);
    }
}

.profile-avatar {
    width: 82px;
    height: 82px;
    border-radius: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.25);
    backdrop-filter: blur(12px);
    font-size: 30px;
    font-weight: 900;
}

.profile-name {
    font-size: 29px;
    font-weight: 900;
}

.profile-id {
    opacity: .72;
    font-size: 11px;
}

.profile-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 13px;
    border-radius: 20px;
    background: rgba(255,255,255,.15);
    font-size: 10px;
    font-weight: 900;
}

.info-card,
.stat-card,
.payment-card {
    background: #fff;
    border: 1px solid #e8eee9;
    box-shadow: 0 9px 28px rgba(0,0,0,.045);
}

.info-card {
    border-radius: 22px;
    padding: 22px;
    height: 100%;
    animation: cardIn 1s ease both;
    transition: .5s ease;
}

.info-card:hover,
.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 45px rgba(0,0,0,.09);
}

@keyframes cardIn {

    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card {
    border-radius: 21px;
    padding: 20px;
    height: 100%;
    transition: .5s ease;
}

.stat-label,
.info-label {
    color: #8a948e;
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .6px;
}

.stat-number {
    color: #172019;
    font-size: 28px;
    font-weight: 900;
    margin-top: 7px;
}

.info-value {
    color: #172019;
    font-size: 14px;
    font-weight: 800;
    margin-top: 6px;
}

.info-icon {
    width: 43px;
    height: 43px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff0f7;
    color: #d81b72;
    font-size: 18px;
}

.plan-box,
.next-payment-box,
.completed-payment-box,
.period-badge,
.paid-badge,
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 900;
}

.plan-box {
    padding: 8px 12px;
}

.plan-weekly {
    background: #ecfdf5;
    color: #047857;
}

.plan-monthly {
    background: #eff6ff;
    color: #1d4ed8;
}

.next-payment-box {
    padding: 8px 12px;
    border-radius: 12px;
    background: #fff0f7;
    color: #b8145f;
}

.completed-payment-box {
    padding: 8px 12px;
    border-radius: 12px;
    background: #dcfce7;
    color: #15803d;
}

.status-badge {
    padding: 7px 11px;
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

.progress-wrap {
    height: 10px;
    background: #edf1ee;
    border-radius: 50px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    width: 0;
    border-radius: 50px;
    background: linear-gradient(
        90deg,
        #d81b72,
        #f04a9b
    );
    transition:
        width 2.5s
        cubic-bezier(.22,1,.36,1);
}

.payment-card {
    border-radius: 23px;
    overflow: hidden;
}

.payment-card-header {
    padding: 19px 20px;
    border-bottom: 1px solid #edf1ee;
}

.payment-table {
    width: 100%;
    margin: 0;
}

.payment-table th {
    background: #fafbfa;
    color: #69756e;
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
    padding: 14px 12px;
    white-space: nowrap;
}

.payment-table td {
    padding: 14px 12px;
    font-size: 11px;
    border-bottom: 1px solid #eef2ef;
    white-space: nowrap;
}

.payment-table tbody tr {
    transition: .3s ease;
}

.payment-table tbody tr:hover {
    background: #fff8fb;
}

.period-badge {
    padding: 7px 11px;
    border-radius: 12px;
    background: #fff0f7;
    color: #b8145f;
}

.paid-badge {
    padding: 6px 10px;
    background: #dcfce7;
    color: #15803d;
}

@media(max-width:600px) {

    .customer-profile {
        padding: 23px;
        border-radius: 22px;
    }

    .profile-name {
        font-size: 23px;
    }

    .profile-avatar {
        width: 65px;
        height: 65px;
        border-radius: 19px;
        font-size: 24px;
    }
}

</style>


<div class="details-page">


    <div class="customer-profile mb-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">

            <div class="d-flex align-items-center gap-3">

                <div class="profile-avatar">

                    <?= htmlspecialchars($initial) ?>

                </div>

                <div>

                    <div class="small opacity-75">
                        CUSTOMER DETAILS
                    </div>

                    <div class="profile-name">

                        <?= htmlspecialchars(
                            $customer['name']
                        ) ?>

                    </div>

                    <div class="profile-id">

                        Customer ID:
                        #<?= (int)$customer['id'] ?>

                    </div>

                </div>

            </div>


            <div class="d-flex flex-wrap gap-2">

                <span class="profile-badge">

                    <i class="bi <?= $periodIcon ?>"></i>

                    <?= htmlspecialchars($plan) ?>

                </span>

                <span class="profile-badge">

                    <i class="bi bi-person-check-fill"></i>

                    <?= htmlspecialchars($displayStatus) ?>

                </span>

            </div>

        </div>

    </div>


    <div class="row g-4 mb-4">


        <div class="col-md-6 col-xl-3">

            <div class="stat-card">

                <div class="stat-label">
                    Total <?= $periodPlural ?>
                </div>

                <div class="stat-number">
                    <?= $totalInstallments ?>
                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="stat-card">

                <div class="stat-label">
                    Current <?= $periodName ?>
                </div>

                <div class="stat-number">
                    <?= $currentPeriod ?>
                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="stat-card">

                <div class="stat-label">
                    Remaining <?= $periodPlural ?>
                </div>

                <div class="stat-number">
                    <?= $remainingPeriods ?>
                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="stat-card">

                <div class="stat-label">
                    <?= $periodName ?> Amount
                </div>

                <div class="stat-number">

                    ₹<?= number_format(
                        $amount,
                        2
                    ) ?>

                </div>

            </div>

        </div>


    </div>


    <div class="row g-4 mb-4">


        <div class="col-lg-6">

            <div class="info-card">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <div class="muted small">
                            PERSONAL INFORMATION
                        </div>

                        <h5 class="fw-bold mb-0">
                            Customer Information
                        </h5>

                    </div>

                    <div class="info-icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>

                </div>


                <div class="row g-4">


                    <div class="col-sm-6">

                        <div class="info-label">
                            Customer Name
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                $customer['name']
                            ) ?>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            Father / Husband
                        </div>

                        <div class="info-value">

                            <?= !empty(
                                $customer['father_name']
                            )
                                ? htmlspecialchars(
                                    $customer['father_name']
                                )
                                : '—'
                            ?>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            Mobile
                        </div>

                        <div class="info-value">

                            <?= !empty(
                                $customer['mobile']
                            )
                                ? htmlspecialchars(
                                    $customer['mobile']
                                )
                                : '—'
                            ?>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            Aadhaar
                        </div>

                        <div class="info-value">

                            <?= !empty(
                                $customer['aadhaar']
                            )
                                ? htmlspecialchars(
                                    $customer['aadhaar']
                                )
                                : '—'
                            ?>

                        </div>

                    </div>


                    <div class="col-12">

                        <div class="info-label">
                            Address
                        </div>

                        <div class="info-value">

                            <?= !empty(
                                $customer['address']
                            )
                                ? nl2br(
                                    htmlspecialchars(
                                        $customer['address']
                                    )
                                )
                                : '—'
                            ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="info-card">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <div class="muted small">
                            COLLECTION INFORMATION
                        </div>

                        <h5 class="fw-bold mb-0">
                            Account Details
                        </h5>

                    </div>

                    <div class="info-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                </div>


                <div class="row g-4">


                    <div class="col-sm-6">

                        <div class="info-label">
                            Plan
                        </div>

                        <div class="info-value">

                            <span
                                class="plan-box <?= $plan === 'Weekly'
                                    ? 'plan-weekly'
                                    : 'plan-monthly'
                                ?>"
                            >

                                <i class="bi <?= $periodIcon ?>"></i>

                                <?= htmlspecialchars($plan) ?>

                            </span>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            <?= $periodName ?> Amount
                        </div>

                        <div class="info-value">

                            ₹<?= number_format(
                                $amount,
                                2
                            ) ?>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            Opening Date
                        </div>

                        <div class="info-value">

                            <?= htmlspecialchars(
                                safeDate(
                                    $openingDate
                                ) ?? '—'
                            ) ?>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            Next Payment
                        </div>

                        <div class="info-value">

                            <?php if (
                                $nextPaymentDate
                            ): ?>

                                <span class="next-payment-box">

                                    <i class="bi <?= $periodIcon ?>"></i>

                                    <?= htmlspecialchars(
                                        $nextPaymentText
                                    ) ?>

                                </span>

                            <?php else: ?>

                                <span class="completed-payment-box">

                                    <i class="bi bi-check-circle-fill"></i>

                                    Completed

                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            Calculated Paid Amount
                        </div>

                        <div
                            class="info-value"
                            style="color:#15803d"
                        >

                            ₹<?= number_format(
                                $calculatedPaidAmount,
                                2
                            ) ?>

                        </div>

                    </div>


                    <div class="col-sm-6">

                        <div class="info-label">
                            Remaining Amount
                        </div>

                        <div
                            class="info-value"
                            style="color:#d81b72"
                        >

                            ₹<?= number_format(
                                $remainingAmount,
                                2
                            ) ?>

                        </div>

                    </div>


                </div>


                <div class="mt-4">

                    <div class="d-flex justify-content-between mb-2">

                        <small class="muted">

                            <?= $periodName ?>
                            Collection Progress

                        </small>

                        <strong>

                            <?= number_format(
                                $progress,
                                1
                            ) ?>%

                        </strong>

                    </div>


                    <div class="progress-wrap">

                        <div
                            class="progress-fill"
                            data-progress="<?= $progress ?>"
                        ></div>

                    </div>

                </div>

            </div>

        </div>


    </div>


    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

        <div>

            <div class="muted small">

                <?= strtoupper($periodName) ?>
                PAYMENT HISTORY

            </div>

            <h4 class="fw-bold mb-0">

                <?= $plan === 'Weekly'
                    ? 'Weekly Payment History'
                    : 'Monthly Payment History'
                ?>

            </h4>

        </div>


        <a
            href="customers.php"
            class="btn btn-light"
        >

            <i class="bi bi-arrow-left me-1"></i>

            Back

        </a>

    </div>


    <div class="payment-card">


        <div class="payment-card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>

                    <strong>

                        <?= count($payments) ?>

                        <?= $periodName ?> Payment(s)

                    </strong>

                    <div class="muted small mt-1">

                        <?= $plan === 'Weekly'
                            ? 'Weekly collection'
                            : 'Monthly collection'
                        ?>

                    </div>

                </div>


                <span
                    class="plan-box <?= $plan === 'Weekly'
                        ? 'plan-weekly'
                        : 'plan-monthly'
                    ?>"
                >

                    <i class="bi <?= $periodIcon ?>"></i>

                    <?= htmlspecialchars($plan) ?>

                </span>

            </div>

        </div>


        <?php if (
            empty($payments)
        ): ?>

            <div class="text-center py-5">

                <div class="info-icon mx-auto mb-3">

                    <i class="bi bi-receipt"></i>

                </div>

                <h5 class="fw-bold">
                    No Payment History
                </h5>

                <div class="muted small">
                    Abhi tak koi payment record nahi hai.
                </div>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table payment-table">

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                <?= $periodName ?>
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Due Date
                            </th>

                            <th>
                                Payment Date
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach (
                        $payments
                        as $payment
                    ): ?>

                        <?php

                        $periodNumber =
                            (int)(
                                $payment[
                                    'installment_no'
                                ] ?? 0
                            );

                        if (
                            $periodNumber <= 0
                        ) {
                            $periodNumber = 1;
                        }

                        ?>

                        <tr>

                            <td>

                                <?= (int)(
                                    $payment['id']
                                ) ?>

                            </td>


                            <td>

                                <span class="period-badge">

                                    <i class="bi <?= $periodIcon ?>"></i>

                                    <?= $periodName ?>
                                    <?= $periodNumber ?>

                                </span>

                            </td>


                            <td>

                                <strong>

                                    ₹<?= number_format(
                                        (float)(
                                            $payment['amount'] ?? 0
                                        ),
                                        2
                                    ) ?>

                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    safeDate(
                                        $payment['due_date']
                                        ?? null
                                    ) ?? '—'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    safeDate(
                                        $payment['payment_date']
                                        ?? null
                                    ) ?? '—'
                                ) ?>

                            </td>


                            <td>

                                <span class="paid-badge">

                                    <i class="bi bi-check-circle-fill"></i>

                                    Paid

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>


    </div>


</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function()
    {

        const progress =
            document.querySelector(
                '.progress-fill'
            );

        if (progress) {

            const value =
                Number(
                    progress.dataset.progress
                ) || 0;

            setTimeout(
                function()
                {

                    progress.style.width =
                        Math.min(
                            100,
                            value
                        ) + '%';

                },
                500
            );
        }

    }
);

</script>


<?php

include 'includes/footer.php';

?>