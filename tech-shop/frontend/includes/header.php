<?php
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Swiper CSS -->
    <link href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo FRONTEND_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center" href="<?php echo FRONTEND_URL; ?>">
                    <div class="logo-icon me-2">
                        <i class="fas fa-laptop text-primary"></i>
                    </div>
                    <span class="fw-bold text-dark"><?php echo SITE_NAME; ?></span>
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo FRONTEND_URL; ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo FRONTEND_URL; ?>/shop.php">Shop</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Categories
                            </a>
                            <ul class="dropdown-menu">
                                <?php
                                $stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
                                while ($category = $stmt->fetch()) {
                                    echo "<li><a class='dropdown-item' href='" . FRONTEND_URL . "/shop.php?category=" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</a></li>";
                                }
                                ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo FRONTEND_URL; ?>/about.php">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo FRONTEND_URL; ?>/contact.php">Contact</a>
                        </li>
                    </ul>

                    <!-- Search Form -->
                    <form class="d-flex me-3" method="GET" action="<?php echo FRONTEND_URL; ?>/shop.php">
                        <div class="input-group">
                            <input class="form-control" type="search" name="search" placeholder="Search products..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Right Side Actions -->
                    <div class="d-flex align-items-center">
                        <!-- Cart -->
                        <a href="<?php echo FRONTEND_URL; ?>/cart.php" class="btn btn-outline-primary position-relative me-3">
                            <i class="fas fa-shopping-cart"></i>
                            <?php 
                            $cart_count = getCartCount();
                            if ($cart_count > 0): 
                            ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cart_count; ?>
                            </span>
                            <?php endif; ?>
                        </a>

                        <!-- User Menu -->
                        <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo FRONTEND_URL; ?>/profile.php">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo FRONTEND_URL; ?>/orders.php">
                                    <i class="fas fa-box me-2"></i>My Orders
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo FRONTEND_URL; ?>/wishlist.php">
                                    <i class="fas fa-heart me-2"></i>Wishlist
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo FRONTEND_URL; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                        <?php else: ?>
                        <a href="<?php echo FRONTEND_URL; ?>/login.php" class="btn btn-primary me-2">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a href="<?php echo FRONTEND_URL; ?>/register.php" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Alert Messages -->
    <div class="container mt-3">
        <?php displayAlert(); ?>
    </div>
