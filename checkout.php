<?php
session_start();
include_once "./includes/functions.php";
include_once "./includes/database.php";
setPageTitle("Checkout");
checkLoginState();

$userId = $_SESSION['user'];

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


    // checking if a product ID is in the URL
    $productId = isset($_GET['product_id']) ? $_GET['product_id'] : null;
    $orderId = uniqid();
    // Insert order details into the orders table
    $insertOrderQuery = "INSERT INTO orders (id, user_id, address, city, zip, state)
                             VALUES (:id, :user_id, :address, :city, :zip, :state)";
    $insertOrderStatement = $pdo->prepare($insertOrderQuery);
    $insertOrderStatement->bindParam(':id', $orderId);
    $insertOrderStatement->bindParam(':user_id', $userId);
    $insertOrderStatement->bindParam(':address', $_POST['address']);
    $insertOrderStatement->bindParam(':city', $_POST['city']);
    $insertOrderStatement->bindParam(':zip', $_POST['zip']);
    $insertOrderStatement->bindParam(':state', $_POST['state']);

    if ($productId) {
        // fetching product details
        $productQuery = "SELECT name, price FROM medicines WHERE id = :product_id";
        $productStatement = $pdo->prepare($productQuery);
        $productStatement->bindParam(':product_id', $productId);
        $productStatement->execute();
        $productDetails = $productStatement->fetch(PDO::FETCH_ASSOC);

        $insertOrderStatement->execute();

        // Insert the specific product into order_items table
        $insertOrderItemQuery = "INSERT INTO order_items (order_id, item_id, quantity)
                                 VALUES (:order_id, :item_id, :quantity)";
        $insertOrderItemStatement = $pdo->prepare($insertOrderItemQuery);
        $insertOrderItemStatement->bindParam(':order_id', $orderId);
        $insertOrderItemStatement->bindParam(':item_id', $productId);
        $insertOrderItemStatement->bindParam(':quantity', $_POST['quantity']); // Make sure you have a quantity input in your form
        $insertOrderItemStatement->execute();

    }
    else {
        // fetching cart items from the database
        $query = "SELECT cart.*, medicines.name, medicines.price
                      FROM cart
                      JOIN medicines ON cart.product_id = medicines.id
                      WHERE cart.user_id = :user_id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':user_id', $userId);
        $statement->execute();
        $cartItems = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cartItems)) {
            setErrorMsg('Add some items to cart before checkout');
            header('Location: checkout.php');
            exit();
        }

        $insertOrderStatement->execute();

        // Insert cart items into order_items table
        foreach ($cartItems as $cartItem) {
            $insertOrderItemQuery = "INSERT INTO order_items (order_id, item_id, quantity)
                                     VALUES (:order_id, :item_id, :quantity)";
            $insertOrderItemStatement = $pdo->prepare($insertOrderItemQuery);
            $insertOrderItemStatement->bindParam(':order_id', $orderId);
            $insertOrderItemStatement->bindParam(':item_id', $cartItem['product_id']);
            $insertOrderItemStatement->bindParam(':quantity', $cartItem['quantity']);
            $insertOrderItemStatement->execute();
        }

        // Empty the cart
        $emptyCartQuery = "DELETE FROM cart WHERE user_id = :user_id";
        $emptyCartStatement = $pdo->prepare($emptyCartQuery);
        $emptyCartStatement->bindParam(':user_id', $userId);
        $emptyCartStatement->execute();
    }
    setSuccessMsg("Order has been placed!");
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
            $query = "SELECT name, phone, state, zipcode, city, address FROM users WHERE id = :user_id";
            $statement = $pdo->prepare($query);
            $statement->bindParam(':user_id', $userId);
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
            <input type="text" id="zip" name="zip" value="<?=$userDetails['zipcode']; ?>"  required>

            <button type="submit">Place Order</button>
        </form>

        <div class="cart-items">
            <h2>Your Cart</h2>
            <?php
            // fetching cart items from the database
            $query = "SELECT cart.*, medicines.name, medicines.price, medicines.image
              FROM cart
              JOIN medicines ON cart.product_id = medicines.id
              WHERE cart.user_id = :user_id";
            $statement = $pdo->prepare($query);
            $statement->bindParam(':user_id', $userId);
            $statement->execute();
            $cartItems = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (isset($_GET['product_id'])) {
                $productQuery = "SELECT name, price, image FROM medicines WHERE id = :product_id";
                $productStatement = $pdo->prepare($productQuery);
                $productStatement->bindParam(':product_id', $_GET['product_id']);
                $productStatement->execute();
                $productDetails = $productStatement->fetch(PDO::FETCH_ASSOC);

                ?>
                <div class='cart-item'>
                    <div class='item-image'><img src='image/<?=$productDetails['image']?>' alt='Product Image'></div>
                    <div class='item-details'>
                        <p><?=$productDetails['name']?></p>
                        <p>Price: <?=$productDetails['price']?></p>
                    </div>
                </div>

                <div class="cart-total">
                    <p>Total Quantity: <span id="productQuantity">1</span></p>
                    <p>Total Price: <span id="productPrice"><?=$productDetails['price']?></span></p>
                </div>
                <?php
            } else {
                if (!empty($cartItems)) {
                    foreach ($cartItems as $cartItem) {
                        ?>
                        <div class='cart-item'>
                            <div class='item-image'><img src='image/<?=$cartItem['image']?>' alt='Product Image'></div>
                            <div class='item-details'>
                                <p><?=$cartItem['name']?></p>
                                <p>Price: <?=$cartItem['price']?></p>
                                <p>Quantity: <?=$cartItem['quantity']?></p>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="cart-total">
                        <p>Total Quantity: <?=array_sum(array_column($cartItems, 'quantity'))?></p>
                        <p>Total Price: <?=array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cartItems))?></p>
                    </div>
                    <?php
                } else {
                    ?>
                    <p class='not-found'>Your cart is empty.</p>
                    <?php
                }
            }
            ?>
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
