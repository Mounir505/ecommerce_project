<?php
require 'admin_auth.php';
require '../config.php';

$page_title = 'Dashboard';
$current    = 'dashboard';

/* ===== KPIs ===== */
$totUsers      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totProducts   = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totCarts      = $pdo->query("SELECT COUNT(*) FROM cart")->fetchColumn();
$lowStockCount = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_qty<=5")->fetchColumn();

/* ===== Top 5 inscriptions ===== */
$latestUsersStmt = $pdo->query("
    SELECT first_name,last_name,email,created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");
$latestUsers = $latestUsersStmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== Top 5 produits low-stock ===== */
$lowStockStmt = $pdo->query("
    SELECT id,name,stock_qty
    FROM products
    WHERE stock_qty<=5
    ORDER BY stock_qty ASC
    LIMIT 5
");
$lowStock = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== Carts récents (5) ===== */
$recentCartsStmt = $pdo->query("
    SELECT c.id,u.first_name,p.name,c.quantity,c.added_at
    FROM cart c
    JOIN users u    ON u.id = c.user_id
    JOIN products p ON p.id = c.product_id
    ORDER BY c.added_at DESC
    LIMIT 5
");
$recentCarts = $recentCartsStmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== Top produit (le plus ajouté au panier) ===== */
$topProductStmt = $pdo->query("
    SELECT p.id, p.name, p.image_path, SUM(c.quantity) AS total_qty
    FROM cart c
    JOIN products p ON p.id = c.product_id
    GROUP BY c.product_id
    ORDER BY total_qty DESC
    LIMIT 1
");
$topProduct = $topProductStmt->fetch(PDO::FETCH_ASSOC);


/* ===== Revenus du mois courant (à partir des paniers) ===== */
$firstDay = date('Y-m-01 00:00:00');          // 1er jour du mois
$revenueStmt = $pdo->prepare("
    SELECT SUM(p.price_mad * c.quantity) AS revenue
    FROM cart c
    JOIN products p ON p.id = c.product_id
    WHERE c.added_at >= :firstDay
");
$revenueStmt->execute([':firstDay' => $firstDay]);
$revenue = (float) $revenueStmt->fetchColumn();          // ex. 12450
$revenueFmt = number_format($revenue, 0, ',', ' ');      // ex. "12 450"


include 'admin_header.php';
?>

<section class="section-p1">

    <!-- ===== KPI CARDS ===== -->
    <div class="kpi-grid">

        <div class="kpi-card">
            <i class="fas fa-user"></i>
            <h3><?= $totUsers ?></h3>
            <p>Utilisateurs</p>
        </div>

        <div class="kpi-card">
            <i class="fas fa-tags"></i>
            <h3><?= $totProducts ?></h3>
            <p>Produits</p>
        </div>

        <div class="kpi-card">
            <i class="fas fa-shopping-bag"></i>
            <h3><?= $totCarts ?></h3>
            <p>Paniers actifs</p>
        </div>

        <div class="kpi-card">
            <i class="fas fa-triangle-exclamation"></i>
            <h3><?= $lowStockCount ?></h3>
            <p>Stocks critiques</p>
        </div>

    </div><!-- /kpi-grid -->

    <!-- ===== ROW 2 : TABLEAUX ===== -->
    <div class="row-2">

        <!-- Derniers inscrits -->
        <div class="dash-box">
            <h4>Derniers inscrits</h4>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latestUsers as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Stock critique -->
        <div class="dash-box">
            <h4>Produits à stock faible</h4>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Produit</th>
                        <th>Qté</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStock as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><span class="badge-low"><?= $p['stock_qty'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /row-2 -->

    <!-- ===== ROW 3 : W🌟W ZONE ===== -->
    <div class="row-wow">

        <!-- Carte Revenus mensuels -->
        <div class="wow-card wow-revenue">
            <h4>Revenus (mois)</h4>

            <h2><?= $revenueFmt ?> MAD</h2>

            <svg viewBox="0 0 120 40" preserveAspectRatio="none">
                <polyline …></polyline>
            </svg>

            <p class="meta">
                Mis à jour : <?= date('d M H:i') ?>
            </p>
        </div>


        <!-- Produit le plus ajouté au panier -->
        <div class="wow-card wow-topprod">
            <h4>Produit le + ajouté au panier</h4>

            <?php if ($topProduct): ?>
                <img src="../<?= htmlspecialchars($topProduct['image_path']) ?>"
                    alt="<?= htmlspecialchars($topProduct['name']) ?>">
                <h5><?= htmlspecialchars($topProduct['name']) ?></h5>
                <p class="meta"><?= $topProduct['total_qty'] ?> ajouts</p>
            <?php else: ?>
                <p style="margin-top:15px;">Pas encore de données.</p>
            <?php endif; ?>
        </div>


        <!-- Quick actions -->
        <div class="wow-actions">
            <h4>Actions rapides</h4>

            <a href="add_product.php" class="quick-btn">
                <i class="fas fa-plus-circle"></i> Ajouter un produit
            </a>

            <a href="products.php" class="quick-btn">
                <i class="fas fa-box-open"></i> Gérer le stock
            </a>

            <a href="users.php" class="quick-btn">
                <i class="fas fa-user-shield"></i> Nouveau rôle admin
            </a>

            <a href="../index.php" class="quick-btn" target="_blank">
                <i class="fas fa-external-link-alt"></i> Voir la boutique
            </a>
        </div>


    </div><!-- /row-wow -->


    <!-- ===== Carts récents ===== -->
    <div class="dash-box" style="margin-top:40px;">
        <h4>Ajouts au panier (5 derniers)</h4>
        <table class="dash-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Client</th>
                    <th>Produit</th>
                    <th>Qté</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentCarts as $c): ?>
                    <tr>
                        <td>#<?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['first_name']) ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= $c['quantity'] ?></td>
                        <td><?= date('d/m H:i', strtotime($c['added_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</section>

<?php include 'admin_footer.php'; ?>