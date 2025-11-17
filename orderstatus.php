<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM orders WHERE user_id=$user_id ORDER BY order_date DESC";
$result = $conn->query($sql);

// Check for order success message
$order_success = false;
if (isset($_SESSION['order_success']) && $_SESSION['order_success']) {
    $order_success = true;
    unset($_SESSION['order_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Orders - QuickBite</title>
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
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    nav ul li a:hover::before {
      width: 300px;
      height: 300px;
    }

    nav ul li a:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
      max-width: 1200px;
      margin: 40px auto;
      padding: 40px;
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      flex: 1;
    }

    .page-header {
      text-align: center;
      margin-bottom: 50px;
      padding-bottom: 30px;
      border-bottom: 3px solid #ff4d4d;
    }

    .page-header h1 {
      font-size: 3em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 15px;
    }

    .page-header p {
      font-size: 1.2em;
      color: #666;
      margin: 0;
    }

    .orders-container {
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .order-card {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border-left: 5px solid #ff4d4d;
      position: relative;
      overflow: hidden;
    }

    .order-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 200px;
      height: 200px;
      background: radial-gradient(circle, rgba(255, 77, 77, 0.05) 0%, transparent 70%);
      border-radius: 50%;
      transform: translate(30%, -30%);
    }

    .order-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 2px solid #f0f0f0;
      flex-wrap: wrap;
      gap: 15px;
    }

    .order-info h2 {
      font-size: 1.8em;
      font-weight: 700;
      color: #1a1a1a;
      margin-bottom: 10px;
    }

    .order-info .order-date {
      color: #666;
      font-size: 1em;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .order-status {
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.95em;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .status-pending {
      background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
      color: #856404;
    }

    .status-preparing {
      background: linear-gradient(135deg, #ff9500 0%, #ffb84d 100%);
      color: #fff;
    }

    .status-delivering {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: #fff;
    }

    .status-completed {
      background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
      color: #fff;
    }

    .status-successful {
      background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
      color: #fff;
    }

    .order-details {
      margin-bottom: 25px;
    }

    .order-items {
      display: flex;
      flex-direction: column;
      gap: 15px;
      margin-bottom: 20px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      background: rgba(255, 77, 77, 0.05);
      border-radius: 12px;
      transition: background 0.3s ease;
    }

    .order-item:hover {
      background: rgba(255, 77, 77, 0.1);
    }

    .item-info {
      flex: 1;
    }

    .item-name {
      font-weight: 600;
      font-size: 1.1em;
      color: #1a1a1a;
      margin-bottom: 5px;
    }

    .item-details {
      font-size: 0.9em;
      color: #666;
      display: flex;
      gap: 15px;
    }

    .item-price {
      font-weight: 700;
      font-size: 1.15em;
      color: #ff4d4d;
    }

    .order-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 20px;
      border-top: 2px solid #f0f0f0;
      flex-wrap: wrap;
      gap: 15px;
    }

    .order-total {
      text-align: right;
    }

    .total-label {
      font-size: 0.9em;
      color: #666;
      margin-bottom: 5px;
    }

    .total-amount {
      font-size: 2em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .empty-state {
      text-align: center;
      padding: 80px 20px;
      background: rgba(255, 255, 255, 0.6);
      border-radius: 20px;
    }

    .empty-state-icon {
      font-size: 5em;
      margin-bottom: 20px;
    }

    .empty-state h2 {
      font-size: 2em;
      color: #333;
      margin-bottom: 15px;
    }

    .empty-state p {
      font-size: 1.1em;
      color: #666;
      margin-bottom: 30px;
    }

    .btn-shop {
      display: inline-block;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      padding: 14px 35px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.1em;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
    }

    .btn-shop:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 77, 77, 0.4);
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
        margin: 20px 15px;
        padding: 25px 20px;
      }

      .page-header h1 {
        font-size: 2em;
      }

      .page-header p {
        font-size: 1em;
      }

      .order-header {
        flex-direction: column;
      }

      .order-card {
        padding: 20px;
      }

      .order-info h2 {
        font-size: 1.4em;
      }

      .order-footer {
        flex-direction: column;
        align-items: flex-start;
      }

      .order-total {
        text-align: left;
        width: 100%;
      }

      .total-amount {
        font-size: 1.6em;
      }

      .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }

      .item-price {
        align-self: flex-end;
      }
    }

    /* Toast Notification */
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
      color: white;
      padding: 20px 25px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(86, 171, 47, 0.4);
      z-index: 10000;
      display: flex;
      align-items: center;
      gap: 15px;
      min-width: 300px;
      max-width: 500px;
      transform: translateX(400px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      font-family: 'Poppins', Arial, sans-serif;
    }

    .toast.show {
      transform: translateX(0);
      opacity: 1;
    }

    .toast-icon {
      font-size: 1.8em;
      flex-shrink: 0;
    }

    .toast-message {
      flex: 1;
      font-weight: 500;
      font-size: 1.05em;
      line-height: 1.4;
    }

    .toast-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 1.2em;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.3s ease;
      flex-shrink: 0;
      padding: 0;
      line-height: 1;
    }

    .toast-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    @media (max-width: 768px) {
      .toast {
        right: 10px;
        left: 10px;
        min-width: auto;
        max-width: none;
        transform: translateY(-100px);
      }

      .toast.show {
        transform: translateY(0);
      }
    }
  </style>
</head>
<body>

  <?php if ($order_success): ?>
    <div id="successToast" class="toast">
      <span class="toast-icon">✅</span>
      <span class="toast-message">Order placed successfully! Your order is being prepared.</span>
      <button class="toast-close" onclick="hideToast('successToast')">×</button>
    </div>
  <?php endif; ?>

  <header>
    <nav>
      <div class="logo">
        <a href="home.php" style="display: flex; align-items: center; text-decoration: none; color: white;">
          <img src="assets/images/default-restaurant.jpg" alt="QuickBite Logo">
          QuickBite
        </a>
      </div>
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
          <li><a href="orderstatus.php" class="active">My Orders</a></li>
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

    if (menuToggle && navMenu) {
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
    }
  </script>

  <div class="container">
    <div class="page-header">
      <h1>My Orders</h1>
      <p>Track your order history and status</p>
    </div>

    <div class="orders-container">
      <?php
      if ($result->num_rows > 0) {
          while ($order = $result->fetch_assoc()) {
              // Format date
              $order_date = date('F j, Y g:i A', strtotime($order['order_date']));
              
              // Get status class
              $status_class = 'status-' . strtolower($order['status']);
              $status_icon = '📦';
              if (strpos(strtolower($order['status']), 'prepar') !== false) {
                  $status_icon = '👨‍🍳';
              } elseif (strpos(strtolower($order['status']), 'deliver') !== false) {
                  $status_icon = '🚚';
              } elseif (strpos(strtolower($order['status']), 'complet') !== false || strpos(strtolower($order['status']), 'success') !== false) {
                  $status_icon = '✅';
              } elseif (strpos(strtolower($order['status']), 'pend') !== false) {
                  $status_icon = '⏳';
              }
              
              echo "<div class='order-card'>";
              echo "<div class='order-header'>";
              echo "<div class='order-info'>";
              echo "<h2>Order #{$order['order_id']}</h2>";
              echo "<div class='order-date'>📅 {$order_date}</div>";
              echo "</div>";
              echo "<span class='order-status {$status_class}'>{$status_icon} {$order['status']}</span>";
              echo "</div>";
              
              echo "<div class='order-details'>";
              
              // Get order items
              $details = $conn->query("
                  SELECT od.*, m.item_name, m.image
                  FROM order_details od
                  JOIN menu_items m ON od.menu_id = m.menu_id
                  WHERE od.order_id = {$order['order_id']}
              ");
              
              if ($details->num_rows > 0) {
                  echo "<div class='order-items'>";
                  while ($item = $details->fetch_assoc()) {
                      $subtotal = $item['quantity'] * $item['price'];
                      echo "<div class='order-item'>";
                      echo "<div class='item-info'>";
                      echo "<div class='item-name'>{$item['item_name']}</div>";
                      echo "<div class='item-details'>";
                      echo "<span>Quantity: <strong>{$item['quantity']}</strong></span>";
                      echo "<span>Price: <strong>RM " . number_format($item['price'], 2) . "</strong></span>";
                      echo "</div>";
                      echo "</div>";
                      echo "<div class='item-price'>RM " . number_format($subtotal, 2) . "</div>";
                      echo "</div>";
                  }
                  echo "</div>";
              }
              
              echo "</div>";
              
              echo "<div class='order-footer'>";
              echo "<div></div>";
              echo "<div class='order-total'>";
              echo "<div class='total-label'>Total Amount</div>";
              echo "<div class='total-amount'>RM " . number_format($order['total_price'], 2) . "</div>";
              echo "</div>";
              echo "</div>";
              
              echo "</div>";
          }
      } else {
          echo "<div class='empty-state'>";
          echo "<div class='empty-state-icon'>🛒</div>";
          echo "<h2>No Orders Yet</h2>";
          echo "<p>You haven't placed any orders yet. Start shopping to see your orders here!</p>";
          echo "<a href='home.php' class='btn-shop'>Start Shopping</a>";
          echo "</div>";
      }
      ?>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>

  <script>
    // Show toast notification for success message
    function showToast(toastId) {
      const toast = document.getElementById(toastId);
      if (toast) {
        toast.classList.add('show');
        // Auto hide after 5 seconds
        setTimeout(() => {
          hideToast(toastId);
        }, 5000);
      }
    }

    function hideToast(toastId) {
      const toast = document.getElementById(toastId);
      if (toast) {
        toast.classList.remove('show');
      }
    }

    // Show success toast on page load if it exists
    window.addEventListener('load', () => {
      const successToast = document.getElementById('successToast');
      if (successToast) {
        setTimeout(() => showToast('successToast'), 100);
      }
    });
  </script>

</body>
</html>
