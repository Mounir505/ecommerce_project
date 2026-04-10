<?php
// admin/delete_product.php
require 'admin_auth.php';
require '../config.php';

// 1) Récupération et validation de l’ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: products.php');
    exit;
}

// 2) Récupérer le chemin de l’image pour la supprimer du disque
$stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
$stmt->execute([$id]);
$image = $stmt->fetchColumn();

if ($image) {
    $fullPath = __DIR__ . '/../' . $image;
    if (file_exists($fullPath)) {
        @unlink($fullPath);
    }
}

// 3) Suppression en base
$del = $pdo->prepare("DELETE FROM products WHERE id = ?");
$del->execute([$id]);

// 4) Redirection avec flash via param ?deleted=1
header('Location: products.php?deleted=1');
exit;
