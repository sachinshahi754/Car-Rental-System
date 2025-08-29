<?php



$servername = "localhost";
$username = "root";
$password = "";
$dbname = "carproject";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$totalBookingsResult = $conn->query("SELECT COUNT(BOOK_ID) as total_bookings FROM booking");
$totalBookings = $totalBookingsResult->fetch_assoc()['total_bookings'] ?? 0;

//  Total ACTUAL Revenue (from payment table)

$totalRevenueResult = $conn->query("SELECT SUM(PRICE) as total_revenue FROM booking");
$totalRevenue = $totalRevenueResult ? $totalRevenueResult->fetch_assoc()['total_revenue'] : 0;


// Get the most popular destination to apply surcharge
$mostPopularDestinationQuery = "SELECT DESTINATION FROM booking WHERE DESTINATION IS NOT NULL AND DESTINATION != '' GROUP BY DESTINATION ORDER BY COUNT(BOOK_ID) DESC LIMIT 1";
$mostPopularDestinationResult = $conn->query($mostPopularDestinationQuery);
$mostPopularDestination = $mostPopularDestinationResult ? $mostPopularDestinationResult->fetch_assoc()['DESTINATION'] : null;

// Calculate Projected DYNAMIC Revenue with surcharges
$projectedRevenue = 0;
$projectedRevenueByMonth = [];

$dynamicCalcQuery = "SELECT b.BOOK_ID, b.BOOK_DATE, b.DESTINATION, c.PRICE as base_price FROM booking b JOIN cars c ON b.CAR_ID = c.CAR_ID";
$dynamicCalcResult = $conn->query($dynamicCalcQuery);

if ($dynamicCalcResult) {
    while($row = $dynamicCalcResult->fetch_assoc()) {
        // Calculate the dynamic price for this historical booking
        $dynamicPrice = (float)$row['base_price'];
        
        // Weekend surcharge
        $dayOfWeek = date('N', strtotime($row['BOOK_DATE']));
        if ($dayOfWeek >= 6) { //  Saturday, Sunday
            $dynamicPrice *= 1.15; // Weekend surcharge
        }
        
        // Popular destination surcharge (20% increase)
        if ($mostPopularDestination && $row['DESTINATION'] === $mostPopularDestination) {
            $dynamicPrice *= 1.20; // 20% surcharge
        }
        
        $projectedRevenue += $dynamicPrice;
        
        // Aggregate for the monthly chart
        $month = date('Y-m', strtotime($row['BOOK_DATE']));
        if (!isset($projectedRevenueByMonth[$month])) {
            $projectedRevenueByMonth[$month] = 0;
        }
        $projectedRevenueByMonth[$month] += $dynamicPrice;
    }
}
ksort($projectedRevenueByMonth);


// Chart: Bookings Over Time (by month)
$bookingsOverTimeQuery = "SELECT DATE_FORMAT(BOOK_DATE, '%Y-%m') as month, COUNT(BOOK_ID) as count FROM booking GROUP BY month ORDER BY month ASC";
$bookingsOverTimeResult = $conn->query($bookingsOverTimeQuery);
$bookingsOverTimeLabels = [];
$bookingsOverTimeData = [];
if ($bookingsOverTimeResult) {
    while($row = $bookingsOverTimeResult->fetch_assoc()) {
        $bookingsOverTimeLabels[] = $row['month'];
        $bookingsOverTimeData[] = $row['count'];
    }
}

// Chart: Actual Revenue by Month
$revenueByMonthQuery = "SELECT DATE_FORMAT(BOOK_DATE, '%Y-%m') as month, SUM(PRICE) as monthly_revenue FROM booking GROUP BY month ORDER BY month ASC";

$revenueByMonthResult = $conn->query($revenueByMonthQuery);
$revenueByMonthLabels = [];
$revenueByMonthData = [];
// Use the booking labels to ensure data aligns, filling in gaps with 0
foreach ($bookingsOverTimeLabels as $monthLabel) {
    $revenueByMonthLabels[] = $monthLabel;
    $found = false;
    if ($revenueByMonthResult && $revenueByMonthResult->num_rows > 0) {
        mysqli_data_seek($revenueByMonthResult, 0); // Reset result pointer
        while($row = $revenueByMonthResult->fetch_assoc()) {
            if ($row['month'] == $monthLabel) {
                $revenueByMonthData[] = $row['monthly_revenue'];
                $found = true;
                break;
            }
        }
    }
    if (!$found) $revenueByMonthData[] = 0;
}


//  Prepare Projected Revenue data for chart, aligning with actual revenue labels
$projectedRevenueChartData = [];
foreach ($revenueByMonthLabels as $month) {
    $projectedRevenueChartData[] = $projectedRevenueByMonth[$month] ?? 0;
}


// Chart: Most Popular Cars
$popularCarsQuery = "SELECT c.CAR_NAME, COUNT(b.BOOK_ID) as booking_count FROM booking b JOIN cars c ON b.CAR_ID = c.CAR_ID GROUP BY c.CAR_NAME ORDER BY booking_count DESC LIMIT 5";
$popularCarsResult = $conn->query($popularCarsQuery);
$popularCarsLabels = [];
$popularCarsData = [];
if ($popularCarsResult) {
    while($row = $popularCarsResult->fetch_assoc()) {
        $popularCarsLabels[] = $row['CAR_NAME'];
        $popularCarsData[] = $row['booking_count'];
    }
}

//  Chart: Popular Destinations
$popularDestinationsQuery = "SELECT DESTINATION, COUNT(BOOK_ID) as booking_count FROM booking WHERE DESTINATION IS NOT NULL AND DESTINATION != '' GROUP BY DESTINATION ORDER BY booking_count DESC LIMIT 5";
$popularDestinationsResult = $conn->query($popularDestinationsQuery);
$popularDestinationsLabels = [];
$popularDestinationsData = [];
if ($popularDestinationsResult) {
    while($row = $popularDestinationsResult->fetch_assoc()) {
        $popularDestinationsLabels[] = $row['DESTINATION'];
        $popularDestinationsData[] = $row['booking_count'];
    }
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Data Visualization Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{
    margin: 0;
    padding: 0;

}
.hai{
    width: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0)50%, rgba(0,0,0,0)50%),url("../images/carbg2.jpg");
    background-position: center;
    background-size: cover;
    height: 109vh;
    animation: infiniteScrollBg 50s linear infinite;
}
.main{
    width: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0)50%, rgba(0,0,0,0)50%);
    background-position: center;
    background-size: cover;
    height: 109vh;
    animation: infiniteScrollBg 50s linear infinite;
}
.navbar{
    width: 1200px;
    height: 75px;
    margin: auto;
}

.icon{
    width:200px;
    float: left;
    height : 70px;
}

.logo{
    color: #ff7200;
    font-size: 35px;
    font-family: Arial;
    padding-left: 20px;
    float:left;
    padding-top: 10px;
    font-weight: bold;

}
.menu{
    width: 400px;
    float: left;
    height: 70px;

}

ul{
    float: left;
    display: flex;
    justify-content: center;
    align-items: center;
}

ul li{
    list-style: none;
    margin-left: 62px;
    margin-top: 27px;
    font-size: 14px;

}
ul li a{
    text-decoration: none;
    color: black;
    font-family: Arial;
    font-weight: bold;
    transition: 0.4s ease-in-out;

}
.header{
    margin-top: 70px;
    margin-left: 650px;
}


.nn{
    width:100px;
     background: #ff7200; 
    border:none;
    height: 40px;
    font-size: 18px;
    border-radius: 10px;
    cursor: pointer;
    color:white;
    transition: 0.4s ease;

}


.nn a{
    text-decoration: none;
    color: black;
    font-weight: bold;
    
}

      body { font-family: 'Inter', sans-serif; background-color: white; }
        .card { background-color: #f3f4f6; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); padding: 1.5rem; }
        .chart-container { position: relative; height: 350px; width: 100%; }
    </style>
</head>
<body >

    
<div >

<div class="navbar">
            <div class="icon">
                <h2 class="logo">CaRs</h2>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="adminvehicle.php">VEHICLE MANAGEMENT</a></li>
                    <li><a href="adminusers.php">USERS</a></li>
                    <li><a href="admindash.php">FEEDBACKS</a></li>
                     <li><a href="datavisualize.php">VIEW REPORT</a></li>
                    
                    <li><a href="adminbook.php">BOOKING REQUEST</a></li>
                  <li> <button class="nn"><a href="index.php">LOGOUT</a></button></li>
                </ul>
            </div> 
            
          
        </div>
   
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
            <h3 class="text-lg font-semibold text-orange-500">Total Bookings</h3>
            <p class="text-4xl font-bold text-black-600 mt-2"><?php echo $totalBookings; ?></p>
        </div>
        <div class="card">
            <h3 class="text-lg font-semibold text-orange-500">Actual Revenue</h3>
            <p class="text-4xl font-bold text-black-600 mt-2">NRS:<?php echo number_format($totalRevenue, 2); ?></p>
        </div>
        <div class="card">
            <h3 class="text-lg font-semibold text-orange-500">Projected Dynamic Revenue</h3>
            <p class="text-4xl font-bold text-black-600 mt-2">NRS:<?php echo number_format($projectedRevenue, 2); ?></p>
        </div>
    </div>



    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="card">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Bookings Over Time</h2>
            <div class="chart-container">
                <canvas id="bookingsChart"></canvas>
            </div>
        </div>
        <div class="card">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Actual vs. Projected Revenue</h2>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="card">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Most Popular Cars</h2>
            <div class="chart-container" style="height: 300px;">
                <canvas id="popularCarsChart"></canvas>
            </div>
        </div>
        <div class="card">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Top 5 Destinations</h2>
            <div class="chart-container">
                <canvas id="destinationsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    // Pass PHP data to JavaScript for charts
    const bookingsOverTimeLabels = <?php echo json_encode($bookingsOverTimeLabels); ?>;
    const bookingsOverTimeData = <?php echo json_encode($bookingsOverTimeData); ?>;
    const revenueByMonthLabels = <?php echo json_encode($revenueByMonthLabels); ?>;
    const revenueByMonthData = <?php echo json_encode($revenueByMonthData); ?>;
    const projectedRevenueChartData = <?php echo json_encode($projectedRevenueChartData); ?>;
    const popularCarsLabels = <?php echo json_encode($popularCarsLabels); ?>;
    const popularCarsData = <?php echo json_encode($popularCarsData); ?>;
    const popularDestinationsLabels = <?php echo json_encode($popularDestinationsLabels); ?>;
    const popularDestinationsData = <?php echo json_encode($popularDestinationsData); ?>;

    // Chart Initializations
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Bookings Over Time Chart (Line)
        new Chart(document.getElementById('bookingsChart'), {
            type: 'line',
            data: {
                labels: bookingsOverTimeLabels,
                datasets: [{
                    label: 'Number of Bookings',
                    data: bookingsOverTimeData,
                    borderColor: 'rgb(246, 143, 59)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 2. Revenue by Month Chart (Bar)
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: revenueByMonthLabels,
                datasets: [
                    {
                        label: 'Actual Revenue',
                        data: revenueByMonthData,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    },
                    {
                        label: 'Projected Dynamic Revenue',
                        data: projectedRevenueChartData,
                        backgroundColor: 'rgba(139, 92, 246, 0.7)',
                        borderColor: 'rgb(139, 92, 246)',
                        borderWidth: 1
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { 
                    y: { beginAtZero: true, ticks: { callback: value => '' + value } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 3. Most Popular Cars Chart (Doughnut)
        new Chart(document.getElementById('popularCarsChart'), {
            type: 'doughnut',
            data: {
                labels: popularCarsLabels,
                datasets: [{
                    label: 'Bookings',
                    data: popularCarsData,
                    backgroundColor: ['#3b82f6', '#10b981', '#f97316', '#8b5cf6', '#ec4899'],
                    hoverOffset: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 4. Popular Destinations Chart (Horizontal Bar)
        new Chart(document.getElementById('destinationsChart'), {
            type: 'bar',
            data: {
                labels: popularDestinationsLabels,
                datasets: [{
                    label: 'Number of Trips',
                    data: popularDestinationsData,
                    backgroundColor: 'rgba(249, 115, 22, 0.7)',
                    borderColor: 'rgb(249, 115, 22)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // This makes it a horizontal bar chart
                responsive: true,
                maintainAspectRatio: false,
                scales: { x: { beginAtZero: true } }
            }
        });


        
    });

</script>

</body>
</html>
