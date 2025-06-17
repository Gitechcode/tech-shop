<?php
$page_title = 'Categories';
include 'includes/header.php';

requireAdmin();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitizeInput($_POST['name']);
                $slug = sanitizeInput($_POST['slug']);
                $description = sanitizeInput($_POST['description']);
                $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                $status = sanitizeInput($_POST['status']);
                
                if (empty($name) || empty($slug)) {
                    $error = 'Name and slug are required.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, parent_id, status) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $slug, $description, $parent_id, $status]);
                        $success = 'Category added successfully!';
                    } catch (PDOException $e) {
                        $error = 'Error adding category: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = sanitizeInput($_POST['name']);
                $slug = sanitizeInput($_POST['slug']);
                $description = sanitizeInput($_POST['description']);
                $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
                $status = sanitizeInput($_POST['status']);
                
                try {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ?, status = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description, $parent_id, $status, $id]);
                    $success = 'Category updated successfully!';
                } catch (PDOException $e) {
                    $error = 'Error updating category: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = 'Category deleted successfully!';
                } catch (PDOException $e) {
                    $error = 'Error deleting category: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get all categories
$stmt = $pdo->query("SELECT c.*, p.name as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.name");
$categories = $stmt->fetchAll();

// Get parent categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
$parent_categories = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Categories</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus me-2"></i>Add Category
        </button>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                            <td><?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-'; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $category['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($category['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select class="form-select" name="parent_id">
                            <option value="">None</option>
                            <?php foreach ($parent_categories as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editCategoryForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" id="edit_slug" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <select class="form-select" name="parent_id" id="edit_parent_id">
                            <option value="">None</option>
                            <?php foreach ($parent_categories as $parent): ?>
                            <option value="<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function editCategory(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_slug').value = category.slug;
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_parent_id').value = category.parent_id || '';
    document.getElementById('edit_status').value = category.status;
    
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
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
</script>
