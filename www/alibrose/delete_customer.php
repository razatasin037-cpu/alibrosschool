<?php

date_default_timezone_set('Asia/Kolkata');

require_once 'includes/auth.php';
require_once 'config/db.php';


/* =====================================================
   CUSTOMER ID
===================================================== */

$id = (int)($_GET['id'] ?? 0);


if ($id <= 0) {

    header('Location: customers.php?error=invalid_id');
    exit;
}


/* =====================================================
   CHECK CUSTOMER
===================================================== */

$stmt = $conn->prepare("
    SELECT id, name
    FROM customers
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

$customer = $result->fetch_assoc();


if (!$customer) {

    header('Location: customers.php?error=not_found');
    exit;
}


/* =====================================================
   START TRANSACTION
===================================================== */

$conn->begin_transaction();


try {


    /* =================================================
       1. DELETE INSTALLMENT PAYMENTS
    ================================================= */

    $paymentDelete = $conn->prepare("
        DELETE FROM installment_payments
        WHERE customer_id = ?
    ");

    $paymentDelete->bind_param(
        "i",
        $id
    );

    $paymentDelete->execute();


    /* =================================================
       2. DELETE ACCOUNTS
    ================================================= */

    $accountDelete = $conn->prepare("
        DELETE FROM accounts
        WHERE customer_id = ?
    ");

    $accountDelete->bind_param(
        "i",
        $id
    );

    $accountDelete->execute();


    /* =================================================
       3. DELETE CUSTOMER
    ================================================= */

    $customerDelete = $conn->prepare("
        DELETE FROM customers
        WHERE id = ?
    ");

    $customerDelete->bind_param(
        "i",
        $id
    );

    $customerDelete->execute();


    /* =================================================
       CHECK CUSTOMER DELETE
    ================================================= */

    if (
        $customerDelete->affected_rows <= 0
    ) {

        throw new Exception(
            'Customer could not be deleted.'
        );
    }


    /* =================================================
       COMMIT
    ================================================= */

    $conn->commit();


    /* =================================================
       SUCCESS
    ================================================= */

    header(
        'Location: customers.php?deleted=1'
    );

    exit;


} catch (
    Throwable $e
) {


    /* =================================================
       ROLLBACK
    ================================================= */

    $conn->rollback();


    /*
    |--------------------------------------------------------------------------
    | Error message
    |--------------------------------------------------------------------------
    */

    header(
        'Location: customers.php?error=delete_failed'
    );

    exit;
}