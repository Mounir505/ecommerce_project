<?php
session_start();
require 'config.php';

// 1. Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Méthode non autorisée';
    exit;
}

// 2. Vérifier si l’utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo 'NON_AUTHENTIFIE';
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);
$size = $_POST['size'] ?? '';

// 3. Vérifier le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo 'PRODUIT INTROUVABLE';
    exit;
}

if ($quantity < 1 || $quantity > $product['stock_qty']) {
    echo 'QUANTITE_INVALIDE';
    exit;
}

// 4. Vérifier si le produit est déjà dans le panier pour ce user
$check = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$check->execute([$user_id, $product_id]);
$existing = $check->fetch();

if ($existing) {
    // mettre à jour la quantité
    $new_qty = $existing['quantity'] + $quantity;
    $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $update->execute([$new_qty, $user_id, $product_id]);
} else {
    // sinon ajouter une nouvelle ligne
    $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, size) VALUES (?, ?, ?, ?)");
    $insert->execute([$user_id, $product_id, $quantity, $size]);
}

echo 'OK';
