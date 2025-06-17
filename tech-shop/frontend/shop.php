<?php
$page_title = 'Shop';
include 'includes/header.php';

// Get filters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($min_price > 0) {
    $where_conditions[] = "COALESCE(p.sale_price, p.price) >= ?";
    $params[] = $min_price;
}

if ($max_price < 10000) {
    $where_conditions[] = "COALESCE(p.sale_price, p.price) <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where_conditions);

// Sort options
$sort_options = [
    'newest' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC',
    'price_low' => 'COALESCE(p.sale_price, p.price) ASC',
    'price_high' => 'COALESCE(p.sale_price, p.price) DESC',
    'name_az' => 'p.name ASC',
    'name_za' => 'p.name DESC'
];

$order_by = isset($sort_options[$sort]) ? $sort_options[$sort] : $sort_options['newest'];

// Get total count
$count_sql = "SELECT COUNT(*) FROM products p WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Get products
$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE $where_clause 
    ORDER BY $order_by 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories_stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $categories_stmt->fetchAll();

// Get selected category name
$selected_category = '';
if ($category_id > 0) {
    $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->execute([$category_id]);
    $selected_category = $cat_stmt->fetchColumn();
}
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo FRONTEND_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active">Shop</li>
            <?php if ($selected_category): ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($selected_category); ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search products...">
                        </div>

                        <!-- Categories -->
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Price Range</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" 
                                           value="<?php echo $min_price; ?>" placeholder="Min" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" 
                                           value="<?php echo $max_price; ?>" placeholder="Max" min="0">
                                </div>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-3">
                            <label class="form-label">Sort By</label>
                            <select class="form-select" name="sort">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name_az" <?php echo $sort == 'name_az' ? 'selected' : ''; ?>>Name: A-Z</option>
                                <option value="name_za" <?php echo $sort == 'name_za' ? 'selected' : ''; ?>>Name: Z-A</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                        
                        <a href="<?php echo FRONTEND_URL; ?>/shop.php" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">
                        <?php if ($search): ?>
                        Search Results for "<?php echo htmlspecialchars($search); ?>"
                        <?php elseif ($selected_category): ?>
                        <?php echo htmlspecialchars($selected_category); ?>
                        <?php else: ?>
                        All Products
                        <?php endif; ?>
                    </h4>
                    <small class="text-muted">Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products</small>
                </div>
                
                <div class="d-flex align-items-center">
                    <span class="me-2">View:</span>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" id="gridView">
                            <i class="fas fa-th"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="listView">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row g-4" id="productsContainer">
                <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p class="text-muted">Try adjusting your search criteria or browse our categories.</p>
                        <a href="<?php echo FRONTEND_URL; ?>/shop.php" class="btn btn-primary">
                            View All Products
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6 product-item">
                    <div class="card product-card h-100 shadow-sm">
                        <div class="position-relative overflow-hidden">
                            <img src="<?php echo SITE_URL . '/' . ($product['image'] ?: '/placeholder.svg?height=250&width=400'); ?>" 
                                 class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            
                            <?php if ($product['sale_price']): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                <?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>% OFF
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($product['stock'] < 10): ?>
                            <span class="badge bg-warning position-absolute top-0 end-0 m-2">Low Stock</span>
                            <?php endif; ?>
                            
                            <div class="product-overlay">
                                <a href="<?php echo FRONTEND_URL; ?>/product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <button class="btn btn-success btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-cart-plus"></i> Add
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2 align-self-start">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </span>
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    <?php if ($product['sale_price']): ?>
                                    <span class="h5 text-primary mb-0"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <small class="text-muted text-decoration-line-through ms-2">
                                        <?php echo formatPrice($product['price']); ?>
                                    </small>
                                    <?php else: ?>
                                    <span class="h5 text-primary mb-0"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <button class="btn btn-outline-primary btn-sm me-1" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Products pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
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
// View toggle functionality
document.getElementById('listView').addEventListener('click', function() {
    document.getElementById('productsContainer').className = 'row g-4 list-view';
    document.querySelectorAll('.product-item').forEach(item => {
        item.className = 'col-12 product-item';
    });
    this.classList.add('active');
    document.getElementById('gridView').classList.remove('active');
});

document.getElementById('gridView').addEventListener('click', function() {
    document.getElementById('productsContainer').className = 'row g-4';
    document.querySelectorAll('.product-item').forEach(item => {
        item.className = 'col-lg-4 col-md-6 product-item';
    });
    this.classList.add('active');
    document.getElementById('listView').classList.remove('active');
});

// Add to cart function
function addToCart(productId) {
    fetch('<?php echo FRONTEND_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showToast(data.message || 'Error adding product to cart', 'error');
        }
    });
}

// Add to wishlist function
function addToWishlist(productId) {
    fetch('<?php echo FRONTEND_URL; ?>/api/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to wishlist!', 'success');
        } else {
            showToast(data.message || 'Error adding to wishlist', 'error');
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

// Update cart count
function updateCartCount() {
    fetch('<?php echo FRONTEND_URL; ?>/api/cart.php?action=count')
    .then(response => response.json())
    .then(data => {
        const cartBadge = document.querySelector('.badge');
        if (data.count > 0) {
            if (cartBadge) {
                cartBadge.textContent = data.count;
            }
        }
    });
}
</script>

<style>
.list-view .product-card {
    flex-direction: row;
}

.list-view .product-image {
    width: 200px;
    height: 150px;
    object-fit: cover;
}

.list-view .card-body {
    flex: 1;
}
</style>
