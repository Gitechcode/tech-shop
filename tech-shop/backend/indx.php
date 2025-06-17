<?php
$page_title = 'Dashboard';
include 'includes/header.php';

// Get dashboard statistics
$stats = [];

try {
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $stats['products'] = $stmt->fetchColumn();

    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $stats['orders'] = $stmt->fetchColumn();

    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stats['users'] = $stmt->fetchColumn();

    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
    $stats['revenue'] = $stmt->fetchColumn() ?: 0;

    // Recent orders
    $stmt = $pdo->query("
        SELECT o.*, u.name as user_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll();

    // Low stock products
    $stmt = $pdo->query("
        SELECT * FROM products 
        WHERE stock <= 10 AND status = 'active' 
        ORDER BY stock ASC 
        LIMIT 5
    ");
    $low_stock = $stmt->fetchAll();

    // Today's sales
    $stmt = $pdo->query("
        SELECT COUNT(*) as orders_today, COALESCE(SUM(total_amount), 0) as sales_today
        FROM orders 
        WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'
    ");
    $today_stats = $stmt->fetch();
    $stats['orders_today'] = $today_stats['orders_today'];
    $stats['sales_today'] = $today_stats['sales_today'];

} catch(Exception $e) {
    // Set default values if queries fail
    $stats = [
        'products' => 0,
        'orders' => 0,
        'users' => 0,
        'revenue' => 0,
        'orders_today' => 0,
        'sales_today' => 0
    ];
    $recent_orders = [];
    $low_stock = [];
}

function getStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Products
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['products']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Orders
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['orders']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Users
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['users']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total Revenue
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatPrice($stats['revenue']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Stats -->
<div class="row mb-4">
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Today's Orders
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($stats['orders_today']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Today's Sales
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo formatPrice($stats['sales_today']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="products.php" class="btn btn-primary btn-block">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="categories.php" class="btn btn-success btn-block">
                            <i class="fas fa-tags me-2"></i>Manage Categories
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="orders.php" class="btn btn-info btn-block">
                            <i class="fas fa-shopping-cart me-2"></i>View Orders
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="users.php" class="btn btn-warning btn-block">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                <p class="text-muted text-center py-4">No orders found.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo getStatusColor($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">Low Stock Alert</h6>
            </div>
            <div class="card-body">
                <?php if (empty($low_stock)): ?>
                <p class="text-muted">All products are well stocked!</p>
                <?php else: ?>
                <?php foreach ($low_stock as $product): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="mr-3">
                        <img src="<?php echo SITE_URL . '/' . ($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                    </div>
                    <div class="flex-grow-1">
                        <div class="font-weight-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                        <small class="text-danger">Only <?php echo $product['stock']; ?> left</small>
                    </div>
                </div>
                <?php endforeach; ?>
                <a href="products.php?filter=low_stock" class="btn btn-danger btn-sm btn-block">
                    View All Low Stock
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
