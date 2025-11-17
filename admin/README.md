# Admin Pages - QuickBite

## How to View the Admin Pages

### Step 1: Start XAMPP
1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL** services

### Step 2: Access the Admin Pages

#### Option A: Direct URL Access
Open your browser and navigate to:
- **Dashboard**: `http://localhost/quickbite/admin/dashboard.php`
- **Manage Orders**: `http://localhost/quickbite/admin/manageorders.php`
- **Manage Restaurants**: `http://localhost/quickbite/admin/managerestaurant.php`

**Note:** You'll be redirected to login if you're not authenticated as an admin.

#### Option B: Login First (Recommended)
1. Go to: `http://localhost/quickbite/login.php`
2. Login with an **admin account**
3. You'll be automatically redirected to the admin dashboard

### Step 3: Create an Admin Account (If Needed)

If you don't have an admin account, you can create one using one of these methods:

#### Method 1: Using phpMyAdmin
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select the `quickbite_db` database
3. Go to the `users` table
4. Click "Insert" and add a new user with:
   - `name`: Your name
   - `email`: Your email
   - `password`: Use this SQL to hash your password: `SELECT PASSWORD('yourpassword')` or use PHP's `password_hash()`
   - `role`: `admin`

#### Method 2: Using SQL Query
Run this SQL in phpMyAdmin (replace with your details):

```sql
INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@quickbite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

**Default password for above:** `password`

**Or use this PHP script to create an admin:**

```php
<?php
include 'db.php';
$name = "Admin";
$email = "admin@quickbite.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password, $role);
$stmt->execute();
echo "Admin created!";
?>
```

### Step 4: Login and Access
1. Go to `http://localhost/quickbite/login.php`
2. Enter your admin email and password
3. You'll be redirected to the admin dashboard automatically

## Admin Pages Overview

### 📊 Dashboard (`dashboard.php`)
- View statistics (restaurants, customers, orders, revenue)
- See recent orders
- View orders by status breakdown

### 📦 Manage Orders (`manageorders.php`)
- View all orders with filtering options
- See detailed order information
- Update order status (Pending, Preparing, Delivering, Completed)

### 🍽️ Manage Restaurants (`managerestaurant.php`)
- Add new restaurants
- Edit existing restaurants
- Delete restaurants
- View restaurant statistics

## Troubleshooting

**Problem:** Redirected to login page
- **Solution:** Make sure you're logged in with an admin account (role = 'admin')

**Problem:** Database connection error
- **Solution:** Check that MySQL is running in XAMPP and the database `quickbite_db` exists

**Problem:** Page shows errors
- **Solution:** Check that all required database tables exist (users, restaurants, orders, order_details, menu_items)


