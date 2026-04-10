<?php
// admin/edit_product.php
require 'admin_auth.php';
require '../config.php';

$page_title = 'Modifier un produit';
$current    = 'products';

// Récupération de l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: products.php');
    exit;
}

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header('Location: products.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validation
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);

    if ($name === '' || $price <= 0) {
        $error = 'Le nom et le prix sont obligatoires.';
    }

    // 2) Upload image optionnel
    $imagePath = $product['image_path'];
    if (!$error && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2 Mo

        if (in_array($_FILES['image']['type'], $allowed) && $_FILES['image']['size'] <= $maxSize) {
            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $destName = 'img/products/' . uniqid('prd_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], '../' . $destName)) {
                $imagePath = $destName;
            } else {
                $error = 'Erreur lors de l’enregistrement de l’image.';
            }
        } else {
            $error = 'Format ou taille d’image invalide.';
        }
    }

    // 3) Mise à jour en base
    if (!$error) {
        $upd = $pdo->prepare("
            UPDATE products
            SET name = :n,
                description = :d,
                price_mad = :p,
                stock_qty = :s,
                image_path = :i
            WHERE id = :id
        ");
        $upd->execute([
            ':n'   => $name,
            ':d'   => $description,
            ':p'   => $price,
            ':s'   => $stock,
            ':i'   => $imagePath,
            ':id'  => $id,
        ]);
        $success = 'Produit mis à jour !';
        // rafraîchir les données
        $product = array_merge($product, ['name' => $name, 'description' => $description, 'price_mad' => $price, 'stock_qty' => $stock, 'image_path' => $imagePath]);
    }
}

include 'admin_header.php';
?>

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4">Modifier le produit #<?= $product['id'] ?></h2>

            <?php if ($error): ?>
                <div id="flash-msg" class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div id="flash-msg" class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Nom du produit</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="<?= htmlspecialchars($product['name']) ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea
                        name="description"
                        rows="4"
                        class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Prix (MAD)</label>
                        <input
                            type="number"
                            name="price"
                            step="0.01"
                            class="form-control"
                            value="<?= htmlspecialchars($product['price_mad']) ?>"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantité en stock</label>
                        <input
                            type="number"
                            name="stock"
                            class="form-control"
                            value="<?= htmlspecialchars($product['stock_qty']) ?>"
                            required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Image actuelle</label><br>
                    <?php if ($product['image_path']): ?>
                        <img
                            src="../<?= htmlspecialchars($product['image_path']) ?>"
                            class="img-thumbnail mb-3"
                            style="max-width:180px;"
                            alt="<?= htmlspecialchars($product['name']) ?>"><br>
                    <?php endif; ?>

                    <label class="form-label">Changer l’image</label>
                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept="image/png,image/jpeg,image/webp">
                </div>

                <button type="submit" class="btn modern-btn">
                    Mettre à jour
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const msg = document.getElementById('flash-msg');
        if (!msg) return;
        setTimeout(() => {
            msg.style.transition = 'opacity .5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    });
</script>

<?php include 'admin_footer.php'; ?>