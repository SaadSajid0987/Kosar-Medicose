<?php
session_start();
include_once './includes/functions.php';
include_once './includes/database.php';

setPageTitle("About Us");

?>

<?php include "./includes/head.php";?>
    <link rel="stylesheet" href="css/about.css"/>
</head>

<body>
<div class="wrapper">

    <?php include './includes/nav.php' ?>

    <!--about Us-->
    <section>
        <div class="container">
            <div class="about">

                <h3 style="padding-left: 30%;">ABOUT US</h3>

                <p style="padding-left: 30%">"<span>PharmaCart</span>".It is the online deals site of medication
                    supported by a PK Pharmacy.
                    "<span>PharmaCart</span>" the pioneer in Pharmacy and PK Pharmacy offers a coutry wide
                    determination
                    of Pharmacy items, natural drugs, fragrant healingn and medication(homeopathy,veterinary items,
                    Medicines
                    and over counter Prescription) at the best costs</p>

                <a href="#" class="btn">Read More</a>
                <div class="about-form">


                </div>
            </div>
        </div>
    </section>
    <!--about Us-->
</div>

<?php include './includes/footer.php';?>
