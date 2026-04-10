<?php
// admin/add_product.php
require 'admin_auth.php';
require '../config.php';

$page_title = 'Ajouter un produit';
$current    = 'products';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Récupération + validation
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);

    if ($name === '' || $price <= 0) {
        $error = 'Le nom et le prix sont obligatoires.';
    }

    // 2) Gestion de l’upload d’image
    $imagePath = null;
    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2 Mo

        if (in_array($_FILES['image']['type'], $allowed) && $_FILES['image']['size'] <= $maxSize) {
            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $destName = 'img/products/' . uniqid('prd_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], '../' . $destName)) {
                $error = 'Erreur lors de l’enregistrement de l’image.';
            } else {
                $imagePath = $destName;
            }
        } else {
            $error = 'Format ou taille d’image invalide (max 2 Mo, jpg/png/webp).';
        }
    }

    // 3) Insertion en base
    if (!$error) {
        $stmt = $pdo->prepare("
            INSERT INTO products
              (name, description, price_mad, stock_qty, image_path)
            VALUES
              (:n, :d, :p, :s, :i)
        ");
        $stmt->execute([
            ':n' => $name,
            ':d' => $description,
            ':p' => $price,
            ':s' => $stock,
            ':i' => $imagePath
        ]);
        $success = 'Produit ajouté avec succès !';
        // Réinitialiser le formulaire
        $name = $description = '';
        $price = $stock = 0;
    }
}

include 'admin_header.php';
?>

<section class="section-p1">
    <h2>Ajouter un nouveau produit</h2>

    <?php if ($error): ?>
        <div id="flash-msg" class="alert alert-warning">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>


    <?php if ($success): ?>
        <div id="flash-msg" class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>


    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nom du produit</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4" class="form-control"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Prix (MAD)</label>
                <input type="number" name="price" class="form-control" step="0.01" required>
            </div>
            <div class="form-group col-md-6">
                <label>Quantité en stock</label>
                <input type="number" name="stock" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label>Image du produit</label>
            <input type="file" name="image" class="form-control-file" accept="image/*">
        </div>
        <button type="submit" class="btn normal">Ajouter le produit</button>
    </form>

</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const msg = document.getElementById('flash-msg');
        if (!msg) return;

        // après 3s, on fait fondre puis on supprime
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s ease-out';
            msg.style.opacity = '0';
            // on enlève complètement l’élément après la transition
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    });
</script>


<?php include 'admin_footer.php'; ?>