<?php
// admin/add_user.php
require 'admin_auth.php';
require '../config.php';

$page_title = 'Ajouter un utilisateur';
$current    = 'users';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Récupération + validation
    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name']  ?? '');
    $email  = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone  = trim($_POST['phone']      ?? '');
    $job    = trim($_POST['job']        ?? '');
    $pass   = $_POST['password'] ?? '';
    $admin  = isset($_POST['is_admin']) ? 1 : 0;

    if (!$first || !$last || !$email || strlen($pass) < 6) {
        $error = 'Prénom, nom, email valides et mot de passe ≥ 6 caractères requis.';
    } else {
        // 2) Vérifier que l'email n'existe pas
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetchColumn() > 0) {
            $error = 'Cet email est déjà utilisé.';
        }
    }

    // 3) Insert
    if (!$error) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("
          INSERT INTO users
            (first_name,last_name,email,phone,job,password_hash,is_admin)
          VALUES
            (:f,:l,:e,:p,:j,:h,:a)
        ");
        $ins->execute([
            ':f' => $first,
            ':l' => $last,
            ':e' => $email,
            ':p' => $phone,
            ':j' => $job,
            ':h' => $hash,
            ':a' => $admin
        ]);
        $success = 'Utilisateur ajouté !';
        // Vide le formulaire
        $first = $last = $email = $phone = $job = $pass = '';
        $admin = 0;
    }
}

include 'admin_header.php';
?>

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4">Ajouter un utilisateur</h2>

            <?php if ($error): ?>
                <div id="flash-msg" class="flash-modern"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div id="flash-msg" class="flash-modern"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input
                            type="text"
                            name="first_name"
                            class="form-control"
                            value="<?= htmlspecialchars($first ?? '') ?>"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input
                            type="text"
                            name="last_name"
                            class="form-control"
                            value="<?= htmlspecialchars($last ?? '') ?>"
                            required>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars($email ?? '') ?>"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?= htmlspecialchars($phone ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Job</label>
                        <input
                            type="text"
                            name="job"
                            class="form-control"
                            value="<?= htmlspecialchars($job ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mot de passe</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            required>
                    </div>
                </div>

                <div class="form-check form-switch mt-4">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="is_admin"
                        id="isAdminSwitch"
                        <?= !empty($admin) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isAdminSwitch">
                        Rôle administrateur
                    </label>
                </div>

                <button type="submit" class="btn modern-btn mt-4">
                    <i class="fas fa-user-plus me-1"></i> Créer l'utilisateur
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fm = document.getElementById('flash-msg');
        if (fm) setTimeout(() => {
            fm.classList.add('hide');
            setTimeout(() => fm.remove(), 400);
        }, 2000);

        // validation bootstrap
        (function() {
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(f => {
                f.addEventListener('submit', e => {
                    if (!f.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    f.classList.add('was-validated');
                });
            });
        })();
    });
</script>

<?php include 'admin_footer.php'; ?>