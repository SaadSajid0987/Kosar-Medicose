<?php
session_start();
include "./includes/functions.php";
include "./includes/database.php";

if (!isAdmin()) {
    setErrorMsg('Not Allowed');
    header('Location: ./');
    exit();
}

// get receipt
$query = "SELECT image FROM payments WHERE order_id = :order_id";
$statement = $pdo->prepare($query);
$statement->bindParam(':order_id', $_GET['order_id']);
$statement->execute();
$receipt = $statement->fetch(PDO::FETCH_ASSOC);

if (!$receipt) {
    setErrorMsg('Receipt not found.');
    header('Location: ./dashboard.php?tab=orders');
    exit();
}


// check if receipt is pdf by checking extension
if (pathinfo($receipt['image'], PATHINFO_EXTENSION) === 'pdf') {
    $receipt['image'] = 'data:application/pdf;base64,' . base64_encode($receipt['image']);
}

