<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);

    header("Location: cart.php?removed=1");
    exit;
} else {
    header("Location: cart.php");
    exit;
}
