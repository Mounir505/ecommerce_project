<?php
// admin/toggle_user_role.php
require 'admin_auth.php';
require '../config.php';

$input = json_decode(file_get_contents('php://input'), true);
$id  = filter_var($input['id'], FILTER_VALIDATE_INT);
$val = $input['val'] ? 1 : 0;

if ($id) {
    $stmt = $pdo->prepare("UPDATE users SET is_admin = :a WHERE id = :id");
    $stmt->execute([':a' => $val, ':id' => $id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
