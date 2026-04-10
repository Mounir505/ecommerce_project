<?php
session_start();
require 'config.php';
require_once 'auth_logger.php';


$error = '';
$info  = '';

/* --- Message après inscription réussie --- */
if (isset($_GET['registered'])) {
  $info = 'Inscription réussie. Vous pouvez maintenant vous connecter.';
}

/* ----------- TRAITEMENT DU FORMULAIRE ----------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
  $pass  = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :e");
  $stmt->execute([':e' => $email]);
  $user = $stmt->fetch();

  if ($user && password_verify($pass, $user['password_hash'])) {
    log_auth_event($pdo, $email, 'success'); // <== LOG ICI

    session_regenerate_id(true);          // sécurité
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['first_name'];

    header('Location: index.php');
    exit;
  }

  log_auth_event($pdo, $email, 'failure'); // <== LOG EN CAS D'ÉCHEC
  $error = 'Email ou mot de passe incorrect.';
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Bootstrap Login Page</title>

  <!-- Feuille de style globale de ton site (optionnelle) -->
  <link rel="stylesheet" href="style.css" />

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

  <!-- =======================
       CUSTOM UTIL CLASSES
  ======================== -->
  <style>
    .border-md {
      border-width: 2px;
    }

    .btn-facebook {
      background: #405d9d;
      border: none;
    }

    .btn-facebook:hover,
    .btn-facebook:focus {
      background: #314879;
    }

    .btn-twitter {
      background: #42aeec;
      border: none;
    }

    .btn-twitter:hover,
    .btn-twitter:focus {
      background: #1799e4;
    }

    body {
      min-height: 100vh;
    }

    .form-control:not(select) {
      padding: 1.5rem 0.5rem;
    }

    select.form-control {
      height: 52px;
      padding-left: 0.5rem;
    }

    .form-control::placeholder {
      color: #ccc;
      font-weight: bold;
      font-size: 0.9rem;
    }

    .form-control:focus {
      box-shadow: none;
    }
  </style>

  <!-- Bootstrap 4.3 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" />
  <!-- Font Awesome 4.7 pour les icônes -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
</head>

<body>

  <!-- ===== Navbar ===== -->


  <!-- ===== Main Content ===== -->
  <div class="container">
    <div class="row py-5 mt-4 align-items-center">
      <!-- Illustration / texte -->
      <div class="col-md-5 pr-lg-5 mb-5 mb-md-0">
        <img src="img/auth/auth2.svg" alt="" class="img-fluid mb-3 d-none d-md-block" />
        <h1>Welcome Back!</h1>
        <p class="font-italic text-muted mb-0">
          Sign in to access your account and continue shopping.
        </p>
        <p class="font-italic text-muted">
          Snippet Adapted From
          <a href="https://bootstrapious.com" class="text-muted"><u>Bootstrapious</u></a>
        </p>
      </div>

      <!-- ===== Login Form ===== -->
      <div class="col-md-7 col-lg-6 ml-auto">
        <form action="login.php" method="post">

          <?php if (!empty($error)) : ?>
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
              <input id="loginEmail" type="email" name="email"
                placeholder="Email Address"
                class="form-control bg-white border-left-0 border-md" required>
            </div>

            <!-- Password -->
            <div class="input-group col-lg-12 mb-4">
              <div class="input-group-prepend">
                <span class="input-group-text bg-white px-4 border-md border-right-0">
                  <i class="fa fa-lock text-muted"></i>
                </span>
              </div>
              <input id="loginPassword" type="password" name="password"
                placeholder="Password"
                class="form-control bg-white border-left-0 border-md" required>
            </div>

            <!-- Submit Button -->
            <div class="form-group col-lg-12 mx-auto mb-0">
              <button type="submit" class="btn btn-primary btn-block py-2">
                <span class="font-weight-bold">Log in to your account</span>
              </button>
            </div>

            <!-- Divider -->
            <div class="form-group col-lg-12 mx-auto d-flex align-items-center my-4">
              <div class="border-bottom w-100 ml-5"></div>
              <span class="px-2 small text-muted font-weight-bold">OR</span>
              <div class="border-bottom w-100 mr-5"></div>
            </div>

            <!-- Social login -->
            <div class="form-group col-lg-12 mx-auto">
              <a href="#" class="btn btn-primary btn-block py-2 btn-facebook">
                <i class="fa fa-facebook-f mr-2"></i>
                <span class="font-weight-bold">Continue with Facebook</span>
              </a>
              <a href="#" class="btn btn-primary btn-block py-2 btn-twitter">
                <i class="fa fa-twitter mr-2"></i>
                <span class="font-weight-bold">Continue with Twitter</span>
              </a>
            </div>

            <!-- Register link -->
            <div class="text-center w-100">
              <p class="text-muted mt-5 mb-0">
                Don't have an account?
                <a href="register.php" class="fw-bold text-body"><u>Register here</u></a>
              </p>
            </div>

          </div><!-- /row -->
        </form>
      </div><!-- /form column -->
    </div><!-- /row -->
  </div><!-- /container -->


  <!-- ======================= SCRIPTS ======================== -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

  <script>
    // Highlight icon border on focus
    $(function() {
      $('input, select').on('focus', function() {
        $(this).parent().find('.input-group-text').css('border-color', '#80bdff');
      });
      $('input, select').on('blur', function() {
        $(this).parent().find('.input-group-text').css('border-color', '#ced4da');
      });
    });
  </script>
  <script src="script.js"></script>
</body>
<style>
  .btn-primary {
    color: #fff;
    background-color: #088178;
    border-color: #275f01;
  }
</style>

</html>