<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items for display
$cart_items_query = $conn->query("
    SELECT c.menu_id, c.quantity, m.price, m.item_name, m.image
    FROM cart c 
    JOIN menu_items m ON c.menu_id = m.menu_id 
    WHERE c.user_id = $user_id
");

$cart_items = [];
$total_price = 0;
while ($item = $cart_items_query->fetch_assoc()) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total_price += $item['subtotal'];
    $cart_items[] = $item;
}

if (isset($_POST['checkout'])) {
    if (count($cart_items) > 0) {
        $payment_method = "Cash";
        $insert_order = $conn->query("
            INSERT INTO orders (user_id, total_price, payment_method, status)
            VALUES ($user_id, $total_price, '$payment_method', 'preparing')
        ");

        if ($insert_order) {
            $order_id = $conn->insert_id;

            foreach ($cart_items as $item) {
                $conn->query("
                    INSERT INTO order_details (order_id, menu_id, quantity, price)
                    VALUES ($order_id, {$item['menu_id']}, {$item['quantity']}, {$item['price']})
                ");
            }

            $conn->query("DELETE FROM cart WHERE user_id = $user_id");

            $_SESSION['order_success'] = true;
            header("Location: orderstatus.php");
            exit();
        } else {
            $checkout_error = "Error placing order. Please try again.";
        }
    } else {
        header("Location: cart.php");
        exit();
    }
}

$checkout_error = $checkout_error ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - QuickBite</title>
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
      text-decoration: none;
      color: white;
    }

    .logo img {
      width: 40px;
      height: 40px;
      margin-right: 12px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 8px;
      margin: 0;
      padding: 0;
      align-items: center;
    }

    nav ul li a {
      text-decoration: none;
      color: white;
      font-weight: 500;
      padding: 10px 18px;
      border-radius: 25px;
      transition: all 0.3s ease;
      font-size: 15px;
    }

    nav ul li a:hover {
      background: rgba(255, 255, 255, 0.15);
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
      margin-bottom: 40px;
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

    .checkout-wrapper {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 40px;
    }

    .order-summary {
      background: rgba(255, 255, 255, 0.6);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .order-summary h2 {
      font-size: 1.8em;
      font-weight: 700;
      color: #ff4d4d;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .order-items {
      margin-bottom: 25px;
    }

    .order-item {
      display: flex;
      gap: 15px;
      padding: 15px;
      background: rgba(255, 77, 77, 0.05);
      border-radius: 12px;
      margin-bottom: 15px;
    }

    .order-item img {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      object-fit: cover;
      flex-shrink: 0;
    }

    .order-item-info {
      flex: 1;
    }

    .order-item-info h3 {
      font-size: 1.1em;
      font-weight: 600;
      color: #1a1a1a;
      margin-bottom: 5px;
    }

    .order-item-info p {
      font-size: 0.9em;
      color: #666;
      margin: 0;
    }

    .order-item-price {
      font-weight: 700;
      font-size: 1.1em;
      color: #ff4d4d;
      text-align: right;
    }

    .order-totals {
      padding-top: 20px;
      border-top: 2px solid #f0f0f0;
    }

    .total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      font-size: 1.05em;
    }

    .total-row.total {
      font-size: 1.5em;
      font-weight: 700;
      color: #ff4d4d;
      padding-top: 15px;
      border-top: 2px solid #f0f0f0;
      margin-top: 15px;
    }

    .checkout-form {
      background: rgba(255, 255, 255, 0.6);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .checkout-form h2 {
      font-size: 1.8em;
      font-weight: 700;
      color: #ff4d4d;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #333;
      font-weight: 600;
      font-size: 0.95em;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 14px 18px;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      font-family: 'Poppins', Arial, sans-serif;
      font-size: 1em;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #ff4d4d;
      box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.1);
      background: white;
    }

    .btn-group {
      display: flex;
      gap: 15px;
      margin-top: 30px;
    }

    .btn-primary {
      flex: 1;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      padding: 16px;
      border: none;
      border-radius: 12px;
      font-size: 1.1em;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
      font-family: 'Poppins', Arial, sans-serif;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 77, 77, 0.4);
    }

    .btn-secondary {
      flex: 1;
      background: #e0e0e0;
      color: #333;
      padding: 16px;
      border: none;
      border-radius: 12px;
      font-size: 1.1em;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: 'Poppins', Arial, sans-serif;
      text-decoration: none;
      text-align: center;
      display: inline-block;
    }

    .btn-secondary:hover {
      background: #d0d0d0;
      transform: translateY(-2px);
    }

    .empty-cart {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-cart-icon {
      font-size: 5em;
      margin-bottom: 20px;
    }

    .empty-cart h2 {
      font-size: 2em;
      color: #333;
      margin-bottom: 15px;
    }

    /* Toast Notification */
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      padding: 20px 25px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(255, 77, 77, 0.4);
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

    .toast.success {
      background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
      box-shadow: 0 8px 25px rgba(86, 171, 47, 0.4);
    }

    .toast.error {
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      box-shadow: 0 8px 25px rgba(255, 77, 77, 0.4);
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

    .alert {
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-weight: 500;
      background: #fee;
      color: #c33;
      border: 1px solid #fcc;
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

    /* Responsive */
    @media (max-width: 768px) {
      nav {
        padding: 0 20px;
      }

      .container {
        margin: 20px 15px;
        padding: 25px 20px;
      }

      .page-header h1 {
        font-size: 2em;
      }

      .checkout-wrapper {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .order-summary,
      .checkout-form {
        padding: 25px 20px;
      }

      .btn-group {
        flex-direction: column;
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
      <ul>
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

  <?php if ($checkout_error): ?>
    <div id="errorToast" class="toast error">
      <span class="toast-icon">⚠️</span>
      <span class="toast-message"><?php echo htmlspecialchars($checkout_error); ?></span>
      <button class="toast-close" onclick="hideToast('errorToast')">×</button>
    </div>
  <?php endif; ?>

  <div class="container">
    <div class="page-header">
      <h1>Checkout</h1>
      <p>Review your order and complete your purchase</p>
    </div>

    <?php if ($checkout_error && !count($cart_items)): ?>
      <div class="alert">
        <?php echo htmlspecialchars($checkout_error); ?>
      </div>
    <?php endif; ?>

    <?php if (count($cart_items) > 0): ?>
      <div class="checkout-wrapper">
        <div class="order-summary">
          <h2>Order Summary</h2>
          
          <div class="order-items">
            <?php foreach ($cart_items as $item): ?>
              <div class="order-item">
                <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                <div class="order-item-info">
                  <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                  <p>Quantity: <?php echo $item['quantity']; ?> × RM <?php echo number_format($item['price'], 2); ?></p>
                </div>
                <div class="order-item-price">
                  RM <?php echo number_format($item['subtotal'], 2); ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="order-totals">
            <div class="total-row">
              <span>Subtotal</span>
              <span>RM <?php echo number_format($total_price, 2); ?></span>
            </div>
            <div class="total-row">
              <span>Delivery Fee</span>
              <span>RM 0.00</span>
            </div>
            <div class="total-row">
              <span>Tax</span>
              <span>RM 0.00</span>
            </div>
            <div class="total-row total">
              <span>Total</span>
              <span>RM <?php echo number_format($total_price, 2); ?></span>
            </div>
          </div>
        </div>

        <div class="checkout-form">
          <h2>Order Details</h2>
          
          <form method="POST" action="">
            <div class="form-group">
              <label for="payment_method">Payment Method</label>
              <select id="payment_method" name="payment_method" required>
                <option value="Cash">Cash on Delivery</option>
                <option value="Card">Credit/Debit Card</option>
                <option value="Online">Online Payment</option>
              </select>
            </div>

            <div class="form-group">
              <label>Delivery Address</label>
              <input type="text" value="<?php echo htmlspecialchars($_SESSION['address'] ?? ''); ?>" readonly>
              <small style="color: #666; font-size: 0.85em;">Update address in your profile</small>
            </div>

            <div class="btn-group">
              <button type="submit" name="checkout" class="btn-primary">Place Order</button>
              <a href="cart.php" class="btn-secondary">Back to Cart</a>
            </div>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="empty-cart">
        <div class="empty-cart-icon">🛒</div>
        <h2>Your Cart is Empty</h2>
        <p>Add items to your cart before checkout.</p>
        <a href="home.php" class="btn-primary" style="display: inline-block; margin-top: 20px; text-decoration: none;">Start Shopping</a>
      </div>
    <?php endif; ?>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>

  <script>
    // Show toast notification if there's an error
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

    // Show error toast on page load if it exists
    window.addEventListener('load', () => {
      const errorToast = document.getElementById('errorToast');
      if (errorToast) {
        setTimeout(() => showToast('errorToast'), 100);
      }
    });
  </script>

</body>
</html>
