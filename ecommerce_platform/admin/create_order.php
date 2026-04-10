<?php
// admin/create_order.php
session_start();
require 'admin_auth.php';
require '../config.php';

// On renvoie toujours du JSON
header('Content-Type: application/json');

try {
    // 1) Récupérer et valider le JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
    if (!$user_id) {
        throw new Exception('User ID manquant');
    }

    // 2) Récupérer les items du panier
    $stmt = $pdo->prepare("
        SELECT c.product_id, c.size, c.quantity, p.price_mad
          FROM cart c
          JOIN products p ON c.product_id = p.id
         WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        throw new Exception('Panier vide, impossible de créer une commande');
    }

    // 3) Calculer le montant total
    $total = 0;
    foreach ($items as $it) {
        $total += $it['price_mad'] * $it['quantity'];
    }

    // 4) Démarrer la transaction
    $pdo->beginTransaction();

    // 5) Insérer dans orders
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status, payment_method, payment_status)
        VALUES (?, ?, 'pending', NULL, 'unpaid')
    ");
    $orderStmt->execute([$user_id, $total]);
    $order_id = $pdo->lastInsertId();

    // 6) Insérer dans order_items
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, size, quantity, unit_price)
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($items as $it) {
        $itemStmt->execute([
            $order_id,
            $it['product_id'],
            $it['size'],
            $it['quantity'],
            $it['price_mad']
        ]);
    }

    // 7) Vider le panier de l’utilisateur
    $clearStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearStmt->execute([$user_id]);

    // 8) Commit
    $pdo->commit();

    // 9) Répondre OK
    echo json_encode([
        'success'  => true,
        'order_id' => $order_id
    ]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}