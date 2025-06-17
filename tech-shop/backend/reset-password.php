<?php
$page_title = 'Reset Password';
include 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn() && isAdmin()) {
    redirect(BACKEND_URL . '/index.php');
}

$error = '';
$success = '';
$valid_token = false;
$email = '';

// Check token
if (isset($_GET['token'])) {
    $token = sanitizeInput($_GET['token']);
    
    $stmt = $pdo->prepare("
        SELECT * FROM password_resets 
        WHERE token = ? AND expires_at > NOW() 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if ($reset) {
        $valid_token = true;
        $email = $reset['email'];
    } else {
        $error = 'Invalid or expired token. Please request a new password reset.';
    }
} else {
    redirect(BACKEND_URL . '/forgot-password.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please enter both password fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Update user password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
        if ($stmt->execute([$password_hash, $email])) {
            // Delete used token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = 'Your password has been reset successfully. You can now login with your new password.';
        } else {
            $error = 'Error resetting password. Please try again.';
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h1 class="h3">Reset Password</h1>
                        <p class="text-muted">Enter your new password</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                    <?php elseif ($valid_token): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Password must be at least 6 characters long</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
