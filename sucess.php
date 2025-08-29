<?php
require_once('connection.php');
session_start();

// Optionally: verify payment using eSewa verify API here (recommended in production)

if (!isset($_SESSION['pending_booking']) || !isset($_SESSION['transaction_uuid'])) {
    echo "Invalid session. Cannot insert booking.";
    exit();
}

$booking = $_SESSION['pending_booking'];

$sql = "INSERT INTO booking (CAR_ID, EMAIL, BOOK_PLACE, BOOK_DATE, DURATION, PHONE_NUMBER, DESTINATION, PRICE, RETURN_DATE)
        VALUES (
            '{$booking['CAR_ID']}',
            '{$booking['EMAIL']}',
            '{$booking['BOOK_PLACE']}',
            '{$booking['BOOK_DATE']}',
            '{$booking['DURATION']}',
            '{$booking['PHONE_NUMBER']}',
            '{$booking['DESTINATION']}',
            '{$booking['PRICE']}',
            '{$booking['RETURN_DATE']}'
        )";

if (mysqli_query($con, $sql)) {
    // Booking success
    unset($_SESSION['pending_booking']);
    unset($_SESSION['transaction_uuid']);
    echo "✅ Booking successful and payment received.";
    // Optionally: redirect to a thank-you page
     
} else {
    echo "❌ Booking failed even though payment was successful.";
}
?>
<a href="cardetails.php" class="home-btn">Go to Homepage</a>
