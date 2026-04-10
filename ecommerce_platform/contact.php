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
    <title>brandname</title>
    <link
        rel="stylesheet"
        href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="style.css">
</head>

<body>



    <section id="page-header" class="about-header">
        <h2>#Let's talk</h2>
        <p>Send us a message, We love to hear from you</p>

    </section>

    <section id="contact-details" class="section-p1">
        <div class="details">
            <span>GET IN TOUCH</span>
            <h2>Visit our store</h2>
            <h3>Head Office</h3>
            <div>
                <li>
                    <i class="fa-solid fa-location-dot"></i>
                    <p>
                        ENSA Marrakech
                    </p>
                </li>
                <li>
                    <i class="fa-solid fa-phone"></i>
                    <p>
                        06 12 34 56 78
                    </p>
                </li>
                <li>
                    <i class="fa-solid fa-envelope-open"></i>
                    <p>
                        contact@ensa.ma
                    </p>
                </li>
            </div>
        </div>

        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3396.524299761218!2d-8.02294042522396!3d31.646872640882673!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xdafee9e5e2bd20f%3A0x2cf9300f935096fa!2sNational%20School%20of%20Applied%20Sciences!5e0!3m2!1sen!2sma!4v1745948285289!5m2!1sen!2sma" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></iframe>
        </div>
    </section>
    <section id="form-details">
        <form action="">
            <span>Leave a Message</span>
            <h2>We love to hear from you</h2>
            <input type="text" placeholder="Your Name">
            <input type="text" placeholder="Your Email">
            <input type="text" placeholder="Subject">
            <textarea name="" id="" cols="30" rows="10" placeholder="Your Message"></textarea>
            <button type="submit">Send</button>
        </form>

        <div class="people">
            <div>
                <img src="img/people/mounir.jpeg" alt="">
                <p><span>MERGHICH MOUNIR</span> Cyber Security Engineer <br> 06 01 86 84 31 <br> m.merghiche3719@uca.ac.ma<br> </p>
            </div>
        </div>

    </section>
    <section id="newsletter" class="section-p1 section-m1">

        <div class="newstext">
            <h4>Sign Up For Newsletters</h4>
            <p>Get E-mail updates about our latest shop and <span>special offers.</span></p>
        </div>

        <div class="form">
            <input type="text" placeholder="Your email address">
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