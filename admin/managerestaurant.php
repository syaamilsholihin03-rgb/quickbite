<?php
session_start();
include '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = '';

// Handle add restaurant
if (isset($_POST['add_restaurant'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['restaurant_name']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
    $loc = mysqli_real_escape_string($conn, trim($_POST['location']));
    $owner_id = intval($_SESSION['user_id']);

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO restaurants (owner_id, restaurant_name, description, location) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $owner_id, $name, $desc, $loc);
        
        if ($stmt->execute()) {
            $message = 'Restaurant added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error: Could not add restaurant.';
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Restaurant name is required.';
        $message_type = 'error';
    }
}

// Handle update restaurant
if (isset($_POST['update_restaurant'])) {
    $restaurant_id = intval($_POST['restaurant_id']);
    $name = mysqli_real_escape_string($conn, trim($_POST['restaurant_name']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
    $loc = mysqli_real_escape_string($conn, trim($_POST['location']));

    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE restaurants SET restaurant_name = ?, description = ?, location = ? WHERE restaurant_id = ?");
        $stmt->bind_param("sssi", $name, $desc, $loc, $restaurant_id);
        
        if ($stmt->execute()) {
            $message = 'Restaurant updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error: Could not update restaurant.';
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Restaurant name is required.';
        $message_type = 'error';
    }
}

// Handle delete restaurant
if (isset($_POST['delete_restaurant'])) {
    $restaurant_id = intval($_POST['restaurant_id']);
    
    // Check if restaurant has menu items or orders
    $check_menu = $conn->query("SELECT COUNT(*) AS count FROM menu_items WHERE restaurant_id = $restaurant_id")->fetch_assoc();
    $check_orders = $conn->query("SELECT COUNT(*) AS count FROM order_details od JOIN menu_items m ON od.menu_id = m.menu_id WHERE m.restaurant_id = $restaurant_id")->fetch_assoc();
    
    if ($check_menu['count'] > 0 || $check_orders['count'] > 0) {
        $message = 'Cannot delete restaurant with existing menu items or orders.';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("DELETE FROM restaurants WHERE restaurant_id = ?");
        $stmt->bind_param("i", $restaurant_id);
        
        if ($stmt->execute()) {
            $message = 'Restaurant deleted successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error: Could not delete restaurant.';
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// Get restaurant for editing
$edit_restaurant = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM restaurants WHERE restaurant_id = $edit_id");
    if ($edit_result->num_rows > 0) {
        $edit_restaurant = $edit_result->fetch_assoc();
    }
}

// Get all restaurants
$restaurants = $conn->query("SELECT r.*, 
    (SELECT COUNT(*) FROM menu_items WHERE restaurant_id = r.restaurant_id) AS menu_count,
    (SELECT COUNT(*) FROM order_details od JOIN menu_items m ON od.menu_id = m.menu_id WHERE m.restaurant_id = r.restaurant_id) AS order_count
    FROM restaurants r 
    ORDER BY r.restaurant_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Restaurants - Admin</title>
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

    .content-wrapper {
      display: grid;
      grid-template-columns: 400px 1fr;
      gap: 30px;
      align-items: start;
    }

    .form-section {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 20px;
      max-height: calc(100vh - 100px);
      overflow-y: auto;
    }

    .form-section h2 {
      font-size: 24px;
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
      font-size: 14px;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 14px;
      font-family: 'Poppins', Arial, sans-serif;
      transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #ff4d4d;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }

    .form-actions {
      display: flex;
      gap: 10px;
    }

    .btn {
      padding: 12px 30px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary {
      background: #ff4d4d;
      color: white;
    }

    .btn-primary:hover {
      background: #ff3333;
    }

    .btn-secondary {
      background: #6c757d;
      color: white;
    }

    .btn-secondary:hover {
      background: #5a6268;
    }

    .btn-danger {
      background: #dc3545;
      color: white;
    }

    .btn-danger:hover {
      background: #c82333;
    }

    .btn-edit {
      background: #28a745;
      color: white;
      padding: 8px 20px;
      font-size: 13px;
    }

    .btn-edit:hover {
      background: #218838;
    }

    .restaurants-section {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .restaurants-section h2 {
      font-size: 24px;
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .restaurants-grid {
      display: flex;
      flex-direction: row;
      gap: 25px;
      overflow-x: auto;
      padding-bottom: 10px;
      scrollbar-width: thin;
      scrollbar-color: #ff4d4d #f0f0f0;
    }

    .restaurants-grid::-webkit-scrollbar {
      height: 8px;
    }

    .restaurants-grid::-webkit-scrollbar-track {
      background: #f0f0f0;
      border-radius: 10px;
    }

    .restaurants-grid::-webkit-scrollbar-thumb {
      background: #ff4d4d;
      border-radius: 10px;
    }

    .restaurants-grid::-webkit-scrollbar-thumb:hover {
      background: #ff3333;
    }

    .restaurant-card {
      background: #f9f9f9;
      border: 1px solid #e0e0e0;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      flex: 0 0 350px;
      min-width: 350px;
    }

    .restaurant-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .restaurant-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f0f0f0;
    }

    .restaurant-name {
      font-size: 22px;
      font-weight: 700;
      color: #333;
      margin-bottom: 5px;
    }

    .restaurant-stats {
      display: flex;
      gap: 15px;
      margin-top: 10px;
    }

    .stat-badge {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 13px;
      color: #666;
      background: #f9f9f9;
      padding: 5px 12px;
      border-radius: 20px;
    }

    .restaurant-description {
      color: #666;
      line-height: 1.6;
      margin-bottom: 15px;
      font-size: 14px;
    }

    .restaurant-location {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #666;
      font-size: 14px;
      margin-bottom: 20px;
    }

    .restaurant-actions {
      display: flex;
      gap: 10px;
    }

    .restaurant-actions form {
      display: inline;
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

    @media (max-width: 1200px) {
      .content-wrapper {
        grid-template-columns: 1fr;
      }

      .form-section {
        position: static;
        max-height: none;
      }
    }

    @media (max-width: 768px) {
      .restaurant-card {
        flex: 0 0 280px;
        min-width: 280px;
      }

      .form-actions {
        flex-direction: column;
      }

      .btn {
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
      <h1>Manage Restaurants</h1>
      <p>Add, edit, and manage restaurants on the platform</p>
    </div>

    <?php if ($message): ?>
      <div class="message <?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <div class="content-wrapper">
      <div class="form-section">
        <h2><?php echo $edit_restaurant ? 'Edit Restaurant' : 'Add New Restaurant'; ?></h2>
        <form method="POST">
          <?php if ($edit_restaurant): ?>
            <input type="hidden" name="restaurant_id" value="<?php echo $edit_restaurant['restaurant_id']; ?>">
          <?php endif; ?>
          
          <div class="form-group">
            <label for="restaurant_name">Restaurant Name *</label>
            <input type="text" id="restaurant_name" name="restaurant_name" 
                   value="<?php echo $edit_restaurant ? htmlspecialchars($edit_restaurant['restaurant_name']) : ''; ?>" 
                   required>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" 
                      placeholder="Enter restaurant description..."><?php echo $edit_restaurant ? htmlspecialchars($edit_restaurant['description']) : ''; ?></textarea>
          </div>

          <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" 
                   value="<?php echo $edit_restaurant ? htmlspecialchars($edit_restaurant['location']) : ''; ?>" 
                   placeholder="e.g., Kuala Lumpur, Malaysia">
          </div>

          <div class="form-actions">
            <?php if ($edit_restaurant): ?>
              <button type="submit" name="update_restaurant" class="btn btn-primary">Update Restaurant</button>
              <a href="managerestaurant.php" class="btn btn-secondary">Cancel</a>
            <?php else: ?>
              <button type="submit" name="add_restaurant" class="btn btn-primary">Add Restaurant</button>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <div class="restaurants-section">
        <h2>Existing Restaurants (<?php echo $restaurants->num_rows; ?>)</h2>
      
      <?php if ($restaurants->num_rows > 0): ?>
        <div class="restaurants-grid">
          <?php while ($restaurant = $restaurants->fetch_assoc()): ?>
            <div class="restaurant-card">
              <div class="restaurant-header">
                <div>
                  <div class="restaurant-name"><?php echo htmlspecialchars($restaurant['restaurant_name']); ?></div>
                  <div class="restaurant-stats">
                    <span class="stat-badge">🍽️ <?php echo $restaurant['menu_count']; ?> Menu Items</span>
                    <span class="stat-badge">📦 <?php echo $restaurant['order_count']; ?> Orders</span>
                  </div>
                </div>
              </div>

              <?php if (!empty($restaurant['description'])): ?>
                <div class="restaurant-description">
                  <?php echo htmlspecialchars($restaurant['description']); ?>
                </div>
              <?php endif; ?>

              <?php if (!empty($restaurant['location'])): ?>
                <div class="restaurant-location">
                  📍 <?php echo htmlspecialchars($restaurant['location']); ?>
                </div>
              <?php endif; ?>

              <div class="restaurant-actions">
                <a href="?edit=<?php echo $restaurant['restaurant_id']; ?>" class="btn btn-edit">Edit</a>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this restaurant? This action cannot be undone.');">
                  <input type="hidden" name="restaurant_id" value="<?php echo $restaurant['restaurant_id']; ?>">
                  <button type="submit" name="delete_restaurant" class="btn btn-danger">Delete</button>
                </form>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">🍽️</div>
          <h2>No Restaurants Yet</h2>
          <p>Start by adding your first restaurant above.</p>
        </div>
      <?php endif; ?>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>
</body>
</html>
