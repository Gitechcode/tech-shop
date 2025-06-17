<?php
$page_title = 'Edit User';
include 'includes/header.php';
requireAdmin();

$user = [
    'id' => 0,
    'name' => '',
    'email' => '',
    'phone' => '',
    'role' => 'user',
    'status' => 'active',
    'created_at' => '',
    'updated_at' => ''
];

$errors = [];
$success = false;

// Edit existing user
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $db_user = $stmt->fetch();
    
    if ($db_user) {
        $user = array_merge($user, $db_user);
        $page_title = 'Edit User: ' . $user['name'];
    } else {
        showAlert('User not found', 'danger');
        redirect(BACKEND_URL . '/users.php');
    }
} else {
    $page_title = 'Add New User';
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['name'])) {
        $errors[] = 'Name is required';
    }
    
    if (empty($_POST['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } else {
        // Check if email exists (for new users or when changing email)
        if ($user['id'] === 0 || $_POST['email'] !== $user['email']) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already exists. Please choose a different one.';
            }
        }
    }
    
    // Validate password for new users or when changing password
    if ($user['id'] === 0 || !empty($_POST['password'])) {
        if (empty($_POST['password'])) {
            $errors[] = 'Password is required for new users';
        } elseif (strlen($_POST['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }
    }
    
    // If no errors, save user
    if (empty($errors)) {
        try {
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'email' => sanitizeInput($_POST['email']),
                'phone' => sanitizeInput($_POST['phone']),
                'role' => sanitizeInput($_POST['role']),
                'status' => sanitizeInput($_POST['status'])
            ];
            
            if ($user['id'] > 0) {
                // Update existing user
                $sql = "UPDATE users SET 
                        name = :name, 
                        email = :email, 
                        phone = :phone, 
                        role = :role, 
                        status = :status, 
                        updated_at = NOW() 
                        WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $data['id'] = $user['id'];
                
                // Update password if provided
                if (!empty($_POST['password'])) {
                    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$password_hash, $user['id']]);
                }
            } else {
                // Insert new user
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (
                        name, email, phone, password, role, status, created_at, updated_at
                    ) VALUES (
                        :name, :email, :phone, :password, :role, :status, NOW(), NOW()
                    )";
                
                $stmt = $pdo->prepare($sql);
                $data['password'] = $password_hash;
            }
            
            $stmt->execute($data);
            
            if ($user['id'] === 0) {
                $user_id = $pdo->lastInsertId();
                showAlert('User created successfully', 'success');
                redirect(BACKEND_URL . '/user-edit.php?id=' . $user_id);
            } else {
                $success = true;
                showAlert('User updated successfully', 'success');
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $user = $stmt->fetch();
            }
        } catch (Exception $e) {
            $errors[] = 'Error saving user: ' . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
        <a href="users.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
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
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <?php echo $user['id'] > 0 ? 'Password (leave blank to keep current)' : 'Password *'; ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   <?php echo $user['id'] === 0 ? 'required' : ''; ?>>
                            <?php if ($user['id'] === 0): ?>
                            <small class="text-muted">Password must be at least 6 characters long</small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i><?php echo $user['id'] > 0 ? 'Update User' : 'Create User'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($user['id'] > 0): ?>
        <div class="col-lg-4">
            <!-- User Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">User Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="avatar-circle me-3">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h5>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Registered:</td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Updated:</td>
                            <td><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- User Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">User Activity</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get recent orders
                    $stmt = $pdo->prepare("
                        SELECT * FROM orders 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([$user['id']]);
                    $recent_orders = $stmt->fetchAll();
                    ?>
                    
                    <h6>Recent Orders</h6>
                    <?php if (empty($recent_orders)): ?>
                    <p class="text-muted">No orders found for this user.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush mb-4">
                        <?php foreach ($recent_orders as $order): ?>
                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    <small class="d-block text-muted">
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-primary"><?php echo formatPrice($order['total_amount']); ?></span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <a href="orders.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-shopping-cart me-1"></i>View All Orders
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    background-color: #6c757d;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
}
</style>
