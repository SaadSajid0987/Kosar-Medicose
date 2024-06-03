<?php
session_start();
include_once "./includes/functions.php";
include_once "./includes/database.php";
setPageTitle("Update Order");
checkLoginState();

$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderId) {
    // handling missing order ID
    setErrorMsg("Order ID not provided.");
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user'];

if (isset($_POST['update_item_id'])) {
    $updateItemId = $_POST['update_item_id'];
    $updateQuantity = $_POST['quantity'];

    // validating quantity
    if ($updateQuantity <= 0) {
        setErrorMsg("Quantity must be greater than 0.");
        header("Location: edit_order.php?order_id=$orderId");
        exit();
    }


    // checking if the product ID is provided in the URL
    if (!$updateItemId) {
        // handling missing product ID
        setErrorMsg("Item ID not provided.");
        header("Location: edit_order.php?order_id=$orderId");
        exit();
    }

    $updateItemQuery = "UPDATE order_items SET quantity = :quantity WHERE item_id = :id AND order_id = :order_id";
    $updateItemStatement = $pdo->prepare($updateItemQuery);
    $updateItemStatement->bindParam(':id', $updateItemId);
    $updateItemStatement->bindParam(':order_id', $orderId);
    $updateItemStatement->bindParam(':quantity', $updateQuantity);
    $updateItemStatement->execute();

    setSuccessMsg("Item updated successfully!");
    header("Location: edit_order.php?order_id=$orderId");
    exit();
}

if (isset($_POST['remove_item_id'])) {

    // get all items in order
    $query = "SELECT * FROM order_items WHERE order_id = :order_id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':order_id', $orderId);
    $statement->execute();
    $orderItems = $statement->fetchAll(PDO::FETCH_ASSOC);

    // if item is 1, send error
    if (count($orderItems) == 1) {
        setErrorMsg("Order must have at least one item!");
        header("Location: edit_order.php?order_id=$orderId");
        exit();
    }

    $removeItemId = $_POST['remove_item_id'];
    $removeItemQuery = "DELETE FROM order_items WHERE item_id = :id AND order_id = :order_id";
    $removeItemStatement = $pdo->prepare($removeItemQuery);
    $removeItemStatement->bindParam(':id', $removeItemId);
    $removeItemStatement->bindParam(':order_id', $orderId);
    $removeItemStatement->execute();

    setSuccessMsg("Item removed successfully!");
    header("Location: edit_order.php?order_id=$orderId");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // updating user details in the users table
    $updateUserQuery = "UPDATE users SET
                            address = :address,
                            state = :state,
                            city = :city,
                            zipcode = :zipcode
                            WHERE id = :user_id";
    $updateUserStatement = $pdo->prepare($updateUserQuery);
    $updateUserStatement->bindParam(':user_id', $userId);
    $updateUserStatement->bindParam(':address', $_POST['address']);
    $updateUserStatement->bindParam(':state', $_POST['state']);
    $updateUserStatement->bindParam(':city', $_POST['city']);
    $updateUserStatement->bindParam(':zipcode', $_POST['zip']);
    $updateUserStatement->execute();

    // updating order
    $updateOrderQuery = "UPDATE orders SET
                            address = :address,
                            city = :city,
                            zip = :zip,
                            state = :state
                            WHERE id = :order_id";
    $updateOrderStatement = $pdo->prepare($updateOrderQuery);
    $updateOrderStatement->bindParam(':order_id', $orderId);
    $updateOrderStatement->bindParam(':address', $_POST['address']);
    $updateOrderStatement->bindParam(':city', $_POST['city']);
    $updateOrderStatement->bindParam(':zip', $_POST['zip']);
    $updateOrderStatement->bindParam(':state', $_POST['state']);
    $updateOrderStatement->execute();


    setSuccessMsg("Order updated successfully!");
    header("Location: orders.php");
    exit();
}


?>

<?php include "./includes/head.php"?>
<link rel="stylesheet" href="./css/checkout.css">
</head>

<body>
<div class="container">
    <?php include "./includes/nav.php"?>

    <div class="checkout-container">

        <form action="" method="post" class="checkout-form">
            <h2>Checkout Form</h2>

            <?php
            $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;

            // fetching user details from the database
            $query = "SELECT users.name, users.phone, orders.address, orders.city, orders.zip, orders.state FROM users JOIN orders ON users.id = orders.user_id WHERE orders.id = :order_id";
            $statement = $pdo->prepare($query);
            $statement->bindParam(':order_id', $orderId);
            $statement->execute();
            $userDetails = $statement->fetch(PDO::FETCH_ASSOC);
            ?>

            <?php
            if (isset($_GET['product_id'])) {
                echo '<label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="1" max="10" value="1" required>';
            }
            ?>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?=$userDetails['name']; ?>" readonly>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?=$userDetails['phone']; ?>" readonly>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?=$userDetails['city']; ?>"  required>

            <label for="state">State:</label>
            <input type="text" id="state" name="state" value="<?=$userDetails['state']; ?>"  required>

            <label for="address">Address:</label>
            <textarea id="address" name="address" rows="4" required><?=$userDetails['address']; ?></textarea>

            <label for="zip">Zip Code:</label>
            <input type="text" id="zip" name="zip" value="<?=$userDetails['zip']; ?>"  required>

            <button type="submit">Update Order</button>
        </form>

        <div class="cart-items">
            <h2>Items</h2>
            <?php

            // getting order items
            $query = "SELECT order_items.*, medicines.name, medicines.price, medicines.image
                        FROM order_items
                            JOIN medicines ON order_items.item_id = medicines.id
                        WHERE order_id = :order_id";


            $statement = $pdo->prepare($query);
            $statement->bindParam(':order_id', $orderId);
            $statement->execute();
            $cartItems = $statement->fetchAll(PDO::FETCH_ASSOC);


            foreach ($cartItems as $cartItem) {
                ?>
                <div class='cart-item'>
                    <div class='item-image'><img src='image/<?=$cartItem['image']?>' alt='Product Image'></div>
                    <div class='item-details'>
                        <p><?=$cartItem['name']?></p>
                        <p>Price: <?=$cartItem['price']?></p>
                        <form class="update_quantity_form" method="post" action="">
                            <input type="hidden" name="update_item_id" value="<?=$cartItem['item_id']?>">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" min="1" max="10" value="<?=$cartItem['quantity']?>" required>
                            <input type="submit" value="Update"/>
                        </form>
                    </div>
                    <form class="remove_item_form" method="post" action="">
                        <input type="hidden" name="remove_item_id" value="<?=$cartItem['item_id']?>">
                        <input type="submit" value="X"/>
                    </form>

                </div>

                <?php
            }
            ?>
            <div class="cart-total">
                <p>Total Quantity: <?=array_sum(array_column($cartItems, 'quantity'))?></p>
                <p>Total Price: <?=array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cartItems))?></p>
            </div>

        </div>

    </div>

</div>
<?php if (isset($_GET['product_id'])):?>
    <script>

        const quantityInput = document.querySelector('#quantity');
        quantityInput.addEventListener('change', () => {
            let quantity = quantityInput.value;
            if (quantity === '' || quantity < 1) quantityInput.value =1;
            if (quantity > 10)  quantityInput.value = 10;
            quantity = quantityInput.value;
            document.getElementById("productQuantity").innerText = quantity;
            document.getElementById("productPrice").innerText = parseInt(quantity) * parseFloat(<?=$productDetails['price']?>) || 'Error';
        });
    </script>

<?php endif;?>
<?php include "./includes/footer.php"?>
