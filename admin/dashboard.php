<?php
session_start();
include '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role']!='admin') { 
    header("Location: ../login.php"); 
    exit(); 
}

// Get statistics
$restaurants = $conn->query("SELECT COUNT(*) AS total FROM restaurants")->fetch_assoc()['total'];
$customers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='customer' OR role IS NULL")->fetch_assoc()['total'];
$orders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(total_price) AS revenue FROM orders WHERE status='completed'")->fetch_assoc();
$revenue = $total_revenue['revenue'] ?? 0;

// Get pending orders count
$pending_orders = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='pending'")->fetch_assoc()['total'];

// Get recent orders
$recent_orders = $conn->query("SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id=u.user_id ORDER BY o.order_date DESC LIMIT 5");

// Get orders by status
$orders_by_status = [
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
  <title>Admin Dashboard - QuickBite</title>
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

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .stat-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .stat-card-title {
      font-size: 14px;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 1px;
      font-weight: 600;
    }

    .stat-card-icon {
      font-size: 32px;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
    }

    .stat-card-icon.restaurants { background: #e3f2fd; }
    .stat-card-icon.customers { background: #f3e5f5; }
    .stat-card-icon.orders { background: #fff3e0; }
    .stat-card-icon.revenue { background: #e8f5e9; }

    .stat-card-value {
      font-size: 36px;
      font-weight: 700;
      color: #333;
      margin-bottom: 5px;
    }

    .stat-card-footer {
      font-size: 14px;
      color: #999;
    }

    .content-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    .card {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .card-header h2 {
      font-size: 24px;
      color: #333;
    }

    .card-header a {
      color: #ff4d4d;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
    }

    .order-item {
      padding: 15px;
      border-bottom: 1px solid #f0f0f0;
      transition: background 0.3s ease;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .order-item:hover {
      background: #f9f9f9;
    }

    .order-item-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;
    }

    .order-id {
      font-weight: 700;
      color: #333;
      font-size: 16px;
    }

    .order-status {
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .order-status.pending { background: #fff3cd; color: #856404; }
    .order-status.preparing { background: #cfe2ff; color: #084298; }
    .order-status.delivering { background: #d1e7dd; color: #0f5132; }
    .order-status.completed { background: #d4edda; color: #155724; }

    .order-details {
      display: flex;
      justify-content: space-between;
      color: #666;
      font-size: 14px;
    }

    .order-total {
      font-weight: 700;
      color: #ff4d4d;
    }

    .status-list {
      list-style: none;
    }

    .status-item {
      display: flex;
      justify-content: space-between;
      padding: 15px;
      border-bottom: 1px solid #f0f0f0;
    }

    .status-item:last-child {
      border-bottom: none;
    }

    .status-label {
      font-weight: 600;
      color: #666;
      text-transform: capitalize;
    }

    .status-count {
      font-weight: 700;
      color: #333;
      font-size: 18px;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #999;
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

    @media (max-width: 968px) {
      .content-grid {
        grid-template-columns: 1fr;
      }

      .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
      <h1>Admin Dashboard</h1>
      <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! Here's an overview of your platform.</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Total Restaurants</div>
          <div class="stat-card-icon restaurants">🍽️</div>
        </div>
        <div class="stat-card-value"><?php echo $restaurants; ?></div>
        <div class="stat-card-footer">Active restaurants</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Total Customers</div>
          <div class="stat-card-icon customers">👥</div>
        </div>
        <div class="stat-card-value"><?php echo $customers; ?></div>
        <div class="stat-card-footer">Registered users</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Total Orders</div>
          <div class="stat-card-icon orders">📦</div>
        </div>
        <div class="stat-card-value"><?php echo $orders; ?></div>
        <div class="stat-card-footer">All time orders</div>
      </div>

      <div class="stat-card">
        <div class="stat-card-header">
          <div class="stat-card-title">Total Revenue</div>
          <div class="stat-card-icon revenue">💰</div>
        </div>
        <div class="stat-card-value">RM <?php echo number_format($revenue, 2); ?></div>
        <div class="stat-card-footer">From completed orders</div>
      </div>
    </div>

    <div class="content-grid">
      <div class="card">
        <div class="card-header">
          <h2>Recent Orders</h2>
          <a href="manageorders.php">View All →</a>
        </div>
        <?php if ($recent_orders->num_rows > 0): ?>
          <?php while ($order = $recent_orders->fetch_assoc()): ?>
            <div class="order-item">
              <div class="order-item-header">
                <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                <span class="order-status <?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span>
              </div>
              <div class="order-details">
                <div>
                  <div><strong><?php echo htmlspecialchars($order['name']); ?></strong></div>
                  <div style="font-size: 12px; color: #999;"><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></div>
                </div>
                <div class="order-total">RM <?php echo number_format($order['total_price'], 2); ?></div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="empty-state">
            <p>No orders yet</p>
          </div>
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Orders by Status</h2>
        </div>
        <ul class="status-list">
          <li class="status-item">
            <span class="status-label">Pending</span>
            <span class="status-count"><?php echo $orders_by_status['pending']; ?></span>
          </li>
          <li class="status-item">
            <span class="status-label">Preparing</span>
            <span class="status-count"><?php echo $orders_by_status['preparing']; ?></span>
          </li>
          <li class="status-item">
            <span class="status-label">Delivering</span>
            <span class="status-count"><?php echo $orders_by_status['delivering']; ?></span>
          </li>
          <li class="status-item">
            <span class="status-label">Completed</span>
            <span class="status-count"><?php echo $orders_by_status['completed']; ?></span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>
</body>
</html>
