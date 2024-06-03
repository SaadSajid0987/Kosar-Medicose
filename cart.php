<?php
session_start();
include "./includes/database.php";
include "./includes/functions.php";
checkLoginState();
setPageTitle('Cart');
?>

<?php include "./includes/head.php";?>
<link rel="stylesheet" href="./css/cart.css">
</head>

<body>
<?php include "./includes/nav.php";?>

<main>
    <section class="cart">
        <div class="container">
            <h1 class="heading">Your Cart</h1>
            <hr>
            <?php
            $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;

            // fetching cart items from the database
            $query = "SELECT cart.*, medicines.id, medicines.name, medicines.description, medicines.price, medicines.image
                      FROM cart
                      JOIN medicines ON cart.product_id = medicines.id
                      WHERE cart.user_id = :user_id";
            $statement = $pdo->prepare($query);
            $statement->bindParam(':user_id', $userId);
            $statement->execute();
            $cartItems = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (empty($cartItems)) {
                echo "<p class='not-found'>Your cart is empty.</p>";
            } else {
                 $index=0;
                foreach ($cartItems as $cartItem) {
                    ?>
                    <div class="cart-item">
                        <img src="image/<?php echo $cartItem['image']; ?>" alt="Product Image">
                        <div class="item-details">
                            <h2><?php echo $cartItem['name']; ?></h2>
                            <p class="desc">Description: <?php echo $cartItem['description']; ?></p>
                            <p class="price">Price: <?php echo $cartItem['price']; ?></p>
                            <label for="quantity">Quantity:</label>
                            <input id="<?=$index?>" class="quantity-input" type="number" name="quantity" value="<?php echo $cartItem['quantity']; ?>" min="1" max="10" maxlength="2">
                        </div>

                        <a  href="functions/delete_from_cart.php?product_id=<?=$cartItem['id']?>" class="fas fa-trash-can delete-icon"></a>
                    </div>
                    <?php
                      $index++;
                }
                ?>
                <div class="cart-total">
                    <p>Total Quantity: <span id="total-quantity"><?php echo array_sum(array_column($cartItems, 'quantity')); ?></span></p>
                    <p>Total Price: <span id="total-price"><?php echo array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $cartItems)); ?></span></p>
                </div>
                <a href="checkout.php" class="checkout-btn">Checkout</a>
                <?php
            }
            ?>
        </div>
    </section>
</main>

<script>
    const cartItems = <?=json_encode($cartItems);?>;
</script>

<script src="./js/cart.js"></script>


<?php include "./includes/footer.php";?>