<?php
session_start();
include_once './includes/database.php';
include_once './includes/functions.php';
setPageTitle("Login");

if (isset($_SESSION['user'])) {
    header('location: index.php');
    exit();
}

// fetching settings from the database
$querySettings = "SELECT setting_name, setting_value FROM settings";
$statementSettings = $pdo->prepare($querySettings);
$statementSettings->execute();
$currentSettings = $statementSettings->fetchAll(PDO::FETCH_ASSOC);

// creating associative array for easier access to settings
$settingsMap = [];
foreach ($currentSettings as $setting) {
    $settingsMap[$setting['setting_name']] = $setting['setting_value'];
}

$passwordLength = $settingsMap['password_length'];
$loginAllowed =  $settingsMap['allow_login'];
$signupAllowed =  $settingsMap['allow_signup'];

if (isset($_POST['login'])) {
    // handling login logic here
    $emailOrPhone = sanitizeInput($_POST['email_or_phone']);
    $password = sanitizeInput($_POST['password']);

    // performing necessary database checks
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR phone = :phone");
    $stmt->bindParam(':email', $emailOrPhone);
    $stmt->bindParam(':phone', $emailOrPhone);
    $stmt->execute();


    // checking if login is successful
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if ($loginAllowed || $user['role'] !== 'user') {

            if ($user['confirmed']) {
                $_SESSION['user'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                setSuccessMsg("Login successful!");

                if ($user['role'] == 'admin' || $user['role'] == 'superadmin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: index.php");
                }
            }
            else {
                $_SESSION['temp_email'] = $user['email'];
                $_SESSION['temp_name'] = $user['name'];

                setErrorMsg("Confirm your email to login");
                header("Location: ./confirm_account.php");
                exit();
            }

        }
        else {
            setErrorMsg('Login is locked');
            header('Location: ./login.php');
        }
        exit();

    } else {
        setErrorMsg('Invalid login credentials');
        reload();
    }
}

if (isset($_POST['signup'])) {
    if ($signupAllowed) {
        // handling signup logic here
        $id = uniqid();
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $password = sanitizeInput($_POST['password']);

        // validating password length
        if (strlen($password) < $passwordLength) {
            setErrorMsg("Password must be at least $passwordLength characters long");
            reload();
        }

        // using password length setting from the database
        $passwordLength = $settingsMap['password_length'];

        // validating password length
        if (strlen($password) < $passwordLength) {
            setErrorMsg("Password must be at least $passwordLength characters long");
            reload();
        }

        if (strlen($phone) != 11) {
            setErrorMsg("Phone number must be 11 digits long");
            reload();
        }

        if ($phone[0] != '0' || $phone[1] != '3') {
            setErrorMsg("Invalid phone number");
            reload();
        }




        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // checking if user with the same email or phone number already exists
        $checkUserStmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR phone = :phone");
        $checkUserStmt->bindParam(':email', $email);
        $checkUserStmt->bindParam(':phone', $phone);
        $checkUserStmt->execute();

        $existingUser = $checkUserStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            setErrorMsg("User with the same email or phone number already exists");
        } else {
            // performing necessary database inserts
            $insertStmt = $pdo->prepare("INSERT INTO users (id, name, email, phone, password) VALUES (:id, :name, :email, :phone, :password)");
            $insertStmt->bindParam(':id', $id);
            $insertStmt->bindParam(':name', $name);
            $insertStmt->bindParam(':email', $email);
            $insertStmt->bindParam(':phone', $phone);
            $insertStmt->bindParam(':password', $hashedPassword);

            // Execute the query and handle errors
            if ($insertStmt->execute()) {
                // Retrieve the newly inserted user ID
                $newUserId = $pdo->lastInsertId();

                $_SESSION['temp_phone'] = $phone;
                $_SESSION['temp_name'] = $name;

                setSuccessMsg("Signup successful!");

                header("Location: ./functions/send_otp.php?for=acc");
                exit();
            } else {
                setErrorMsg("Registration failed!");
            }
        }
    }
    else {
        setErrorMsg('Signup is locked');
        reload();
    }
}
?>
<?php include "./includes/head.php";?>
<link rel="stylesheet" href="css/login.css" />
</head>

<body>
<?php include './includes/nav.php' ?>
<div class="container">
    <div class="forms-container">
        <div class="signin-signup">
            <form action="" method="post" class="sign-in-form">
                <h2 class="title">LOG IN</h2>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input name="email_or_phone" type="text" placeholder="Email or Phone" required/>
                </div>
                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input name="password" minlength="6" maxlength="20" type="password" placeholder="Password" required/>
                </div>
                <input type="submit" name="login" value="Log in" class="btn solid" />
                <a href="reset_password.php">Forgot Password</a>
            </form>
            <form action="" method="post" class="sign-up-form">
                <h2 class="title">SIGN UP</h2>
                <div class="input-field">
                    <i class="fas fa-envelope"></i>
                    <input name="name" type="text" placeholder="Name" required/>
                </div>
                <div class="input-field">
                    <i class="fas fa-envelope"></i>
                    <input name="email" type="email" placeholder="Email" required/>
                </div>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input name="phone" type="tel" placeholder="Phone Number (03xxxxxxxxx)" pattern="03[0123457]\d{8}" required/>
                </div>

                <div class="input-field">
                    <i class="fas fa-lock"></i>
                    <input name="password" minlength="<?=$passwordLength?>" maxlength="20" type="password" placeholder="Password" required/>
                </div>
                <input name="signup" type="submit" class="btn" value="SIGN UP" />

            </form>
        </div>
    </div>

    <div class="panels-container">
        <div class="panel left-panel">
            <div class="content">
                <h3>New here ?</h3>
                <p>Sign up to continue your journey with us</p>
                <button class="btn transparent" id="sign-up-btn">
                    SIGN UP
                </button>
            </div>
        </div>
        <div class="panel right-panel">
            <div class="content">
                <h3>One of us ?</h3>
                <p>
                    Enter your Valid Phone Number to continue
                </p>
                <button class="btn transparent" id="sign-in-btn">
                    Sign In
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    const sign_in_btn = document.querySelector("#sign-in-btn");
    const sign_up_btn = document.querySelector("#sign-up-btn");
    const container = document.querySelector(".container");

    sign_up_btn.addEventListener("click", () => {
        container.classList.add("sign-up-mode");
    });

    sign_in_btn.addEventListener("click", () => {
        container.classList.remove("sign-up-mode");
    });
</script>

<?php include './includes/footer.php';?>
