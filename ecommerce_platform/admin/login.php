<?php
session_start();
require '../config.php';   // le même fichier de connexion PDO
require_once '../auth_logger.php'; // chemin relatif depuis admin/


// Si un admin est déjà connecté, on l’envoie directement sur le dashboard
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$info  = '';

// --- Traitement du formulaire ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $pass  = $_POST['password'] ?? '';

    // On cherche l’utilisateur concerné
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :e AND is_admin = 1");
    $stmt->execute([':e' => $email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin['password_hash'])) {

        // ➕ Journalisation succès
        log_auth_event($pdo, $email, 'success', true);

        // OK : initialisation de session
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['name']     = $admin['first_name'];
        $_SESSION['is_admin'] = 1;

        header('Location: dashboard.php');
        exit;
    }

    // ➕ Journalisation échec
    log_auth_event($pdo, $email, 'failure', true);
    $error = 'Email ou mot de passe incorrect, ou vous n’êtes pas administrateur.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Login</title>

    <!-- Style général de ton site -->
    <link rel="stylesheet" href="../style.css">

    <!-- Bootstrap 4.3 + FontAwesome (identiques à ta page login) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        /* on reprend tes utilitaires internes */
        .border-md {
            border-width: 2px;
        }

        body {
            min-height: 100vh;
        }

        .btn-primary {
            background: #088178;
            border-color: #275f01;
        }

        /* autres petits styles copiés de ta page login… */
    </style>
</head>

<body>

    <div class="container">
        <div class="row py-5 mt-4 align-items-center">

            <!-- Illustration / texte -->
            <div class="col-md-5 pr-lg-5 mb-5 mb-md-0">
                <img src="../img/auth/auth2.svg" alt="" class="img-fluid mb-3 d-none d-md-block" />
                <h1>Admin Area</h1>
                <p class="font-italic text-muted mb-0">
                    Please sign in with an administrator account.
                </p>
            </div>

            <!-- Formulaire -->
            <div class="col-md-7 col-lg-6 ml-auto">
                <form action="login.php" method="post">

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Email -->
                        <div class="input-group col-lg-12 mb-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white px-4 border-md border-right-0">
                                    <i class="fa fa-envelope text-muted"></i>
                                </span>
                            </div>
                            <input type="email" name="email" placeholder="Admin Email"
                                class="form-control bg-white border-left-0 border-md" required>
                        </div>

                        <!-- Password -->
                        <div class="input-group col-lg-12 mb-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white px-4 border-md border-right-0">
                                    <i class="fa fa-lock text-muted"></i>
                                </span>
                            </div>
                            <input type="password" name="password" placeholder="Password"
                                class="form-control bg-white border-left-0 border-md" required>
                        </div>

                        <!-- Submit -->
                        <div class="form-group col-lg-12 mx-auto mb-0">
                            <button type="submit" class="btn btn-primary btn-block py-2">
                                <span class="font-weight-bold">Log in as Admin</span>
                            </button>
                        </div>
                    </div><!-- /row -->

                </form>
            </div><!-- /col -->
        </div><!-- /row -->
    </div><!-- /container -->

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>

</html>