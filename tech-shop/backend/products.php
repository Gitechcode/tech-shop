<?php
$page_title = 'Products';
include 'includes/header.php';

requireAdmin();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $result = addProduct($_POST, $_FILES);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'edit':
                $result = editProduct($_POST, $_FILES);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'delete':
                $result = deleteProduct($_POST['id']);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_clause = "1=1";
$params = [];

// Apply filters
if (isset($_GET['category']) && $_GET['category'] != '') {
    $where_clause .= " AND p.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['status']) && $_GET['status'] != '') {
    $where_clause .= " AND p.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['search']) && $_GET['search'] != '') {
    $where_clause .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = '%' . $_GET['search'] . '%';
    $params[] = '%' . $_GET['search'] . '%';
}

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
    ORDER BY p.created_at DESC 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories_stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $categories_stmt->fetchAll();

// Functions
function addProduct($data, $files) {
    global $pdo;
    
    try {
        $name = sanitizeInput($data['name']);
        $slug = generateSlug($name);
        $description = sanitizeInput($data['description']);
        $short_description = sanitizeInput($data['short_description']);
        $price = (float)$data['price'];
        $sale_price = !empty($data['sale_price']) ? (float)$data['sale_price'] : null;
        $sku = sanitizeInput($data['sku']);
        $stock = (int)$data['stock'];
        $category_id = (int)$data['category_id'];
        $brand = sanitizeInput($data['brand']);
        $featured = isset($data['featured']) ? 1 : 0;
        $status = sanitizeInput($data['status']);
        
        // Handle image upload
        $image = '';
        if (isset($files['image']) && $files['image']['error'] === 0) {
            $image = uploadFile($files['image'], 'products');
            if (!$image) {
                return ['success' => false, 'message' => 'Error uploading image'];
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO products (name, slug, description, short_description, price, sale_price, sku, stock, category_id, brand, image, featured, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$name, $slug, $description, $short_description, $price, $sale_price, $sku, $stock, $category_id, $brand, $image, $featured, $status]);
        
        return ['success' => true, 'message' => 'Product added successfully!'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding product: ' . $e->getMessage()];
    }
}

function editProduct($data, $files) {
    global $pdo;
    
    try {
        $id = (int)$data['id'];
        $name = sanitizeInput($data['name']);
        $slug = generateSlug($name);
        $description = sanitizeInput($data['description']);
        $short_description = sanitizeInput($data['short_description']);
        $price = (float)$data['price'];
        $sale_price = !empty($data['sale_price']) ? (float)$data['sale_price'] : null;
        $sku = sanitizeInput($data['sku']);
        $stock = (int)$data['stock'];
        $category_id = (int)$data['category_id'];
        $brand = sanitizeInput($data['brand']);
        $featured = isset($data['featured']) ? 1 : 0;
        $status = sanitizeInput($data['status']);
        
        // Get current product
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        $image = $product['image'];
        
        // Handle image upload
        if (isset($files['image']) && $files['image']['error'] === 0) {
            $new_image = uploadFile($files['image'], 'products');
            if ($new_image) {
                // Delete old image
                if ($image && file_exists($_SERVER['DOCUMENT_ROOT'] . '/tech-shop/' . $image)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/tech-shop/' . $image);
                }
                $image = $new_image;
            }
        }
        
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, slug = ?, description = ?, short_description = ?, price = ?, sale_price = ?, sku = ?, stock = ?, category_id = ?, brand = ?, image = ?, featured = ?, status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$name, $slug, $description, $short_description, $price, $sale_price, $sku, $stock, $category_id, $brand, $image, $featured, $status, $id]);
        
        return ['success' => true, 'message' => 'Product updated successfully!'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()];
    }
}

function deleteProduct($id) {
    global $pdo;
    
    try {
        $id = (int)$id;
        
        // Get product to delete image
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Delete image file
            if ($product['image'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/tech-shop/' . $product['image'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/tech-shop/' . $product['image']);
            }
            
            // Delete product
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Product deleted successfully!'];
        } else {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()];
    }
}

function generateSlug($text) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    return $slug;
}
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add Product
        </button>
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
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                               placeholder="Product name or SKU">
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="out_of_stock" <?php echo (isset($_GET['status']) && $_GET['status'] == 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="products.php" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Products (<?php echo $total_products; ?> total)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img src="<?php echo SITE_URL . '/' . ($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="font-weight-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($product['brand']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($product['sku']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>
                                <?php if ($product['sale_price']): ?>
                                <span class="text-success"><?php echo formatPrice($product['sale_price']); ?></span>
                                <br><small class="text-muted text-decoration-line-through"><?php echo formatPrice($product['price']); ?></small>
                                <?php else: ?>
                                <?php echo formatPrice($product['price']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $product['stock'] > 10 ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $product['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="editProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Products pagination">
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

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sku" class="form-label">SKU *</label>
                            <input type="text" class="form-control" id="sku" name="sku" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Short Description</label>
                        <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sale_price" class="form-label">Sale Price</label>
                            <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="stock" class="form-label">Stock *</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" class="form-control" id="brand" name="brand">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="featured" name="featured">
                        <label class="form-check-label" for="featured">
                            Featured Product
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data" id="editProductForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body" id="editProductContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function editProduct(id) {
    // Load product data via AJAX
    fetch(`api/get-product.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_id').value = id;
                document.getElementById('editProductContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('editProductModal')).show();
            } else {
                alert('Error loading product data');
            }
        });
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
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
