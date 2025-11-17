<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['delete'])) {
    $cart_id = $_POST['cart_id'];
    $user_id = $_SESSION['user_id'];

    $conn->query("DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id");
}

header("Location: cart.php");
exit();
?>
