<?php
session_start();
require_once('connection.php');

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Join booking with cars to get car details
$sql = "SELECT b.BOOK_ID, b.CAR_ID, b.BOOK_DATE, b.RETURN_DATE, b.BOOK_STATUS,
               c.CAR_NAME, c.FUEL_TYPE, c.PRICE
        FROM booking b
        JOIN cars c ON b.CAR_ID = c.CAR_ID
        WHERE b.EMAIL = ?
        ORDER BY b.BOOK_DATE DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function calculateDays($start, $end) {
    $start_ts = strtotime($start);
    $end_ts = strtotime($end);
    $diff = $end_ts - $start_ts;
    return floor($diff / (60 * 60 * 24)) + 1; // inclusive days
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Booking History - Detailed</title>
    <style>
        table {
            border-collapse: collapse;
            width: 95%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        th {
            background-color: #ff7200;
            color: white;
        }
        caption {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #ff7200;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>

<table>
    <caption>Your Detailed Booking History</caption>
    <?php if ($result->num_rows > 0): ?>
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>Car Name</th>
            <th>Fuel Type</th>
            <th>Booking Date</th>
            <th>Return Date</th>
            <th>Days</th>
            <th>Price/Day</th>
            <th>Total Price</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): 
            $days = calculateDays($row['BOOK_DATE'], $row['RETURN_DATE']);
            $totalPrice = $days * $row['PRICE'];
        ?>
    <tr>
    <td><?= htmlspecialchars($row['BOOK_ID']) ?></td>
    <td><?= htmlspecialchars($row['CAR_NAME']) ?></td>
    <td><?= htmlspecialchars($row['FUEL_TYPE']) ?></td>
    <td><?= formatDate($row['BOOK_DATE']) ?></td>
    <td><?= formatDate($row['RETURN_DATE']) ?></td>
    <td><?= $days ?></td>
    <td><?= "Nrs. " . number_format($row['PRICE'], 2) ?></td>
    <td><?= "Nrs. " . number_format($totalPrice, 2) ?></td>
    <td><?= htmlspecialchars($row['BOOK_STATUS']) ?></td>
</tr>

        <?php endwhile; ?>
    </tbody>
    <?php else: ?>
    <tbody>
        <tr><td colspan="9" style="text-align:center;">You have no booking history yet.</td></tr>
    </tbody>
    <?php endif; ?>
</table>

</body>
</html>
