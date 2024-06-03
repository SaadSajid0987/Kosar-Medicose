<?php
session_start();
date_default_timezone_set('UTC');
include_once "./includes/functions.php";
include_once "./includes/database.php";

setPageTitle('Enter OTP');
include "./includes/head.php";

if (isset($_SESSION['user'])) {
    header('Location: ./');
    exit();
}

// checking if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // retrieving the OTP and temporary email entered by the user
    $otp = $_POST['otp'];
    $phone = $_SESSION['temp_phone'] ?? $_POST['temp_phone'];

    // getting the current date and time in the UTC timezone
    $currentDateTime = date('Y-m-d H:i:s');

    // query to check if the entered OTP and email are correct and not expired
    $query = "SELECT * FROM users WHERE otp = :otp AND phone = :phone AND otpExpiresIn > :current_datetime";
    $statement = $pdo->prepare($query);
    $statement->bindParam(":otp", $otp, PDO::PARAM_STR);
    $statement->bindParam(":phone", $phone, PDO::PARAM_STR);
    $statement->bindParam(":current_datetime", $currentDateTime, PDO::PARAM_STR);
    $statement->execute();

    // if the OTP and email are correct and not expired
    if ($statement->rowCount() > 0) {
        // setting the 'confirmed' field to true
        $updateQuery = "UPDATE users SET confirmed = 1 WHERE otp = :otp";
        $updateStatement = $pdo->prepare($updateQuery);
        $updateStatement->bindParam(":otp", $otp, PDO::PARAM_STR);
        $updateStatement->execute();
        setSuccessMsg("Account confirmed! Please login");

        // redirect the user to the homepage or any other page
        header('Location: ./login.php');
    } else {
        // displaying error message if OTP is incorrect or expired
        setErrorMsg("Invalid OTP or OTP has expired. Please try again.");
        header('Location: ' . $_SERVER['PHP_SELF']);
    }
    exit();
}
?>

<link rel="stylesheet" href="css/otp.css" />
</head>
<body>
<?php include './includes/nav.php'; ?>
<div class="container">
    <div class="row">
        <h1>Enter OTP</h1>
        <h6 class="information-text">An OTP has been sent to your registered email. Please enter the OTP below.</h6>
        <form action="" method="post" class="form-group">
            <?php if (!isset($_SESSION['temp_email'])) : ?>

                <input type="email" name="temp_email" id="temp_email" placeholder="Enter Email" required>
                <br/><br/>
            <?php endif; ?>
            <input type="text" name="otp" id="otp" placeholder="Enter OTP" required>
            <br/><br/>
            <button type="submit">Verify OTP</button>
        </form>
        <div class="footer">

            <h5>Didn't receive the OTP? <a href="./functions/send_otp.php?for=acc">Resend OTP</a></h5>
        </div>
    </div>
</div>
<?php include "./includes/footer.php"; ?>
