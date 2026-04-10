<?php
// admin/clear_user_cart.php
session_start();
require 'admin_auth.php';    // vérifie que c'est un admin
require '../config.php';

header('Content-Type: application/json');

// 1. Récupérer user_id passé en GET ou POST
$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
if (!$user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID manquant']);
    exit;
}

// 2. Supprimer tous les articles du panier de cet utilisateur
try {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    echo json_encode(['success' => true, 'message' => 'Panier vidé avec succès']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
