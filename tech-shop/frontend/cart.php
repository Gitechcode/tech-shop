<?php
$page_title = 'Shopping Cart';
include 'includes/header.php';

// Get cart items
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $session_id = session_id();
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.sale_price, p.image, p.stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.session_id = ?
    ");
    $stmt->execute([$session_id]);
}

$cart_items = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['sale_price'] ?: $item['price'];
    $subtotal += $price * $item['quantity'];
}

$shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
$tax = $subtotal * 0.08; // 8% tax
$total = $subtotal + $shipping + $tax;
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo FRONTEND_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active">Shopping Cart</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                        <?php if (!empty($cart_items)): ?>
                        <span class="badge bg-primary ms-2"><?php echo count($cart_items); ?> items</span>
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($cart_items)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4>Your cart is empty</h4>
                        <p class="text-muted">Add some products to your cart to get started.</p>
                        <a href="<?php echo FRONTEND_URL; ?>/shop.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                <tr data-product-id="<?php echo $item['product_id']; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo SITE_URL . '/' . ($item['image'] ?: '/placeholder.svg?height=80&width=80'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">Stock: <?php echo $item['stock']; ?> available</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $price = $item['sale_price'] ?: $item['price'];
                                        echo formatPrice($price);
                                        if ($item['sale_price']): ?>
                                        <br><small class="text-muted text-decoration-line-through"><?php echo formatPrice($item['price']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                    onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" max="<?php echo $item['stock']; ?>"
                                                   onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" 
                                                    onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="fw-bold">
                                        <?php echo formatPrice($price * $item['quantity']); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-danger btn-sm" 
                                                onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="<?php echo FRONTEND_URL; ?>/shop.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <button class="btn btn-outline-secondary" onclick="clearCart()">
                            <i class="fas fa-trash me-2"></i>Clear Cart
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($cart_items)): ?>
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span><?php echo $shipping > 0 ? formatPrice($shipping) : 'Free'; ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <span><?php echo formatPrice($tax); ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total:</span>
                        <span class="text-success"><?php echo formatPrice($total); ?></span>
                    </div>

                    <?php if ($shipping == 0): ?>
                    <div class="alert alert-success py-2">
                        <small><i class="fas fa-truck me-1"></i>Free shipping applied!</small>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info py-2">
                        <small><i class="fas fa-info-circle me-1"></i>Add <?php echo formatPrice(100 - $subtotal); ?> more for free shipping!</small>
                    </div>
                    <?php endif; ?>

                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo FRONTEND_URL; ?>/checkout.php" class="btn btn-success w-100 btn-lg">
                        <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                    </a>
                    <?php else: ?>
                    <a href="<?php echo FRONTEND_URL; ?>/login.php?redirect=<?php echo urlencode(FRONTEND_URL . '/checkout.php'); ?>" 
                       class="btn btn-success w-100 btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Checkout
                    </a>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            Don't have an account? 
                            <a href="<?php echo FRONTEND_URL; ?>/register.php">Sign up</a>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coupon Code -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="card-title">Have a coupon code?</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="couponCode" placeholder="Enter coupon code">
                        <button class="btn btn-outline-primary" type="button" onclick="applyCoupon()">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Update quantity
function updateQuantity(productId, quantity) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }

    fetch('<?php echo FRONTEND_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast(data.message || 'Error updating quantity', 'error');
        }
    });
}

// Remove from cart
function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('<?php echo FRONTEND_URL; ?>/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast(data.message || 'Error removing item', 'error');
            }
        });
    }
}

// Clear cart
function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        fetch('<?php echo FRONTEND_URL; ?>/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'clear'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast(data.message || 'Error clearing cart', 'error');
            }
        });
    }
}

// Apply coupon
function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value;
    if (!couponCode) {
        showToast('Please enter a coupon code', 'error');
        return;
    }

    fetch('<?php echo FRONTEND_URL; ?>/api/coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'apply',
            code: couponCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Coupon applied successfully!', 'success');
            location.reload();
        } else {
            showToast(data.message || 'Invalid coupon code', 'error');
        }
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
</script>
