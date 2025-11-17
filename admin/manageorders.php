<?php
session_start();
include '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $message = 'Order status updated successfully!';
        $message_type = 'success';
    } else {
        $message = 'Error updating order status.';
        $message_type = 'error';
    }
    $stmt->close();
}

// Handle filter
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'all';

// Build query
$query = "SELECT o.*, u.name, u.email, u.phone 
          FROM orders o 
          JOIN users u ON o.user_id = u.user_id";
          
if ($status_filter != 'all') {
    $query .= " WHERE o.status = '$status_filter'";
}

$query .= " ORDER BY o.order_date DESC";

$orders = $conn->query($query);

// Get order counts for filter
$order_counts = [
    'all' => $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'],
    'pending' => $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='pending'")->fetch_assoc()['total'],
    'preparing' => $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='preparing'")->fetch_assoc()['total'],
    'delivering' => $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='delivering'")->fetch_assoc()['total'],
    'completed' => $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='completed'")->fetch_assoc()['total']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders - Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
      color: #333;
      display: flex;
      flex-direction: column;
    }

    header {
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      padding: 15px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 40px;
    }

    .logo {
      font-size: 24px;
      font-weight: 700;
    }

    nav ul {
      list-style: none;
      display: flex;
      gap: 20px;
      align-items: center;
    }

    nav ul li a {
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 25px;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    nav ul li a:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .container {
      max-width: 1400px;
      margin: 40px auto;
      padding: 0 40px;
      flex: 1;
    }

    .page-header {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    .page-header h1 {
      font-size: 32px;
      color: #333;
      margin-bottom: 10px;
    }

    .page-header p {
      color: #666;
      font-size: 16px;
    }

    .filter-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }

    .filter-tab {
      padding: 12px 24px;
      background: white;
      border: 2px solid #e0e0e0;
      border-radius: 25px;
      text-decoration: none;
      color: #666;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .filter-tab:hover {
      border-color: #ff4d4d;
      color: #ff4d4d;
    }

    .filter-tab.active {
      background: #ff4d4d;
      color: white;
      border-color: #ff4d4d;
    }

    .filter-tab .count {
      background: rgba(0, 0, 0, 0.1);
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 12px;
    }

    .filter-tab.active .count {
      background: rgba(255, 255, 255, 0.3);
    }

    .message {
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .orders-container {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .order-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .order-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .order-info h3 {
      font-size: 20px;
      color: #333;
      margin-bottom: 8px;
    }

    .order-meta {
      display: flex;
      flex-direction: column;
      gap: 5px;
      color: #666;
      font-size: 14px;
    }

    .order-status-badge {
      padding: 8px 20px;
      border-radius: 25px;
      font-size: 14px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .order-status-badge.pending { background: #fff3cd; color: #856404; }
    .order-status-badge.preparing { background: #cfe2ff; color: #084298; }
    .order-status-badge.delivering { background: #d1e7dd; color: #0f5132; }
    .order-status-badge.completed { background: #d4edda; color: #155724; }

    .order-details-section {
      margin-bottom: 20px;
    }

    .order-details-section h4 {
      font-size: 16px;
      color: #333;
      margin-bottom: 15px;
    }

    .order-items {
      background: #f9f9f9;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #e0e0e0;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .item-info {
      flex: 1;
    }

    .item-name {
      font-weight: 600;
      color: #333;
      margin-bottom: 5px;
    }

    .item-details {
      font-size: 13px;
      color: #666;
    }

    .item-price {
      font-weight: 700;
      color: #ff4d4d;
    }

    .customer-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
    }

    .info-label {
      font-size: 12px;
      color: #999;
      text-transform: uppercase;
      margin-bottom: 5px;
      font-weight: 600;
    }

    .info-value {
      font-size: 14px;
      color: #333;
      font-weight: 500;
    }

    .order-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 20px;
      border-top: 2px solid #f0f0f0;
    }

    .order-total {
      text-align: right;
    }

    .total-label {
      font-size: 12px;
      color: #999;
      text-transform: uppercase;
      margin-bottom: 5px;
    }

    .total-amount {
      font-size: 24px;
      font-weight: 700;
      color: #ff4d4d;
    }

    .status-form {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .status-select {
      padding: 10px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      color: #333;
      background: white;
      cursor: pointer;
      transition: border-color 0.3s ease;
    }

    .status-select:focus {
      outline: none;
      border-color: #ff4d4d;
    }

    .btn-update {
      padding: 10px 25px;
      background: #ff4d4d;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn-update:hover {
      background: #ff3333;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .empty-state-icon {
      font-size: 64px;
      margin-bottom: 20px;
    }

    .empty-state h2 {
      color: #333;
      margin-bottom: 10px;
    }

    .empty-state p {
      color: #666;
    }

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

    @media (max-width: 768px) {
      .order-header {
        flex-direction: column;
        gap: 15px;
      }

      .order-footer {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
      }

      .status-form {
        flex-direction: column;
      }

      .status-select, .btn-update {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <div class="logo">🍔 QuickBite Admin</div>
      <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="managerestaurant.php">Manage Restaurants</a></li>
        <li><a href="manageorders.php">Manage Orders</a></li>
        <li><a href="../logout.php">Logout</a></li>
      </ul>
    </nav>
  </header>

  <div class="container">
    <div class="page-header">
      <h1>Manage Orders</h1>
      <p>View and manage all customer orders</p>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="filter-tabs">
      <a href="?status=all" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">
        All Orders <span class="count"><?php echo $order_counts['all']; ?></span>
      </a>
      <a href="?status=pending" class="filter-tab <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
        Pending <span class="count"><?php echo $order_counts['pending']; ?></span>
      </a>
      <a href="?status=preparing" class="filter-tab <?php echo $status_filter == 'preparing' ? 'active' : ''; ?>">
        Preparing <span class="count"><?php echo $order_counts['preparing']; ?></span>
      </a>
      <a href="?status=delivering" class="filter-tab <?php echo $status_filter == 'delivering' ? 'active' : ''; ?>">
        Delivering <span class="count"><?php echo $order_counts['delivering']; ?></span>
      </a>
      <a href="?status=completed" class="filter-tab <?php echo $status_filter == 'completed' ? 'active' : ''; ?>">
        Completed <span class="count"><?php echo $order_counts['completed']; ?></span>
      </a>
    </div>

    <div class="orders-container">
      <?php if ($orders->num_rows > 0): ?>
        <?php while ($order = $orders->fetch_assoc()): ?>
          <?php
          // Get order items
          $order_items_query = $conn->prepare("
            SELECT od.*, m.item_name, m.image
            FROM order_details od
            JOIN menu_items m ON od.menu_id = m.menu_id
            WHERE od.order_id = ?
          ");
          $order_items_query->bind_param("i", $order['order_id']);
          $order_items_query->execute();
          $order_items = $order_items_query->get_result();
          ?>
          
          <div class="order-card">
            <div class="order-header">
              <div class="order-info">
                <h3>Order #<?php echo $order['order_id']; ?></h3>
                <div class="order-meta">
                  <span>📅 <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></span>
                  <span>💳 Payment: <?php echo htmlspecialchars($order['payment_method'] ?? 'Cash'); ?></span>
                </div>
              </div>
              <span class="order-status-badge <?php echo strtolower($order['status']); ?>">
                <?php echo ucfirst($order['status']); ?>
              </span>
            </div>

            <div class="order-details-section">
              <h4>Customer Information</h4>
              <div class="customer-info">
                <div class="info-item">
                  <span class="info-label">Name</span>
                  <span class="info-value"><?php echo htmlspecialchars($order['name']); ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Email</span>
                  <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <?php if (!empty($order['phone'])): ?>
                <div class="info-item">
                  <span class="info-label">Phone</span>
                  <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="order-details-section">
              <h4>Order Items</h4>
              <div class="order-items">
                <?php if ($order_items->num_rows > 0): ?>
                  <?php while ($item = $order_items->fetch_assoc()): ?>
                    <div class="order-item">
                      <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                        <div class="item-details">
                          Quantity: <?php echo $item['quantity']; ?> × RM <?php echo number_format($item['price'], 2); ?>
                        </div>
                      </div>
                      <div class="item-price">
                        RM <?php echo number_format($item['quantity'] * $item['price'], 2); ?>
                      </div>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <p style="color: #999;">No items found</p>
                <?php endif; ?>
              </div>
            </div>

            <div class="order-footer">
              <form method="POST" class="status-form">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                <select name="status" class="status-select">
                  <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                  <option value="delivering" <?php echo $order['status'] == 'delivering' ? 'selected' : ''; ?>>Delivering</option>
                  <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
                <button type="submit" name="update_status" class="btn-update">Update Status</button>
              </form>
              <div class="order-total">
                <div class="total-label">Total Amount</div>
                <div class="total-amount">RM <?php echo number_format($order['total_price'], 2); ?></div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">📦</div>
          <h2>No Orders Found</h2>
          <p>There are no orders matching your filter criteria.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>
</body>
</html>
