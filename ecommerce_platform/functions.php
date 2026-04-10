<?php
/* connexion PDO unique — adapte host / db / user / pass */
$pdo = new PDO(
    'mysql:host=localhost;dbname=myshop_db;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

session_start();

/* ↳ redirect si l’utilisateur n’est pas connecté */
function require_login()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/* ↳ récupère ou crée le panier actif */
function getCartId(PDO $pdo, int $uid): int
{
    $id = $pdo->prepare("SELECT id FROM carts WHERE user_id=?");
    $id->execute([$uid]);
    $cid = $id->fetchColumn();
    if ($cid) return $cid;

    $pdo->prepare("INSERT INTO carts(user_id) VALUES(?)")->execute([$uid]);
    return (int)$pdo->lastInsertId();
}
