<?php
session_start();
date_default_timezone_set('UTC');
include_once "./includes/functions.php";
include_once "./includes/database.php";
setPageTitle('Reset Password');

if (isset($_SESSION['user'])) {
	header('Location: ./');
	exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (!isset($_SESSION['temp_email'])) {
		setErrorMsg("Enter your email");
		header('Location: ./reset_password.php');
		exit();
	}
	$email = $_SESSION['temp_email'];
	$enteredOtp = $_POST['otp'];
	$newPassword = $_POST['new_password'];
	$confirmPassword = $_POST['confirm_password'];

	// validating the password and confirm password
	if ($newPassword !== $confirmPassword) {
		setErrorMsg("Passwords do not match.");
	} else {
		// getting the current date and time in the UTC timezone
		$currentDateTime = date('Y-m-d H:i:s');

		// query to check if the entered OTP and email are correct and not expired
		$query = "SELECT * FROM users WHERE otp = :otp AND email = :email";
		$statement = $pdo->prepare($query);
		$statement->bindParam(":email", $email, PDO::PARAM_STR);
		$statement->bindParam(":otp", $enteredOtp, PDO::PARAM_STR);

		$statement->execute();

		if ($statement->rowCount() > 0) {
			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
			$resetQuery = "UPDATE users SET password = :password, otp = NULL, otpExpiresIn = NULL WHERE email = :email";
			$resetStatement = $pdo->prepare($resetQuery);
			$resetStatement->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
			$resetStatement->bindParam(":email", $email, PDO::PARAM_STR);
			$resetStatement->execute();

			setSuccessMsg("Password reset successfully. Please login with your new password.");
			header("Location: ./login.php");
			exit();
		} else {
			setErrorMsg("Invalid OTP or OTP has expired. Please try again.");
			header("Location: ./reset_password.php?otp=" . $_GET['otp']);
			exit();
		}
	}
}

include "./includes/head.php";
?>

<link rel="stylesheet" href="css/otp.css" />
</head>
<body>
<?php include './includes/nav.php'; ?>
<div class="container">
	<div class="row">
		<?php if (!isset($_GET['otp'])) : ?>
			<h1>Reset Password</h1>
			<h6 class="information-text">Enter your registered phone number to receive an otp for resetting your password.</h6>
			<form action="./functions/send_otp.php?for=password" method="post" class="form-group">
				<input type="tel" name="phone" id="phone" placeholder="Enter Phone Number" required>
				<br/><br/>
				<button type="submit">Send Code</button>
				<div class="footer">

					<h5>Remember your password? <a href="login.php">Sign In</a></h5>
				</div>
			</form>
		<?php else : ?>
			<h1>Reset Password</h1>
			<h6 class="information-text">Enter the code sent to your email and set a new password.</h6>
			<form action="" method="post" class="form-group">
				<input type="text" name="otp" id="otp" placeholder="Enter OTP" required>
				<br/><br/>
				<input type="password" name="new_password" id="new_password" placeholder="Enter New Password" required>
				<br/><br/>
				<input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
				<br/><br/>
				<button type="submit">Reset Password</button>
				<div class="footer">
					<h5>Didn't receive the OTP? <a href="./reset_password.php">Resend OTP</a></h5>
					<h5>Remember your password? <a href="login.php">Sign In</a></h5>
				</div>
			</form>
		<?php endif; ?>
	</div>
</div>
<?php include "./includes/footer.php"; ?>
