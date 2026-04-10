<?php
// admin/view_cart.php
session_start();
require 'admin_auth.php';
require '../config.php';

$user_id = intval($_GET['user_id'] ?? 0);
if (!$user_id) {
    header('Location: carts.php');
    exit;
}

// Récupérer l’utilisateur
$stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    http_response_code(404);
    echo "Utilisateur introuvable.";
    exit;
}

// Récupérer les articles du panier
$stmt = $pdo->prepare("
    SELECT c.id AS cart_id,
           p.name, p.image_path,
           c.size, c.quantity,
           p.price_mad
      FROM cart c
      JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?
     ORDER BY c.added_at DESC
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panier de <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> · Admin</title>
    <!-- mêmes CSS que carts.php -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style.css">

</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="container my-5">
        <a href="carts.php" class="btn btn-light mb-3"><i class="fas fa-arrow-left"></i> Retour</a>
        <h1>Panier de <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>

        <?php if (empty($items)): ?>
            <div class="alert alert-info">Ce panier est vide.</div>
        <?php else: ?>
            <table id="viewCartTable" class="dash-table table nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Produit</th>
                        <th>Taille</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Sous-total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it):
                        $sub = $it['price_mad'] * $it['quantity'];
                    ?>
                        <tr data-cart-id="<?= $it['cart_id'] ?>">
                            <td>
                                <img src="../<?= htmlspecialchars($it['image_path']) ?>" alt="" width="60">
                            </td>

                            <td><?= htmlspecialchars($it['name']) ?></td>
                            <td><?= htmlspecialchars($it['size']) ?></td>
                            <td><?= $it['quantity'] ?></td>
                            <td><?= number_format($it['price_mad'], 2) ?> MAD</td>
                            <td><?= number_format($sub, 2) ?> MAD</td>
                            <td>
                                <button class="btn btn-sm btn-danger remove-item" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between mt-4">
                <button id="clearAll" class="btn btn-outline-danger">
                    <i class="fas fa-trash"></i> Vider panier
                </button>
                <button id="createOrder" class="btn btn-success">
                    <i class="fas fa-file-invoice"></i> Créer commande
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'admin_footer.php'; ?>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        $(function() {
            // 1) Init DataTable
            $('#viewCartTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }]
            });

            // 2) Supprimer un item
            $('#viewCartTable').on('click', '.remove-item', function() {
                let cartId = $(this).closest('tr').data('cart-id');
                if (!confirm('Supprimer cet article du panier ?')) return;
                $.post('../remove_cart_item.php', {
                    cart_id: cartId
                }, data => {
                    location.reload();
                });
            });

            // 3) Vider tout
            $('#clearAll').on('click', () => {
                if (!confirm('Vider entièrement le panier ?')) return;
                $.getJSON('../clear_cart.php', {
                    user_id: <?= $user_id ?>
                }, d => {
                    alert(d.success ? '✅ Panier vidé' : '❌ ' + d.message);
                    location.href = 'carts.php';
                });
            });

            // 4) Créer commande
            $('#createOrder').on('click', () => {
                if (!confirm('Créer une commande à partir de ce panier ?')) return;
                $.post('create_order.php', JSON.stringify({
                    user_id: <?= $user_id ?>
                }), resp => {
                    if (resp.success) {
                        alert('✅ Commande #' + resp.order_id + ' créée');
                        location.href = 'view_order.php?id=' + resp.order_id;
                    } else alert('❌ ' + resp.message);
                }, 'json');
            });
        });
    </script>
</body>

</html>