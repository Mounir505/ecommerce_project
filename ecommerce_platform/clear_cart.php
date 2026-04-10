<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Delete all items from cart for this user
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
} catch (PDOException $e) {
    error_log("Error clearing cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error clearing cart']);
}
?>