<?php
session_start();
include_once "./includes/functions.php";
include_once "./includes/database.php";
setPageTitle("Orders");

$userId = $_SESSION['user'];

// Fetching all orders with their items
$query = "SELECT orders.*, order_items.item_id, order_items.quantity, medicines.name, medicines.price, medicines.image
          FROM orders
          LEFT JOIN order_items ON orders.id = order_items.order_id
          LEFT JOIN medicines ON order_items.item_id = medicines.id
          WHERE orders.user_id = :user_id";
$statement = $pdo->prepare($query);
$statement->bindParam(':user_id', $userId);
$statement->execute();
$orders = $statement->fetchAll(PDO::FETCH_ASSOC);

// Organize orders and items
$organizedOrders = [];
foreach ($orders as $order) {
    $orderId = $order['id'];
    if (!isset($organizedOrders[$orderId])) {
        $organizedOrders[$orderId] = [
            'details' => [
                'id' => $order['id'],
                'status' => $order['status'],
                'address' => $order['address'],
                'city' => $order['city'],
                'zip' => $order['zip'],
                'state' => $order['state']
            ],
            'items' => [],
            'totalAmount' => 0
        ];
    }
    $itemAmount = $order['quantity'] * $order['price'];
    $organizedOrders[$orderId]['items'][] = [
        'name' => $order['name'],
        'quantity' => $order['quantity'],
        'price' => $order['price'],
        'image' => $order['image'],
        'itemTotal' => $itemAmount
    ];
    $organizedOrders[$orderId]['totalAmount'] += $itemAmount;
}
?>

<?php include "./includes/head.php"?>
<link rel="stylesheet" href="./css/orders.css">
</head>

<body>
<div class="container">
    <?php include "./includes/nav.php"?>

    <div class="orders-container">
        <h1>Your Orders</h1>

        <?php if (!empty($organizedOrders)): ?>
            <?php foreach ($organizedOrders as $order): ?>
                <div class="order-card">
                    <div class="order-details">
                        <h3>Order ID: <?= $order['details']['id'] ?></h3>
                        <p>Status: <span <?=  $order['details']['status'] == 'cancelled' ? 'style="color: red"' : '' ?>><?= $order['details']['status'] ?></span></p>
                        <p>Address: <?= $order['details']['address'] ?></p>
                        <p>City: <?= $order['details']['city'] ?></p>
                        <p>Zip: <?= $order['details']['zip'] ?></p>
                        <p>State: <?= $order['details']['state'] ?></p>
                    </div>

                    <div class="order-items">
                        <h4>Order Items</h4>
                        <ul>
                            <?php foreach ($order['items'] as $item): ?>
                                <li>
                                    <div class="order-item">
                                        <img class="order-item-image" src="./image/<?= $item['image'] ?>">
                                        <div>
                                            <p><?= $item['name'] ?></p>
                                            <p>Quantity: <?= $item['quantity'] ?></p>
                                            <p>Price: <?= $item['price'] ?></p>
                                            <p>Item total: <?= $item['itemTotal'] ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <p>Total Order Amount: <?= $order['totalAmount'] ?></p>
                    <div class="order-btns">
                        <?php $status = strtolower($order['details']['status']);?>
                        <?php if ($status == 'payment pending'):?>
                            <a href="./payment.php?order_id=<?= $order['details']['id'] ?>" class="payment">Payment</a>
                        <?php endif;?>
                        <?php if ($status == 'payment pending' || $status == 'confirming'):?>
                            <a href="./edit_order.php?order_id=<?= $order['details']['id'] ?>" class="edit_order">Edit</a>
                        <?php endif;?>
                        <?php if ($status != 'cancelled'):?>
                            <a href="./functions/cancel_order.php?order_id=<?= $order['details']['id'] ?>" class="cancel">Cancel</a>
                        <?php endif;?>
                        <a href="./reorder.php?order_id=<?= $order['details']['id'] ?>" class="reorder">Reorder</a>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div><!-- closing orders-container -->
</div><!-- closing container -->

<?php include './includes/footer.php'?>
</body>
</html>
