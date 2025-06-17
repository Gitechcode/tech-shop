<?php
$page_title = 'Forgot Password';
include 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn() && isAdmin()) {
    redirect(BACKEND_URL . '/index.php');
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (email, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$email, $token, $expires]);
            
            // In a real application, send an email with the reset link
            // For now, just show a success message
            $reset_link = BACKEND_URL . '/reset-password.php?token=' . $token;
            
            $success = 'Password reset instructions have been sent to your email address.';
        } else {
            $error = 'No admin account found with that email address';
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
                        <h1 class="h3">Forgot Password</h1>
                        <p class="text-muted">Enter your email address to reset your password</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <?php if (isset($reset_link)): ?>
                        <p class="mt-2 mb-0">
                            <strong>Demo Link:</strong> <a href="<?php echo $reset_link; ?>"><?php echo $reset_link; ?></a>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
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
            
            <div class="text-center mt-3">
                <a href="../index.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to Store
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
