<?php
session_start();
include 'db.php';

// Ambil semua restoran dari database
$sql = "SELECT * FROM restaurants";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QuickBite - Home</title>
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

    /* Responsive Navbar */
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
      padding: 20px 20px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 0;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      flex: 1;
    }

    .welcome-section {
      text-align: center;
      margin-bottom: 20px;
      padding: 15px 0;
      border-bottom: 2px solid #f0f0f0;
    }

    .welcome-section h2 {
      font-size: 1.8em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 5px;
      text-align: center;
    }

    .welcome-section p {
      font-size: 0.95em;
      color: #666;
      margin-top: 5px;
    }

    h3 {
      text-align: center;
      font-size: 1.6em;
      font-weight: 600;
      color: #333;
      margin: 20px 0 20px;
      position: relative;
      display: inline-block;
      width: 100%;
    }

    h3::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      border-radius: 2px;
    }

    .restaurants-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 25px;
      margin-top: 20px;
      padding: 0 15px;
    }

    /* Restaurant Cards */
    .restaurant-card {
      border: none;
      border-radius: 20px;
      padding: 0;
      width: 100%;
      max-width: 320px;
      margin: 0;
      background: white;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }

    .restaurant-card::before {
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

    .restaurant-card:hover {
      transform: translateY(-10px) scale(1.02);
      box-shadow: 0 15px 40px rgba(255, 77, 77, 0.3);
    }

    .restaurant-card:hover::before {
      opacity: 1;
    }

    .restaurant-card img {
      width: 100%;
      height: 200px;
      border-radius: 0;
      object-fit: contain;
      object-position: center;
      margin-bottom: 0;
      transition: transform 0.4s ease;
      background-color: #f8f8f8;
      padding: 10px;
    }

    .restaurant-card:hover img {
      transform: scale(1.05);
    }

    .restaurant-card-content {
      padding: 25px;
      position: relative;
      z-index: 2;
      background: white;
    }

    .restaurant-card h4 {
      margin: 0 0 10px 0;
      font-size: 1.4em;
      font-weight: 600;
      color: #333;
    }

    .restaurant-card p {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
      margin-bottom: 20px;
      min-height: 60px;
    }

    .restaurant-card a {
      display: inline-block;
      margin-top: 0;
      padding: 12px 30px;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      text-decoration: none;
      border-radius: 25px;
      font-weight: 600;
      font-size: 15px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
      position: relative;
      overflow: hidden;
    }

    .restaurant-card a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.2);
      transition: left 0.3s ease;
    }

    .restaurant-card a:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 77, 77, 0.4);
    }

    .restaurant-card a:hover::before {
      left: 100%;
    }

    .no-restaurants {
      text-align: center;
      padding: 60px 20px;
      color: #999;
      font-size: 1.2em;
    }

    .no-restaurants::before {
      content: '🍽️';
      display: block;
      font-size: 4em;
      margin-bottom: 20px;
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
      .container {
        margin: 20px 0;
        padding: 20px 15px;
        border-radius: 0;
      }

      .welcome-section h2 {
        font-size: 1.5em;
      }

      .welcome-section p {
        font-size: 0.9em;
      }

      h3 {
        font-size: 1.5em;
      }

      .restaurants-grid {
        gap: 20px;
        padding: 0 10px;
      }

      .restaurant-card {
        width: 100%;
        max-width: 100%;
      }
    }
  </style>
</head>
<body>

  <header>
    <nav>
      <div class="logo">
        <img src="assets/images/default-restaurant.jpg" alt="QuickBite Logo">
        QuickBite
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
  </script>

  <div class="container">
    <div class="welcome-section">
      <?php if (isset($_SESSION['user_id'])): ?>
        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h2>
      <?php else: ?>
        <h2>Welcome to QuickBite! 👋</h2>
      <?php endif; ?>
      <p>Discover amazing restaurants and order your favorite meals</p>
    </div>

    <h3>Available Restaurants</h3>

    <div class="restaurants-grid">
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $img = !empty($row['image']) ? $row['image'] : 'default-restaurant.jpg';
              echo "<div class='restaurant-card'>";
              echo "<img src='assets/images/$img' alt='" . htmlspecialchars($row['restaurant_name']) . "'>";
              echo "<div class='restaurant-card-content'>";
              echo "<h4>" . htmlspecialchars($row['restaurant_name']) . "</h4>";
              echo "<p>" . htmlspecialchars($row['description']) . "</p>";
              echo "<a href='menu.php?id=" . $row['restaurant_id'] . "'>View Menu →</a>";
              echo "</div>";
              echo "</div>";
          }
      } else {
          echo "<div class='no-restaurants'>";
          echo "<p>No restaurants available yet.</p>";
          echo "<p style='font-size: 0.9em; margin-top: 10px;'>Check back soon for new dining options!</p>";
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