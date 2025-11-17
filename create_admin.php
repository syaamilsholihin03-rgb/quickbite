<?php
/**
 * Quick Admin User Creator
 * Run this file once to create an admin user
 * Then delete this file for security
 */

include 'db.php';

$admin_email = 'admin@quickbite.com';
$admin_password = 'admin123'; // Change this!
$admin_name = 'Admin User';

// Check if admin already exists
$check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND role = 'admin'");
$check->bind_param("s", $admin_email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "<h2>Admin user already exists!</h2>";
    echo "<p>Email: <strong>$admin_email</strong></p>";
    echo "<p>You can login with this account.</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
} else {
    // Create admin user
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    $role = 'admin';
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $admin_name, $admin_email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✅ Admin user created successfully!</h2>";
        echo "<h3>Login Credentials:</h3>";
        echo "<p><strong>Email:</strong> $admin_email</p>";
        echo "<p><strong>Password:</strong> $admin_password</p>";
        echo "<p><strong>Role:</strong> admin</p>";
        echo "<hr>";
        echo "<p><strong>⚠️ Important:</strong> Please change the password after first login!</p>";
        echo "<p><a href='login.php' style='display: inline-block; padding: 10px 20px; background: #ff4d4d; color: white; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
    } else {
        echo "<h2 style='color: red;'>❌ Error creating admin user</h2>";
        echo "<p>" . $conn->error . "</p>";
    }
    $stmt->close();
}

$check->close();
?>


