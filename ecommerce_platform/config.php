<?php
$host = '127.0.0.1';
$db   = 'myshop_db';
$user = 'root';      // par défaut sous XAMPP
$pass = '';          // mot de passe vide par défaut
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Connexion échouée : ' . $e->getMessage());
}
