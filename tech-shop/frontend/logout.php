<?php
require_once '../config/config.php';

// Destroy session
session_start();
session_unset();
session_destroy();

// Clear any remember me cookies if they exist
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page with success message
header('Location: ' . FRONTEND_URL . '/?logged_out=1');
exit;
?>
