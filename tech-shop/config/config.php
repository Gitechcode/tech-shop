<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 3600,
        'cookie_httponly' => true,
        'cookie_secure' => false,
        'use_strict_mode' => true
    ]);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tech_shop');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'TechShop');
define('SITE_URL', 'http://localhost/tech-shop');
define('FRONTEND_URL', 'http://localhost/tech-shop/frontend');
define('BACKEND_URL', 'http://localhost/tech-shop/backend');

// Development mode
define('DEVELOPMENT', true);

// Include database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    if (DEVELOPMENT) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . FRONTEND_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        header('Location: ' . BACKEND_URL . '/login.php');
        exit;
    }
    if (!isAdmin()) {
        header('Location: ' . BACKEND_URL . '/login.php?error=access_denied');
        exit;
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">';
        echo $alert['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['alert']);
    }
}

function generateOrderNumber() {
    return 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Set timezone
date_default_timezone_set('America/New_York');
?>
