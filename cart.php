<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'cart.php';
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ambil semua item dalam cart
$sql = "SELECT c.cart_id, m.item_name, m.price, c.quantity, m.image, m.menu_id
        FROM cart c 
        JOIN menu_items m ON c.menu_id = m.menu_id
        WHERE c.user_id = $user_id";
$result = $conn->query($sql);

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Cart - QuickBite</title>
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

    nav ul li a.active {
      background: rgba(255, 255, 255, 0.25);
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
      margin: 20px 0;
      padding: 30px 40px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 0;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      flex: 1;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #f0f0f0;
    }

    .page-header h1 {
      font-size: 2.5em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0;
    }

    .continue-shopping {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #ff4d4d;
      text-decoration: none;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 25px;
      background: rgba(255, 77, 77, 0.1);
      transition: all 0.3s ease;
    }

    .continue-shopping:hover {
      background: rgba(255, 77, 77, 0.2);
      transform: translateX(-5px);
    }

    .continue-shopping::before {
      content: '← ';
      font-size: 18px;
    }

    .cart-items {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-bottom: 30px;
    }

    .cart-item {
      display: flex;
      align-items: center;
      gap: 20px;
      background: white;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }

    .cart-item:hover {
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
      transform: translateY(-2px);
    }

    .cart-item-image {
      width: 120px;
      height: 120px;
      border-radius: 12px;
      object-fit: cover;
      background-color: #f8f8f8;
      padding: 5px;
      flex-shrink: 0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .cart-item-image:hover {
      transform: scale(1.05);
    }

    .cart-item-details {
      flex: 1;
      min-width: 0;
    }

    .cart-item-name {
      font-size: 1.2em;
      font-weight: 600;
      color: #333;
      margin: 0 0 8px 0;
    }

    .cart-item-price {
      font-size: 1em;
      color: #666;
      margin: 0;
    }

    .cart-item-quantity {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
      color: #333;
      font-size: 1.1em;
    }

    .cart-item-subtotal {
      font-size: 1.3em;
      font-weight: 700;
      color: #ff4d4d;
      min-width: 100px;
      text-align: right;
    }

    .cart-item-actions {
      display: flex;
      gap: 10px;
    }

    .btn-delete {
      background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      cursor: pointer;
      font-size: 14px;
      box-shadow: 0 3px 10px rgba(255, 68, 68, 0.3);
    }

    .btn-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 68, 68, 0.4);
    }

    .cart-summary {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      margin-top: 30px;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #dee2e6;
    }

    .summary-row:last-child {
      border-bottom: none;
      margin-top: 10px;
      padding-top: 20px;
    }

    .summary-label {
      font-size: 1.1em;
      color: #666;
      font-weight: 500;
    }

    .summary-value {
      font-size: 1.2em;
      font-weight: 600;
      color: #333;
    }

    .summary-total {
      font-size: 1.8em;
      font-weight: 700;
      color: #ff4d4d;
    }

    .checkout-btn {
      width: 100%;
      padding: 18px 30px;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      border: none;
      border-radius: 25px;
      font-size: 1.2em;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 20px rgba(255, 77, 77, 0.4);
      margin-top: 20px;
      position: relative;
      overflow: hidden;
    }

    .checkout-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.2);
      transition: left 0.3s ease;
    }

    .checkout-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(255, 77, 77, 0.5);
    }

    .checkout-btn:hover::before {
      left: 100%;
    }

    .checkout-btn:active {
      transform: translateY(-1px);
    }

    .empty-cart {
      text-align: center;
      padding: 80px 20px;
      color: #999;
    }

    .empty-cart-icon {
      font-size: 5em;
      margin-bottom: 20px;
      display: block;
    }

    .empty-cart h2 {
      font-size: 2em;
      color: #666;
      margin-bottom: 15px;
    }

    .empty-cart p {
      font-size: 1.1em;
      margin-bottom: 30px;
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
        padding: 20px 15px;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .page-header h1 {
        font-size: 2em;
      }

      .cart-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .cart-item-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
      }

      .cart-item-subtotal {
        text-align: left;
        width: 100%;
      }

      .cart-item-actions {
        width: 100%;
      }

      .btn-delete {
        flex: 1;
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
          <li><a href="cart.php" class="active">Cart</a></li>
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
  </script>

<div class="container">
    <div class="page-header">
      <h1>Your Shopping Cart</h1>
      <a href="home.php" class="continue-shopping">Continue Shopping</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <div class="cart-items">
<?php
    while ($row = $result->fetch_assoc()) {
        $subtotal = $row['price'] * $row['quantity'];
        $total += $subtotal;
            $img = !empty($row['image']) ? 'assets/images/' . $row['image'] : 'assets/images/default-restaurant.jpg';
            echo "<div class='cart-item'>";
            echo "<img src='$img' alt='".htmlspecialchars($row['item_name'])."' class='cart-item-image'>";
            echo "<div class='cart-item-details'>";
            echo "<h3 class='cart-item-name'>".htmlspecialchars($row['item_name'])."</h3>";
            echo "<p class='cart-item-price'>RM ".number_format($row['price'], 2)." each</p>";
            echo "</div>";
            echo "<div class='cart-item-quantity'>";
            echo "<span>Qty: <strong>".$row['quantity']."</strong></span>";
            echo "</div>";
            echo "<div class='cart-item-subtotal'>RM ".number_format($subtotal, 2)."</div>";
            echo "<div class='cart-item-actions'>";
            echo "<form method='POST' action='delete_cart_item.php' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to remove this item?\");'>";
            echo "<input type='hidden' name='cart_id' value='".$row['cart_id']."'>";
            echo "<button type='submit' name='delete' class='btn-delete'>Remove</button>";
            echo "</form>";
            echo "</div>";
            echo "</div>";
    }
        ?>
      </div>

      <div class="cart-summary">
        <div class="summary-row">
          <span class="summary-label">Subtotal</span>
          <span class="summary-value">RM <?php echo number_format($total, 2); ?></span>
        </div>
        <div class="summary-row">
          <span class="summary-label">Tax</span>
          <span class="summary-value">RM 0.00</span>
        </div>
        <div class="summary-row">
          <span class="summary-label">Delivery Fee</span>
          <span class="summary-value">RM 0.00</span>
        </div>
        <div class="summary-row">
          <span class="summary-label summary-total">Total</span>
          <span class="summary-value summary-total">RM <?php echo number_format($total, 2); ?></span>
        </div>
        <a href="checkout.php" class="checkout-btn" style="text-decoration: none; display: block; text-align: center;">Proceed to Checkout</a>
      </div>
    <?php else: ?>
      <div class="empty-cart">
        <span class="empty-cart-icon">🛒</span>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added any items to your cart yet.</p>
        <a href="home.php" class="continue-shopping">Start Shopping</a>
      </div>
<?php endif; ?>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
</footer>

</body>
</html>
