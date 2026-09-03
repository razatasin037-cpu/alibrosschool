<?php

require_once '../config/db.php';

$page_title = 'Collection';

$plan = ($_GET['plan'] ?? 'Weekly') === 'Monthly'
    ? 'Monthly'
    : 'Weekly';

$today = date('Y-m-d');

$error = '';
$success = '';


function getFirstWeeklyDate($openingDate)
{
    $d = new DateTime($openingDate);

    if ((int)$d->format('N') !== 1) {
        $d->modify('next monday');
    }

    return $d;
}

function getDueDate($openingDate, $plan, $installmentNo)
{
    $installmentNo = max(1, (int)$installmentNo);

    if ($plan === 'Weekly') {

        $date = getFirstWeeklyDate($openingDate);

        if ($installmentNo > 1) {
            $date->modify(
                '+' . ($installmentNo - 1) . ' weeks'
            );
        }

        return $date->format('Y-m-d');
    }

    $opening = new DateTime($openingDate);

    $year = (int)$opening->format('Y');
    $month = (int)$opening->format('m');

    $monthIndex =
        ($month - 1) + ($installmentNo - 1);

    $year += intdiv($monthIndex, 12);
    $month = ($monthIndex % 12) + 1;

    $lastDay = cal_days_in_month(
        CAL_GREGORIAN,
        $month,
        $year
    );

    return sprintf(
        '%04d-%02d-%02d',
        $year,
        $month,
        min(10, $lastDay)
    );
}

function getPaymentRecords($conn, $customerId)
{
    $paid = [];

    $stmt = $conn->prepare("
        SELECT installment_no
        FROM installment_payments
        WHERE customer_id = ?
        ORDER BY installment_no ASC
    ");

    $stmt->bind_param('i', $customerId);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $paid[(int)$row['installment_no']] = true;
    }

    return $paid;
}

function getSchedule(
    $conn,
    $customerId,
    $openingDate,
    $plan,
    $totalInstallments,
    $today
) {
    $paid = getPaymentRecords(
        $conn,
        $customerId
    );

    $due = [];
    $future = [];

    for (
        $i = 1;
        $i <= $totalInstallments;
        $i++
    ) {
        $dueDate = getDueDate(
            $openingDate,
            $plan,
            $i
        );

        if (isset($paid[$i])) {
            continue;
        }

        if ($dueDate <= $today) {
            $due[] = [
                'installment_no' => $i,
                'due_date' => $dueDate
            ];
        } else {
            $future[] = [
                'installment_no' => $i,
                'due_date' => $dueDate
            ];
        }
    }

    return [
        'paid' => $paid,
        'paid_count' => count($paid),
        'due' => $due,
        'future' => $future
    ];
}

function getNextPaymentDate($schedule)
{
    if (!empty($schedule['due'])) {
        return $schedule['due'][0]['due_date'];
    }

    if (!empty($schedule['future'])) {
        return $schedule['future'][0]['due_date'];
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $selectedIds = $_POST['ids'] ?? [];

    if (!is_array($selectedIds)) {
        $selectedIds = [];
    }

    $selectedIds = array_values(
        array_unique(
            array_filter(
                array_map('intval', $selectedIds)
            )
        )
    );

    if (empty($selectedIds)) {

        $error = 'Pehle customer select karo.';

    } else {

        $conn->begin_transaction();

        try {

            $totalPaidAmount = 0;
            $totalInstallmentsPaid = 0;

            foreach ($selectedIds as $customerId) {

                $stmt = $conn->prepare("
                    SELECT
                        id,
                        name,
                        opening_date,
                        plan,
                        amount,
                        total_installments,
                        status
                    FROM customers
                    WHERE id = ?
                    AND plan = ?
                    AND status = 'Active'
                    LIMIT 1
                ");

                $stmt->bind_param(
                    'is',
                    $customerId,
                    $plan
                );

                $stmt->execute();

                $customer =
                    $stmt->get_result()->fetch_assoc();

                if (!$customer) {
                    continue;
                }

                $schedule = getSchedule(
                    $conn,
                    $customerId,
                    $customer['opening_date'],
                    $plan,
                    (int)$customer['total_installments'],
                    $today
                );

                if (empty($schedule['due'])) {
                    continue;
                }

                $amount = (float)$customer['amount'];

                foreach ($schedule['due'] as $due) {

                    $payment = $conn->prepare("
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

                    $payment->bind_param(
                        'iidss',
                        $customerId,
                        $due['installment_no'],
                        $amount,
                        $today,
                        $due['due_date']
                    );

                    if (!$payment->execute()) {
                        throw new Exception(
                            $payment->error
                        );
                    }

                    $totalPaidAmount += $amount;
                    $totalInstallmentsPaid++;
                }

                $afterPayment = getSchedule(
                    $conn,
                    $customerId,
                    $customer['opening_date'],
                    $plan,
                    (int)$customer['total_installments'],
                    $today
                );

                $paidCount =
                    $afterPayment['paid_count'];

                if (
                    $paidCount >=
                    (int)$customer['total_installments']
                ) {

                    $update = $conn->prepare("
                        UPDATE customers
                        SET
                            paid_installments = ?,
                            next_due_date = NULL,
                            status = 'Completed'
                        WHERE id = ?
                    ");

                    $update->bind_param(
                        'ii',
                        $paidCount,
                        $customerId
                    );

                } else {

                    $nextDue =
                        getNextPaymentDate(
                            $afterPayment
                        );

                    $update = $conn->prepare("
                        UPDATE customers
                        SET
                            paid_installments = ?,
                            next_due_date = ?,
                            status = 'Active'
                        WHERE id = ?
                    ");

                    $update->bind_param(
                        'isi',
                        $paidCount,
                        $nextDue,
                        $customerId
                    );
                }

                if (!$update->execute()) {
                    throw new Exception(
                        $update->error
                    );
                }
            }

            if ($totalInstallmentsPaid === 0) {
                throw new Exception(
                    'Selected customers ka aaj tak koi unpaid installment nahi hai.'
                );
            }

            $conn->commit();

            $success =
                $totalInstallmentsPaid .
                ' installment receive hue. Total ₹' .
                number_format(
                    $totalPaidAmount,
                    2
                );

        } catch (Throwable $e) {

            $conn->rollback();

            $error = $e->getMessage();
        }
    }
}

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        father_name,
        mobile,
        opening_date,
        plan,
        amount,
        total_installments,
        paid_installments,
        next_due_date,
        status
    FROM customers
    WHERE plan = ?
    AND status = 'Active'
    ORDER BY name ASC
");

$stmt->bind_param('s', $plan);
$stmt->execute();

$result = $stmt->get_result();

$customers = [];
$totalDueAmount = 0;
$totalDueCustomers = 0;
$totalFutureCustomers = 0;

while ($customer = $result->fetch_assoc()) {

    $schedule = getSchedule(
        $conn,
        (int)$customer['id'],
        $customer['opening_date'],
        $plan,
        (int)$customer['total_installments'],
        $today
    );

    $customer['due_count'] =
        count($schedule['due']);

    $customer['due_amount'] =
        $customer['due_count'] *
        (float)$customer['amount'];

    $customer['due_installments'] =
        $schedule['due'];

    $customer['next_future_due'] =
        getNextPaymentDate($schedule);

    $customer['paid_installments'] =
        $schedule['paid_count'];

    $customers[] = $customer;

    if ($customer['due_count'] > 0) {

        $totalDueCustomers++;

        $totalDueAmount +=
            $customer['due_amount'];

    } elseif (!empty($schedule['future'])) {

        $totalFutureCustomers++;
    }
}

include '../includes/header.php';

?>

<style>

.collection-hero {
    background:
        linear-gradient(
            135deg,
            #7b123f,
            #d81b72,
            #f04a9b
        );

    color: #fff;

    border-radius: 26px;

    padding: 28px;

    box-shadow:
        0 20px 50px #d81b7225;
}


.collection-stat {
    background: #fff;

    border: 1px solid #f1d8e5;

    border-radius: 20px;

    padding: 20px;

    height: 100%;

    box-shadow:
        0 10px 30px #7b123f0c;
}


.collection-stat-value {
    font-size: 27px;

    font-weight: 800;
}


.customer-row {
    transition: .15s;
}


.customer-row:hover {
    background: #fff7fb;
}


.pay-check {
    width: 19px;

    height: 19px;

    accent-color: #d81b72;

    cursor: pointer;
}


.locked-row {
    opacity: .58;

    background: #faf8f9;
}


.lock-icon {
    width: 34px;

    height: 34px;

    border-radius: 10px;

    display: grid;

    place-items: center;

    background: #f1edf0;

    color: #927f89;
}


.due-badge {
    background: #fff0f0;

    color: #b91c1c;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 800;
}


.upcoming-badge {
    background: #f3f0ff;

    color: #6d28d9;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 800;
}


.payment-bar {
    position: sticky;

    bottom: 15px;

    z-index: 100;

    background: #fff;

    border: 1px solid #f1d8e5;

    border-radius: 20px;

    padding: 15px 18px;

    box-shadow:
        0 15px 40px #7b123f20;
}



/* PREMIUM COLLECTION ANIMATIONS */

.collection-hero {
    position: relative;
    overflow: hidden;
    animation: heroIn .7s cubic-bezier(.22,1,.36,1) both;
}

.collection-hero::before {
    content: "";
    position: absolute;
    width: 280px;
    height: 280px;
    border-radius: 50%;
    right: -90px;
    top: -150px;
    border: 1px solid rgba(255,255,255,.18);
    box-shadow:
        0 0 0 28px rgba(255,255,255,.035),
        0 0 0 56px rgba(255,255,255,.025);
    animation: heroOrb 12s linear infinite;
}

.collection-hero::after {
    content: "";
    position: absolute;
    left: -80px;
    bottom: -130px;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
    animation: softFloat 6s ease-in-out infinite;
}

.collection-hero > * {
    position: relative;
    z-index: 2;
}

.collection-stat {
    position: relative;
    overflow: hidden;
    animation: statIn .65s cubic-bezier(.22,1,.36,1) both;
}

.collection-stat:nth-child(1) {
    animation-delay: .08s;
}

.collection-stat:nth-child(2) {
    animation-delay: .16s;
}

.collection-stat:nth-child(3) {
    animation-delay: .24s;
}

.collection-stat:nth-child(4) {
    animation-delay: .32s;
}

.collection-stat::after {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 70%;
    height: 100%;
    transform: skewX(-20deg);
    background: linear-gradient(
        90deg,
        transparent,
        rgba(216,27,114,.06),
        transparent
    );
    transition: left .7s ease;
}

.collection-stat:hover::after {
    left: 140%;
}

.collection-stat:hover {
    transform: translateY(-5px);
    transition: transform .35s ease, box-shadow .35s ease;
    box-shadow: 0 18px 40px rgba(123,18,63,.13);
}

.customer-row {
    animation: rowIn .45s ease both;
}

.customer-row:nth-child(1) { animation-delay: .05s; }
.customer-row:nth-child(2) { animation-delay: .08s; }
.customer-row:nth-child(3) { animation-delay: .11s; }
.customer-row:nth-child(4) { animation-delay: .14s; }
.customer-row:nth-child(5) { animation-delay: .17s; }
.customer-row:nth-child(6) { animation-delay: .20s; }
.customer-row:nth-child(7) { animation-delay: .23s; }
.customer-row:nth-child(8) { animation-delay: .26s; }

.customer-row:hover {
    transform: translateX(3px);
    transition: transform .25s ease, background .25s ease;
}

.due-badge,
.upcoming-badge {
    transition: transform .25s ease, box-shadow .25s ease;
}

.due-badge:hover,
.upcoming-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(216,27,114,.10);
}

.lock-icon {
    transition: transform .3s ease, background .3s ease;
}

.locked-row:hover .lock-icon {
    transform: rotate(-8deg) scale(1.08);
    background: #f7eaf1;
}

.payment-bar {
    animation: barIn .7s cubic-bezier(.22,1,.36,1) .25s both;
}

.btn-pink {
    position: relative;
    overflow: hidden;
    transition: transform .3s ease, box-shadow .3s ease;
}

.btn-pink::after {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 70%;
    height: 100%;
    transform: skewX(-20deg);
    background: rgba(255,255,255,.18);
    transition: left .65s ease;
}

.btn-pink:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(216,27,114,.22);
}

.btn-pink:hover::after {
    left: 135%;
}

@keyframes heroIn {
    from {
        opacity: 0;
        transform: translateY(-18px) scale(.98);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes heroOrb {
    to {
        transform: rotate(360deg);
    }
}

@keyframes softFloat {
    50% {
        transform: translate(35px,-18px) scale(1.05);
    }
}

@keyframes statIn {
    from {
        opacity: 0;
        transform: translateY(22px) scale(.97);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes rowIn {
    from {
        opacity: 0;
        transform: translateX(-12px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes barIn {
    from {
        opacity: 0;
        transform: translateY(25px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .collection-hero,
    .collection-stat,
    .customer-row,
    .payment-bar {
        animation: none !important;
    }
}


/* =========================================================
   ULTRA PREMIUM COLLECTION MOTION SYSTEM
========================================================= */

html {
    scroll-behavior: smooth;
}

.collection-hero {
    isolation: isolate;
    transform: translateY(18px);
    opacity: 0;
    animation: premiumHero .9s cubic-bezier(.16,1,.3,1) forwards;
}

.collection-hero::before {
    width: 420px;
    height: 420px;
    right: -170px;
    top: -260px;
    border: 1px solid rgba(255,255,255,.28);
    box-shadow:
        0 0 0 35px rgba(255,255,255,.035),
        0 0 0 70px rgba(255,255,255,.025),
        0 0 80px rgba(255,255,255,.12);
    animation:
        heroOrbit 16s linear infinite,
        heroGlow 4s ease-in-out infinite;
}

.collection-hero::after {
    width: 300px;
    height: 300px;
    left: -130px;
    bottom: -190px;
    background: radial-gradient(
        circle,
        rgba(255,255,255,.16) 0,
        rgba(255,255,255,.04) 45%,
        transparent 72%
    );
    filter: blur(2px);
    animation: heroFloat 7s ease-in-out infinite;
}

.collection-hero h2 {
    animation: titleReveal .8s cubic-bezier(.16,1,.3,1) .18s both;
}

.collection-hero .small,
.collection-hero .opacity-75 {
    animation: subtitleReveal .7s ease .32s both;
}

.collection-hero .btn {
    position: relative;
    overflow: hidden;
    transform: translateY(12px);
    opacity: 0;
    animation: buttonReveal .7s cubic-bezier(.16,1,.3,1) .42s forwards;
    transition:
        transform .3s ease,
        box-shadow .3s ease,
        background .3s ease;
}

.collection-hero .btn:nth-child(2) {
    animation-delay: .5s;
}

.collection-hero .btn:hover {
    transform: translateY(-3px) scale(1.035);
    box-shadow: 0 12px 28px rgba(0,0,0,.16);
}

.collection-hero .btn::after {
    content: "";
    position: absolute;
    inset: -50% auto -50% -80%;
    width: 55%;
    transform: rotate(20deg);
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255,255,255,.65),
        transparent
    );
    transition: left .7s ease;
}

.collection-hero .btn:hover::after {
    left: 140%;
}

.collection-stat {
    opacity: 0;
    transform: translateY(35px) scale(.94);
    animation: statReveal .7s cubic-bezier(.16,1,.3,1) forwards;
}

.collection-stat:nth-child(1) { animation-delay: .16s; }
.collection-stat:nth-child(2) { animation-delay: .25s; }
.collection-stat:nth-child(3) { animation-delay: .34s; }
.collection-stat:nth-child(4) { animation-delay: .43s; }

.collection-stat::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(
        135deg,
        rgba(216,27,114,.35),
        transparent 40%,
        rgba(123,18,63,.18)
    );
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity .35s ease;
    pointer-events: none;
}

.collection-stat:hover::before {
    opacity: 1;
}

.collection-stat:hover {
    transform: translateY(-8px) scale(1.015);
}

.collection-stat-value {
    transition: transform .3s ease;
}

.collection-stat:hover .collection-stat-value {
    transform: scale(1.06);
}

.customer-row {
    opacity: 0;
    animation: rowReveal .5s cubic-bezier(.16,1,.3,1) forwards;
}

.customer-row:nth-child(1) { animation-delay: .08s; }
.customer-row:nth-child(2) { animation-delay: .12s; }
.customer-row:nth-child(3) { animation-delay: .16s; }
.customer-row:nth-child(4) { animation-delay: .20s; }
.customer-row:nth-child(5) { animation-delay: .24s; }
.customer-row:nth-child(6) { animation-delay: .28s; }
.customer-row:nth-child(7) { animation-delay: .32s; }
.customer-row:nth-child(8) { animation-delay: .36s; }
.customer-row:nth-child(9) { animation-delay: .40s; }
.customer-row:nth-child(10) { animation-delay: .44s; }

.customer-row:hover {
    transform: translateX(6px) !important;
    box-shadow:
        inset 4px 0 0 #d81b72,
        0 7px 20px rgba(123,18,63,.06);
}

.pay-check {
    position: relative;
    transition:
        transform .2s ease,
        filter .2s ease;
}

.pay-check:hover {
    transform: scale(1.18);
    filter: drop-shadow(0 0 6px rgba(216,27,114,.35));
}

.pay-check:checked {
    animation: checkPop .35s cubic-bezier(.16,1,.3,1);
}

.due-badge,
.upcoming-badge {
    transition:
        transform .3s ease,
        box-shadow .3s ease;
}

.due-badge {
    animation: duePulse 2.2s ease-in-out infinite;
}

.upcoming-badge {
    animation: lockedGlow 3s ease-in-out infinite;
}

.lock-icon {
    position: relative;
    transition:
        transform .4s cubic-bezier(.16,1,.3,1),
        box-shadow .3s ease;
}

.lock-icon::after {
    content: "";
    position: absolute;
    inset: -5px;
    border-radius: 14px;
    border: 1px solid rgba(146,127,137,.25);
    animation: lockRing 2.2s ease-out infinite;
}

.locked-row:hover .lock-icon {
    transform: rotate(-8deg) scale(1.16);
    box-shadow: 0 8px 20px rgba(123,18,63,.12);
}

.payment-bar {
    transform: translateY(40px);
    opacity: 0;
    animation: paymentReveal .8s cubic-bezier(.16,1,.3,1) .55s forwards;
    backdrop-filter: blur(14px);
}

.payment-bar::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background:
        linear-gradient(
            100deg,
            rgba(216,27,114,.04),
            transparent 35%,
            rgba(216,27,114,.035)
        );
    pointer-events: none;
}

.btn-pink {
    position: relative;
    overflow: hidden;
    transform: translateZ(0);
    transition:
        transform .3s cubic-bezier(.16,1,.3,1),
        box-shadow .3s ease;
}

.btn-pink:hover:not(:disabled) {
    transform: translateY(-3px) scale(1.01);
    box-shadow:
        0 14px 32px rgba(216,27,114,.28),
        0 0 0 4px rgba(216,27,114,.06);
}

.btn-pink:active:not(:disabled) {
    transform: scale(.97);
}

.btn-pink::before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: -100%;
    width: 55%;
    transform: skewX(-22deg);
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255,255,255,.38),
        transparent
    );
    animation: buttonShine 3.5s ease-in-out infinite;
}

#selectedAmount,
#selectedTopAmount,
#selectedCount {
    transition: transform .25s ease;
}

.selection-bump {
    animation: selectionBump .3s cubic-bezier(.16,1,.3,1);
}

@keyframes premiumHero {
    from {
        opacity: 0;
        transform: translateY(30px) scale(.97);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes titleReveal {
    from {
        opacity: 0;
        transform: translateY(18px);
        filter: blur(8px);
    }
    to {
        opacity: 1;
        transform: none;
        filter: blur(0);
    }
}

@keyframes subtitleReveal {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes buttonReveal {
    from {
        opacity: 0;
        transform: translateY(18px) scale(.94);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes statReveal {
    from {
        opacity: 0;
        transform: translateY(35px) scale(.94);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes rowReveal {
    from {
        opacity: 0;
        transform: translateX(-18px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes paymentReveal {
    from {
        opacity: 0;
        transform: translateY(40px) scale(.98);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@keyframes heroOrbit {
    to {
        transform: rotate(360deg);
    }
}

@keyframes heroGlow {
    50% {
        box-shadow:
            0 0 0 35px rgba(255,255,255,.055),
            0 0 0 70px rgba(255,255,255,.035),
            0 0 110px rgba(255,255,255,.2);
    }
}

@keyframes heroFloat {
    50% {
        transform: translate(45px,-25px) scale(1.08);
    }
}

@keyframes checkPop {
    0% { transform: scale(1); }
    45% { transform: scale(1.4); }
    100% { transform: scale(1); }
}

@keyframes duePulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(185,28,28,0);
    }
    50% {
        box-shadow: 0 0 0 5px rgba(185,28,28,.07);
    }
}

@keyframes lockedGlow {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(109,40,217,0);
    }
    50% {
        box-shadow: 0 0 0 5px rgba(109,40,217,.06);
    }
}

@keyframes lockRing {
    0% {
        opacity: .5;
        transform: scale(.85);
    }
    70%, 100% {
        opacity: 0;
        transform: scale(1.25);
    }
}

@keyframes buttonShine {
    0%, 55% {
        left: -100%;
    }
    75%, 100% {
        left: 150%;
    }
}

@keyframes selectionBump {
    50% {
        transform: scale(1.12);
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
    }
}

</style>


<div class="collection-hero mb-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

        <div>

            <div class="small opacity-75">
                GULABI ALIBROSE
            </div>

            <h2 class="fw-bold mb-1">
                <?= $plan ?> Collection
            </h2>

            <div class="opacity-75">
                Today:
                <?= date('d M Y') ?>
            </div>

        </div>


        <div class="d-flex gap-2">

            <a
                href="?plan=Weekly"
                class="btn <?= $plan === 'Weekly'
                    ? 'btn-light'
                    : 'btn-outline-light'
                ?>"
            >
                Weekly
            </a>


            <a
                href="?plan=Monthly"
                class="btn <?= $plan === 'Monthly'
                    ? 'btn-light'
                    : 'btn-outline-light'
                ?>"
            >
                Monthly
            </a>

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


    <div class="col-md-6 col-xl-3">

        <div class="collection-stat">

            <div class="muted small">
                DUE CUSTOMERS
            </div>

            <div class="collection-stat-value mt-2">
                <?= $totalDueCustomers ?>
            </div>

            <small class="muted">
                Aaj tak due
            </small>

        </div>

    </div>


    <div class="col-md-6 col-xl-3">

        <div class="collection-stat">

            <div class="muted small">
                TOTAL DUE
            </div>

            <div class="collection-stat-value mt-2 amount-red">
                ₹<?= number_format(
                    $totalDueAmount,
                    2
                ) ?>
            </div>

            <small class="muted">
                Sab unpaid installments
            </small>

        </div>

    </div>


    <div class="col-md-6 col-xl-3">

        <div class="collection-stat">

            <div class="muted small">
                FUTURE / LOCKED
            </div>

            <div class="collection-stat-value mt-2">
                <?= $totalFutureCustomers ?>
            </div>

            <small class="muted">
                Date aane par unlock
            </small>

        </div>

    </div>


    <div class="col-md-6 col-xl-3">

        <div class="collection-stat">

            <div class="muted small">
                SELECTED
            </div>

            <div
                class="collection-stat-value mt-2"
                id="selectedTopAmount"
            >
                ₹0.00
            </div>

            <small class="muted">
                Selected customers
            </small>

        </div>

    </div>

</div>


<form
    method="post"
    id="paymentForm"
>


<div class="cardx p-3">


    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">

        <div>

            <h4 class="fw-bold mb-1">
                <?= $plan ?> Customers
            </h4>

            <div class="muted small">
                Aaj tak ke due installments payable hain.
                Future installments locked hain.
            </div>

        </div>


        <label
            class="d-flex align-items-center gap-2"
            style="cursor:pointer"
        >

            <input
                type="checkbox"
                class="pay-check"
                id="selectAll"
            >

            <strong>
                Select All Due
            </strong>

        </label>

    </div>


    <div class="table-wrap">

        <table class="table align-middle">

            <thead>

            <tr>

                <th width="50">
                    Select
                </th>

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
                    Due Installments
                </th>

                <th>
                    Next Date
                </th>

                <th>
                    Status
                </th>

            </tr>

            </thead>


            <tbody>


            <?php if (empty($customers)): ?>

                <tr>

                    <td
                        colspan="7"
                        class="text-center py-5"
                    >

                        <div class="iconbox mx-auto mb-3">

                            <i class="bi bi-people"></i>

                        </div>

                        <h5 class="fw-bold">
                            No Customers
                        </h5>

                        <div class="muted">
                            Is plan mein koi customer nahi hai.
                        </div>

                    </td>

                </tr>

            <?php endif; ?>


            <?php foreach ($customers as $customer): ?>

                <?php

                $hasDue =
                    $customer['due_count'] > 0;

                ?>


                <tr
                    class="customer-row <?= !$hasDue
                        ? 'locked-row'
                        : ''
                    ?>"
                >


                    <td>

                        <?php if ($hasDue): ?>

                            <input
                                type="checkbox"
                                class="pay-check customer-check"
                                name="ids[]"
                                value="<?= (int)$customer['id'] ?>"
                                data-amount="<?= (float)$customer['due_amount'] ?>"
                            >

                        <?php else: ?>

                            <div
                                class="lock-icon"
                                title="Future payment locked"
                            >

                                <i class="bi bi-lock-fill"></i>

                            </div>

                        <?php endif; ?>

                    </td>


                    <td>

                        <strong>
                            <?= htmlspecialchars(
                                $customer['name']
                            ) ?>
                        </strong>

                        <?php if (!empty($customer['mobile'])): ?>

                            <br>

                            <small class="muted">
                                <?= htmlspecialchars(
                                    $customer['mobile']
                                ) ?>
                            </small>

                        <?php endif; ?>

                    </td>


                    <td>

                        <span class="badge text-bg-light">
                            <?= htmlspecialchars(
                                $customer['plan']
                            ) ?>
                        </span>

                    </td>


                    <td>

                        ₹<?= number_format(
                            (float)$customer['amount'],
                            2
                        ) ?>

                    </td>


                    <td>

                        <?php if ($hasDue): ?>

                            <span class="due-badge">

                                <?= $customer['due_count'] ?>

                                Due

                            </span>

                            <br>

                            <strong class="amount-red">

                                ₹<?= number_format(
                                    $customer['due_amount'],
                                    2
                                ) ?>

                            </strong>

                        <?php else: ?>

                            <span class="upcoming-badge">

                                <i class="bi bi-lock-fill"></i>

                                Upcoming

                            </span>

                        <?php endif; ?>

                    </td>


                    <td>

                        <?php if ($hasDue): ?>

                            <strong>

                                <?= date(
                                    'd M Y',
                                    strtotime(
                                        $customer[
                                            'due_installments'
                                        ][0]['due_date']
                                    )
                                ) ?>

                            </strong>

                            <?php if (
                                $customer['next_future_due']
                            ): ?>

                                <br>

                                <small class="muted">

                                    Next:
                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $customer[
                                                'next_future_due'
                                            ]
                                        )
                                    ) ?>

                                </small>

                            <?php endif; ?>

                        <?php else: ?>

                            <strong>

                                <?= date(
                                    'd M Y',
                                    strtotime(
                                        $customer[
                                            'next_future_due'
                                        ]
                                    )
                                ) ?>

                            </strong>

                            <br>

                            <small class="muted">
                                Locked
                            </small>

                        <?php endif; ?>

                    </td>


                    <td>

                        <?php if ($hasDue): ?>

                            <span class="due-badge">
                                PAYMENT DUE
                            </span>

                        <?php else: ?>

                            <span class="upcoming-badge">
                                <i class="bi bi-lock-fill"></i>
                                LOCKED
                            </span>

                        <?php endif; ?>

                    </td>


                </tr>


            <?php endforeach; ?>


            </tbody>

        </table>

    </div>


</div>


<div class="payment-bar mt-3">

    <div class="row align-items-center g-3">


        <div class="col-md-3">

            <div class="muted small">
                SELECTED CUSTOMERS
            </div>

            <strong
                id="selectedCount"
                class="fs-5"
            >
                0
            </strong>

        </div>


        <div class="col-md-3">

            <div class="muted small">
                TOTAL PAYMENT
            </div>

            <strong
                id="selectedAmount"
                class="fs-5"
            >
                ₹0.00
            </strong>

        </div>


        <div class="col-md-6">

            <button
                type="submit"
                name="pay_selected"
                class="btn btn-pink w-100"
                id="payButton"
                disabled
            >

                <i class="bi bi-cash-coin me-1"></i>

                Pay All Selected Due

            </button>

        </div>


    </div>

</div>


</form>


<script>

const selectAll =
    document.getElementById('selectAll');

const checks =
    document.querySelectorAll('.customer-check');

const selectedCount =
    document.getElementById('selectedCount');

const selectedAmount =
    document.getElementById('selectedAmount');

const selectedTopAmount =
    document.getElementById('selectedTopAmount');

const payButton =
    document.getElementById('payButton');


function updateSelection()
{

    let count = 0;

    let amount = 0;


    checks.forEach(
        function(check)
        {

            if (check.checked)
            {

                count++;

                amount += Number(
                    check.dataset.amount || 0
                );

            }

        }
    );


    selectedCount.textContent =
        count;

    selectedCount.classList.remove('selection-bump');
    selectedAmount.classList.remove('selection-bump');
    selectedTopAmount.classList.remove('selection-bump');

    void selectedCount.offsetWidth;

    selectedCount.classList.add('selection-bump');
    selectedAmount.classList.add('selection-bump');
    selectedTopAmount.classList.add('selection-bump');


    const formatted =
        '₹' +
        amount.toLocaleString(
            'en-IN',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );


    selectedAmount.textContent =
        formatted;


    selectedTopAmount.textContent =
        formatted;


    payButton.disabled =
        count === 0;


    if (selectAll)
    {

        selectAll.checked =
            checks.length > 0 &&
            count === checks.length;

    }

}


selectAll?.addEventListener(
    'change',
    function()
    {

        checks.forEach(
            function(check)
            {

                check.checked =
                    selectAll.checked;

            }
        );

        updateSelection();

    }
);


checks.forEach(
    function(check)
    {

        check.addEventListener(
            'change',
            updateSelection
        );

    }
);


document
    .getElementById('paymentForm')
    ?.addEventListener(
        'submit',
        function(event)
        {

            let count = 0;

            checks.forEach(
                function(check)
                {

                    if (check.checked)
                    {
                        count++;
                    }

                }
            );


            if (count === 0)
            {

                event.preventDefault();

                alert(
                    'Pehle due customer select karo.'
                );

                return;

            }


            if (
                !confirm(
                    count +
                    ' customer ke aaj tak ke saare due installments receive karne hain?'
                )
            )
            {

                event.preventDefault();

            }

        }
    );


updateSelection();

</script>



<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.collection-stat-value').forEach(function (el) {

        const original = el.textContent.trim();

        if (!original || original === '0' || original === '₹0.00') {
            return;
        }

        const match = original.match(/[\d,.]+/);

        if (!match) {
            return;
        }

        const number = parseFloat(
            match[0].replace(/,/g, '')
        );

        if (!Number.isFinite(number) || number <= 0) {
            return;
        }

        const prefix = original.startsWith('₹') ? '₹' : '';
        const hasDecimal = original.includes('.00');

        let start = 0;
        const duration = 700;
        const startTime = performance.now();

        function animateValue(now) {

            const progress = Math.min(
                (now - startTime) / duration,
                1
            );

            const eased =
                1 - Math.pow(1 - progress, 3);

            const value = number * eased;

            el.textContent =
                prefix +
                value.toLocaleString(
                    'en-IN',
                    {
                        minimumFractionDigits:
                            hasDecimal ? 2 : 0,
                        maximumFractionDigits:
                            hasDecimal ? 2 : 0
                    }
                );

            if (progress < 1) {
                requestAnimationFrame(animateValue);
            }
        }

        requestAnimationFrame(animateValue);
    });

});
</script>

<?php include '../includes/footer.php'; ?>