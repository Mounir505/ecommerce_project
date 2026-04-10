<?php
require 'config.php';
include 'header.php';

try {
  // Check if search query exists
  $search = isset($_GET['search']) ? $_GET['search'] : '';
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $itemsPerPage = 8;

  if (!empty($search)) {
    // Search query with LIKE for partial matches
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :search LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($page - 1) * $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
  } else {
    // Get paginated products if no search
    $stmt = $pdo->prepare("SELECT * FROM products LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', ($page - 1) * $itemsPerPage, PDO::PARAM_INT);
    $stmt->execute();
  }
  $products = $stmt->fetchAll();

  // Get total count for pagination
  $countStmt = $pdo->query("SELECT COUNT(*) FROM products");
  $totalItems = $countStmt->fetchColumn();
  $totalPages = ceil($totalItems / $itemsPerPage);
} catch (PDOException $e) {
  die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fashion Store</title>
  <link
    rel="stylesheet"
    href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

  <link rel="stylesheet" href="style.css" />
</head>

<body>


  <section id="page-header">
    <h2>Welcome Hero !</h2>
    <p>Save more with coupons & up to 70% off!</p>
  </section>

  <!-- Updated search form with Font Awesome -->
  <section id="search-section" class="section-p1">
    <form id="searchForm" class="search-form">
      <div class="search-input-wrapper">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i> Search
        </button>
      </div>
    </form>
  </section>

  <section id="product1" class="section-p1">
    <div class="pro-container<?= ($page === $totalPages) ? ' last-page' : '' ?>" id="productContainer">
      <?php foreach ($products as $product): ?>
        <div class="pro" onclick="window.location.href='sproduct.php?id=<?= $product['id'] ?>'">
          <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" />
          <div class="des">
            <span>adidas</span>
            <h5><?= htmlspecialchars($product['name']) ?></h5>
            <div class="star">
              <i class="fas fa-star"></i><i class="fas fa-star"></i>
              <i class="fas fa-star"></i><i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <h4><?= $product['price_mad'] ?> MAD</h4>
          </div>
          <a href="#"><i class="fal fa-shopping-cart cart"></i></a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Add loading spinner -->
  <div id="loadingSpinner" class="loading-spinner" style="display: none;">
    <i class="fas fa-spinner fa-spin"></i>
  </div>

  <!-- Add no results message -->
  <div id="noResults" class="no-results" style="display: none;">
    <i class="fas fa-search"></i>
    <p>No products found</p>
  </div>

  <!-- Updated pagination section -->
  <section id="pagination" class="section-p1">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $page - 1 ?></a>
    <?php endif; ?>
    <a href="?page=<?= $page ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="active"><?= $page ?></a>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $page + 1 ?></a>
      <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><i class="fal fa-long-arrow-alt-right"></i></a>
    <?php endif; ?>
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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const searchForm = document.getElementById('searchForm');
      const searchInput = document.getElementById('searchInput');
      const productContainer = document.getElementById('productContainer');
      const loadingSpinner = document.getElementById('loadingSpinner');
      const noResults = document.getElementById('noResults');
      const paginationContainer = document.getElementById('paginationContainer');

      // Function to show error message
      function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `
          <i class="fas fa-exclamation-circle"></i>
          <p>${message}</p>
        `;
        productContainer.parentNode.insertBefore(errorDiv, productContainer);
        setTimeout(() => errorDiv.remove(), 5000);
      }

      // Function to update pagination
      function updatePagination(pagination) {
        let paginationHTML = '';

        if (pagination.totalPages > 1) {
          if (pagination.currentPage > 1) {
            paginationHTML += `
              <a href="?page=${pagination.currentPage - 1}${searchInput.value ? '&search=' + encodeURIComponent(searchInput.value) : ''}" class="pagination-btn">
                <i class="fas fa-chevron-left"></i>
              </a>
            `;
          }

          for (let i = 1; i <= pagination.totalPages; i++) {
            paginationHTML += `
              <a href="?page=${i}${searchInput.value ? '&search=' + encodeURIComponent(searchInput.value) : ''}" 
                 class="pagination-btn ${i === pagination.currentPage ? 'active' : ''}">
                ${i}
              </a>
            `;
          }

          if (pagination.currentPage < pagination.totalPages) {
            paginationHTML += `
              <a href="?page=${pagination.currentPage + 1}${searchInput.value ? '&search=' + encodeURIComponent(searchInput.value) : ''}" class="pagination-btn">
                <i class="fas fa-chevron-right"></i>
              </a>
            `;
          }
        }

        paginationContainer.innerHTML = paginationHTML;
      }

      searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch(1); // Reset to first page on new search
      });

      searchInput.addEventListener('input', function() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
          performSearch(1); // Reset to first page on new search
        }, 500);
      });

      function performSearch(page = 1) {
        const searchTerm = searchInput.value.trim();

        // Show loading spinner
        loadingSpinner.style.display = 'block';
        noResults.style.display = 'none';

        // Create AJAX request
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `search_products.php?search=${encodeURIComponent(searchTerm)}&page=${page}`, true);

        xhr.onload = function() {
          if (xhr.status === 200) {
            try {
              const response = JSON.parse(xhr.responseText);

              if (response.status === 'error') {
                showError(response.message);
                return;
              }

              // Clear current products
              productContainer.innerHTML = '';

              if (response.data.length === 0) {
                noResults.style.display = 'block';
              } else {
                // Add new products
                response.data.forEach(product => {
                  const productHTML = `
                    <div class="pro" onclick="window.location.href='sproduct.php?id=${product.id}'">
                      <img src="${product.image_path}" alt="${product.name}" />
                      <div class="des">
                        <span>adidas</span>
                        <h5>${product.name}</h5>
                        <div class="star">
                          <i class="fas fa-star"></i><i class="fas fa-star"></i>
                          <i class="fas fa-star"></i><i class="fas fa-star"></i>
                          <i class="fas fa-star"></i>
                        </div>
                        <h4>${product.price_mad} MAD</h4>
                      </div>
                      <a href="#"><i class="fal fa-shopping-cart cart"></i></a>
                    </div>
                  `;
                  productContainer.innerHTML += productHTML;
                });
              }
            } catch (e) {
              console.error('Error parsing JSON:', e);
              showError('Error processing the response');
            }
          } else {
            showError('Server error occurred');
          }

          // Hide loading spinner
          loadingSpinner.style.display = 'none';
        };

        xhr.onerror = function() {
          console.error('Request failed');
          loadingSpinner.style.display = 'none';
          showError('Network error occurred');
        };

        xhr.send();
      }
    });
  </script>
  <script src="script.js"></script>

  <style>
    .error-message {
      background-color: #fff3cd;
      color: #856404;
      padding: 15px;
      margin: 10px 0;
      border-radius: 4px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: fadeIn 0.3s ease-in;
    }

    .error-message i {
      font-size: 20px;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Pagination styles */
    .pagination-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 60px;
      height: 60px;
      margin: 0 10px;
      border-radius: 10px;
      background-color: #10807a;
      color: #fff;
      text-decoration: none;
      font-weight: 400;
      font-size: 2rem;
      transition: all 0.2s ease;
      border: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .pagination-btn:hover,
    .pagination-btn.active {
      background-color: #0b5e58;
      color: #fff;
    }

    .pagination-btn i {
      font-size: 2rem;
    }

    #paginationContainer {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 20px;
    }

    .pro-container.last-page {
      justify-content: flex-start !important;
      gap: 30px;
      /* or whatever space you want between products */
    }
  </style>
</body>

</html>