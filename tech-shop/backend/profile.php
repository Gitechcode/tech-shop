<?php
$page_title = 'My Profile';
include 'includes/header.php';
requireAdmin();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    showAlert('User not found', 'danger');
    redirect(BACKEND_URL . '/index.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile
    if (isset($_POST['update_profile'])) {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        
        // Check if email exists (if changed)
        if ($email !== $user['email']) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already exists. Please choose a different one.';
            }
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$name, $email, $phone, $user_id])) {
                // Update session variables
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $success = true;
                showAlert('Profile updated successfully', 'success');
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            } else {
                $errors[] = 'Error updating profile';
            }
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = 'All password fields are required';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters long';
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            if ($stmt->execute([$password_hash, $user_id])) {
                $success = true;
                showAlert('Password changed successfully', 'success');
            } else {
                $errors[] = 'Error changing password';
            }
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Profile</h1>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="text-muted mb-3">Administrator</p>
                    
                    <div class="d-flex justify-content-center">
                        <span class="badge bg-success me-2">Active</span>
                        <span class="badge bg-primary">Admin</span>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="row text-center">
                        <div class="col">
                            <h5 class="mb-0"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></h5>
                            <small class="text-muted">Joined Date</small>
                        </div>
                        <div class="col">
                            <h5 class="mb-0"><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'N/A'; ?></h5>
                            <small class="text-muted">Last Login</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Role:</td>
                            <td>Administrator</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Edit Profile -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Password must be at least 6 characters long</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.avatar-circle {
    background-color: #6c757d;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
