<?php
$page_title = 'Product Details';
include 'includes/header.php';

// Get product ID from URL
if (!isset($_GET['id'])) {
    redirect('shop.php');
}

$id = (int)$_GET['id'];

// Get product details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 'active'
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('shop.php');
}

// Update page title
$page_title = $product['name'];

// Get product attributes
$stmt = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id = ?");
$stmt->execute([$id]);
$attributes = $stmt->fetchAll();

// Get related products
$stmt = $pdo->prepare("
    SELECT p.* 
    FROM products p
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
    ORDER BY p.created_at DESC
    LIMIT 4
");
$stmt->execute([$product['category_id'], $id]);
$related_products = $stmt->fetchAll();

// Get product reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ? AND r.status = 'approved'
    ORDER BY r.created_at DESC
");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

// Calculate average rating
$avg_rating = 0;
$total_reviews = count($reviews);
if ($total_reviews > 0) {
    $sum = 0;
    foreach ($reviews as $review) {
        $sum += $review['rating'];
    }
    $avg_rating = $sum / $total_reviews;
}

// Process review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        showAlert('Please login to submit a review', 'warning');
        redirect('login.php?redirect=product.php?id=' . $id);
    }
    
    $rating = (int)$_POST['rating'];
    $comment = sanitizeInput($_POST['comment']);
    
    if ($rating < 1 || $rating > 5) {
        showAlert('Please select a valid rating', 'danger');
    } elseif (empty($comment)) {
        showAlert('Please enter a review comment', 'danger');
    } else {
        // Check if user already reviewed this product
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        if ($stmt->fetch()) {
            showAlert('You have already reviewed this product', 'warning');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (product_id, user_id, rating, comment, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            if ($stmt->execute([$id, $_SESSION['user_id'], $rating, $comment])) {
                showAlert('Thank you for your review! It will be visible after approval.', 'success');
            } else {
                showAlert('Error submitting review', 'danger');
            }
        }
    }
}
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
            <?php if ($product['category_name']): ?>
            <li class="breadcrumb-item"><a href="shop.php?category=<?php echo $product['category_slug']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="product-images">
                <div class="main-image mb-3">
                    <img src="<?php echo $product['image'] ?: '/placeholder.svg?height=500&width=500'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="img-fluid rounded" id="mainImage">
                </div>
                
                <?php if (!empty($product['gallery'])): ?>
                <div class="image-gallery d-flex">
                    <div class="gallery-item me-2">
                        <img src="<?php echo $product['image'] ?: '/placeholder.svg?height=100&width=100'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="img-thumbnail gallery-thumb active" 
                             onclick="changeMainImage(this.src)">
                    </div>
                    
                    <?php foreach (json_decode($product['gallery'], true) as $image): ?>
                    <div class="gallery-item me-2">
                        <img src="<?php echo $image; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="img-thumbnail gallery-thumb" 
                             onclick="changeMainImage(this.src)">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <h1 class="h2 mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="mb-3">
                <div class="d-flex align-items-center">
                    <div class="ratings me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= round($avg_rating)): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star text-warning"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="text-muted"><?php echo $total_reviews; ?> reviews</span>
                </div>
            </div>
            
            <div class="mb-3">
                <?php if ($product['sale_price']): ?>
                <div class="h3 mb-0 text-primary">
                    <?php echo formatPrice($product['sale_price']); ?>
                    <small class="text-muted text-decoration-line-through"><?php echo formatPrice($product['price']); ?></small>
                </div>
                <?php else: ?>
                <div class="h3 mb-0 text-primary"><?php echo formatPrice($product['price']); ?></div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($product['short_description'])): ?>
            <div class="mb-4">
                <p><?php echo htmlspecialchars($product['short_description']); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <div class="d-flex align-items-center mb-2">
                    <span class="me-3">Availability:</span>
                    <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">In Stock (<?php echo $product['stock']; ?>)</span>
                    <?php else: ?>
                    <span class="badge bg-danger">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($product['sku'])): ?>
                <div class="mb-2">
                    <span class="me-3">SKU:</span>
                    <span><?php echo htmlspecialchars($product['sku']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($product['brand'])): ?>
                <div class="mb-2">
                    <span class="me-3">Brand:</span>
                    <span><?php echo htmlspecialchars($product['brand']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($product['category_name'])): ?>
                <div class="mb-2">
                    <span class="me-3">Category:</span>
                    <a href="shop.php?category=<?php echo $product['category_slug']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
                </div>
                <?php endif; ?>
            </div>
            
            <form action="cart.php" method="POST" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" onclick="decrementQuantity()">-</button>
                            <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button type="button" class="btn btn-outline-secondary" onclick="incrementQuantity(<?php echo $product['stock']; ?>)">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                        <i class="far fa-heart"></i>
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary" onclick="addToCompare(<?php echo $product['id']; ?>)">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>
            </form>
            
            <div class="social-share mt-4">
                <p class="mb-2">Share this product:</p>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-info"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-danger"><i class="fab fa-pinterest"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-success"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab">Specifications</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews (<?php echo $total_reviews; ?>)</button>
                </li>
            </ul>
            
            <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <?php if (!empty($product['description'])): ?>
                    <div class="product-description">
                        <?php echo $product['description']; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No description available for this product.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Specifications Tab -->
                <div class="tab-pane fade" id="specifications" role="tabpanel">
                    <?php if (!empty($attributes)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <?php foreach ($attributes as $attr): ?>
                                <tr>
                                    <th width="30%"><?php echo htmlspecialchars($attr['attribute_name']); ?></th>
                                    <td><?php echo htmlspecialchars($attr['attribute_value']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (!empty($product['weight'])): ?>
                                <tr>
                                    <th>Weight</th>
                                    <td><?php echo htmlspecialchars($product['weight']); ?></td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['dimensions'])): ?>
                                <tr>
                                    <th>Dimensions</th>
                                    <td><?php echo htmlspecialchars($product['dimensions']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No specifications available for this product.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <h4 class="mb-4">Customer Reviews</h4>
                            
                            <?php if (empty($reviews)): ?>
                            <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                            <?php else: ?>
                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                <div class="review-item mb-4 pb-4 border-bottom">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <h5 class="mb-0"><?php echo htmlspecialchars($review['user_name']); ?></h5>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                        </div>
                                        <div class="ratings">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="fas fa-star text-warning"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-warning"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Write a Review</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!isLoggedIn()): ?>
                                    <p>Please <a href="login.php?redirect=product.php?id=<?php echo $id; ?>">login</a> to write a review.</p>
                                    <?php else: ?>
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="rating" class="form-label">Rating</label>
                                            <div class="rating-stars">
                                                <div class="d-flex">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <div class="me-2">
                                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" class="d-none" required>
                                                        <label for="star<?php echo $i; ?>" class="star-label">
                                                            <i class="far fa-star"></i>
                                                        </label>
                                                    </div>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Your Review</label>
                                            <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                                        </div>
                                        
                                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="related-products mt-5">
        <h3 class="mb-4">Related Products</h3>
        
        <div class="row">
            <?php foreach ($related_products as $related): ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    <div class="product-image position-relative">
                        <a href="product.php?id=<?php echo $related['id']; ?>">
                            <img src="<?php echo $related['image'] ?: '/placeholder.svg?height=300&width=300'; ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>" 
                                 class="card-img-top">
                        </a>
                        
                        <?php if ($related['sale_price']): ?>
                        <div class="product-badge bg-danger text-white position-absolute top-0 start-0 m-2 px-2 py-1 rounded">
                            Sale
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-actions position-absolute top-0 end-0 m-2">
                            <button class="btn btn-sm btn-outline-secondary mb-1" onclick="addToWishlist(<?php echo $related['id']; ?>)">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="quickView(<?php echo $related['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </a>
                        </h5>
                        
                        <div class="product-price mb-3">
                            <?php if ($related['sale_price']): ?>
                            <span class="text-primary"><?php echo formatPrice($related['sale_price']); ?></span>
                            <small class="text-muted text-decoration-line-through"><?php echo formatPrice($related['price']); ?></small>
                            <?php else: ?>
                            <span class="text-primary"><?php echo formatPrice($related['price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn btn-primary btn-sm w-100" onclick="addToCart(<?php echo $related['id']; ?>)">
                            <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Quick View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="/placeholder.svg" alt="Product Image" class="img-fluid" id="quickViewImage">
                    </div>
                    <div class="col-md-6">
                        <h4 id="quickViewTitle"></h4>
                        <div class="mb-3" id="quickViewPrice"></div>
                        <p id="quickViewDescription"></p>
                        <button class="btn btn-primary" id="quickViewAddToCart">
                            <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                        </button>
                        <a href="#" class="btn btn-outline-secondary" id="quickViewDetails">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Change main image
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.classList.remove('active');
        if (thumb.src === src) {
            thumb.classList.add('active');
        }
    });
}

// Quantity controls
function incrementQuantity(max) {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue < max) {
        input.value = currentValue + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

// Rating stars
document.querySelectorAll('.star-label').forEach((star, index) => {
    star.addEventListener('mouseover', () => {
        // Fill in this star and all previous stars
        for (let i = 0; i <= index; i++) {
            document.querySelectorAll('.star-label')[i].querySelector('i').className = 'fas fa-star text-warning';
        }
        
        // Empty all next stars
        for (let i = index + 1; i < 5; i++) {
            document.querySelectorAll('.star-label')[i].querySelector('i').className = 'far fa-star text-warning';
        }
    });
    
    star.addEventListener('click', () => {
        // Set the radio button value
        document.getElementById('star' + (index + 1)).checked = true;
    });
});

// Reset stars when mouse leaves the container
document.querySelector('.rating-stars').addEventListener('mouseleave', () => {
    // Find the selected rating
    let selectedRating = 0;
    document.querySelectorAll('input[name="rating"]').forEach((radio, index) => {
        if (radio.checked) {
            selectedRating = index + 1;
        }
    });
    
    // Update stars based on selected rating
    document.querySelectorAll('.star-label').forEach((star, index) => {
        if (index < selectedRating) {
            star.querySelector('i').className = 'fas fa-star text-warning';
        } else {
            star.querySelector('i').className = 'far fa-star text-warning';
        }
    });
});

// Add to wishlist
function addToWishlist(productId) {
    // Check if user is logged in
    <?php if (!isLoggedIn()): ?>
    window.location.href = 'login.php?redirect=product.php?id=<?php echo $id; ?>';
    return;
    <?php endif; ?>
    
    // Send AJAX request
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            action: 'add'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to wishlist!', 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

// Add to compare
function addToCompare(productId) {
    // Get current compare list from localStorage
    let compareList = JSON.parse(localStorage.getItem('compareList')) || [];
    
    // Check if product is already in the list
    if (compareList.includes(productId)) {
        showToast('Product is already in your compare list', 'info');
        return;
    }
    
    // Limit to 4 products
    if (compareList.length >= 4) {
        showToast('You can compare up to 4 products at a time', 'warning');
        return;
    }
    
    // Add product to compare list
    compareList.push(productId);
    localStorage.setItem('compareList', JSON.stringify(compareList));
    
    showToast('Product added to compare list!', 'success');
}

// Quick view
function quickView(productId) {
    // Send AJAX request to get product details
    fetch('api/products.php?id=' + productId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const product = data.product;
            
            // Update modal content
            document.getElementById('quickViewImage').src = product.image || '/placeholder.svg?height=300&width=300';
            document.getElementById('quickViewTitle').textContent = product.name;
            
            // Format price
            let priceHtml = '';
            if (product.sale_price) {
                priceHtml = `<span class="text-primary h4">${formatPrice(product.sale_price)}</span>
                            <small class="text-muted text-decoration-line-through">${formatPrice(product.price)}</small>`;
            } else {
                priceHtml = `<span class="text-primary h4">${formatPrice(product.price)}</span>`;
            }
            document.getElementById('quickViewPrice').innerHTML = priceHtml;
            
            document.getElementById('quickViewDescription').textContent = product.short_description || 'No description available.';
            
            // Update buttons
            document.getElementById('quickViewAddToCart').onclick = function() {
                addToCart(product.id);
                bootstrap.Modal.getInstance(document.getElementById('quickViewModal')).hide();
            };
            
            document.getElementById('quickViewDetails').href = 'product.php?id=' + product.id;
            
            // Show modal
            const quickViewModal = new bootstrap.Modal(document.getElementById('quickViewModal'));
            quickViewModal.show();
        } else {
            showToast('Error loading product details', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

// Add to cart
function addToCart(productId) {
    // Send AJAX request
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1,
            action: 'add'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to cart!', 'success');
            updateCartCount(data.cart_count);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

// Format price
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// Show toast notification
function showToast(message, type) {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toastContainer.remove();
    });
}

// Update cart count in header
function updateCartCount(count) {
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.style.display = count > 0 ? 'inline-block' : 'none';
    }
}
</script>

<style>
.gallery-thumb {
    width: 80px;
    height: 80px;
    object-fit: cover;
    cursor: pointer;
    transition: all 0.2s;
}

.gallery-thumb.active {
    border-color: #0d6efd;
}

.star-label {
    cursor: pointer;
    font-size: 1.25rem;
}

.product-description img {
    max-width: 100%;
    height: auto;
}
</style>
