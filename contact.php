<?php
session_start();
include_once './includes/functions.php';
include_once './includes/database.php';

setPageTitle("Contact");

// checking if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // validating and process the form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        setErrorMsg("error", "All fields are required.");
    } else {

        // TODO: EMAIL LOGIC
    }

    // redirecting back to the contact page
    header("Location: contact.php");
    exit();
}


$name = $email = "";
if (isset($_SESSION['user'])) {
    $userId = $_SESSION['user'];
    $query = "SELECT name, email FROM users WHERE id = :user_id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':user_id', $userId);
    $statement->execute();
    $userDetails = $statement->fetch(PDO::FETCH_ASSOC);
    $name = $userDetails['name'];
    $email = $userDetails['email'];
}

?>

<?php include "./includes/head.php";?>
<link rel="stylesheet" href="css/contact.css"/>
</head>

<body>
<div class="wrapper">

    <?php include './includes/nav.php' ?>

    <section>
        <div class="container">

            <h2>Contact Us</h2>

            <form class="contact-form" action="contact.php" method="post">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?=$name?>" required <?=$name!== "" ? "readonly" : ""?>>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?=$email?>" required  <?=$email!== "" ? "readonly" : ""?>>

                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea>

                <button type="submit">Submit</button>
            </form>
        </div>
    </section>
</div>

<?php include './includes/footer.php';?>
