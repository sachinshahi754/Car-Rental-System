<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaRs - Feedback</title>
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
        width: 400px;
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
    .home-btn {
        width: 150px;
        background: #ff7200;
        color: white;
        border: none;
        padding: 10px;
        font-size: 18px;
        border-radius: 5px;
        cursor: pointer;
        margin: 25px 0 0 100px;
        transition: 0.4s ease;
    }
    .home-btn a {
        text-decoration: none;
        color: white;
    }
    .home-btn:hover {
        background: #e65c00;
    }
    .feedback-container {
        width: 80%;
        margin: 50px auto;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        display: flex;
    }
    .feedback-header {
        flex: 1;
        padding-right: 50px;
    }
    .feedback-header h2 {
        font-size: 72px;
        color: #333;
        margin-bottom: 20px;
    }
    .feedback-header h2 strong {
        font-size: 80px;
        color: #ff7200;
    }
    .feedback-form {
        flex: 1;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }
    textarea.form-control {
        height: 150px;
        resize: vertical;
    }
    .submit-btn {
        background: #ff7200;
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 18px;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.4s ease;
    }
    .submit-btn:hover {
        background: #e65c00;
    }
    
    @keyframes infiniteScrollBg {
        0% { background-position: 50% 0; }
        100% { background-position: 50% 100%; }
    }
    </style>
</head>
<body>
<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "carproject"); // Change db name if needed

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if (isset($_POST['submit'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $comment = htmlspecialchars(trim($_POST['comment']));

    $stmt = $conn->prepare("INSERT INTO feedback (name, email, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $comment);
    
    if ($stmt->execute()) {
        echo "<script>alert('Thank you for your feedback!');</script>";
    } else {
        echo "<script>alert('Failed to submit feedback.');</script>";
    }

    $stmt->close();
}
?>

        
        <div class="main">
            <button class="home-btn"><a href="../cardetails.php">Go To Home</a></button>
            
            <div class="feedback-container">
                <div class="feedback-header">
                    <h2><strong>F</strong>eedback.</h2>
                </div>
                <div class="feedback-form">
                    <form method="POST">
                        <div class="form-group">
                            <label><h4>Name:</h4></label>
                            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <label><h4>Email:</h4></label>
                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <label><h4>Comments:</h4></label>
                            <textarea class="form-control" name="comment" placeholder="Your Feedback" required></textarea>
                        </div>
                        <input type="submit" class="submit-btn" value="SUBMIT" name="submit">
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>