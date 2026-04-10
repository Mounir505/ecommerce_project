<?php
// admin/users.php
require 'admin_auth.php';
require '../config.php';

$page_title = 'Utilisateurs';
$current    = 'users';

// 1) Recherche
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM users
        WHERE first_name LIKE :s OR last_name LIKE :s OR email LIKE :s
        ORDER BY created_at DESC
    ");
    $stmt->execute([':s' => "%{$search}%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
}
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'admin_header.php';
?>

<!-- Flash après suppression ou promo/démotion -->
<?php if (isset($_GET['msg'])): ?>
    <div id="flash-msg" class="flash-modern">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
<?php endif; ?>

<section class="section-p1">

    <!-- SEARCH & ADD -->
    <form method="get" class="search-form mb-3">
        <div class="input-group modern-search-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search"></i>
            </span>
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Rechercher un utilisateur..."
                class="form-control modern-search-input">
            <button class="btn btn-outline-secondary modern-search-btn" type="submit">
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>




    <div class="users-card">
        <div class="users-header">
            <h2>Gestion des utilisateurs</h2>
            <a href="add_user.php" class="quick-btn">
                <i class="fas fa-user-plus"></i> Ajouter
            </a>
        </div>

        <div class="users-table">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Tél.</th>
                        <th>Job</th>
                        <th>Inscrit le</th>
                        <th>Admin?</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['phone']) ?></td>
                            <td><?= htmlspecialchars($u['job']) ?></td>
                            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <label class="toggle-switch">
                                    <input
                                        type="checkbox"
                                        class="role-toggle"
                                        data-id="<?= $u['id'] ?>"
                                        <?= $u['is_admin'] ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td class="actions">
                                <a href="edit_user.php?id=<?= $u['id'] ?>" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_user.php?id=<?= $u['id'] ?>"
                                    onclick="return confirm('Supprimer cet utilisateur ?');"
                                    title="Supprimer">
                                    <i class="fas fa-trash-alt" style="color:#e60023"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</section>

<!-- JS animations & toggles -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1) Flash auto-hide
        const fm = document.getElementById('flash-msg');
        if (fm) {
            setTimeout(() => {
                fm.classList.add('hide');
                setTimeout(() => fm.remove(), 400);
            }, 2000);
        }

        // 2) Search slide
        const tog = document.querySelector('.search-toggle');
        const grp = document.querySelector('.modern-search-group');
        const inp = document.querySelector('.modern-search-input');
        tog.addEventListener('click', () => {
            grp.classList.toggle('active');
            if (grp.classList.contains('active')) setTimeout(() => inp.focus(), 400);
        });

        // 3) Promote/Démote AJAX
        document.querySelectorAll('.role-toggle').forEach(cb => {
            cb.addEventListener('change', () => {
                const id = cb.dataset.id;
                const val = cb.checked ? 1 : 0;
                fetch('toggle_user_role.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id,
                            val
                        })
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (j.success) {
                            const msg = document.createElement('div');
                            msg.className = 'flash-modern';
                            msg.textContent = cb.checked ?
                                'Promu admin ✔️' :
                                'Rétrogradé ✔️';
                            document.body.append(msg);
                            setTimeout(() => {
                                msg.classList.add('hide');
                                setTimeout(() => msg.remove(), 400);
                            }, 2000);
                        }
                    });
            });
        });
    });
</script>

<?php include 'admin_footer.php'; ?>