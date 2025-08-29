<?php 
    // connection.php should contain your mysqli connection code ($con)
    require_once('connection.php');
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['email'])) {
        header("Location: index.html"); // Redirect to login if not logged in
        exit();
    }

    // --- Fetch Car and User Details ---
    $carid = $_GET['id'];
    $sql_car = "SELECT * FROM cars WHERE CAR_ID='$carid'";
    $car_result = mysqli_query($con, $sql_car);
    $car_details = mysqli_fetch_assoc($car_result);
    $carprice = $car_details['PRICE'];

    $value = $_SESSION['email'];
    $sql_user = "SELECT * FROM users WHERE EMAIL='$value'";
    $user_result = mysqli_query($con, $sql_user);
    $user_rows = mysqli_fetch_assoc($user_result);
    $uemail = $user_rows['EMAIL'];

// Get the most popular destination from the database
$popularDestQuery = "SELECT DESTINATION FROM booking GROUP BY DESTINATION ORDER BY COUNT(*) DESC LIMIT 1";
$popularDestResult = mysqli_query($con, $popularDestQuery);
$mostPopularDestination = $popularDestResult ? mysqli_fetch_assoc($popularDestResult)['DESTINATION'] : null;

// In your form submission handling:


    // --- Handle Form Submission ---
    if(isset($_POST['book'])){
        
        $bplace = mysqli_real_escape_string($con, $_POST['place']);
        $bdate = date('Y-m-d', strtotime($_POST['date']));
        $dur = mysqli_real_escape_string($con, $_POST['dur']);
        $phno = mysqli_real_escape_string($con, $_POST['ph']);
        $des = mysqli_real_escape_string($con, $_POST['des']);
        $rdate = date('Y-m-d', strtotime($_POST['rdate']));
        
        if(empty($bplace) || empty($bdate) || empty($dur) || empty($phno) || empty($des) || empty($rdate)){
            echo '<script>alert("Please fill all fields.")</script>';
        } else {
           if($bdate < $rdate){
        // Base price calculation
        $price = $dur * $carprice;
        
       $bookingDayOfWeek = date('N', strtotime($bdate)); // 6 = Saturday, 7 = Sunday
if ($bookingDayOfWeek >= 6) {
    $price += $price * 0.15; // Correct 15% weekend surcharge
}

        
        // Popular destination surcharge (20% increase)
        if ($mostPopularDestination && strtolower($des) == strtolower($mostPopularDestination)) {
            $price *= 1.20; // 20% surcharge
            $isPopularDestination = true;
        }

$_SESSION['pending_booking'] = [
    'CAR_ID' => $carid,
    'EMAIL' => $uemail,
    'BOOK_PLACE' => $bplace,
    'BOOK_DATE' => $bdate,
    'DURATION' => $dur,
    'PHONE_NUMBER' => $phno,
    'DESTINATION' => $des,
    'PRICE' => $price,
    'RETURN_DATE' => $rdate
];

header("Location: pay.php");
exit();
                
                if($result){
                    $_SESSION['email'] = $uemail;
                    header("Location: pay.php");
                    exit();
                } else {
                    echo '<script>alert("Booking failed. Please check connection.")</script>';
                }
            } else {
                echo '<script>alert("Return date must be after the booking date.")</script>';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAR BOOKING</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Basic Reset & Font */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
           
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
        }

        /* Layout */
        .container {
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 2rem;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        /* Form Styling */
        h2 { text-align: center; margin-bottom: 1.5rem; color: #ff7200; font-size: 2rem; }
        form label { font-size: 1rem; font-style: italic; display: block; margin-bottom: 0.5rem; }
        form input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #fff;
            color: #333;
            box-shadow: inset 1px 1px 5px rgba(0,0,0,0.1);
            outline: none;
            transition: border-color 0.3s;
        }
        form input:focus { border-color: #ff7200; }
        .btnn {
            width: 100%;
            padding: 0.75rem;
            background: #ff7200;
            border: none;
            margin-top: 1rem;
            font-size: 1.1rem;
            border-radius: 6px;
            cursor: pointer;
            color: #fff;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }
        .btnn:hover { background: #fff; color: #ff7200; }
        #priceEstimate {
            color: #ff7200;
            font-size: 1.25rem;
            font-weight: bold;
            text-align: center;
            margin-top: 1rem;
            padding: 0.5rem;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 6px;
            min-height: 3rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <form id="register" method="POST">
            <h2>BOOKING</h2>
            <h3>CAR: <?php echo htmlspecialchars($car_details['CAR_NAME']); ?></h3><br>

            <label for="place">Booking Place:</label>
            <input type="text" name="place" id="place" placeholder="Enter Pickup Location" required>

            <label for="datefield">Booking Date:</label>
            <input type="date" name="date" id="datefield" required>

            <label for="dur">Duration (days):</label>
            <input type="number" name="dur" min="1" max="30" id="dur" placeholder="Enter Rent Period (e.g., 3)" required>

            <label for="ph">Phone Number:</label>
            <input type="tel" name="ph" maxlength="10" id="ph" placeholder="Enter Your Phone Number" required>

            <label for="des">Destination:</label>
            <input type="text" name="des" id="des" placeholder="Enter Your Destination" required>

            <label for="dfield">Return Date:</label>
            <input type="date" name="rdate" id="dfield" required>

            <div id="priceEstimate"></div>

            <input type="submit" class="btnn" value="BOOK NOW" name="book">
        </form>
    </div>

<script>
    // --- Set min dates to prevent booking in the past ---
    var today = new Date().toISOString().split('T')[0];
    document.getElementById("datefield").setAttribute("min", today);
    document.getElementById("dfield").setAttribute("min", today);


    // --- CORRECTED DYNAMIC PRICE CALCULATION (JavaScript) ---
    const carPricePerDay = <?php echo json_encode($carprice, JSON_NUMERIC_CHECK); ?>;
    let pricingFactors = {};


// Update your calculatePrice function:
function calculatePrice() {
    const durationInput = document.getElementById('dur').value;
    const bookingDateInput = document.getElementById('datefield').value;
    const destinationInput = document.getElementById('des').value;
    const priceEstimateDiv = document.getElementById('priceEstimate');

    if (durationInput && bookingDateInput) {
        const duration = parseInt(durationInput, 10);
        let totalPrice = duration * carPricePerDay;
        let surcharges = [];

        // Weekend surcharge
        const bookingDay = new Date(bookingDateInput).getDay();
        if (bookingDay === 0 || bookingDay === 6) {
            totalPrice *= 1.15; // 15% increase for weekend
            surcharges.push("Weekend (+15%)");
        }

        // Popular destination surcharge (check if input matches most popular)
        const mostPopularDest = "<?php echo addslashes($mostPopularDestination); ?>";
        if (mostPopularDest && destinationInput.toLowerCase() === mostPopularDest.toLowerCase()) {
            const surchargeAmount = totalPrice * 0.15;
            totalPrice *= 1.20;
            surcharges.push(`Popular Destination (+20%)`);
        }

        // Display price with breakdown
        let priceText = `Estimated Price: NRS${totalPrice.toFixed(2)}`;
        if (surcharges.length > 0) {
            priceText += `<br><small>Includes: ${surcharges.join(', ')}</small>`;
        }
        priceEstimateDiv.innerHTML = priceText;
    } else {
        priceEstimateDiv.textContent = 'Enter booking details to see price.';
    }
}

// Add event listener for destination input
document.getElementById('des').addEventListener('input', calculatePrice);

    function autoCalculateReturnDate() {
    const bookingDateInput = document.getElementById('datefield').value;
    const durationInput = document.getElementById('dur').value;
    const returnDateInput = document.getElementById('dfield');

    if (bookingDateInput && durationInput) {
        const bookingDate = new Date(bookingDateInput);
        const duration = parseInt(durationInput, 10);

        if (!isNaN(duration) && duration > 0) {
            bookingDate.setDate(bookingDate.getDate() + duration);
            const returnDate = bookingDate.toISOString().split('T')[0];
            returnDateInput.value = returnDate;
            returnDateInput.setAttribute("min", returnDate); // Also update the min attribute
        }
    }
}


    // --- Event Listeners ---
    // When user changes booking date or duration, recalculate return date
document.getElementById('datefield').addEventListener('change', () => {
    autoCalculateReturnDate();
    calculatePrice(); // recalculate price estimate too if needed
});

document.getElementById('dur').addEventListener('input', () => {
    autoCalculateReturnDate();
    calculatePrice();
});

    // Fetch factors when the page loads
  

    // Recalculate price when user changes duration or date
    document.getElementById('dur').addEventListener('input', calculatePrice);
    document.getElementById('datefield').addEventListener('change', calculatePrice);

</script>

</body>
</html>
