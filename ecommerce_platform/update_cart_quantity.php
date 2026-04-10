<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id  = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);

    // Validation minimale
    if ($quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantité invalide']);
        exit;
    }

    // Vérifier que l’item appartient bien à ce user
    $stmt = $pdo->prepare("SELECT product_id FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cart_item = $stmt->fetch();

    if (!$cart_item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Article introuvable']);
        exit;
    }

    // Vérifier le stock
    $stmt = $pdo->prepare("SELECT stock_qty FROM products WHERE id = ?");
    $stmt->execute([$cart_item['product_id']]);
    $product = $stmt->fetch();

    if ($quantity > $product['stock_qty']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Stock insuffisant']);
        exit;
    }

    // Mise à jour en base
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);

    // Réponse JSON pour AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success'  => true,
        'cart_id'  => $cart_id,
        'quantity' => $quantity
    ]);
    exit;
}

// Si on arrive quelque part sans POST, on renvoie 405
http_response_code(405);
