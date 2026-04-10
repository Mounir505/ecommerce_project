<?php
session_start();
require 'config.php';

$count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT product_id) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $count = $stmt->fetchColumn();
}

echo json_encode(['count' => $count]);
