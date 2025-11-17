<?php
session_start();
include 'db.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!isset($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$restaurant_id = $_GET['id'];

if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = "menu.php?id=$restaurant_id";
        header("Location: login.php");
        exit();
    }
    
    $menu_id = $_POST['menu_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    $check = $conn->query("SELECT * FROM cart WHERE user_id=$user_id AND menu_id=$menu_id");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE cart SET quantity = quantity + $quantity WHERE user_id=$user_id AND menu_id=$menu_id");
    } else {
        $conn->query("INSERT INTO cart (user_id, menu_id, quantity) VALUES ($user_id, $menu_id, $quantity)");
    }

    header("Location: menu.php?id=$restaurant_id&added=1");
    exit();
}

// Ambil maklumat restoran
$rest = $conn->query("SELECT restaurant_name, image FROM restaurants WHERE restaurant_id = $restaurant_id")->fetch_assoc();

// Ambil semua menu
$sql = "SELECT * FROM menu_items WHERE restaurant_id = $restaurant_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu - <?php echo htmlspecialchars($rest['restaurant_name']); ?> | QuickBite</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Navbar */
    header {
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      padding: 15px 0;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 100%;
      margin: auto;
      padding: 0 40px;
    }

    .logo {
      display: flex;
      align-items: center;
      font-size: 24px;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.2s ease;
      text-decoration: none;
      color: white;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    .logo img {
      width: 40px;
      height: 40px;
      margin-right: 12px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }

    .logo:hover img {
      border-color: rgba(255, 255, 255, 0.6);
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 8px;
      margin: 0;
      padding: 0;
      align-items: center;
    }

    nav ul li {
      position: relative;
    }

    nav ul li a {
      text-decoration: none;
      color: white;
      font-weight: 500;
      padding: 10px 18px;
      border-radius: 25px;
      transition: all 0.3s ease;
      display: inline-block;
      font-size: 15px;
      position: relative;
      overflow: hidden;
    }

    nav ul li a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transition: left 0.3s ease;
      z-index: -1;
    }

    nav ul li a:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    nav ul li a:hover::before {
      left: 0;
    }

    nav ul li:last-child a:hover {
      background: rgba(255, 255, 255, 0.15);
    }

    /* Mobile Menu Toggle */
    .menu-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 5px;
      padding: 5px;
      z-index: 1001;
    }

    .menu-toggle span {
      width: 25px;
      height: 3px;
      background: white;
      border-radius: 3px;
      transition: all 0.3s ease;
    }

    .menu-toggle.active span:nth-child(1) {
      transform: rotate(45deg) translate(8px, 8px);
    }

    .menu-toggle.active span:nth-child(2) {
      opacity: 0;
    }

    .menu-toggle.active span:nth-child(3) {
      transform: rotate(-45deg) translate(7px, -7px);
    }

    /* Page Layout */
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      color: #333;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      position: relative;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.85);
      z-index: -1;
    }

    .container {
      max-width: 100%;
      margin: 10px 0;
      padding: 15px 20px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 0;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      flex: 1;
    }

    .restaurant-header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      margin-bottom: 20px;
      padding: 15px 0;
      border-bottom: 2px solid #f0f0f0;
    }

    .restaurant-logo {
      width: 140px;
      height: 140px;
      border-radius: 20px;
      object-fit: contain;
      background-color: #f8f8f8;
      padding: 12px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease;
      flex-shrink: 0;
    }

    .restaurant-logo:hover {
      transform: scale(1.05);
    }

    .restaurant-header-content {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .restaurant-header h1 {
      font-size: 3em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0 0 5px 0;
      line-height: 1.2;
    }

    .restaurant-header p {
      font-size: 0.95em;
      color: #666;
      margin: 0;
    }

    .back-link {
      display: inline-block;
      margin-bottom: 10px;
      color: #ff4d4d;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      padding: 6px 14px;
      border-radius: 20px;
      background: rgba(255, 77, 77, 0.1);
      font-size: 14px;
    }

    .back-link:hover {
      background: rgba(255, 77, 77, 0.2);
      transform: translateX(-5px);
    }

    .back-link::before {
      content: '← ';
      margin-right: 5px;
    }

    .menu-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      margin-top: 15px;
    }

    .menu-item {
      border: none;
      border-radius: 20px;
      padding: 0;
      width: 100%;
      max-width: 320px;
      background: white;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }

    .menu-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(255, 77, 77, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%);
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: 1;
    }

    .menu-item:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 15px 40px rgba(255, 77, 77, 0.3);
    }

    .menu-item:hover::before {
      opacity: 1;
    }

    .menu-item img {
      width: 100%;
      height: 220px;
      object-fit: contain;
      object-position: center;
      background-color: #f8f8f8;
      padding: 10px;
      transition: transform 0.4s ease;
    }

    .menu-item:hover img {
      transform: scale(1.05);
    }

    .menu-item-content {
      padding: 25px;
      position: relative;
      z-index: 2;
      background: white;
    }

    .menu-item h3 {
      margin: 0 0 10px 0;
      font-size: 1.5em;
      font-weight: 600;
      color: #333;
    }

    .menu-item .price {
      font-size: 1.8em;
      font-weight: 700;
      color: #ff4d4d;
      margin: 10px 0;
      display: block;
    }

    .menu-item .description {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
      margin-bottom: 20px;
      min-height: 50px;
    }

    .menu-item form {
      display: flex;
      gap: 10px;
      align-items: center;
      margin-top: 15px;
    }

    .quantity-input {
      width: 70px;
      padding: 10px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      text-align: center;
      transition: all 0.3s ease;
    }

    .quantity-input:focus {
      outline: none;
      border-color: #ff4d4d;
      box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.1);
    }

    .add-to-cart-btn {
      flex: 1;
      padding: 12px 20px;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
      position: relative;
      overflow: hidden;
    }

    .add-to-cart-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.2);
      transition: left 0.3s ease;
    }

    .add-to-cart-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 77, 77, 0.4);
    }

    .add-to-cart-btn:hover::before {
      left: 100%;
    }

    .add-to-cart-btn:active {
      transform: translateY(0);
    }

    .no-menu {
      text-align: center;
      padding: 60px 20px;
      color: #999;
      font-size: 1.2em;
    }

    .no-menu::before {
      content: '🍽️';
      display: block;
      font-size: 4em;
      margin-bottom: 20px;
    }

    /* Toast Notification */
    .toast {
      position: fixed;
      top: 90px;
      right: 20px;
      background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
      color: white;
      padding: 18px 25px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
      z-index: 10000;
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 500;
      font-size: 16px;
      opacity: 0;
      transform: translateX(400px);
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      max-width: 350px;
    }

    .toast.show {
      opacity: 1;
      transform: translateX(0);
    }

    .toast-icon {
      font-size: 24px;
      flex-shrink: 0;
    }

    .toast-message {
      flex: 1;
    }

    .toast-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      line-height: 1;
      transition: background 0.2s ease;
      flex-shrink: 0;
    }

    .toast-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    /* Footer */
    footer {
      text-align: center;
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
      padding: 25px 0;
      margin-top: auto;
      box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
    }

    footer p {
      margin: 0;
      font-size: 14px;
      opacity: 0.9;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      nav {
        padding: 0 20px;
      }

      .menu-toggle {
        display: flex;
      }

      nav ul {
        position: fixed;
        top: 70px;
        left: -100%;
        flex-direction: column;
        background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
        width: 100%;
        padding: 20px 0;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        transition: left 0.3s ease;
        gap: 0;
      }

      nav ul.active {
        left: 0;
      }

      nav ul li {
        width: 100%;
        text-align: center;
      }

      nav ul li a {
        display: block;
        padding: 15px 20px;
        border-radius: 0;
        width: 100%;
      }

      .logo {
        font-size: 20px;
      }

      .logo img {
        width: 35px;
        height: 35px;
      }

      .container {
        margin: 10px 0;
        padding: 15px 15px;
      }

      .restaurant-header {
        flex-direction: column;
        gap: 15px;
        margin-bottom: 15px;
        padding: 10px 0;
        text-align: center;
      }

      .restaurant-header-content {
        align-items: center;
      }

      .restaurant-header h1 {
        font-size: 2em;
        text-align: center;
      }

      .restaurant-logo {
        width: 120px;
        height: 120px;
      }

      .menu-grid {
        gap: 20px;
      }

      .toast {
        right: 10px;
        left: 10px;
        max-width: none;
        top: 80px;
        padding: 15px 20px;
        font-size: 14px;
      }

      .menu-item {
        max-width: 100%;
      }

      .menu-item form {
        flex-direction: column;
      }

      .quantity-input {
        width: 100%;
      }

      .add-to-cart-btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <header>
    <nav>
      <a href="home.php" class="logo">
        <img src="assets/images/default-restaurant.jpg" alt="QuickBite Logo">
        QuickBite
      </a>
      <div class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <ul id="navMenu">
        <li><a href="home.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li><a href="orderstatus.php">My Orders</a></li>
          <li><a href="cart.php">Cart</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Login</a></li>
          <li><a href="signup.php">Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <script>
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');

    menuToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
      menuToggle.classList.toggle('active');
    });

    // Close menu when clicking on a link
    document.querySelectorAll('#navMenu a').forEach(link => {
      link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        menuToggle.classList.remove('active');
      });
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
        navMenu.classList.remove('active');
        menuToggle.classList.remove('active');
      }
    });

    // Show toast notification if item was added to cart
    <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
    function showToast() {
      const toast = document.getElementById('cartToast');
      if (toast) {
        toast.classList.add('show');
        
        // Auto hide after 4 seconds
        setTimeout(() => {
          hideToast();
        }, 4000);
      }
    }

    function hideToast() {
      const toast = document.getElementById('cartToast');
      if (toast) {
        toast.classList.remove('show');
        // Remove from URL without reload
        if (window.history.replaceState) {
          const url = new URL(window.location);
          url.searchParams.delete('added');
          window.history.replaceState({}, '', url);
        }
      }
    }

    // Show toast when page loads
    window.addEventListener('load', () => {
      setTimeout(showToast, 100);
    });

    // Close button handler
    document.addEventListener('DOMContentLoaded', () => {
      const closeBtn = document.querySelector('.toast-close');
      if (closeBtn) {
        closeBtn.addEventListener('click', hideToast);
      }
    });
    <?php endif; ?>
  </script>

  <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
  <div id="cartToast" class="toast">
    <span class="toast-icon">✓</span>
    <span class="toast-message">Item added to cart successfully!</span>
    <button class="toast-close" aria-label="Close">×</button>
  </div>
  <?php endif; ?>

  <div class="container">
    <a href="home.php" class="back-link">Back to Restaurants</a>
    
    <div class="restaurant-header">
      <?php
      // Determine which logo to use
      $restaurant_name = htmlspecialchars($rest['restaurant_name']);
      $logo_path = '';
      
      // Check if it's Burger Town
      if (stripos($restaurant_name, 'burger town') !== false || stripos($restaurant_name, 'burgertown') !== false) {
          $logo_path = 'assets/images/burger-town.jpg';
      } elseif (!empty($rest['image'])) {
          $logo_path = 'assets/images/' . htmlspecialchars($rest['image']);
      }
      
      // Display logo if path is set
      if ($logo_path) {
          echo "<img src='$logo_path' alt='$restaurant_name Logo' class='restaurant-logo'>";
      }
      ?>
      <div class="restaurant-header-content">
        <h1><?php echo $restaurant_name; ?></h1>
        <p>Explore our delicious menu</p>
      </div>
    </div>

    <div class="menu-grid">
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $img = !empty($row['image']) ? $row['image'] : 'default-restaurant.jpg';
              echo "<div class='menu-item'>";
              echo "<img src='assets/images/$img' alt='".htmlspecialchars($row['item_name'])."'>";
              echo "<div class='menu-item-content'>";
              echo "<h3>" . htmlspecialchars($row['item_name']) . "</h3>";
              echo "<span class='price'>RM" . number_format($row['price'], 2) . "</span>";
              echo "<p class='description'>" . htmlspecialchars($row['description']) . "</p>";
              echo "<form method='POST'>";
              echo "<input type='hidden' name='menu_id' value='".$row['menu_id']."'>";
              echo "<input type='number' name='quantity' value='1' min='1' class='quantity-input'>";
              echo "<button type='submit' name='add_to_cart' class='add-to-cart-btn'>Add to Cart</button>";
              echo "</form>";
              echo "</div>";
              echo "</div>";
          }
      } else {
          echo "<div class='no-menu'>";
          echo "<p>No menu items available at this time.</p>";
          echo "<p style='font-size: 0.9em; margin-top: 10px;'>Please check back later!</p>";
          echo "</div>";
      }
      ?>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>

</body>
</html>

