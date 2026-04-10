<?php
// admin/products.php
require 'admin_auth.php';
require '../config.php';

$page_title = 'Produits';
$current    = 'products';

// Recherche
$search = trim($_GET['search'] ?? '');

// Récupérer les produits (filtrés si recherche)
if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM products
       WHERE name LIKE :s
       ORDER BY id DESC"
    );
    $stmt->execute([':s' => "%{$search}%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


include 'admin_header.php';
?>
<?php if ($success): ?>
    <div id="flash-msg" class="flash-modern">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>


<section class="section-p1">
    <form method="get" class="search-form mb-3">
        <div class="input-group modern-search-group">
            <!-- Icône cliquable pour toggle -->
            <button type="button" class="input-group-text bg-white border-end-0 search-toggle">
                <i class="fas fa-search"></i>
            </button>
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Rechercher un produit..."
                class="form-control modern-search-input">
            <button class="btn btn-outline-secondary modern-search-btn" type="submit">
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>



    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2>Gestion des produits</h2>
        <a href="add_product.php" class="quick-btn">
            <i class="fas fa-plus-circle"></i> Ajouter un produit
        </a>
    </div>

    <div style="overflow-x:auto;">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prix (MAD)</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>#<?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= number_format($p['price_mad'], 2, ',', ' ') ?></td>
                        <td>
                            <span class="<?= $p['stock_qty'] <= 5 ? 'badge bg-danger' : '' ?>">
                                <?= $p['stock_qty'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($p['image_path']): ?>
                                <img src="../<?= htmlspecialchars($p['image_path']) ?>"
                                    style="width:50px;border-radius:4px;" alt="">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_product.php?id=<?= $p['id'] ?>" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            &nbsp;
                            <a href="delete_product.php?id=<?= $p['id'] ?>"
                                onclick="return confirm('Supprimer ce produit ?');"
                                title="Supprimer" style="color:#e60023;">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const msg = document.getElementById('flash-msg');
        if (!msg) return;

        // après 2s, on ajoute la classe hide pour déclencher le fade & slide up
        setTimeout(() => {
            msg.classList.add('hide');
            // on supprime l’élément après la transition
            setTimeout(() => msg.remove(), 400);
        }, 2000);
    });
</script>


<?php include 'admin_footer.php'; ?>