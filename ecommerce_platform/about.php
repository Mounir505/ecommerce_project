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


  <section id="page-header" class="about-header">
    <h2>Meet the Awesome Team</h2>
    <p>Styled for You, Loved by All.</p>
  </section>

  <section id="about-head" class="section-p1">
    <img src="img/about/a6.jpg" alt="">
    <div>
      <h2>Who We Are!</h2>
      <p>
        At Fashion, we believe that fashion is more than just clothing — it’s a form of self-expression. Our mission is to bring you carefully curated collections that blend timeless elegance with modern trends. From sharp menswear to soft, sophisticated womenswear, we craft each piece with quality, comfort, and style in mind. Whether you're dressing for everyday moments or special occasions, we're here to help you look and feel your best.
      </p>
      <abbr title="">
        Timeless fashion, modern edge — curated for your everyday confidence.
      </abbr>
      <br>
      <br>
      <marquee bgcolor="#ccc" loop="-1" scrollamount="5" width="100%">
        ✨ New arrivals just dropped — shop the latest trends now! ✨
      </marquee>
    </div>

  </section>

  <section id="about-app" class="section-p1">
    <h1>Download Our <a href="#">App</a></h1>
    <div class="video">
      <video autoplay muted loop src="img/about/1.mp4"></video>

    </div>
  </section>

  <section id="feature" class="section-p1">
    <div class="fe-box">
      <img src="img/features/f1.png" alt="" />
      <h6>Free Shipping</h6>
    </div>

    <div class="fe-box">
      <img src="img/features/f2.png" alt="" />
      <h6>Online Order</h6>
    </div>

    <div class="fe-box">
      <img src="img/features/f3.png" alt="" />
      <h6>Save Money</h6>
    </div>

    <div class="fe-box">
      <img src="img/features/f4.png" alt="" />
      <h6>Promotions</h6>
    </div>

    <div class="fe-box">
      <img src="img/features/f5.png" alt="" />
      <h6>Happy Sell</h6>
    </div>

    <div class="fe-box">
      <img src="img/features/f6.png" alt="" />
      <h6>F24/7 Support</h6>
    </div>
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