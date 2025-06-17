<?php
$page_title = 'Order Details';
include 'includes/header.php';
requireAdmin();

if (!isset($_GET['id'])) {
    redirect(BACKEND_URL . '/orders.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    showAlert('Order not found', 'danger');
    redirect(BACKEND_URL . '/orders.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$order_items = $stmt->fetchAll();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = sanitizeInput($_POST['status']);
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            // Update order status in our local variable
            $order['status'] = $status;
            
            // Add status update to order history
            $stmt = $pdo->prepare("
                INSERT INTO order_history (order_id, status, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
            $stmt->execute([$id, $status, $notes, $_SESSION['user_id']]);
            
            showAlert('Order status updated successfully', 'success');
        } else {
            showAlert('Error updating order status', 'danger');
        }
    } else {
        showAlert('Invalid status', 'danger');
    }
}

// Get order history
$stmt = $pdo->prepare("
    SELECT oh.*, u.name as user_name
    FROM order_history oh
    LEFT JOIN users u ON oh.created_by = u.id
    WHERE oh.order_id = ?
    ORDER BY oh.created_at DESC
");
$stmt->execute([$id]);
$order_history = $stmt->fetchAll();

// Get shipping address
$shipping_address = json_decode($order['shipping_address'], true);

// Get billing address
$billing_address = json_decode($order['billing_address'], true);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
        <div>
            <a href="orders.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Back to Orders
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary" onclick="printOrder()">
                    <i class="fas fa-print me-1"></i>Print
                </button>
                <button type="button" class="btn btn-outline-info" onclick="emailInvoice()">
                    <i class="fas fa-envelope me-1"></i>Email Invoice
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Image</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo SITE_URL . '/' . ($item['product_image'] ?: '/placeholder.svg?height=50&width=50'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <?php if (!empty($item['options'])): ?>
                                        <small class="text-muted">
                                            <?php 
                                            $options = json_decode($item['options'], true);
                                            if (is_array($options)) {
                                                foreach ($options as $key => $value) {
                                                    echo htmlspecialchars(ucfirst($key) . ': ' . $value) . '<br>';
                                                }
                                            }
                                            ?>
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['total']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end">Subtotal:</td>
                                    <td><?php echo formatPrice($order['subtotal']); ?></td>
                                </tr>
                                <?php if ($order['discount_amount'] > 0): ?>
                                <tr>
                                    <td colspan="4" class="text-end">Discount:</td>
                                    <td>-<?php echo formatPrice($order['discount_amount']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td colspan="4" class="text-end">Shipping:</td>
                                    <td><?php echo formatPrice($order['shipping_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end">Tax:</td>
                                    <td><?php echo formatPrice($order['tax_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="fw-bold"><?php echo formatPrice($order['total_amount']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order Notes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($order['notes'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                    <?php else: ?>
                    <p class="text-muted">No notes for this order.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Updated By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($order_history)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No history found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($order_history as $history): ?>
                                <tr>
                                    <td><?php echo date('M d, Y h:i A', strtotime($history['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($history['status']) {
                                            case 'pending':
                                                $status_class = 'bg-warning';
                                                break;
                                            case 'processing':
                                                $status_class = 'bg-info';
                                                break;
                                            case 'shipped':
                                                $status_class = 'bg-primary';
                                                break;
                                            case 'delivered':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($history['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($history['notes'] ?: 'No notes'); ?></td>
                                    <td><?php echo htmlspecialchars($history['user_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <?php
                            $status_class = '';
                            switch ($order['status']) {
                                case 'pending':
                                    $status_class = 'bg-warning';
                                    break;
                                case 'processing':
                                    $status_class = 'bg-info';
                                    break;
                                case 'shipped':
                                    $status_class = 'bg-primary';
                                    break;
                                case 'delivered':
                                    $status_class = 'bg-success';
                                    break;
                                case 'cancelled':
                                    $status_class = 'bg-danger';
                                    break;
                            }
                            ?>
                            <div>
                                <span class="badge <?php echo $status_class; ?> fs-6">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Update Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Status Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Optional notes about this status update"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted">Order Number:</td>
                            <td class="fw-medium"><?php echo htmlspecialchars($order['order_number']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date:</td>
                            <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Payment Method:</td>
                            <td><?php echo ucfirst($order['payment_method']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Payment Status:</td>
                            <td>
                                <span class="badge bg-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php if (!empty($order['transaction_id'])): ?>
                        <tr>
                            <td class="text-muted">Transaction ID:</td>
                            <td><?php echo htmlspecialchars($order['transaction_id']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($order['coupon_code'])): ?>
                        <tr>
                            <td class="text-muted">Coupon Code:</td>
                            <td><?php echo htmlspecialchars($order['coupon_code']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-circle me-3">
                            <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($order['customer_name']); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($order['customer_phone'])): ?>
                    <div class="mb-3">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <a href="users.php?id=<?php echo $order['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-user me-1"></i>View Customer Profile
                    </a>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Shipping Address</h5>
                </div>
                <div class="card-body">
                    <?php if ($shipping_address): ?>
                    <address class="mb-0">
                        <?php echo htmlspecialchars($shipping_address['name']); ?><br>
                        <?php echo htmlspecialchars($shipping_address['address_line1']); ?><br>
                        <?php if (!empty($shipping_address['address_line2'])): ?>
                        <?php echo htmlspecialchars($shipping_address['address_line2']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($shipping_address['city']); ?>, 
                        <?php echo htmlspecialchars($shipping_address['state']); ?> 
                        <?php echo htmlspecialchars($shipping_address['postal_code']); ?><br>
                        <?php echo htmlspecialchars($shipping_address['country']); ?>
                    </address>
                    <?php else: ?>
                    <p class="text-muted mb-0">No shipping address provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Billing Address</h5>
                </div>
                <div class="card-body">
                    <?php if ($billing_address): ?>
                    <address class="mb-0">
                        <?php echo htmlspecialchars($billing_address['name']); ?><br>
                        <?php echo htmlspecialchars($billing_address['address_line1']); ?><br>
                        <?php if (!empty($billing_address['address_line2'])): ?>
                        <?php echo htmlspecialchars($billing_address['address_line2']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($billing_address['city']); ?>, 
                        <?php echo htmlspecialchars($billing_address['state']); ?> 
                        <?php echo htmlspecialchars($billing_address['postal_code']); ?><br>
                        <?php echo htmlspecialchars($billing_address['country']); ?>
                    </address>
                    <?php else: ?>
                    <p class="text-muted mb-0">No billing address provided.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    background-color: #6c757d;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>

<script>
// Print order
function printOrder() {
    window.print();
}

// Email invoice
function emailInvoice() {
    // In a real implementation, this would send an invoice email to the customer
    alert('Sending invoice email to <?php echo htmlspecialchars($order['customer_email']); ?>');
}
</script>
