<?php
session_start();
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tech2etc Ecommerce Tutorial</title>
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

  <link rel="stylesheet" href="style.css">
</head>

<body>


  <section id="page-header" class="blog-header">
    <h2>Inside the Wardrobe</h2>
    <p>Ready for more? Explore the latest trends and tips!</p>
  </section>

  <section id="blog">
    <div class="blog-box">
      <div class="blog-img">
        <img src="img/blog/b1.jpg" alt="">
      </div>
      <div class="blog-details">
        <h4>How to wear a suit</h4>
        <p>Step into confidence with a tailored suit that speaks power and elegance.</p>
        <a href="#">Read More</a>
      </div>
      <h1>
        01
      </h1>
    </div>
    <div class="blog-box">
      <div class="blog-img">
        <img src="img/blog/b2.jpg" alt="">
      </div>
      <div class="blog-details">
        <h4>Effortless Elegance in Every Stitch</h4>
        <p>Minimalist cuts and soft tones define this season's timeless silhouettes.</p>
        <a href="#">Read More</a>
      </div>
      <h1>
        02
      </h1>
    </div>
    <div class="blog-box">
      <div class="blog-img">
        <img src="img/blog/b3.jpg" alt="">
      </div>
      <div class="blog-details">
        <h4>Inside Our Boutique: Where Style Begins</h4>
        <p>Take a peek into our curated space that brings fashion dreams to life.</p>
        <a href="#">Read More</a>
      </div>
      <h1>
        03
      </h1>
    </div>
    <div class="blog-box">
      <div class="blog-img">
        <img src="img/blog/b4.jpg" alt="">
      </div>
      <div class="blog-details">
        <h4>Casual Chic Meets Cozy Layers</h4>
        <p>Pair comfort with style in soft knits and relaxed fits for cooler days.</p>
        <a href="#">Read More</a>
      </div>
      <h1>
        04
      </h1>
    </div>

  </section>

  <section id="pagination" class="section-p1">
    <?php
    // Calculate the range of pages to show
    if ($totalPages <= 3) {
      $start = 1;
      $end = $totalPages;
    } else if ($page == 1) {
      $start = 1;
      $end = 3;
    } else if ($page == $totalPages) {
      $start = max(1, $totalPages - 2);
      $end = $totalPages;
    } else {
      $start = $page - 1;
      $end = $page + 1;
    }

    // Previous arrow
    if ($page > 1) {
      echo '<a href="?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . '"><i class="fal fa-long-arrow-alt-left"></i></a>';
    }

    // Page numbers
    for ($i = $start; $i <= $end; $i++) {
      $active = $i === $page ? 'class="active"' : '';
      echo '<a href="?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . '" ' . $active . '>' . $i . '</a>';
    }

    // Next arrow
    if ($page < $totalPages) {
      echo '<a href="?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . '"><i class="fal fa-long-arrow-alt-right"></i></a>';
    }
    ?>
  </section>

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

  <script src="script.js"></script>

</body>

</html>