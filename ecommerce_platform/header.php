<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php'; // s'assurer que PDO est disponible

/*--- lien actif ---*/
$current = basename($_SERVER['PHP_SELF']);
function isActive(string $file): string
{
    global $current;
    return $current === $file ? 'class="active"' : '';
}

// 🛒 Compteur du panier pour l'utilisateur connecté
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT product_id) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = $stmt->fetchColumn();
}

?>

<!-- =========  NAVBAR ========= -->
<section id="header">
    <a href="index.php"><img src="img/logo.png" class="logo" alt="Logo"></a>

    <ul id="navbar">
        <li><a href="index.php" <?= isActive('index.php');   ?>>Home</a></li>
        <li><a href="shop.php" <?= isActive('shop.php');    ?>>Shop</a></li>
        <li><a href="blog.php" <?= isActive('blog.php');    ?>>Blog</a></li>
        <li><a href="about.php" <?= isActive('about.php');   ?>>About</a></li>
        <li><a href="contact.php" <?= isActive('contact.php'); ?>>Contact</a></li>

        <li>
            <a href="cart.php" class="cart-link <?= isActive('cart.php') ?>">
                <i class="fas fa-shopping-bag big-cart-icon"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
        </li>



        <!-- ---- Droite : utilisateur ---- -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Lien vers la page profil -->
            <li><a href="profile.php"><i class="fa fa-user-circle"></i> <?= htmlspecialchars($_SESSION['name']); ?></a></li>
            <!-- Lien Logout -->
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php" <?= isActive('login.php'); ?>>Login / Register</a></li>
        <?php endif; ?>

        <a href="#" id="close"><i class="far fa-times"></i></a>
    </ul>

    <!-- Mobile -->
    <div id="mobile">
        <a href="cart.php"><i class="fal fa-shopping-bag"></i></a>
        <i id="bar" class="fas fa-outdent"></i>
    </div>
</section>
<script>
    function updateCartCount() {
        fetch('cart_count.php')
            .then(response => response.json())
            .then(data => {
                const cartCountEl = document.querySelector('.cart-count');
                if (data.count > 0) {
                    if (!cartCountEl) {
                        // Créer la pastille si elle n'existe pas
                        const span = document.createElement('span');
                        span.className = 'cart-count';
                        span.textContent = data.count;
                        document.querySelector('.cart-link').appendChild(span);
                    } else {
                        cartCountEl.textContent = data.count;
                    }
                } else if (cartCountEl) {
                    cartCountEl.remove(); // retirer la pastille si vide
                }
            });
    }

    // Exécuter au chargement + après ajout panier
    document.addEventListener('DOMContentLoaded', updateCartCount);
</script>