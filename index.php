<?php
session_start();
include_once './includes/functions.php';
include_once './includes/database.php';

setPageTitle('Home Page');

?>

<?php include "./includes/head.php";?>
    <link rel="stylesheet" href="css/style.css" />
  </head>

<body>
<?php include "./includes/nav.php";?>

  <main>
    <section class="main">
      <div>
        <img class="main-img" src="./image/main_logo.jpg" alt="logo"/>
        <a href="medicines.php" class="btn">Shop Now</a>
      </div>
    </section>
  </main>


<?php include './includes/footer.php';?>