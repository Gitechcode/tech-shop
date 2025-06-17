<?php
require_once '../../config/config.php';

requireAdmin();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Get categories
$categories_stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $categories_stmt->fetchAll();

ob_start();
?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="edit_name" class="form-label">Product Name *</label>
        <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="edit_sku" class="form-label">SKU *</label>
        <input type="text" class="form-control" id="edit_sku" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
    </div>
</div>

<div class="mb-3">
    <label for="edit_short_description" class="form-label">Short Description</label>
    <textarea class="form-control" id="edit_short_description" name="short_description" rows="2"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
</div>

<div class="mb-3">
    <label for="edit_description" class="form-label">Description</label>
    <textarea class="form-control" id="edit_description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="edit_price" class="form-label">Price *</label>
        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="edit_sale_price" class="form-label">Sale Price</label>
        <input type="number" class="form-control" id="edit_sale_price" name="sale_price" step="0.01" value="<?php echo $product['sale_price']; ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label for="edit_stock" class="form-label">Stock *</label>
        <input type="number" class="form-control" id="edit_stock" name="stock" value="<?php echo $product['stock']; ?>" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="edit_category_id" class="form-label">Category *</label>
        <select class="form-select" id="edit_category_id" name="category_id" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="edit_brand" class="form-label">Brand</label>
        <input type="text" class="form-control" id="edit_" class="form-label">Brand</label>
        <input type="text" class="form-control" id="edit_brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="edit_image" class="form-label">Product Image</label>
        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
        <?php if ($product['image']): ?>
        <div class="mt-2">
            <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" alt="Current Image" class="img-thumbnail" style="max-width: 100px;">
        </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6 mb-3">
        <label for="edit_status" class="form-label">Status *</label>
        <select class="form-select" id="edit_status" name="status" required>
            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="out_of_stock" <?php echo $product['status'] == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
        </select>
    </div>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" id="edit_featured" name="featured" <?php echo $product['featured'] ? 'checked' : ''; ?>>
    <label class="form-check-label" for="edit_featured">
        Featured Product
    </label>
</div>
<?php
$html = ob_get_clean();

echo json_encode(['success' => true, 'html' => $html]);
?>
