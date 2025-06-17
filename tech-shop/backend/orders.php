<?php
$page_title = 'Orders';
include 'includes/header.php';

requireAdmin();

$success = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $order_id = (int)$_POST['order_id'];
        $status = sanitizeInput($_POST['status']);
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            $success = 'Order status updated successfully!';
            
            // Log the action
            $stmt = $pdo->prepare("
                INSERT INTO admin_logs (admin_id, action, table_name, record_id, new_values, created_at) 
                VALUES (?, 'update_order_status', 'orders', ?, ?, NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $order_id, json_encode(['status' => $status])]);
        } else {
            $error = 'Error updating order status.';
        }
    }
}

// Get orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_clause = "1=1";
$params = [];

// Apply filters
if (isset($_GET['status']) && $_GET['status'] != '') {
    $where_clause .= " AND o.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['search']) && $_GET['search'] != '') {
    $where_clause .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// Get total count
$count_sql = "SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.id WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_orders = $count_stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Get orders
$sql = "
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE $where_clause 
    ORDER BY o.created_at DESC 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Orders</h1>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                               placeholder="Order number, customer name or email">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo (isset($_GET['status']) && $_GET['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="orders.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Orders (<?php echo $total_orders; ?> total)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($order['order_number']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="font-weight-bold"><?php echo htmlspecialchars($order['user_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                            </td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo getPaymentStatusColor($order['payment_status']); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                                <br><small class="text-muted"><?php echo ucfirst($order['payment_method']); ?></small>
                            </td>
                            <td>
                                <select class="form-select form-select-sm" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn btn-primary btn-sm" onclick="printOrder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Orders pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function updateOrderStatus(orderId, status) {
    if (confirm('Are you sure you want to update this order status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" value="${orderId}">
            <input type="hidden" name="status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    } else {
        // Reset the select to original value
        location.reload();
    }
}

function printOrder(orderId) {
    window.open(`print-order.php?id=${orderId}`, '_blank');
}

// Initialize DataTable
$(document).ready(function() {
    $('#dataTable').DataTable({
        "paging": false,
        "searching": false,
        "info": false,
        "ordering": true
    });
});
</script>

<?php
function getPaymentStatusColor($status) {
    switch ($status) {
        case 'paid': return 'success';
        case 'pending': return 'warning';
        case 'failed': return 'danger';
        case 'refunded': return 'info';
        default: return 'secondary';
    }
}
?>
