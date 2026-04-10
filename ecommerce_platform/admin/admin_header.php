<?php
/* =========  admin/admin_header.php  ========= */

/* — Sécurité : on vérifie la session admin — */
require_once 'admin_auth.php';      // démarre la session + vérif is_admin
require_once '../config.php';       // connexion PDO (si tu en as besoin ici)

/* --- lien actif --- */
$current = basename($_SERVER['PHP_SELF']);
function isActive(string $file): string
{
    global $current;
    return $current === $file ? 'class="active"' : '';
}

/* Cart count n’est pas utile dans l’admin */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> · MyShop</title>

    <!-- Même feuille de style globale que le front -->
    <link rel="stylesheet" href="../style.css">

    <!-- Font Awesome (pour les icônes du menu burger, etc.) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-Zg+TcH+2jt1vhk8gvfYv4…" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6VJZq1aw+..."
        crossorigin="anonymous" />
    <!-- DataTables CSS -->
    <link
        href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"
        rel="stylesheet" />

</head>

<body>
    <!-- =========  NAVBAR ADMIN ========= -->
    <section id="header">
        <!-- Logo (lien vers la page d’accueil publique) -->
        <a href="../index.php"><img src="../img/logo.png" class="logo" alt="Logo"></a>

        <ul id="navbar">
            <!-- Liens back-office -->
            <li><a href="dashboard.php" <?= isActive('dashboard.php');   ?>>Dashboard</a></li>
            <li><a href="products.php" <?= isActive('products.php');    ?>>Produits</a></li>
            <li><a href="users.php" <?= isActive('users.php');       ?>>Utilisateurs</a></li>
            <li><a href="carts.php" <?= isActive('carts.php');       ?>>Paniers</a></li>
            <!-- Exemple futur : commandes
        <li><a href="orders.php"      <?= isActive('orders.php');      ?>>Commandes</a></li> -->

            <!-- À droite : nom de l’admin + logout -->
            <li><a href="#"><i class="fa fa-user-shield"></i>
                    <?= htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></a></li>
            <li><a href="logout.php">Logout</a></li>

            <a href="#" id="close"><i class="far fa-times"></i></a>
        </ul>

        <!-- Mobile -->
        <div id="mobile">
            <i id="bar" class="fas fa-outdent"></i>
        </div>
    </section>

    <!-- Inclure le même JS que le front pour le menu burger -->
    <script src="../script.js"></script>
</body>

</html>