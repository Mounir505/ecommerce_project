<?php
require 'config.php';
include 'header.php'; // navbar
$is_logged_in = isset($_SESSION['user_id']);


// Sécurité : valider l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "<h2>Produit non valide</h2>";
  exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
  echo "<h2>Produit introuvable</h2>";
  exit;
}

// Maintenant que $product est disponible, on construit les images
$main_image = $product['image_path'];  // ex: img/products/f1.jpg
$basename = pathinfo($main_image, PATHINFO_FILENAME); // f1
$img_dir = pathinfo($main_image, PATHINFO_DIRNAME);   // img/products

$images = [
  "$img_dir/{$basename}.jpg",
  "$img_dir/{$basename}2.jpg",
  "$img_dir/{$basename}3.jpg",
  "$img_dir/{$basename}4.jpg"
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cara Store</title>
  <link
    rel="stylesheet"
    href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="style.css" />
  <script>
    const IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
  </script>


</head>

<body>

  <section id="page-header">
    <h2>Welcome Hero !</h2>
    <p>Save more with coupons & up to 70% off!</p>
  </section>

  <section id="prodetails" class="section-p1">
    <div class="single-pro-image">
      <img src="<?= htmlspecialchars($product['image_path']) ?>" width="100%" id="MainImg" alt="<?= htmlspecialchars($product['name']) ?>" />
      <div class="small-img-group">
        <?php foreach ($images as $img): ?>
          <div class="small-img-col">
            <img src="<?= htmlspecialchars($img) ?>" width="100%" class="small-img" alt="">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="single-pro-details">
      <h6>Home / T-Shirt</h6>
      <h4><?= htmlspecialchars($product['name']) ?></h4>
      <h2><?= $product['price_mad'] ?> MAD</h2>

      <form id="add-to-cart-form">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

        <label for="size"><strong>Select Size:</strong></label>
        <select name="size" required>
          <option value="">Choose size</option>
          <option value="S">Small</option>
          <option value="M">Medium</option>
          <option value="L">Large</option>
          <option value="XL">X-Large</option>
        </select>

        <label for="quantity"><strong>Quantity:</strong></label>
        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock_qty'] ?>" required />

        <button type="submit" class="normal">🛒 Add To Cart</button>
        <br>
        <span id="cart-msg" class="cart-msg"></span>


      </form>



      <h4>Product Details</h4>
      <span><?= nl2br(htmlspecialchars($product['description'])) ?></span>
    </div>
  </section>



  <section id="product1" class="section-p1">
    <h2>Featured Products</h2>
    <p>Summer Collection New Modern Design</p>
    <div class="pro-container">
      <?php
      // récupérer 4 produits aléatoires ou les plus récents
      $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
      $featured = $stmt->fetchAll();

      foreach ($featured as $featuredProduct): ?>
        <div class="pro" onclick="window.location.href='sproduct.php?id=<?= $featuredProduct['id'] ?>'">
          <img src="<?= htmlspecialchars($featuredProduct['image_path']) ?>" alt="<?= htmlspecialchars($featuredProduct['name']) ?>" />
          <div class="des">
            <span>adidas</span>
            <h5><?= htmlspecialchars($featuredProduct['name']) ?></h5>
            <div class="star">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <h4><?= $featuredProduct['price_mad'] ?> MAD</h4>
          </div>
          <a href="#"><i class="fal fa-shopping-cart cart"></i></a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>


  <section id="newsletter" class="section-p1 section-m1">
    <div class="newstext">
      <h4>Sign Up For Newsletters</h4>
      <p>
        Get E-mail updates about our latest shop and
        <span id="cart-msg" class="cart-msg"></span>
        <span id="cart-auth-msg" class="cart-msg"></span>

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
  <script>
    var MainImg = document.getElementById("MainImg");
    var smallImg = document.getElementsByClassName("small-img");

    smallImg[0].onclick = function() {
      MainImg.src = smallImg[0].src;
    };

    smallImg[1].onclick = function() {
      MainImg.src = smallImg[1].src;
    };

    smallImg[2].onclick = function() {
      MainImg.src = smallImg[2].src;
    };

    smallImg[3].onclick = function() {
      MainImg.src = smallImg[3].src;
    };
  </script>
  <script src="script.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script>
    document.querySelector("form").addEventListener("submit", function(e) {
      e.preventDefault();

      const msg = document.getElementById("cart-msg");
      msg.className = "cart-msg";
      msg.innerHTML = "";
      msg.style.display = "none";

      if (typeof IS_LOGGED_IN === "undefined" || IS_LOGGED_IN === false) {
        msg.innerHTML = `
        <i class="fas fa-lock"></i>
        Veuillez <a href="login.php">vous connecter</a> pour ajouter ce produit au panier.`;
        msg.classList.add("show", "error");
        msg.style.display = "inline-block";

        setTimeout(() => {
          msg.classList.remove("show", "error");
          msg.innerHTML = "";
          msg.style.display = "none";
        }, 4000);
        return;
      }

      const formData = new FormData(this);

      fetch("add_to_cart_ajax.php", {
          method: "POST",
          body: formData,
        })
        .then((response) => response.text())
        .then((result) => {
          if (result.trim() === "OK") {
            msg.innerHTML = `
            <i class="fas fa-check-circle"></i> Produit ajouté au panier !`;
            msg.classList.add("show", "success");
            msg.style.display = "inline-block";

            updateCartCount?.();

            setTimeout(() => {
              msg.classList.remove("show", "success");
              msg.innerHTML = "";
              msg.style.display = "none";
            }, 3000);
          }
        })
        .catch((error) => {
          alert("Erreur réseau : " + error);
        });
    });
  </script>





  </script>




</body>

</html>