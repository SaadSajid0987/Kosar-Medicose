<?php
session_start();
include_once "./includes/functions.php";
include_once "./includes/database.php";
setPageTitle("Payment");

// check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// check if order ID is provided
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

// check if order exists
$orderId = $_GET['order_id'];
$query = "SELECT * FROM orders WHERE id = :id AND user_id = :user_id";
$statement = $pdo->prepare($query);
$statement->bindParam(':id', $orderId);
$statement->bindParam(':user_id', $_SESSION['user']);
$statement->execute();
$order = $statement->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setErrorMsg("Order not found.");
    header("Location: orders.php");
    exit();
}

$orderStatus = strtolower($order['status']);
if ($orderStatus !== 'payment pending') {
    setErrorMsg("You can't pay for this order because it's current status is $orderStatus.");
    header("Location: orders.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = $_POST['tid'];
    $receipt = $_FILES['receipt'];

    // check if transaction ID is provided
    if (!$tid) {
        setErrorMsg("Transaction ID not provided.");
        header("Location: payment.php?order_id=$orderId");
        exit();
    }

    // check if receipt is provided
    if (!$receipt) {
        setErrorMsg("Receipt not provided.");
        header("Location: payment.php?order_id=$orderId");
        exit();
    }

    // check if receipt is an image or PDF
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = pathinfo($receipt['name'], PATHINFO_EXTENSION);
    if (!in_array($extension, $allowedExtensions)) {
        setErrorMsg("Invalid receipt format. Only JPG, JPEG, PNG, and PDF files are allowed.");
        header("Location: payment.php?order_id=$orderId");
        exit();
    }

    // check if receipt size is less than 5 MB
    if ($receipt['size'] > 5 * 1024 * 1024) {
        setErrorMsg("Receipt size exceeds 5 MB.");
        header("Location: payment.php?order_id=$orderId");
        exit();
    }

    // uploading receipt
    $receiptFolder = "receipts/";
    $receiptName = $orderId . "_" . $tid . "_" . $receipt['name'];
    $receiptPath = $receiptFolder . $receiptName;

    // create folder if it doesn't exist
    if (!file_exists($receiptFolder)) {
        mkdir($receiptFolder, 0777, true);
    }


    if (!move_uploaded_file($receipt['tmp_name'], $receiptPath)) {
        setErrorMsg("Failed to upload receipt. Please try again.");
        header("Location: payment.php?order_id=$orderId");
        exit();
    }


    // inserting payment

    $query = "INSERT INTO payments (order_id, tid, image) VALUES (:order_id, :tid, :image_path)";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':order_id', $orderId);
    $statement->bindParam(':tid', $tid);
    $statement->bindParam(':image_path', $receiptPath);
    $statement->execute();

    // updating order status
    $updateQuery = "UPDATE orders SET status = 'Confirming' WHERE id = :id AND user_id = :user_id";
    $updateStatement = $pdo->prepare($updateQuery);
    $updateStatement->bindParam(':id', $orderId);
    $updateStatement->bindParam(':user_id', $_SESSION['user']);
    $updateStatement->execute();

    setSuccessMsg("Payment receipt uploaded!");
    header("Location: orders.php");
    exit();


}


?>

<?php include "./includes/head.php"?>
    <link rel="stylesheet" href="./css/payment.css">
</head>
<body>
<div class="container">
    <?php include "./includes/nav.php"?>

    <div class="card">
        <h1>Account Details</h1>
        <p><strong>Account Title:</strong> Muhammad Saad Atta</p>
        <p><strong>Easypaisa/SadaPay/JazzCash:</strong> 03180980824</p>
        <p><strong>Account Number (UBL):</strong> 290257388 </p>
    </div>
    <div class="card">
        <h1>Upload Your Paid Receipt</h1>
        <p>Enter your Transaction ID</p>
       <form method="post" enctype="multipart/form-data" action="">
           <input name="tid" type="text" id="transactionID" placeholder="Transaction ID">
           <input name="receipt" type="file" id="receiptInput" accept="image/*,application/pdf">
           <p id="fileName">No file selected</p>
           <button id="submitBtn">Submit</button>
       </form>
    </div>
</div>
<script>
    document.getElementById('receiptInput').addEventListener('change', function(event) {
        const input = event.target;
        const fileName = input.files.length ? input.files[0].name : 'No file selected';
        document.getElementById('fileName').textContent = fileName;
    });


</script>

<?php include "./includes/footer.php"?>
