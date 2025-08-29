<?php
session_start();
unset($_SESSION['pending_booking']);
unset($_SESSION['transaction_uuid']);
echo "❌ Payment failed. Your booking was not processed.";

?>
 <a href="cardetails.php" class="home-btn">Go to Homepage</a>