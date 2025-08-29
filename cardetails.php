<?php 
require_once('connection.php');
session_start();

// Initialize variables with default values
$rows = [];
$cars = [];

// Check if email is set in session
if(isset($_SESSION['email'])) {
    $value = $_SESSION['email'];
    
    // Get user data
    $sql = "SELECT * FROM users WHERE EMAIL='$value'";
    $name = mysqli_query($con, $sql);
    if($name && mysqli_num_rows($name) > 0) {
        $rows = mysqli_fetch_assoc($name);
    }
    // Get user booking history
$userEmail = $_SESSION['email'];
$bookedFeatures = [];

$bookingQuery = "SELECT c.FUEL_TYPE, c.CAPACITY, c.PRICE 
                 FROM booking b 
                 JOIN cars c ON b.CAR_ID = c.CAR_ID 
                 WHERE b.EMAIL = '$userEmail'";
$bookingResult = mysqli_query($con, $bookingQuery);

if ($bookingResult && mysqli_num_rows($bookingResult) > 0) {
    $count = 0;
    $totalCapacity = 0;
    $totalPrice = 0;
    $fuelTypes = [];

    while($row = mysqli_fetch_assoc($bookingResult)) {
        $totalCapacity += $row['CAPACITY'];
        $totalPrice += $row['PRICE'];
        $fuelTypes[] = $row['FUEL_TYPE'];
        $count++;
    }

    // Average capacity and price
    $avgCapacity = $totalCapacity / $count;
    $avgPrice = $totalPrice / $count;
    $preferredFuel = array_count_values($fuelTypes);
    arsort($preferredFuel);
    $topFuel = array_key_first($preferredFuel);

    $bookedFeatures = [
        'avgCapacity' => $avgCapacity,
        'avgPrice' => $avgPrice,
        'fuelType' => $topFuel
    ];
}

// Get available cars
$sql2 = "SELECT * FROM cars WHERE AVAILABLE='Y'";
$cars = mysqli_query($con, $sql2);

// Initialize recommended cars array
$recommendedCars = [];

// Only process if both cars and booking features exist
if ($cars && !empty($bookedFeatures)) {
    // Re-fetch cars for recommendation
    mysqli_data_seek($cars, 0); // Reset pointer to start
    while ($car = mysqli_fetch_assoc($cars)) {
        $score = 0;

        // Feature 1: Fuel Type
        if (strtolower($car['FUEL_TYPE']) == strtolower($bookedFeatures['fuelType'])) {
            $score += 1;
        }

        // Feature 2: Capacity similarity
        $capacityDiff = abs($car['CAPACITY'] - $bookedFeatures['avgCapacity']);
        if ($capacityDiff <= 2) $score += 1;

        // Feature 3: Price similarity
        $priceDiff = abs($car['PRICE'] - $bookedFeatures['avgPrice']);
        if ($priceDiff <= 500) $score += 1;

        if ($score >= 2) {
            $recommendedCars[] = $car;
        }
    }

    // Reset again to show full car list in "Our Vehicles"
    mysqli_data_seek($cars, 0);
}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaRs - Our Vehicles</title>
    <style>
    * {
        margin: 0;
        padding: 0;
    }
    .hai {
        width: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0)50%, rgba(0,0,0,0)50%), url("../images/carbg2.jpg");
        background-position: center;
        background-size: cover;
        min-height: 100vh;
        animation: infiniteScrollBg 50s linear infinite;
    }
    .main {
        width: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0)50%, rgba(0,0,0,0)50%);
        background-position: center;
        background-size: cover;
        min-height: 100vh;
    }
    .navbar {
        width: 1200px;
        height: 75px;
        margin: auto;
    }
    .icon {
        width: 200px;
        float: left;
        height: 70px;
    }
    .logo {
        color: #ff7200;
        font-size: 35px;
        font-family: Arial;
        padding-left: 20px;
        float: left;
        padding-top: 10px;
    }
    .menu {
        width: 600px;
        float: left;
        height: 70px;
    }
    ul {
        float: left;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    ul li {
        list-style: none;
        margin-left: 30px;
        margin-top: 27px;
        font-size: 14px;
    }
    ul li a {
        text-decoration: none;
        color: black;
        font-family: Arial;
        font-weight: bold;
        transition: 0.4s ease-in-out;
    }
    ul li a:hover {
        color: #ff7200;
    }
    .nn {
        width: 100px;
        border: none;
        height: 40px;
        font-size: 18px;
        border-radius: 10px;
        cursor: pointer;
        color: white;
        transition: 0.4s ease;
        background: #ff7200;
    }
    .nn a {
        text-decoration: none;
        color: white;
        font-weight: bold;
    }
    .overview {
        text-align: center;
        margin-top: 40px;
        font-size: 30px;
        color: #333;
        font-family: Arial;
    }
    .circle {
        border-radius: 50%;
        width: 40px;
        height: 40px;
    }
    .phello {
        width: 200px;
        margin-left: 10px;
        font-size: 12px;
    }
    #stat {
        margin-left: 10px;
        font-size: 12px;
    }
    #pname {
        font-weight: bold;
        color: #ff7200;
    }
    
    /* Car Listing Styles */
    .cars-container {
        width: 90%;
        margin: 30px auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(600px, 1fr));
        gap: 30px;
    }
    .car-box {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        display: flex;
        transition: transform 0.3s ease;
    }
    .car-box:hover {
        transform: translateY(-5px);
    }
    .car-img {
        width: 250px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 20px;
    }
    .car-details {
        flex: 1;
    }
    .car-details h1 {
        color: #333;
        font-size: 22px;
        margin-bottom: 10px;
    }
    .car-details h2 {
        color: #666;
        font-size: 16px;
        margin-bottom: 5px;
    }
    .book-btn {
        background: #ff7200;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.4s ease;
        margin-top: 15px;
        display: inline-block;
        text-decoration: none;
    }
    .book-btn:hover {
        background: #e65c00;
    }
    
    @keyframes infiniteScrollBg {
        0% { background-position: 50% 0; }
        100% { background-position: 50% 100%; }
    }
    </style>
</head>
<body>
    <div class="hai">
        <div class="navbar">
            <div class="icon">
                <h2 class="logo">CaRs</h2>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="#">HOME</a></li>
                    <li><a href="aboutus2.html">ABOUT</a></li>
                    <li><a href="contactus2.html">CONTACT</a></li>
                    <li><a href="feedback/Feedbacks.php">FEEDBACK</a></li>
                    <li><button class="nn"><a href="index.php">LOGOUT</a></button></li>
                    <li><img src="images/profile.png" class="circle" alt="Profile"></li>
                    <li><p class="phello">HELLO! &nbsp;<span id="pname"><?php echo isset($rows['FNAME']) ? $rows['FNAME']." ".$rows['LNAME'] : 'Guest'; ?></span></p></li>
                    <li><a id="stat" href="bookinstatus.php">BOOKING STATUS</a></li>
                    <li><a id="stat" href="bokinghistory.php">BOOKING HISTORY</a></li>
                </ul>
            </div>
        </div>
        
        <div class="main">
            <?php if (!empty($recommendedCars)) { ?>
    <h1 class="overview">Recommended for You</h1>
    <div class="cars-container">
        <?php foreach ($recommendedCars as $car) { ?>
            <div class="car-box">
                <img src="images/<?php echo $car['CAR_IMG']; ?>" class="car-img">
                <div class="car-details">
                    <h1><?php echo $car['CAR_NAME']; ?></h1>
                    <h2>Fuel Type: <?php echo $car['FUEL_TYPE']; ?></h2>
                    <h2>Capacity: <?php echo $car['CAPACITY']; ?> persons</h2>
                    <h2>Rent Per Day: NRS <?php echo number_format($car['PRICE'], 2); ?>/-</h2>
                    <a href="booking.php?id=<?php echo $car['CAR_ID']; ?>" class="book-btn">Book Now</a>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
</div>
            <h1 class="overview">OUR VEHICLES</h1>
            
            <div class="cars-container">
                <?php
                if(isset($cars) && $cars && mysqli_num_rows($cars) > 0) {
                    while($result = mysqli_fetch_array($cars)) {
                        $res = $result['CAR_ID']; 
                ?>
                <div class="car-box">
                    <img src="images/<?php echo $result['CAR_IMG']; ?>" class="car-img" alt="<?php echo $result['CAR_NAME']; ?>">
                    <div class="car-details">
                        <h1><?php echo $result['CAR_NAME']; ?></h1>
                        <h2>Fuel Type: <?php echo $result['FUEL_TYPE']; ?></h2>
                        <h2>Capacity: <?php echo $result['CAPACITY']; ?> persons</h2>
                        <h2>Rent Per Day: NRS <?php echo number_format($result['PRICE'], 2); ?>/-</h2>
                        <a href="booking.php?id=<?php echo $res; ?>" class="book-btn">Book Now</a>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo '<p style="text-align:center; grid-column:1/-1;">No vehicles available at the moment.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>