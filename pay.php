<?php
require_once('connection.php');
session_start();

if (!isset($_SESSION['pending_booking'])) {
    echo "No pending booking data.";
    exit();
}

$booking = $_SESSION['pending_booking'];

$amount = $booking['PRICE'];
$taxAmount = 0;
$serviceCharge = 0;
$deliveryCharge = 0;
$totalAmount = $amount + $taxAmount + $serviceCharge + $deliveryCharge;

$productCode = "EPAYTEST";
$secretKey = "8gBm/:&EnhH.1/q";

// Generate unique transaction ID (you can also save it in session if needed)
$transactionUUID = uniqid("TXN_");

$_SESSION['transaction_uuid'] = $transactionUUID;

$successUrl = "http://localhost/car_rental_project/sucess.php";
$failureUrl = "http://localhost/car_rental_project/failed.php";

$signedFieldNames = "total_amount,transaction_uuid,product_code";
$stringToSign = "total_amount=$totalAmount,transaction_uuid=$transactionUUID,product_code=$productCode";
$signature = base64_encode(hash_hmac('sha256', $stringToSign, $secretKey, true));
?>

<form id="esewaPaymentForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
    <input type="hidden" name="amount" value="<?= $amount ?>">
    <input type="hidden" name="tax_amount" value="<?= $taxAmount ?>">
    <input type="hidden" name="total_amount" value="<?= $totalAmount ?>">
    <input type="hidden" name="transaction_uuid" value="<?= $transactionUUID ?>">
    <input type="hidden" name="product_code" value="<?= $productCode ?>">
    <input type="hidden" name="product_service_charge" value="<?= $serviceCharge ?>">
    <input type="hidden" name="product_delivery_charge" value="<?= $deliveryCharge ?>">
    <input type="hidden" name="success_url" value="<?= $successUrl ?>">
    <input type="hidden" name="failure_url" value="<?= $failureUrl ?>">
    <input type="hidden" name="signed_field_names" value="<?= $signedFieldNames ?>">
    <input type="hidden" name="signature" value="<?= $signature ?>">
</form>

<script>
    document.getElementById('esewaPaymentForm').submit();
</script>
