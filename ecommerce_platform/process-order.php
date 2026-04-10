<?php
// Start session and include configuration
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (empty($input) || !isset($input['payment_method'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Get cart items
$stmt = $pdo->prepare("SELECT cart.id AS cart_id, products.id AS product_id, products.name, 
                      products.price_mad AS price, cart.quantity
                      FROM cart
                      JOIN products ON cart.product_id = products.id
                      WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Create order in database
try {
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, 
                         shipping_name, shipping_address, shipping_city, shipping_zip, 
                         shipping_country, shipping_email, shipping_phone, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $status = 'processing';
    $payment_method = $input['payment_method'];
    $shipping_info = $input['shipping_info'];
    
    $stmt->execute([
        $user_id,
        $total,
        $status,
        $payment_method,
        $shipping_info['name'],
        $shipping_info['address'],
        $shipping_info['city'],
        $shipping_info['zip'],
        $shipping_info['country'],
        $shipping_info['email'],
        $shipping_info['phone']
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                         VALUES (?, ?, ?, ?)");
    
    foreach ($cart_items as $item) {
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
    }
    
    // Insert payment details
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, amount, status, 
                         payment_date, transaction_id) 
                         VALUES (?, ?, ?, ?, NOW(), ?)");
    
    $payment_status = 'completed';
    $transaction_id = '';
    
    if ($payment_method === 'paypal' && isset($input['payment_data']['id'])) {
        $transaction_id = $input['payment_data']['id'];
        
        // If we have exchange rate info, store it
        if (isset($input['exchange_rate_info'])) {
            // This assumes you have a payment_details table for additional info
            // You might need to create this table if it doesn't exist
            $stmt_details = $pdo->prepare("INSERT INTO payment_details (payment_id, original_currency, 
                                           exchange_currency, exchange_rate, exchange_timestamp)
                                           VALUES (LAST_INSERT_ID(), ?, ?, ?, ?)");
            
            $original_currency = $input['original_currency'] ?? 'MAD';
            $exchange_currency = $input['payment_data']['currency'] ?? 'USD';
            $exchange_rate = $input['exchange_rate_info']['rate'] ?? 0;
            $exchange_timestamp = $input['exchange_rate_info']['timestamp'] ?? date('Y-m-d H:i:s');
            
            $stmt_details->execute([
                $original_currency,
                $exchange_currency,
                $exchange_rate,
                $exchange_timestamp
            ]);
        }
    } else if ($payment_method === 'card') {
        $transaction_id = 'card_' . time();
    }
    
    $stmt->execute([
        $order_id,
        $payment_method,
        $total,
        $payment_status,
        $transaction_id
    ]);
    
    // Clear cart after successful order
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Commit transaction
    $pdo->commit();
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Order processed successfully',
        'order_id' => $order_id
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Order processing failed: ' . $e->getMessage()
    ]);
}
?>