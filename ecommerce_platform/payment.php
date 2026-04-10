<?php
session_start();
require 'config.php';
include 'header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart total for display
$stmt = $pdo->prepare("SELECT SUM(products.price_mad * cart.quantity) as total
                       FROM cart
                       JOIN products ON cart.product_id = products.id
                       WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch();
$cart_total = $result['total'] ?? 0;

// Get currency settings
$currency = 'MAD'; // Default currency
$exchange_rate = 0.1; // Default exchange rate (MAD to USD)

// You could load these from database or configuration file
// $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'currency'");
// $stmt->execute();
// $currency_result = $stmt->fetch();
// $currency = $currency_result['value'] ?? 'MAD';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Cara Store</title>
    <link rel="stylesheet" href="style.css">
    <!-- PayPal SDK with your actual client ID -->
    <script src="https://www.paypal.com/sdk/js?client-id=ATtWugWSKRCJdzAePSOj8RvUnVof1k35JyV08RzrZStUK9-xtzeMqfvJ4yyG2DnHe0FvyTpAaV_T-rbv&currency=USD"></script>
    <!-- Stripe SDK -->
    <script src="https://js.stripe.com/v3/"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <section id="page-header" class="payment-header">
        <h2>Checkout</h2>
        <p>Complete your purchase securely</p>
    </section>

    <section id="payment" class="section-p1">
        <div class="payment-container">
            <div class="order-summary">
                <h3>Order Summary</h3>
                <div id="order-items">
                    <!-- Items will be loaded dynamically -->
                </div>
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span id="order-subtotal"><?= $cart_total ?> <?= $currency ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping</span>
                        <span id="order-shipping">Free</span>
                    </div>
                    <div class="total-row discount-row" style="display: none;">
                        <span>Discount</span>
                        <span id="order-discount">0 <?= $currency ?></span>
                    </div>
                    <div class="total-row final-total">
                        <span>Total</span>
                        <span id="order-total"><?= $cart_total ?> <?= $currency ?></span>
                    </div>
                </div>
            </div>

            <div class="shipping-details">
                <h3>Shipping Information</h3>
                <p>Enter your shipping details to complete your purchase.</p>
                <form id="shipping-form">
                    <div class="form-group">
                        <label for="full-name">Full Name</label>
                        <input type="text" id="full-name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Street Address</label>
                        <input type="text" id="address" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" required>
                        </div>
                        <div class="form-group">
                            <label for="zip">ZIP/Postal Code</label>
                            <input type="text" id="zip" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" required>
                            <option value="">Select Country</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="UK">United Kingdom</option>
                            <option value="AU">Australia</option>
                            <option value="FR">France</option>
                            <option value="DE">Germany</option>
                            <option value="JP">Japan</option>
                            <option value="MA">Morocco</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" required>
                    </div>
                </form>
            </div>

            <div class="payment-methods">
                <h3>Payment Method</h3>
                <p>Choose your preferred payment method to complete your purchase.</p>
                <div class="payment-tabs">
                    <button class="payment-tab active" data-method="paypal">PayPal</button>
                    <button class="payment-tab" data-method="card">Credit Card</button>
                </div>

                <div id="paypal-container" class="payment-method-container active">
                    <p>Click the PayPal button below to complete your purchase securely.</p>
                    <div id="paypal-button-container"></div>
                    <button type="submit" class="normal payment-button">Pay Now (<?= $cart_total ?> <?= $currency ?>)</button>
                    <div class="currency-note">
                        <small>* Payment will be processed in USD at current exchange rates</small>
                    </div>
                </div>

                <div id="card-container" class="payment-method-container">
                    <form id="card-form">
                        <div class="form-group">
                            <label for="card-number">Card Number</label>
                            <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card-expiry">Expiry (MM/YY)</label>
                                <input type="text" id="card-expiry" placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="form-group">
                                <label for="card-cvc">CVC</label>
                                <input type="text" id="card-cvc" placeholder="123" maxlength="3" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="card-name">Name on Card</label>
                            <input type="text" id="card-name" required>
                        </div>
                        <button type="submit" class="normal payment-button">Pay Now (<?= $cart_total ?> <?= $currency ?>)</button>
                    </form>
                </div>
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

    <!-- Add variables for JavaScript -->
    <script>
        // Pass PHP variables to JavaScript
        window.orderTotal = <?= json_encode($cart_total) ?>;
        window.currency = <?= json_encode($currency) ?>;
    </script>
    <script src="script.js"></script>
    <script src="payment.js"></script>
</body>

</html>