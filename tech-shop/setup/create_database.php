<?php
// Create database and tables
try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS tech_shop");
    $pdo->exec("USE tech_shop");
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create products table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category_id INT,
            image VARCHAR(255),
            stock_quantity INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )
    ");
    
    // Create orders table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    
    // Insert admin user
    $admin_password = password_hash('password', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT IGNORE INTO users (name, email, password, role) 
        VALUES ('Admin', 'admin@techshop.com', '$admin_password', 'admin')
    ");
    
    // Insert test user
    $user_password = password_hash('password', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT IGNORE INTO users (name, email, password, role) 
        VALUES ('John Doe', 'john@example.com', '$user_password', 'user')
    ");
    
    // Insert sample categories
    $pdo->exec("
        INSERT IGNORE INTO categories (id, name, description) VALUES 
        (1, 'Laptops', 'High-performance laptops for work and gaming'),
        (2, 'Smartphones', 'Latest smartphones with cutting-edge technology'),
        (3, 'Accessories', 'Tech accessories and peripherals')
    ");
    
    // Insert sample products
    $pdo->exec("
        INSERT IGNORE INTO products (name, description, price, category_id, stock_quantity) VALUES 
        ('MacBook Pro 16\"', 'Powerful laptop for professionals', 2499.99, 1, 10),
        ('iPhone 15 Pro', 'Latest iPhone with advanced features', 999.99, 2, 25),
        ('Dell XPS 13', 'Ultrabook with premium design', 1299.99, 1, 15),
        ('Samsung Galaxy S24', 'Android flagship smartphone', 899.99, 2, 20),
        ('Wireless Mouse', 'Ergonomic wireless mouse', 29.99, 3, 50),
        ('USB-C Hub', 'Multi-port USB-C hub', 49.99, 3, 30),
        ('Gaming Laptop', 'High-performance gaming laptop', 1899.99, 1, 8),
        ('Bluetooth Headphones', 'Noise-cancelling headphones', 199.99, 3, 40)
    ");
    
    echo "✅ Database and tables created successfully!<br>";
    echo "✅ Admin user created: admin@techshop.com / password<br>";
    echo "✅ Test user created: john@example.com / password<br>";
    echo "✅ Sample data inserted<br><br>";
    echo "<a href='../backend/login.php' class='btn btn-primary'>Go to Admin Panel</a> ";
    echo "<a href='../frontend/index.php' class='btn btn-success'>Go to Website</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1>TechShop Database Setup</h1>
</body>
</html>
