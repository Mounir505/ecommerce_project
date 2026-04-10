<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* ------- données ------- */
$stmt = $pdo->prepare("SELECT first_name,last_name,email,phone,job,avatar FROM users WHERE id=:id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

/* ------- mise à jour ------- */
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- suppression de l'avatar ---
    if (isset($_POST['delete_avatar'])) {
        $upload_dir = 'uploads/avatars';
        if (!empty($user['avatar']) && file_exists($upload_dir . '/' . $user['avatar'])) {
            unlink($upload_dir . '/' . $user['avatar']);
        }
        $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?")->execute([$_SESSION['user_id']]);
        $user['avatar'] = null;
        $success = "Votre photo de profil a été supprimée.";
    }

    // --- mise à jour du profil (infos + avatar) ---
    if (isset($_POST['update_profile'])) {
        try {
            // Mise à jour avatar sécurisée
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                $max_size = 2 * 1024 * 1024; // 2MB max

                $file_tmp  = $_FILES['avatar']['tmp_name'];
                $file_type = mime_content_type($file_tmp);
                $file_size = $_FILES['avatar']['size'];

                if (!in_array($file_type, $allowed_types)) {
                    $error = "Format d'image invalide (jpg/png/webp uniquement).";
                } elseif ($file_size > $max_size) {
                    $error = "L'image dépasse 2 Mo.";
                } else {
                    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $avatar_name = 'user_' . $_SESSION['user_id'] . '.' . $ext;
                    $upload_dir = 'uploads/avatars';
                    $avatar_path = $upload_dir . '/' . $avatar_name;

                    // Créer le dossier s'il n'existe pas
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true); // récursif
                    }

                    // Supprimer l'ancien avatar s'il existe
                    if (!empty($user['avatar']) && file_exists($upload_dir . '/' . $user['avatar'])) {
                        unlink($upload_dir . '/' . $user['avatar']);
                    }

                    // Déplacement
                    if (move_uploaded_file($file_tmp, $avatar_path)) {
                        // Forcer les bons droits sur le fichier (lecture/écriture pour Apache)
                        chmod($avatar_path, 0644);

                        // Mise à jour BDD
                        $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$avatar_name, $_SESSION['user_id']]);
                        $user['avatar'] = $avatar_name;
                    } else {
                        $error = "Erreur lors de l'enregistrement du fichier sur le serveur.";
                    }
                }
            }

            // Mise à jour des infos utilisateur
            $pdo->prepare("UPDATE users SET first_name=:f,last_name=:l,phone=:p,job=:j WHERE id=:id")
                ->execute([
                    ':f' => trim($_POST['first_name']),
                    ':l' => trim($_POST['last_name']),
                    ':p' => trim($_POST['phone']),
                    ':j' => trim($_POST['job']),
                    ':id' => $_SESSION['user_id']
                ]);

            $_SESSION['name'] = trim($_POST['first_name']);
            $success = 'Votre profil a été mis à jour.';
            $user = array_merge($user, $_POST);
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
    // --- changement de mot de passe sécurisé ---
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Vérifie le mot de passe actuel
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $pw_error = "Mot de passe actuel incorrect.";
        } elseif (strlen($new) < 6) {
            $pw_error = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        } elseif ($new !== $confirm) {
            $pw_error = "La confirmation ne correspond pas.";
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$new_hash, $_SESSION['user_id']]);
            $pw_success = "Mot de passe mis à jour avec succès.";
        }
    }
}

include 'header.php';
?>




<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Mon profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --hx-primary: #0d9488;
            --hx-bg: #f6f8fb;
        }

        #hx-profile {
            font-family: Poppins, sans-serif;
            background: var(--hx-bg);
            min-height: 100vh;
        }

        /* ---------- layout ---------- */
        #hx-sidebar {
            position: fixed;
            top: 80px;
            left: 0;
            width: 70px;
            height: calc(100vh - 80px);
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        .hx-navlink {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #64748b;
            border-left: 3px solid transparent;
            transition: .2s;
        }

        .hx-navlink.active,
        .hx-navlink:hover {
            color: var(--hx-primary);
            border-color: var(--hx-primary);
        }

        #hx-main {
            margin-left: 70px;
            padding: 2rem;
        }

        /* ---------- card ---------- */
        .hx-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
            padding: 2rem;
        }

        /* ---------- avatar ---------- */
        .hx-avatar-box {
            position: relative;
            width: 120px;
            margin: 24px auto 0 auto;
            /* Center and add top margin */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hx-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--hx-primary);
            color: #fff;
            font-size: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hx-upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--hx-primary);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* ---------- boutons ---------- */
        /* bouton vert global HX */
        .btn-hx {
            --bs-btn-bg: var(--hx-primary);
            --bs-btn-border-color: var(--hx-primary);
            --bs-btn-hover-bg: #0b7f75;
            /* 10 % plus sombre */
            --bs-btn-hover-border-color: #0b7f75;
            --bs-btn-active-bg: #096e65;
            /* clic */
            --bs-btn-active-border-color: #096e65;
            --bs-btn-disabled-bg: var(--hx-primary);
            --bs-btn-disabled-border-color: var(--hx-primary);
            color: #fff;
        }


        .timeline {
            border-left: 2px solid var(--hx-primary);
            margin-left: .7rem;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.2rem;
            padding-left: 1rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -9px;
            top: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--hx-primary);
        }

        /* ---------- responsive ---------- */
        @media (max-width:992px) {
            #hx-sidebar {
                height: auto;
                flex-direction: row;
                width: 100%;
                top: auto;
                left: 0;
                border-right: 0;
                border-bottom: 1px solid #e2e8f0;
            }

            .hx-navlink {
                flex: 1;
                border-left: 0;
                border-bottom: 3px solid transparent;
            }

            .hx-navlink.active {
                border-bottom-color: var(--hx-primary);
            }

            #hx-main {
                margin: 0;
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body id="hx-profile">

    <!-- ===== sidebar ===== -->
    <nav id="hx-sidebar">
        <a class="hx-navlink active" data-bs-toggle="tab" href="#tab-profil" title="Profil"><i class="fa fa-user"></i></a>
        <a class="hx-navlink" data-bs-toggle="tab" href="#tab-security" title="Sécurité"><i class="fa fa-lock"></i></a>
        <a class="hx-navlink" data-bs-toggle="tab" href="#tab-activity" title="Activité"><i class="fa fa-clock-rotate-left"></i></a>
    </nav>

    <!-- ===== main ===== -->
    <div id="hx-main">
        <div class="tab-content">
            <!-- PROFIL -->
            <section class="tab-pane fade show active" id="tab-profil">
                <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <div class="hx-card">
                    <form method="post" enctype="multipart/form-data" class="row g-4 align-items-start">

                        <!-- avatar -->
                        <div class="col-12 col-md-auto text-center">
                            <div class="hx-avatar-box mx-auto">
                                <?php
                                $has_avatar = !empty($user['avatar']) && file_exists("uploads/avatars/" . $user['avatar']);
                                $avatar = $has_avatar
                                    ? "uploads/avatars/" . $user['avatar']
                                    : "https://ui-avatars.com/api/?name=" . urlencode($user['first_name'] . ' ' . $user['last_name']) . "&size=256&background=0d9488&color=fff";
                                ?>
                                <img id="hxAvatar" src="<?= $avatar ?>" class="hx-avatar mb-2" alt="avatar">

                                <label class="hx-upload-btn" title="Changer">
                                    <i class="fa fa-camera"></i>
                                    <input type="file" name="avatar" hidden accept="image/*" onchange="previewAvatar(event)">
                                </label>
                            </div>

                            <!-- bouton supprimer -->
                            <?php if ($has_avatar): ?>
                                <button type="submit" name="delete_avatar" class="btn btn-sm btn-outline-danger mt-3 w-100"
                                    onclick="return confirm('Supprimer votre photo de profil ?')">
                                    <i class="fa fa-trash me-2"></i>Supprimer la photo
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- champs texte -->
                        <div class="col">
                            <div class="row g-3">
                                <input type="hidden" name="update_profile" value="1">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Prénom</label>
                                    <input name="first_name" class="form-control" required value="<?= htmlspecialchars($user['first_name']); ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Nom</label>
                                    <input name="last_name" class="form-control" required value="<?= htmlspecialchars($user['last_name']); ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Téléphone</label>
                                    <input name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold">Métier</label>
                                    <input name="job" class="form-control" value="<?= htmlspecialchars($user['job']); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input class="form-control-plaintext ps-0" readonly value="<?= htmlspecialchars($user['email']); ?>">
                                </div>
                                <div class="col-12 d-flex flex-column flex-sm-row gap-3 mt-2">
                                    <button class="btn btn-hx text-white flex-fill" type="submit" name="update_profile">
                                        <i class="fa fa-floppy-disk me-2"></i>Sauvegarder
                                    </button>
                                    <a href="logout.php" class="btn btn-outline-danger flex-fill">
                                        <i class="fa fa-right-from-bracket me-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- /row -->
        </div><!-- /card -->
        </section>

        <!-- SECURITE -->
        <section class="tab-pane fade" id="tab-security">
            <div class="hx-card col-lg-7">
                <h5 class="fw-bold mb-4"><i class="fa fa-shield-halved me-2"></i>Sécurité</h5>

                <?php if (!empty($pw_success)) : ?>
                    <div class="alert alert-success"><?= $pw_success ?></div>
                <?php elseif (!empty($pw_error)) : ?>
                    <div class="alert alert-danger"><?= $pw_error ?></div>
                <?php endif; ?>

                <form method="post" class="row g-3">
                    <input type="hidden" name="change_password" value="1">

                    <div class="col-12">
                        <label class="form-label fw-semibold">Mot de passe actuel</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Confirmer le nouveau</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <div>
                        <button class="btn btn-hx text-white mt-2"><i class="fa fa-key me-2"></i>Changer le mot de passe</button>
                    </div>
                </form>
            </div>
        </section>




        <!-- ACTIVITE -->
        <section class="tab-pane fade" id="tab-activity">
            <div class="hx-card">
                <h5 class="fw-bold mb-4"><i class="fa fa-list me-2"></i>Activité récente</h5>
                <div class="timeline">
                    <div class="timeline-item"><small class="text-muted">Aujourd'hui · 10:32</small>
                        <p class="mb-1">Connexion réussie.</p>
                    </div>
                    <div class="timeline-item"><small class="text-muted">28 Avr · 18:10</small>
                        <p class="mb-1">Profil mis à jour (téléphone).</p>
                    </div>
                    <div class="timeline-item"><small class="text-muted">15 Avr · 09:02</small>
                        <p class="mb-1">Compte créé.</p>
                    </div>
                </div>
            </div>
        </section>

    </div><!-- /tab-content -->
    </div><!-- /main -->
    <section id="newsletter" class="section-p1 section-m1">
        <div class="newstext">
            <h4>Sign Up For Newsletters</h4>
            <p>
                Get E-mail updates about our latest shop and
                <span>special offers.</span>
            </p>
        </div>

        <div class="form">
            <input type="text" placeholder="Your email address" />
            <button class="normal">Sign Up</button>
        </div>
    </section>

    <footer class="section-p1">
        <div class="col">
            <img src="img/logo.png" alt="" />
            <h4>Contact</h4>
            <p><strong>Address: </strong> Morocco </p>
            <p><strong>Phone:</strong> 06 01 86 84 31</p>
            <p><strong>Hours:</strong> 10:00 - 18:00, Mon - Sat</p>
            <div class="follow">
                <h4>Follow us</h4>
                <div class="icon">
                    <i class="fab fa-facebook-f"></i>
                    <i class="fab fa-twitter"></i>
                    <i class="fab fa-instagram"></i>
                    <i class="fab fa-pinterest-p"></i>
                    <i class="fab fa-youtube"></i>
                </div>
            </div>
        </div>

        <div class="col">
            <h4>About</h4>
            <a href="#">About us</a>
            <a href="#">Delivery Information</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms & Conditions</a>
            <a href="#">Contact Us</a>
        </div>

        <div class="col">
            <h4>My Account</h4>
            <a href="#">Sign In</a>
            <a href="#">View Cart</a>
            <a href="#">My Wishlist</a>
            <a href="#">Track My Order</a>
            <a href="#">Help</a>
        </div>

        <div class="col install">
            <h4>Install App</h4>
            <p>From App Store or Google Play</p>
            <div class="row">
                <img src="img/pay/app.jpg" alt="" />
                <img src="img/pay/play.jpg" alt="" />
            </div>
            <p>Secured Payment Gateways</p>
            <img src="img/pay/pay.png" alt="" />
        </div>

        <div class="copyright">
            <p>© <?php echo date("Y"); ?>, Fashion Store - HTML CSS Javascript Project</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
    <script>
        function previewAvatar(e) {
            const f = e.target.files[0];
            if (f) document.getElementById('hxAvatar').src = URL.createObjectURL(f);
        }
        setTimeout(() => {
            const msg = document.querySelector('.alert-success');
            if (msg) msg.style.display = "none";
        }, 3000);
    </script>

</body>

</html>