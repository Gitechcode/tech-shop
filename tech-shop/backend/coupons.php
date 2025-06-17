<?php
$page_title = 'Coupons';
include 'includes/header.php';
requireAdmin();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        if ($stmt->execute([$id])) {
            showAlert('Coupon deleted successfully', 'success');
        } else {
            showAlert('Error deleting coupon', 'danger');
        }
    } elseif ($_GET['action'] === 'toggle-status') {
        $stmt = $pdo->prepare("SELECT status FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        $current_status = $stmt->fetchColumn();
        
        $new_status = ($current_status === 'active') ? 'inactive' : 'active';
        
        $stmt = $pdo->prepare("UPDATE coupons SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $id])) {
            showAlert('Coupon status updated successfully', 'success');
        } else {
            showAlert('Error updating coupon status', 'danger');
        }
    }
    
    // Redirect to remove action from URL
    redirect(BACKEND_URL . '/coupons.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(sanitizeInput($_POST['code']));
    $type = sanitizeInput($_POST['type']);
    $value = (float)$_POST['value'];
    $min_purchase = !empty($_POST['min_purchase']) ? (float)$_POST['min_purchase'] : null;
    $max_discount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
    $start_date = sanitizeInput($_POST['start_date']);
    $end_date = !empty($_POST['end_date']) ? sanitizeInput($_POST['end_date']) : null;
    $usage_limit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
    $status = sanitizeInput($_POST['status']);
    
    // Check if code exists
    $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
    $stmt->execute([$code, isset($_POST['id']) ? (int)$_POST['id'] : 0]);
    if ($stmt->fetch()) {
        showAlert('Coupon code already exists', 'danger');
    } else {
        if (isset($_POST['id']) && $_POST['id'] > 0) {
            // Update existing coupon
            $stmt = $pdo->prepare("
                UPDATE coupons 
                SET code = ?, type = ?, value = ?, min_purchase = ?, max_discount = ?, 
                    start_date = ?, end_date = ?, usage_limit = ?, status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            if ($stmt->execute([$code, $type, $value, $min_purchase, $max_discount, $start_date, $end_date, $usage_limit, $status, (int)$_POST['id']])) {
                showAlert('Coupon updated successfully', 'success');
            } else {
                showAlert('Error updating coupon', 'danger');
            }
        } else {
            // Insert new coupon
            $stmt = $pdo->prepare("
                INSERT INTO coupons (code, type, value, min_purchase, max_discount, start_date, end_date, usage_limit, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            if ($stmt->execute([$code, $type, $value, $min_purchase, $max_discount, $start_date, $end_date, $usage_limit, $status])) {
                showAlert('Coupon created successfully', 'success');
            } else {
                showAlert('Error creating coupon', 'danger');
            }
        }
    }
}

// Get coupons
$stmt = $pdo->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM orders WHERE coupon_code = c.code) as usage_count
    FROM coupons c
    ORDER BY created_at DESC
");
$coupons = $stmt->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Coupons</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal">
            <i class="fas fa-plus me-2"></i>Add New Coupon
        </button>
    </div>

    <!-- Coupons Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Purchase</th>
                            <th>Validity</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">No coupons found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td>
                                <span class="badge bg-dark"><?php echo htmlspecialchars($coupon['code']); ?></span>
                            </td>
                            <td>
                                <?php if ($coupon['type'] === 'percentage'): ?>
                                <span class="badge bg-info">Percentage</span>
                                <?php else: ?>
                                <span class="badge bg-primary">Fixed Amount</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($coupon['type'] === 'percentage'): ?>
                                <?php echo $coupon['value']; ?>%
                                <?php if (!empty($coupon['max_discount'])): ?>
                                <small class="text-muted d-block">Max: <?php echo formatPrice($coupon['max_discount']); ?></small>
                                <?php endif; ?>
                                <?php else: ?>
                                <?php echo formatPrice($coupon['value']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($coupon['min_purchase'])): ?>
                                <?php echo formatPrice($coupon['min_purchase']); ?>
                                <?php else: ?>
                                <span class="text-muted">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?php echo date('M d, Y', strtotime($coupon['start_date'])); ?></div>
                                <?php if (!empty($coupon['end_date'])): ?>
                                <small class="text-muted">to <?php echo date('M d, Y', strtotime($coupon['end_date'])); ?></small>
                                <?php else: ?>
                                <small class="text-muted">No expiry</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $coupon['usage_count']; ?> uses
                                <?php if (!empty($coupon['usage_limit'])): ?>
                                <small class="text-muted d-block">Limit: <?php echo $coupon['usage_limit']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $coupon['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($coupon['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editCoupon(<?php echo htmlspecialchars(json_encode($coupon)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="coupons.php?action=toggle-status&id=<?php echo $coupon['id']; ?>" 
                                       class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger" 
                                       onclick="confirmDelete(<?php echo $coupon['id']; ?>, '<?php echo htmlspecialchars($coupon['code']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponModalTitle">Add New Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="couponForm">
                    <input type="hidden" id="couponId" name="id" value="0">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Coupon Code *</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                            <small class="text-muted">Will be converted to uppercase</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Discount Type *</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="value" class="form-label">Discount Value *</label>
                            <input type="number" class="form-control" id="value" name="value" step="0.01" min="0" required>
                            <small class="text-muted" id="valueHelp">For percentage, enter a number between 1-100</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="min_purchase" class="form-label">Minimum Purchase</label>
                            <input type="number" class="form-control" id="min_purchase" name="min_purchase" step="0.01" min="0">
                            <small class="text-muted">Leave empty for no minimum</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="max_discount" class="form-label">Maximum Discount</label>
                            <input type="number" class="form-control" id="max_discount" name="max_discount" step="0.01" min="0">
                            <small class="text-muted">For percentage discounts only</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                            <small class="text-muted">Leave empty for no expiry</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="usage_limit" class="form-label">Usage Limit</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1">
                        <small class="text-muted">Leave empty for unlimited usage</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('couponForm').submit()">Save Coupon</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the coupon: <span id="deleteCouponCode"></span>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Toggle value help text based on discount type
document.getElementById('type').addEventListener('change', function() {
    const valueHelp = document.getElementById('valueHelp');
    if (this.value === 'percentage') {
        valueHelp.textContent = 'For percentage, enter a number between 1-100';
    } else {
        valueHelp.textContent = 'Enter the fixed discount amount';
    }
});

function confirmDelete(id, code) {
    document.getElementById('deleteCouponCode').textContent = code;
    document.getElementById('confirmDeleteBtn').href = 'coupons.php?action=delete&id=' + id;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function editCoupon(coupon) {
    document.getElementById('couponModalTitle').textContent = 'Edit Coupon';
    document.getElementById('couponId').value = coupon.id;
    document.getElementById('code').value = coupon.code;
    document.getElementById('type').value = coupon.type;
    document.getElementById('value').value = coupon.value;
    document.getElementById('min_purchase').value = coupon.min_purchase || '';
    document.getElementById('max_discount').value = coupon.max_discount || '';
    document.getElementById('start_date').value = coupon.start_date;
    document.getElementById('end_date').value = coupon.end_date || '';
    document.getElementById('usage_limit').value = coupon.usage_limit || '';
    document.getElementById('status').value = coupon.status;
    
    // Update help text
    const valueHelp = document.getElementById('valueHelp');
    if (coupon.type === 'percentage') {
        valueHelp.textContent = 'For percentage, enter a number between 1-100';
    } else {
        valueHelp.textContent = 'Enter the fixed discount amount';
    }
    
    const couponModal = new bootstrap.Modal(document.getElementById('couponModal'));
    couponModal.show();
}

// Reset form when modal is closed
document.getElementById('couponModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('couponModalTitle').textContent = 'Add New Coupon';
    document.getElementById('couponForm').reset();
    document.getElementById('couponId').value = '0';
    document.getElementById('valueHelp').textContent = 'For percentage, enter a number between 1-100';
});
</script>
