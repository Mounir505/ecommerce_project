<?php
session_start();
require 'config.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les produits du panier
$stmt = $pdo->prepare("SELECT cart.id AS cart_id, products.name, products.image_path, products.price_mad, products.stock_qty, cart.quantity

                       FROM cart
                       JOIN products ON cart.product_id = products.id
                       WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <script>
        // Sauvegarde la position juste avant de quitter la page
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('cartScroll', window.scrollY);
        });
        // Au chargement, si on a une position stockée, on y retourne
        window.addEventListener('DOMContentLoaded', () => {
            const y = sessionStorage.getItem('cartScroll');
            if (y !== null) {
                window.scrollTo(0, parseInt(y, 10));
                sessionStorage.removeItem('cartScroll');
            }
        });
    </script>

    <meta charset="UTF-8">
    <title>My Cart - Cara Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link
        rel="stylesheet"
        href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</head>


<body>

    <section id="page-header" class="about-header">
        <h2>#Your Cart</h2>
        <p>Here are your selected products</p>
    </section>

    <section id="cart" class="section-p1">
        <table width="100%">
            <thead>
                <tr>
                    <th>REMOVE</th>
                    <th>IMAGE</th>
                    <th>PRODUCT</th>
                    <th>PRICE</th>
                    <th>QUANTITY</th>
                    <th>SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item):
                    $subtotal = $item['price_mad'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td>
                            <form action="remove_cart_item.php" method="POST" onsubmit="return confirm('Supprimer ce produit du panier ?');">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <button type="submit" style="background: none; border: none; cursor: pointer;">
                                    <i class="fa-solid fa-trash" style="color: red;"></i>
                                </button>
                            </form>
                        </td>
                        <td><img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['price_mad'] ?> MAD</td>
                        <td>
                            <input
                                type="number"
                                class="qty-input"
                                value="<?= $item['quantity'] ?>"
                                data-cart-id="<?= $item['cart_id'] ?>"
                                data-price="<?= $item['price_mad'] ?>"
                                data-subtotal-id="subtotal-<?= $item['cart_id'] ?>"
                                min="1"
                                max="<?= $item['stock_qty'] ?>"
                                style="width:60px;text-align:center;">
                        </td>

                        <td id="subtotal-<?= $item['cart_id'] ?>"><?= number_format($subtotal, 2) ?> MAD</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section id="cart-add" class="section-p1">
        <div id="coupon">
            <h3>Apply Coupon</h3>
            <input type="text" placeholder="Enter Your Coupon Code">
            <button class="normal">Apply</button>
        </div>
        <div id="subtotal">
            <h3>Cart Total</h3>
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td class="cart-total"><?= number_format($total, 2) ?> MAD</td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td>Free</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong class="cart-total"><?= number_format($total, 2) ?> MAD</strong></td>
                </tr>
            </table>
            <button class="normal" onclick="window.location.href='payment.php?total=<?= $total ?>'">Proceed to Checkout</button>
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

    <?php include 'footer.php'; ?>
    <script src="script.js"></script>
    <script>
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('input', function() {
                const id = this.dataset.cartId;
                const qte = parseInt(this.value, 10);
                const pr = parseFloat(this.dataset.price);
                const sub = this.dataset.subtotalId;
                if (qte < 1) {
                    alert('Quantité invalide');
                    return;
                }

                fetch('update_cart_quantity.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `cart_id=${id}&quantity=${qte}`
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) throw data.message;
                        document.getElementById(sub).innerText =
                            (pr * data.quantity).toFixed(2) + ' MAD';
                        let t = 0;
                        document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
                            t += parseFloat(el.innerText.replace(' MAD', ''));
                        });
                        document.querySelectorAll('.cart-total').forEach(el => {
                            el.innerText = t.toFixed(2) + ' MAD';
                        });
                    })
                    .catch(e => alert('AJAX error: ' + e));
            });
        });
    </script>

</body>

</html>