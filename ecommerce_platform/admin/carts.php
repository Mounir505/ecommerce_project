<?php
// admin/carts.php
session_start();
require 'admin_auth.php';
require '../config.php';

$page_title = 'Gestion des Paniers';

// 1) Récup de tous les paniers groupés
$stmt = $pdo->query("
  SELECT u.id AS user_id,
         CONCAT(u.first_name,' ',u.last_name) AS user_name,
         u.email,
         COUNT(c.id) AS item_count,
         SUM(p.price_mad * c.quantity) AS total,
         MAX(c.added_at) AS last_added
  FROM cart c
  JOIN users u ON c.user_id = u.id
  JOIN products p ON c.product_id = p.id
  GROUP BY u.id
  ORDER BY last_added DESC
");
$carts = $stmt->fetchAll();

// 2) Calculs KPI
$totalUsersWithCart = count($carts);
$totalItemsInCarts   = array_sum(array_column($carts, 'item_count'));
$totalCartValue      = array_sum(array_column($carts, 'total'));
// NOUVEAU : moyenne d’articles par panier
$avgItemsPerCart = $totalUsersWithCart
    ? round($totalItemsInCarts / $totalUsersWithCart, 2)
    : 0;


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title><?= htmlspecialchars($page_title) ?> · Admin</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6VJZq1aw+..."
        crossorigin="anonymous" />

    <!-- FontAwesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-…"
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />

    <!-- DataTables + Buttons CSS -->
    <link
        href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"
        rel="stylesheet" />
    <link
        href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"
        rel="stylesheet" />

    <!-- Ton CSS principal -->
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <!-- Toast container (pour notifications) -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:10800">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i id="toastIcon" class="bi me-2"></i>
                <strong id="toastTitle" class="me-auto"></strong>
                <small class="text-muted">Maintenant</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fermer"></button>
            </div>
            <div id="toastBody" class="toast-body"></div>
        </div>
    </div>

    <?php include 'admin_header.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4"><?= htmlspecialchars($page_title) ?></h1>

        <!-- KPI GRID -->
        <div class="kpi-grid mb-4">
            <div class="kpi-card">
                <i class="fas fa-users"></i>
                <h3><?= $totalUsersWithCart ?></h3>
                <p>Utilisateurs actifs</p>
            </div>
            <div class="kpi-card">
                <i class="fas fa-boxes"></i>
                <h3><?= $totalItemsInCarts ?></h3>
                <p>Articles en panier</p>
            </div>
            <div class="kpi-card">
                <i class="fas fa-coins"></i>
                <h3><?= number_format($totalCartValue, 2) ?> MAD</h3>
                <p>Valeur totale</p>
            </div>
            <div class="kpi-card">
                <i class="fas fa-chart-line"></i>
                <h3><?= $avgItemsPerCart ?></h3>
                <p>Moyenne articles/panier</p>
            </div>

        </div>

        <!-- DATA TABLE -->
        <table id="cartsTable" class="dash-table table nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Articles</th>
                    <th>Total (MAD)</th>
                    <th>Dernier ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carts as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['user_name']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= $c['item_count'] ?></td>
                        <td><?= number_format($c['total'], 2) ?></td>
                        <td><?= $c['last_added'] ?></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-outline-primary view-cart" data-user="<?= $c['user_id'] ?>" title="Voir">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success process-payment" data-user="<?= $c['user_id'] ?>" title="Paiement">
                                <i class="fas fa-credit-card"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger clear-cart" data-user="<?= $c['user_id'] ?>" title="Vider">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include 'admin_footer.php'; ?>

    <!-- Scripts -->
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-…"
        crossorigin="anonymous"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-…"
        crossorigin="anonymous"></script>

    <!-- DataTables + Buttons -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        $(document).ready(function() {
            // 1) Initialisation DataTable (une seule fois !)
            const table = $('#cartsTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i>'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i>'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i>'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i>'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i>'
                    }
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    search: "Rechercher :",
                    paginate: {
                        previous: "Précédent",
                        next: "Suivant"
                    },
                    info: "_START_ à _END_ sur _TOTAL_"
                }
            });

            // 2) Handlers JS
            $('#cartsTable').on('click', '.clear-cart', function() {
                const userId = $(this).data('user');
                if (!confirm('Vider tout le panier ?')) return;
                $.getJSON('admin/clear_user_cart.php', {
                    user_id: userId
                }, data => {
                    alert(data.success ? '✅ Panier vidé' : '❌ ' + data.message);
                    if (data.success) table.row($(this).parents('tr')).remove().draw();
                });
            });

            $('#cartsTable').on('click', '.view-cart', function() {
                const userId = $(this).data('user');
                window.location.href = 'view_cart.php?user_id=' + userId;
            });

            $('#cartsTable').on('click', '.process-payment', function() {
                const userId = $(this).data('user');
                if (!confirm('Marquer le paiement comme effectué ?')) return;
                $.ajax({
                    url: 'admin/process_payment.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        user_id: userId
                    }),
                    dataType: 'json'
                }).done(data => {
                    alert(data.success ? '✅ Paiement validé' : '❌ ' + data.message);
                    if (data.success) table.row($(this).parents('tr')).remove().draw();
                });
            });

        });
    </script>
</body>

</html>