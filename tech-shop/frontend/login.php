<?php
require_once '../config/config.php';

$page_title = 'Login';
include 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            // Check user credentials
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set remember me cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                }
                
                // Merge guest cart with user cart
                $session_id = session_id();
                $stmt = $pdo->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?");
                $stmt->execute([$user['id'], $session_id]);
                
                showAlert('Welcome back, ' . $user['name'] . '!', 'success');
                
                // Redirect to intended page or home
                $redirect_url = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect_url);
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch(Exception $e) {
            $error = 'Database error occurred.';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="logo-icon mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-laptop text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                            <a href="forgot-password.php" class="text-decoration-none">
                                Forgot password?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Don't have an account? 
                            <a href="register.php" class="text-decoration-none fw-bold">
                                Sign up here
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Demo Credentials -->
            <div class="card mt-3 border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">
                        <i class="fas fa-info-circle me-2"></i>Demo Credentials
                    </h6>
                    <p class="card-text small mb-2">
                        <strong>User:</strong> john@example.com / password<br>
                        <strong>Admin:</strong> admin@techshop.com / password
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>
