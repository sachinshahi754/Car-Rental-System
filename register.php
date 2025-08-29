<!DOCTYPE html>
<html lang="en">
<head>
    <title>REGISTRATION</title>
    <link rel="stylesheet" href="css/regs.css" type="text/css">
    <style>
        body {
            background: #ffffff;
            font-family: sans-serif;
        }

        input#psw, input#cpsw {
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 3px;
            outline: 0;
            padding: 7px;
            background-color: #fff;
            box-shadow: inset 1px 1px 5px rgba(0, 0, 0, 0.3);
        }

        #message {
            display: none;
            background: #f1f1f1;
            color: #000;
            position: relative;
            padding: 20px;
            width: 400px;
            margin-left: 1000px;
            margin-top: -500px;
        }

        #message p {
            padding: 10px 35px;
            font-size: 18px;
        }

        .valid {
            color: green;
        }

        .valid:before {
            position: relative;
            left: -35px;
            content: "✔";
        }

        .invalid {
            color: red;
        }

        .invalid:before {
            position: relative;
            left: -35px;
            content: "✖";
        }

        #back {
            display: block;
            height: 40px;
            background: #ff7200;
            color: #fff;
            font-size: 18px;
            border-radius: 10px;
            text-align: center;
            line-height: 40px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
        }

        #back:hover {
            background: #fff;
            color: #ff7200;
            
        }
    </style>
</head>
<body>

<?php
require_once('connection.php');
if (isset($_POST['regs'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $lic = mysqli_real_escape_string($con, $_POST['lic']);
    $ph = mysqli_real_escape_string($con, $_POST['ph']);
    $pass = mysqli_real_escape_string($con, $_POST['pass']);
    $cpass = mysqli_real_escape_string($con, $_POST['cpass']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $Pass = md5($pass); // Consider using password_hash() instead of md5

    if (empty($fname) || empty($lname) || empty($email) || empty($lic) || empty($ph) || empty($pass) || empty($gender)) {
        echo '<script>alert("Please fill in all the fields")</script>';
    } else {
        if ($pass === $cpass) {
            $sql2 = "SELECT * FROM users WHERE EMAIL='$email'";
            $res = mysqli_query($con, $sql2);

            if (mysqli_num_rows($res) > 0) {
                echo '<script>alert("Email already exists. Press OK to login.")</script>';
                echo '<script>window.location.href = "index.php";</script>';
            } else {
                $sql = "INSERT INTO users (FNAME, LNAME, EMAIL, LIC_NUM, PHONE_NUMBER, PASSWORD, GENDER) 
                        VALUES('$fname', '$lname', '$email', '$lic', $ph, '$Pass', '$gender')";
                $result = mysqli_query($con, $sql);

                if ($result) {
                    echo '<script>alert("Registration Successful. Press OK to login.")</script>';
                    echo '<script>window.location.href = "index.php";</script>';
                } else {
                    echo '<script>alert("Please check the connection")</script>';
                }
            }
        } else {
            echo '<script>alert("Passwords do not match.")</script>';
            echo '<script>window.location.href = "register.php";</script>';
        }
    }
}
?>

<!-- Home Button -->
<a href="index.php" id="back">HOME</a>

<!-- Registration Form -->
<div class="main">
    <div class="register">
        <h2>Register Here</h2>
        <form id="register" action="register.php" method="POST">
            <label>First Name:</label><br>
            <input type="text" name="fname" id="name" placeholder="Enter Your First Name" required><br><br>

            <label>Last Name:</label><br>
            <input type="text" name="lname" id="name" placeholder="Enter Your Last Name" required><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" id="name"
                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                   title="ex: example@ex.com"
                   placeholder="Enter Valid Email" required><br><br>

            <label>License Number:</label><br>
            <input type="text" name="lic" id="name" placeholder="Enter Your License Number" required><br><br>

            <label>Phone Number:</label><br>
            <input type="tel" name="ph" maxlength="10" onkeypress="return onlyNumberKey(event)"
                   id="name" placeholder="Enter Your Phone Number" required><br><br>

            <label>Password:</label><br>
            <input type="password" name="pass" maxlength="12" id="psw"
                   placeholder="Enter Password"
                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                   title="Must contain at least one number, one uppercase and lowercase letter, and be at least 8 characters"
                   required><br><br>

            <label>Confirm Password:</label><br>
            <input type="password" name="cpass" id="cpsw" placeholder="Re-enter the Password" required><br><br>

            <label>Gender:</label><br><br>
            <label><input type="radio" name="gender" value="male" required> Male</label>&nbsp;&nbsp;
            <label><input type="radio" name="gender" value="female"> Female</label><br><br>

            <input type="submit" class="btnn" value="REGISTER" name="regs">
        </form>
    </div>
</div>

<!-- Password Message Box -->
<div id="message">
    <h3>Password must contain the following:</h3>
    <p id="letter" class="invalid">A <b>lowercase</b> letter</p>
    <p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
    <p id="number" class="invalid">A <b>number</b></p>
    <p id="length" class="invalid">Minimum <b>8 characters</b></p>
</div>

<!-- JavaScript Validation -->
<script>
    var myInput = document.getElementById("psw");
    var letter = document.getElementById("letter");
    var capital = document.getElementById("capital");
    var number = document.getElementById("number");
    var length = document.getElementById("length");

    myInput.onfocus = function () {
        document.getElementById("message").style.display = "block";
    }

    myInput.onblur = function () {
        document.getElementById("message").style.display = "none";
    }

    myInput.onkeyup = function () {
        var lowerCaseLetters = /[a-z]/g;
        if (myInput.value.match(lowerCaseLetters)) {
            letter.classList.remove("invalid");
            letter.classList.add("valid");
        } else {
            letter.classList.remove("valid");
            letter.classList.add("invalid");
        }

        var upperCaseLetters = /[A-Z]/g;
        if (myInput.value.match(upperCaseLetters)) {
            capital.classList.remove("invalid");
            capital.classList.add("valid");
        } else {
            capital.classList.remove("valid");
            capital.classList.add("invalid");
        }

        var numbers = /[0-9]/g;
        if (myInput.value.match(numbers)) {
            number.classList.remove("invalid");
            number.classList.add("valid");
        } else {
            number.classList.remove("valid");
            number.classList.add("invalid");
        }

        if (myInput.value.length >= 8) {
            length.classList.remove("invalid");
            length.classList.add("valid");
        } else {
            length.classList.remove("valid");
            length.classList.add("invalid");
        }
    }

    function onlyNumberKey(evt) {
        var ASCIICode = (evt.which) ? evt.which : evt.keyCode;
        return !(ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57));
    }
</script>
</body>
</html>
