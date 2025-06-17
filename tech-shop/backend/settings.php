<?php
$page_title = 'Settings';
include 'includes/header.php';

requireAdmin();

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = $_POST['settings'];
    
    try {
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$key, $value]);
        }
        $success = 'Settings updated successfully!';
    } catch (PDOException $e) {
        $error = 'Error updating settings: ' . $e->getMessage();
    }
}

// Get current settings
$stmt = $pdo->query("SELECT * FROM settings");
$current_settings = [];
while ($row = $stmt->fetch()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// Default settings if not set
$default_settings = [
    'site_name' => 'TechShop',
    'site_email' => 'info@techshop.com',
    'site_phone' => '+1 (555) 123-4567',
    'site_address' => '123 Tech Street, Digital City, TC 12345',
    'currency' => 'USD',
    'tax_rate' => '8.0',
    'shipping_rate' => '15.00',
    'free_shipping_minimum' => '100.00'
];

$settings = array_merge($default_settings, $current_settings);
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Settings</h1>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <!-- General Settings -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Site Name</label>
                            <input type="text" class="form-control" name="settings[site_name]" 
                                   value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Email</label>
                            <input type="email" class="form-control" name="settings[site_email]" 
                                   value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Phone</label>
                            <input type="text" class="form-control" name="settings[site_phone]" 
                                   value="<?php echo htmlspecialchars($settings['site_phone']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Address</label>
                            <textarea class="form-control" name="settings[site_address]" rows="3"><?php echo htmlspecialchars($settings['site_address']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- E-commerce Settings -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">E-commerce Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select class="form-select" name="settings[currency]">
                                <option value="USD" <?php echo $settings['currency'] == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                <option value="EUR" <?php echo $settings['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                <option value="GBP" <?php echo $settings['currency'] == 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" name="settings[tax_rate]" 
                                   value="<?php echo htmlspecialchars($settings['tax_rate']); ?>" 
                                   step="0.1" min="0" max="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Rate ($)</label>
                            <input type="number" class="form-control" name="settings[shipping_rate]" 
                                   value="<?php echo htmlspecialchars($settings['shipping_rate']); ?>" 
                                   step="0.01" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Free Shipping Minimum ($)</label>
                            <input type="number" class="form-control" name="settings[free_shipping_minimum]" 
                                   value="<?php echo htmlspecialchars($settings['free_shipping_minimum']); ?>" 
                                   step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
