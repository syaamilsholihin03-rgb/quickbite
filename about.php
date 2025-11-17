<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - QuickBite</title>
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
      max-width: 1000px;
      margin: 40px auto;
      padding: 40px;
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      flex: 1;
    }

    .about-header {
      text-align: center;
      margin-bottom: 50px;
      padding-bottom: 30px;
      border-bottom: 3px solid #ff4d4d;
    }

    .about-header h1 {
      font-size: 3em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 15px;
    }

    .about-header p {
      font-size: 1.2em;
      color: #666;
      margin: 0;
    }

    .about-content {
      line-height: 1.8;
      font-size: 1.1em;
      color: #444;
    }

    .about-content p {
      margin-bottom: 25px;
      text-align: justify;
    }

    .about-content p:last-child {
      margin-bottom: 0;
    }

    .main-description {
      font-weight: 700 !important;
      font-size: 1.2em !important;
    }

    .highlight-section {
      background: linear-gradient(135deg, rgba(255, 77, 77, 0.95) 0%, rgba(255, 107, 107, 0.95) 100%);
      padding: 30px;
      border-radius: 15px;
      margin: 30px 0;
      border-left: 5px solid #ff4d4d;
      color: white;
    }

    .highlight-section h3 {
      color: white;
      font-size: 1.5em;
      margin-bottom: 15px;
      font-weight: 600;
    }

    .highlight-section p {
      color: white;
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

      .about-header h1 {
        font-size: 2em;
      }

      .about-header p {
        font-size: 1em;
      }

      .about-content {
        font-size: 1em;
      }

      .main-description {
        font-size: 1.1em !important;
      }

      .highlight-section {
        padding: 20px;
      }

      .highlight-section h3 {
        font-size: 1.3em;
      }
    }
  </style>
</head>
<body>

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
        <li><a href="about.php" class="active">About</a></li>
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
    <div class="about-header">
      <h1>About QuickBite</h1>
      <p>Your trusted food delivery platform</p>
    </div>

    <div class="about-content">
      <p class="main-description">
        QuickBite is a modern food-delivery platform created to make ordering meals fast, simple, and stress-free. Built for both hungry customers and busy restaurant owners, QuickBite connects multiple restaurants into one easy-to-use system where anyone can discover food, place orders, and track their delivery in real time.
      </p>

      <div class="highlight-section">
        <h3>For Customers</h3>
        <p>
          Our platform is designed to deliver convenience. Customers can browse menus, search for their favourite cuisines, place secure online payments, and receive instant updates from the moment an order is made until it arrives at their door.
        </p>
      </div>

      <div class="highlight-section">
        <h3>For Restaurants</h3>
        <p>
          At the same time, QuickBite helps restaurants digitalize their operations — from managing menus to handling orders — making the entire process more organized, accurate, and efficient.
        </p>
      </div>

      <p>
        With streamlined communication, automated order handling, and powerful analytics, QuickBite improves customer satisfaction while helping restaurants grow their business. Whether you're craving something delicious or managing a busy kitchen, QuickBite brings everything together in one reliable, centralized platform.
      </p>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>

</body>
</html>

