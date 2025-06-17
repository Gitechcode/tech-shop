<?php
$page_title = 'Users Management';
include 'includes/header.php';

$success = '';
$error = '';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_status':
                    $user_id = (int)$_POST['user_id'];
                    $status = sanitizeInput($_POST['status']);
                    
                    if ($user_id == $_SESSION['user_id'] && $status !== 'active') {
                        $error = 'You cannot deactivate your own account.';
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
                        if ($stmt->execute([$status, $user_id])) {
                            $success = 'User status updated successfully!';
                        } else {
                            $error = 'Error updating user status.';
                        }
                    }
                    break;
                    
                case 'delete_user':
                    $user_id = (int)$_POST['user_id'];
                    
                    if ($user_id == $_SESSION['user_id']) {
                        $error = 'You cannot delete your own account.';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        if ($stmt->execute([$user_id])) {
                            $success = 'User deleted successfully!';
                        } else {
                            $error = 'Error deleting user.';
                        }
                    }
                    break;
                    
                case 'add_user':
                    $name = sanitizeInput($_POST['name']);
                    $email = sanitizeInput($_POST['email']);
                    $password = $_POST['password'];
                    $role = sanitizeInput($_POST['role']);
                    $status = sanitizeInput($_POST['status']);
                    
                    if (empty($name) || empty($email) || empty($password)) {
                        $error = 'All fields are required.';
                    } else {
                        // Check if email already exists
                        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $error = 'Email already exists.';
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                            if ($stmt->execute([$name, $email, $hashed_password, $role, $status])) {
                                $success = 'User added successfully!';
                            } else {
                                $error = 'Error adding user.';
                            }
                        }
                    }
                    break;
            }
        }
    } catch(Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_clause = "1=1";
$params = [];

// Apply filters
if (isset($_GET['role']) && $_GET['role'] != '') {
    $where_clause .= " AND role = ?";
    $params[] = $_GET['role'];
}

if (isset($_GET['status']) && $_GET['status'] != '') {
    $where_clause .= " AND status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['search']) && $_GET['search'] != '') {
    $where_clause .= " AND (name LIKE ? OR email LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
}

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM users WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_users = $count_stmt->fetchColumn();
    $total_pages = ceil($total_users / $per_page);

    // Get users
    $sql = "SELECT * FROM users WHERE $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    // Get user statistics
    $stats = [];
    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    while ($row = $stmt->fetch()) {
        $stats[$row['role']] = $row['count'];
    }
} catch(Exception $e) {
    $users = [];
    $total_users = 0;
    $total_pages = 0;
    $stats = ['user' => 0, 'admin' => 0];
    $error = 'Database error: ' . $e->getMessage();
}
?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['user'] ?? 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Admins</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['admin'] ?? 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-friends fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                           placeholder="Name or email">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <option value="">All Roles</option>
                        <option value="user" <?php echo (isset($_GET['role']) && $_GET['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="banned" <?php echo (isset($_GET['status']) && $_GET['status'] == 'banned') ? 'selected' : ''; ?>>Banned</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="users.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Users (<?php echo $total_users; ?> total)</h6>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-1"></i>Add New User
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No users found</h4>
            <p class="text-muted">Try adjusting your search criteria</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['role'] == 'admin' ? 'warning' : 'info'; ?>">
                                <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'user'; ?> me-1"></i>
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <select class="form-select form-select-sm" onchange="updateUserStatus(<?php echo $user['id']; ?>, this.value)" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="banned" <?php echo $user['status'] == 'banned' ? 'selected' : ''; ?>>Banned</option>
                            </select>
                        </td>
                        <td>
                            <div><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                            <small class="text-muted"><?php echo date('g:i A', strtotime($user['created_at'])); ?></small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="viewUser(<?php echo $user['id']; ?>)" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Delete User">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="banned">Banned</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function updateUserStatus(userId, status) {
    if (confirm('Are you sure you want to update this user status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="user_id" value="${userId}">
            <input type="hidden" name="status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    } else {
        location.reload();
    }
}

function viewUser(userId) {
    alert('View user details - ID: ' + userId + '\n\nThis will show detailed user information including order history and activity.');
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone and will also delete all their orders and data.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
