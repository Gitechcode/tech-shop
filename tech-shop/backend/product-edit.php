<?php
$page_title = 'Edit Product';
include 'includes/header.php';
requireAdmin();

$product = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'description' => '',
    'short_description' => '',
    'price' => '',
    'sale_price' => '',
    'sku' => '',
    'stock' => 0,
    'category_id' => 0,
    'brand' => '',
    'weight' => '',
    'dimensions' => '',
    'image' => '',
    'gallery' => '',
    'featured' => 0,
    'status' => 'active',
    'meta_title' => '',
    'meta_description' => ''
];

$errors = [];
$success = false;

// Get categories
$categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categories_stmt->fetchAll();

// Edit existing product
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $db_product = $stmt->fetch();
    
    if ($db_product) {
        $product = array_merge($product, $db_product);
        $page_title = 'Edit Product: ' . $product['name'];
    } else {
        showAlert('Product not found', 'danger');
        redirect(BACKEND_URL . '/products.php');
    }
    
    // Get product attributes
    $attr_stmt = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id = ?");
    $attr_stmt->execute([$id]);
    $attributes = $attr_stmt->fetchAll();
} else {
    $page_title = 'Add New Product';
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['name'])) {
        $errors[] = 'Product name is required';
    }
    
    if (empty($_POST['price']) || !is_numeric($_POST['price'])) {
        $errors[] = 'Valid price is required';
    }
    
    // Generate slug if not provided
    $slug = !empty($_POST['slug']) ? sanitizeInput($_POST['slug']) : createSlug($_POST['name']);
    
    // Check if slug exists (for new products or when changing slug)
    if ($product['id'] === 0 || $slug !== $product['slug']) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $errors[] = 'Product slug already exists. Please choose a different one.';
        }
    }
    
    // Process image upload
    $image_path = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image_path = uploadFile($_FILES['image'], 'products');
        if (!$image_path) {
            $errors[] = 'Error uploading image. Please check file type and size.';
        }
    }
    
    // Process gallery uploads
    $gallery = !empty($product['gallery']) ? json_decode($product['gallery'], true) : [];
    if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
        foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery']['size'][$key] > 0) {
                $file = [
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key]
                ];
                
                $gallery_path = uploadFile($file, 'products/gallery');
                if ($gallery_path) {
                    $gallery[] = $gallery_path;
                }
            }
        }
    }
    
    // If no errors, save product
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'slug' => $slug,
                'description' => $_POST['description'],
                'short_description' => sanitizeInput($_POST['short_description']),
                'price' => (float)$_POST['price'],
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'sku' => sanitizeInput($_POST['sku']),
                'stock' => (int)$_POST['stock'],
                'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
                'brand' => sanitizeInput($_POST['brand']),
                'weight' => sanitizeInput($_POST['weight']),
                'dimensions' => sanitizeInput($_POST['dimensions']),
                'image' => $image_path,
                'gallery' => !empty($gallery) ? json_encode($gallery) : null,
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'status' => sanitizeInput($_POST['status']),
                'meta_title' => sanitizeInput($_POST['meta_title']),
                'meta_description' => sanitizeInput($_POST['meta_description'])
            ];
            
            if ($product['id'] > 0) {
                // Update existing product
                $sql = "UPDATE products SET 
                        name = :name, 
                        slug = :slug, 
                        description = :description, 
                        short_description = :short_description, 
                        price = :price, 
                        sale_price = :sale_price, 
                        sku = :sku, 
                        stock = :stock, 
                        category_id = :category_id, 
                        brand = :brand, 
                        weight = :weight, 
                        dimensions = :dimensions, 
                        image = :image, 
                        gallery = :gallery, 
                        featured = :featured, 
                        status = :status, 
                        meta_title = :meta_title, 
                        meta_description = :meta_description, 
                        updated_at = NOW() 
                        WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $data['id'] = $product['id'];
                $stmt->execute($data);
                
                // Delete existing attributes
                $stmt = $pdo->prepare("DELETE FROM product_attributes WHERE product_id = ?");
                $stmt->execute([$product['id']]);
                
                $product_id = $product['id'];
            } else {
                // Insert new product
                $sql = "INSERT INTO products (
                        name, slug, description, short_description, price, sale_price, 
                        sku, stock, category_id, brand, weight, dimensions, 
                        image, gallery, featured, status, meta_title, meta_description, 
                        created_at, updated_at
                    ) VALUES (
                        :name, :slug, :description, :short_description, :price, :sale_price, 
                        :sku, :stock, :category_id, :brand, :weight, :dimensions, 
                        :image, :gallery, :featured, :status, :meta_title, :meta_description, 
                        NOW(), NOW()
                    )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($data);
                
                $product_id = $pdo->lastInsertId();
            }
            
            // Save attributes
            if (isset($_POST['attribute_names']) && is_array($_POST['attribute_names'])) {
                foreach ($_POST['attribute_names'] as $key => $name) {
                    if (!empty($name) && !empty($_POST['attribute_values'][$key])) {
                        $stmt = $pdo->prepare("
                            INSERT INTO product_attributes (product_id, attribute_name, attribute_value, created_at) 
                            VALUES (?, ?, ?, NOW())
                        ");
                        $stmt->execute([
                            $product_id, 
                            sanitizeInput($name), 
                            sanitizeInput($_POST['attribute_values'][$key])
                        ]);
                    }
                }
            }
            
            $pdo->commit();
            $success = true;
            
            showAlert('Product saved successfully', 'success');
            
            if ($product['id'] === 0) {
                redirect(BACKEND_URL . '/product-edit.php?id=' . $product_id);
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Error saving product: ' . $e->getMessage();
        }
    }
}

// Helper function to create slug
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
        <a href="products.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Products
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

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" 
                                   value="<?php echo htmlspecialchars($product['slug']); ?>">
                            <small class="text-muted">Leave empty to auto-generate from name</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Short Description</label>
                            <textarea class="form-control" id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Full Description</label>
                            <textarea class="form-control" id="description" name="description" rows="10"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Pricing & Inventory</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Regular Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           value="<?php echo htmlspecialchars($product['price']); ?>" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="sale_price" class="form-label">Sale Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price" 
                                           value="<?php echo htmlspecialchars($product['sale_price']); ?>" step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="sku" name="sku" 
                                       value="<?php echo htmlspecialchars($product['sku']); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                       value="<?php echo (int)$product['stock']; ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Attributes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Product Attributes</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addAttributeBtn">
                            <i class="fas fa-plus me-1"></i>Add Attribute
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="attributesContainer">
                            <?php if (isset($attributes) && !empty($attributes)): ?>
                                <?php foreach ($attributes as $index => $attr): ?>
                                <div class="row attribute-row mb-3">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="attribute_names[]" 
                                               placeholder="Attribute Name" value="<?php echo htmlspecialchars($attr['attribute_name']); ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="attribute_values[]" 
                                               placeholder="Attribute Value" value="<?php echo htmlspecialchars($attr['attribute_value']); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-attribute">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="row attribute-row mb-3">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="attribute_names[]" placeholder="Attribute Name">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="attribute_values[]" placeholder="Attribute Value">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-attribute">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="noAttributesMessage" class="text-center py-3 <?php echo (isset($attributes) && !empty($attributes)) ? 'd-none' : ''; ?>">
                            <p class="text-muted mb-0">No attributes added yet. Click "Add Attribute" to add product specifications.</p>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">SEO Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title" 
                                   value="<?php echo htmlspecialchars($product['meta_title']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?php echo htmlspecialchars($product['meta_description']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Product Status -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Product Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="out_of_stock" <?php echo $product['status'] === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                            </select>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                   <?php echo $product['featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="featured">Featured Product</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Product
                            </button>
                            <?php if ($product['id'] > 0): ?>
                            <a href="<?php echo FRONTEND_URL; ?>/product.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-outline-secondary" target="_blank">
                                <i class="fas fa-eye me-2"></i>View Product
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Category</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Additional Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" class="form-control" id="brand" name="brand" 
                                   value="<?php echo htmlspecialchars($product['brand']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="weight" class="form-label">Weight</label>
                            <input type="text" class="form-control" id="weight" name="weight" 
                                   value="<?php echo htmlspecialchars($product['weight']); ?>" placeholder="e.g. 1.5 kg">
                        </div>
                        
                        <div class="mb-3">
                            <label for="dimensions" class="form-label">Dimensions</label>
                            <input type="text" class="form-control" id="dimensions" name="dimensions" 
                                   value="<?php echo htmlspecialchars($product['dimensions']); ?>" placeholder="e.g. 10 x 5 x 3 cm">
                        </div>
                    </div>
                </div>

                <!-- Product Image -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Product Image</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <?php if (!empty($product['image'])): ?>
                            <div class="mb-3 text-center">
                                <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" 
                                     alt="Product Image" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Recommended size: 800x800 pixels</small>
                        </div>
                    </div>
                </div>

                <!-- Product Gallery -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Product Gallery</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <?php if (!empty($product['gallery'])): ?>
                            <div class="row mb-3">
                                <?php foreach (json_decode($product['gallery'], true) as $gallery_image): ?>
                                <div class="col-4 mb-2">
                                    <img src="<?php echo SITE_URL . '/' . $gallery_image; ?>" 
                                         alt="Gallery Image" class="img-thumbnail" style="height: 80px; object-fit: cover;">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <input type="file" class="form-control" id="gallery" name="gallery[]" accept="image/*" multiple>
                            <small class="text-muted">You can select multiple images</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
<script>
// Initialize TinyMCE
tinymce.init({
    selector: '#description',
    height: 400,
    menubar: false,
    plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
});

// Auto-generate slug from name
document.getElementById('name').addEventListener('blur', function() {
    const slugField = document.getElementById('slug');
    if (slugField.value === '') {
        const name = this.value.trim();
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
        
        slugField.value = slug;
    }
});

// Product attributes
document.getElementById('addAttributeBtn').addEventListener('click', function() {
    const container = document.getElementById('attributesContainer');
    const noAttributesMessage = document.getElementById('noAttributesMessage');
    
    const attributeRow = document.createElement('div');
    attributeRow.className = 'row attribute-row mb-3';
    attributeRow.innerHTML = `
        <div class="col-md-5">
            <input type="text" class="form-control" name="attribute_names[]" placeholder="Attribute Name">
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" name="attribute_values[]" placeholder="Attribute Value">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger remove-attribute">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(attributeRow);
    noAttributesMessage.classList.add('d-none');
    
    // Add event listener to the new remove button
    attributeRow.querySelector('.remove-attribute').addEventListener('click', removeAttribute);
});

// Add event listeners to existing remove buttons
document.querySelectorAll('.remove-attribute').forEach(button => {
    button.addEventListener('click', removeAttribute);
});

function removeAttribute() {
    const row = this.closest('.attribute-row');
    row.remove();
    
    const container = document.getElementById('attributesContainer');
    const noAttributesMessage = document.getElementById('noAttributesMessage');
    
    if (container.children.length === 0) {
        noAttributesMessage.classList.remove('d-none');
    }
}
</script>
