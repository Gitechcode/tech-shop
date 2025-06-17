<?php
session_start();

// Database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=tech_shop;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get featured products
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 8");
    $products = $stmt->fetchAll();
} catch(Exception $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop - Your Technology Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-laptop text-primary me-2"></i>
                <span class="fw-bold">TechShop</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <a href="cart.php" class="btn btn-outline-primary me-3">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge bg-danger">0</span>
                    </a>

                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-primary me-2">Login</a>
                    <a href="register.php" class="btn btn-outline-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Welcome to TechShop</h1>
                    <p class="lead mb-4">Discover the latest technology products at unbeatable prices. From laptops to smartphones, we have everything you need.</p>
                    <a href="shop.php" class="btn btn-light btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                </div>
                <div class="col-lg-6">
                    <img src="https://via.placeholder.com/500x300/4e73df/ffffff?text=TechShop" class="img-fluid rounded" alt="TechShop">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Featured Products</h2>
                <p class="text-muted">Check out our latest and most popular products</p>
            </div>

            <div class="row">
                <?php if (empty($products)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No products available at the moment.</p>
                    <a href="../backend/login.php" class="btn btn-primary">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </a>
                </div>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo $product['image'] ?: 'https://via.placeholder.com/300x200/f8f9fa/6c757d?text=Product'; ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary mb-0">$<?php echo number_format($product['price'], 2); ?></span>
                                <button class="btn btn-primary btn-sm">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-laptop me-2"></i>TechShop</h5>
                    <p>Your trusted technology store since 2024.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 TechShop. All rights reserved.</p>
                    <a href="../backend/login.php" class="text-white-50">Admin Panel</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
