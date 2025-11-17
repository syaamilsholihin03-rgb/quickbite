<?php
session_start();
include 'db.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name === '' || $email === '' || $subject === '' || $message === '') {
        $error_message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Here you can save to database or send email
        // For now, just show success message
        $success_message = "Thank you for contacting us! We'll get back to you soon.";
        // Reset form
        $name = $email = $subject = $message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - QuickBite</title>
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

    .contact-header {
      text-align: center;
      margin-bottom: 50px;
      padding-bottom: 30px;
      border-bottom: 3px solid #ff4d4d;
    }

    .contact-header h1 {
      font-size: 3em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 15px;
    }

    .contact-header p {
      font-size: 1.2em;
      color: #666;
      margin: 0;
    }

    .contact-wrapper {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      margin-bottom: 40px;
    }

    .contact-info {
      background: linear-gradient(135deg, rgba(255, 77, 77, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%);
      padding: 30px;
      border-radius: 15px;
      border-left: 5px solid #ff4d4d;
    }

    .contact-info h2 {
      color: #ff4d4d;
      font-size: 1.8em;
      margin-bottom: 25px;
      font-weight: 700;
    }

    .info-item {
      margin-bottom: 25px;
      display: flex;
      align-items: flex-start;
      gap: 15px;
    }

    .info-item .icon {
      font-size: 1.5em;
      color: #ff4d4d;
      margin-top: 5px;
      flex-shrink: 0;
    }

    .info-item h3 {
      color: #333;
      font-size: 1.1em;
      margin-bottom: 5px;
      font-weight: 600;
    }

    .info-item p {
      color: #666;
      margin: 0;
      line-height: 1.6;
    }

    .contact-form {
      background: rgba(255, 255, 255, 0.5);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #333;
      font-weight: 600;
      font-size: 1em;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-family: 'Poppins', Arial, sans-serif;
      font-size: 1em;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.9);
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #ff4d4d;
      box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.1);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 150px;
    }

    .btn-submit {
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      color: white;
      padding: 14px 35px;
      border: none;
      border-radius: 25px;
      font-size: 1.1em;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
      font-family: 'Poppins', Arial, sans-serif;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 77, 77, 0.4);
    }

    .btn-submit:active {
      transform: translateY(0);
    }

    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 25px;
      font-weight: 500;
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
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

      .contact-header h1 {
        font-size: 2em;
      }

      .contact-header p {
        font-size: 1em;
      }

      .contact-wrapper {
        grid-template-columns: 1fr;
        gap: 30px;
      }

      .contact-info,
      .contact-form {
        padding: 25px 20px;
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
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php" class="active">Contact</a></li>
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
    <div class="contact-header">
      <h1>Contact Us</h1>
      <p>We'd love to hear from you. Get in touch with us!</p>
    </div>

    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="contact-wrapper">
      <div class="contact-info">
        <h2>Get in Touch</h2>
        
        <div class="info-item">
          <div class="icon">📍</div>
          <div>
            <h3>Address</h3>
            <p>123 Food Street<br>Kuala Lumpur, 50000<br>Malaysia</p>
          </div>
        </div>

        <div class="info-item">
          <div class="icon">📧</div>
          <div>
            <h3>Email</h3>
            <p>support@quickbite.com<br>info@quickbite.com</p>
          </div>
        </div>

        <div class="info-item">
          <div class="icon">📞</div>
          <div>
            <h3>Phone</h3>
            <p>+60 12-345-6789<br>+60 12-987-6543</p>
          </div>
        </div>

        <div class="info-item">
          <div class="icon">🕒</div>
          <div>
            <h3>Business Hours</h3>
            <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM<br>Sunday: Closed</p>
          </div>
        </div>
      </div>

      <div class="contact-form">
        <h2 style="color: #ff4d4d; font-size: 1.8em; margin-bottom: 25px; font-weight: 700;">Send us a Message</h2>
        
        <form method="POST" action="">
          <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
          </div>

          <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
          </div>

          <div class="form-group">
            <label for="subject">Subject *</label>
            <input type="text" id="subject" name="subject" required value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>">
          </div>

          <div class="form-group">
            <label for="message">Message *</label>
            <textarea id="message" name="message" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
          </div>

          <button type="submit" name="submit_contact" class="btn-submit">Send Message</button>
        </form>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>

</body>
</html>

