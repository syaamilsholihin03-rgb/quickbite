<?php
session_start();
include 'db.php';
$signup_error = '';
$signup_success = false;

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if ($name === '' || $email === '' || $password === '') {
        $signup_error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $signup_error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $signup_error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $signup_error = "Email already registered. Please login instead.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $phone, $address);
            
            if ($stmt->execute()) {
                $signup_success = true;
                $signup_error = '';
            } else {
                $signup_error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - QuickBite</title>
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

    .auth-container {
      display: flex;
      justify-content: center;
      align-items: center;
      flex: 1;
      padding: 40px 20px;
    }

    .auth-box {
      background: rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(15px);
      border-radius: 25px;
      padding: 50px 45px;
      max-width: 550px;
      width: 100%;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .auth-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .auth-header h1 {
      font-size: 2.5em;
      font-weight: 700;
      background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
    }

    .auth-header p {
      color: #666;
      font-size: 1.1em;
      margin: 0;
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

    .form-group label .required {
      color: #ff4d4d;
    }

    .form-group input,
    .form-group textarea {
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

    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #ff4d4d;
      box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.1);
      background: white;
    }

    .btn-primary {
      width: 100%;
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
      margin-top: 10px;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 77, 77, 0.4);
    }

    .btn-primary:active {
      transform: translateY(0);
    }

    .alert {
      padding: 15px 20px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-weight: 500;
    }

    .alert-error {
      background: #fee;
      color: #c33;
      border: 1px solid #fcc;
    }

    .alert-success {
      background: #efe;
      color: #3c3;
      border: 1px solid #cfc;
    }

    .auth-footer {
      text-align: center;
      margin-top: 30px;
      padding-top: 25px;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .auth-footer p {
      color: #666;
      margin: 0;
    }

    .auth-footer a {
      color: #ff4d4d;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .auth-footer a:hover {
      color: #ff6b6b;
      text-decoration: underline;
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

      .auth-box {
        padding: 35px 25px;
      }

      .auth-header h1 {
        font-size: 2em;
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

  <div class="auth-container">
    <div class="auth-box">
      <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join QuickBite and start ordering delicious meals</p>
      </div>

      <?php if ($signup_error): ?>
        <div class="alert alert-error">
          <?php echo htmlspecialchars($signup_error); ?>
        </div>
      <?php endif; ?>

      <?php if ($signup_success): ?>
        <div class="alert alert-success">
          Registration successful! Redirecting to login...
        </div>
        <script>
          setTimeout(function() {
            window.location.href = 'login.php';
          }, 2000);
        </script>
      <?php else: ?>
        <form method="POST" action="">
          <div class="form-group">
            <label for="name">Full Name <span class="required">*</span></label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
          </div>

          <div class="form-group">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          </div>

          <div class="form-group">
            <label for="password">Password <span class="required">*</span></label>
            <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
          </div>

          <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" placeholder="Enter your delivery address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
          </div>

          <button type="submit" name="register" class="btn-primary">Create Account</button>
        </form>
      <?php endif; ?>

      <div class="auth-footer">
        <p>Already have an account? <a href="login.php">Sign in here</a></p>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> QuickBite. All rights reserved.</p>
  </footer>

</body>
</html>
