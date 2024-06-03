<?php
session_start();
include "./includes/functions.php";
include "./includes/database.php";

if (!isAdmin()) {
    setErrorMsg('Not Allowed');
    header('Location: ./');
    exit();
}
?>

<?php include "./includes/admin-header.php";?>

<div class="d-flex flex-column h-auto flex-md-row h-100 flex-grow-1">
    <?php
        include "./includes/admin-nav.php";
        $tab = $_GET['tab'] ?? '';
    ?>

    <?php
    if ($tab == '' || $tab == 'dashboard') {
        include "./includes/admin-dashboard-tab.php";
    }
    else if ($tab == 'products') {
        include "./includes/admin-products-tab.php";
    }
    else if ($tab == 'users') {
        include "./includes/admin-users-tab.php";
    }
    else if ($tab == 'orders') {
        include "./includes/admin-orders-tab.php";
    }

    else if ($tab == 'settings') {
        include "./includes/admin-settings-tab.php";
    }
    ?>



</div>
</div>

<script src="./js/dashboard.js"></script>
<?php include "./includes/admin-footer.php";?>
