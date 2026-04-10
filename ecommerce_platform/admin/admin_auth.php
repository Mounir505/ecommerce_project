<?php
// admin/admin_auth.php
session_start();

/**
 *  Vérifie qu’il existe une session admin.
 *  On l’inclura en tête de chaque page du back-office.
 */
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Pas connecté en admin : on renvoie vers le formulaire de connexion
    header('Location: login.php');
    exit;
}
