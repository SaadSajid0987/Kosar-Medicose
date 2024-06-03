<?php
session_start();
include_once "./includes/functions.php";
include_once "./includes/database.php";
setPageTitle("Medicines");

$categories = array();

$search = $_GET['query'] ?? '';
$viewAll = isset($_GET['viewAll']);

if (!empty($search)) {

    // fetching data from the Medicines table based on the search query
    $query = "SELECT * FROM medicines WHERE name LIKE ? OR category LIKE ?";

    $statement = $pdo->prepare($query);
    $statement->execute(["%$search%", "%$search%"]);
}
else if ($viewAll) {

    // fetching data from the Medicines table
    $query = "SELECT * FROM medicines";

    $statement = $pdo->prepare($query);
    $statement->execute();
}

else {

    // fetching data from the Medicines table
    $query = "SELECT * FROM medicines LIMIT 30";

    $statement = $pdo->prepare($query);
    $statement->execute();
}


while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $category = $row['category'];
    unset($row['category']);
    $categories[$category][] = $row;
}

?>

<?php include "./includes/head.php";?>
<link rel="stylesheet" href="css/medicines.css" />

</head>

<body>

<?php include "./includes/nav.php";?>

<?php if (!empty($categories)):?>
<?php foreach ($categories as $categoryName => $products): ?>
    <main>
        <section class="top">
            <div class="container">
                <h1 class="heading"><?= strtoupper($categoryName) ?></h1>
                <hr>
            </div>
            <div class="box-container">
                <?php foreach ($products as $product): ?>
                    <div class="box">
                        <div class="slide-img">
                            <img src="image/<?= $product['image'] ?>">
                            <div class="overlay">
                                <p><?= $product['description'] ?></p>
                                <a href="#" class="learn-btn">Learn More</a>
                            </div>
                        </div>
                        <div class="stars">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <?php if ($i<$product['rating']):?>
                                    <i class="fas fa-star"></i>

                                <?php else:?>
                                    <i class="far fa-star"></i>
                                <?php endif;?>
                            <?php endfor; ?>
                        </div>
                        <div class="detail-box">
                            <div class="type">
                                <a href="#"><?= $product['name'] ?></a>
                                <span>new arrival</span>
                            </div>
                            <a href="#" class="price"><?= $product['price'] ?></a>
                        </div>
                        <a href="checkout.php?product_id=<?=$product['id']?>" class="my-button">Order Now</a>
                        <a href="./functions/add_to_cart.php?product_id=<?= $product['id'] ?>" class="my-button">Add to Cart</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$viewAll):?>
                <a href="medicines.php?query=<?= $search?>&viewAll=true" class="my-button view-all">View All</a>
            <?php endif; ?>
        </section>
    </main>
<?php endforeach; ?>
<?php else:?>
    <main>
        <section class="top">
            <div class="container">
                <h1 class="heading">No Results Found</h1>
                <hr>
            </div>
        </section>
    </main>
<?php endif; ?>


<?php include './includes/footer.php';?>
