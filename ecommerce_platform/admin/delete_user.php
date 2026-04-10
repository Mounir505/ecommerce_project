<?php
// admin/delete_user.php
require 'admin_auth.php';
require '../config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    // Optionnel : vérifier qu’on ne supprime pas le super-admin
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 1) {
        header('Location: users.php?msg=' . urlencode('Impossible de supprimer un admin.'));
        exit;
    }
    // Supprimer l’utilisateur
    $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $del->execute([$id]);
    header('Location: users.php?msg=' . urlencode('Utilisateur supprimé !'));
    exit;
}
header('Location: users.php');
exit;
