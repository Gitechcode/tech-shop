<?php
require_once '../config/config.php';

// Force authentication check
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - <?php echo SITE_NAME; ?></title>

    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 1rem 1.5rem;
            border-radius: 0;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 16px;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }
        
        .topbar {
            background: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .text-primary {
            color: #4e73df !important;
        }
        
        .text-success {
            color: #1cc88a !important;
        }
        
        .text-info {
            color: #36b9cc !important;
        }
        
        .text-warning {
            color: #f6c23e !important;
        }
        
        .badge-success {
            background-color: #1cc88a;
        }
        
        .badge-warning {
            background-color: #f6c23e;
        }
        
        .badge-danger {
            background-color: #e74a3b;
        }
        
        .badge-info {
            background-color: #36b9cc;
        }
        
        .badge-secondary {
            background-color: #858796;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
        
        .font-weight-bold {
            font-weight: 700 !important;
        }
        
        .text-xs {
            font-size: 0.7rem;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="text-center py-4">
                <h4 class="text-white mb-0">
                    <i class="fas fa-laptop me-2"></i><?php echo SITE_NAME; ?>
                </h4>
                <small class="text-white-50">Admin Panel</small>
            </div>
            
            <hr class="sidebar-divider my-0" style="border-color: rgba(255,255,255,0.15);">
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                       href="index.php">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
                       href="products.php">
                        <i class="fas fa-fw fa-box"></i>
                        Products
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" 
                       href="categories.php">
                        <i class="fas fa-fw fa-tags"></i>
                        Categories
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" 
                       href="orders.php">
                        <i class="fas fa-fw fa-shopping-cart"></i>
                        Orders
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
                       href="users.php">
                        <i class="fas fa-fw fa-users"></i>
                        Users
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" 
                       href="settings.php">
                        <i class="fas fa-fw fa-cog"></i>
                        Settings
                    </a>
                </li>
                
                <hr class="sidebar-divider" style="border-color: rgba(255,255,255,0.15);">
                
                <li class="nav-item">
                    <a class="nav-link" href="../frontend/index.php" target="_blank">
                        <i class="fas fa-fw fa-external-link-alt"></i>
                        View Website
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1">
            <!-- Topbar -->
            <div class="topbar d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                <div class="d-flex align-items-center">
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="container-fluid px-4">
                <?php displayAlert(); ?>
