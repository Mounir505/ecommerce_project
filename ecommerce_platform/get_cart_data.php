<?php
// Start session and include configuration
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return empty response if not logged in
    echo json_encode([
        'items' => [],
        'total' => 0
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart items from database
$stmt = $pdo->prepare("SELECT cart.id AS cart_id, products.id AS product_id, products.name, 
                      products.image_path AS image, products.price_mad AS price, 
                      cart.quantity
                      FROM cart
                      JOIN products ON cart.product_id = products.id
                      WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cart_items as &$item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    
    // Format price as number
    $item['price'] = (float)$item['price'];
}

// Return cart data as JSON
echo json_encode([
    'items' => $cart_items,
    'total' => $total
]);
?>