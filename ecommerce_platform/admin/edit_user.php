<?php
// admin/edit_user.php
require 'admin_auth.php';
require '../config.php';

$page_title = 'Modifier un utilisateur';
$current    = 'users';

// 1) Récupérer l’ID et l’utilisateur
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: users.php');
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    header('Location: users.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) Validation et collecte
    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name']  ?? '');
    $email  = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $phone  = trim($_POST['phone']      ?? '');
    $job    = trim($_POST['job']        ?? '');
    $admin  = isset($_POST['is_admin']) ? 1 : 0;

    if (!$first || !$last || !$email) {
        $error = 'Prénom, nom et email valides requis.';
    } else {
        // 3) Vérifier l’unicité de l’email (hors cet ID)
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?");
        $chk->execute([$email, $id]);
        if ($chk->fetchColumn() > 0) {
            $error = 'Cet email est déjà utilisé par un autre utilisateur.';
        }
    }

    // 4) Enregistrer
    if (!$error) {
        $upd = $pdo->prepare("
            UPDATE users
            SET first_name = :f,
                last_name  = :l,
                email      = :e,
                phone      = :p,
                job        = :j,
                is_admin   = :a
            WHERE id = :id
        ");
        $upd->execute([
            ':f'  => $first,
            ':l'  => $last,
            ':e'  => $email,
            ':p'  => $phone,
            ':j'  => $job,
            ':a'  => $admin,
            ':id' => $id
        ]);
        $success = 'Utilisateur mis à jour !';
        // rafraîchir les données
        $user = array_merge($user, [
            'first_name' => $first,
            'last_name' => $last,
            'email'     => $email,
            'phone'     => $phone,
            'job'       => $job,
            'is_admin'  => $admin
        ]);
    }
}

include 'admin_header.php';
?>

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="card-title mb-4">Modifier l’utilisateur #<?= $user['id'] ?></h2>

            <?php if ($error): ?>
                <div id="flash-msg" class="flash-modern"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div id="flash-msg" class="flash-modern"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>"
                            class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>"
                            class="form-control" required>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                            class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"
                            class="form-control">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Job</label>
                        <input type="text" name="job" value="<?= htmlspecialchars($user['job']) ?>"
                            class="form-control">
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch ms-auto">
                            <input class="form-check-input" type="checkbox" name="is_admin" id="isAdminSwitch"
                                <?= $user['is_admin'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isAdminSwitch">Administrateur</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn modern-btn mt-4">
                    <i class="fas fa-save me-1"></i> Mettre à jour
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
        // Bootstrap validation
        (function() {
            document.querySelectorAll('.needs-validation').forEach(f => {
                f.addEventListener('submit', e => {
                    if (!f.checkValidity()) {
                        e.preventDefault();
                        f.classList.add('was-validated');
                    }
                });
            });
        })();
    });
</script>

<?php include 'admin_footer.php'; ?>