<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin
requireLogin();
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . SITE_URL);
    exit();
}

// Set current page for sidebar
$current_page = 'site-settings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        // Update site name
        $site_name = sanitizeInput($_POST['site_name']);
        $updateNameQuery = "UPDATE site_settings SET setting_value = ? WHERE setting_key = 'site_name'";
        $stmt = $db->prepare($updateNameQuery);
        $stmt->bind_param('s', $site_name);
        $stmt->execute();

        // Update primary color
        $primary_color = sanitizeInput($_POST['primary_color']);
        $updatePrimaryColorQuery = "UPDATE color_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'primary_color'";
        $stmt = $db->prepare($updatePrimaryColorQuery);
        $stmt->bind_param('s', $primary_color);
        $stmt->execute();

        // Update secondary color
        $secondary_color = sanitizeInput($_POST['secondary_color']);
        $updateSecondaryColorQuery = "UPDATE color_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'secondary_color'";
        $stmt = $db->prepare($updateSecondaryColorQuery);
        $stmt->bind_param('s', $secondary_color);
        $stmt->execute();

        // Update other color settings
        $colorSettings = [
            'sidebar_bg' => $_POST['sidebar_bg'],
            'sidebar_text' => $_POST['sidebar_text'],
            'sidebar_active' => $_POST['sidebar_active'],
            'header_bg' => $_POST['header_bg'],
            'header_text' => $_POST['header_text'],
            'body_bg' => $_POST['body_bg'],
            'text_color' => $_POST['text_color'],
            'link_color' => $_POST['link_color'],
            'success_color' => $_POST['success_color'],
            'success_text' => $_POST['success_text'],
            'danger_color' => $_POST['danger_color'],
            'warning_color' => $_POST['warning_color'],
            'info_color' => $_POST['info_color'],
            'footer_bg' => $_POST['footer_bg'],
            'footer_text' => $_POST['footer_text']
        ];

        foreach ($colorSettings as $key => $value) {
            $updateColorQuery = "UPDATE color_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?";
            $stmt = $db->prepare($updateColorQuery);
            $stmt->bind_param('ss', $value, $key);
            $stmt->execute();
        }

        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['site_logo'];
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Only image files are allowed!';
            } else {
                // Validate file size (2MB limit)
                $maxSize = 2 * 1024 * 1024;
                if ($file['size'] > $maxSize) {
                    $error = 'File size must be less than 2MB';
                } else {
                    // Create upload directory if it doesn't exist
                    $uploadDir = '../public/uploads/logos';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'site-logo.' . $extension;
                    $filepath = $uploadDir . '/' . $filename;

                    // Delete old logo if exists
                    $oldLogoQuery = "SELECT setting_value FROM site_settings WHERE setting_key = 'site_logo'";
                    $oldLogoResult = $db->query($oldLogoQuery);
                    $oldLogo = $oldLogoResult->fetch_assoc();
                    
                    if ($oldLogo && $oldLogo['setting_value'] !== 'default-logo.png') {
                        $oldFilepath = $uploadDir . '/' . $oldLogo['setting_value'];
                        if (file_exists($oldFilepath)) {
                            unlink($oldFilepath);
                        }
                    }

                    // Move uploaded file
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // Update database
                        $updateLogoQuery = "UPDATE site_settings SET setting_value = ? WHERE setting_key = 'site_logo'";
                        $stmt = $db->prepare($updateLogoQuery);
                        $stmt->bind_param('s', $filename);
                        $stmt->execute();
                        $success = 'Site settings updated successfully';
                    } else {
                        $error = 'Failed to upload logo';
                    }
                }
            }
        } else {
            $success = 'Site settings updated successfully';
        }
    }
}

// Get current settings
$query = "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('site_name', 'site_logo', 'site_description', 'admin_email', 'maintenance_mode')";
$result = $db->query($query);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get color settings
$colorQuery = "SELECT setting_key, setting_value FROM color_settings";
$colorResult = $db->query($colorQuery);
$colors = [];
while ($row = $colorResult->fetch_assoc()) {
    $colors[$row['setting_key']] = $row['setting_value'];
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Site Settings</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-4">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                   value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="site_logo" class="form-label">Site Logo</label>
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($settings['site_logo']): ?>
                                    <img src="<?php echo SITE_URL; ?>/public/uploads/logos/<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                                         alt="Current Logo" 
                                         style="max-height: 50px; width: auto;">
                                <?php endif; ?>
                                <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                            </div>
                            <small class="text-muted">Recommended size: 200x50px. Maximum file size: 2MB</small>
                        </div>

                        <div class="mb-3">
                            <label for="site_description" class="form-label">Site Description</label>
                            <textarea class="form-control" id="site_description" name="site_description" 
                                      rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                        </div>

                        <h4 class="mb-3">Color Settings</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="primary_color" class="form-label">Primary Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="primary_color" 
                                               name="primary_color" value="<?php echo htmlspecialchars($colors['primary_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['primary_color']); ?>" 
                                               id="primary_color_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="secondary_color" class="form-label">Secondary Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="secondary_color" 
                                               name="secondary_color" value="<?php echo htmlspecialchars($colors['secondary_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['secondary_color']); ?>" 
                                               id="secondary_color_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="sidebar_bg" class="form-label">Sidebar Background</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="sidebar_bg" 
                                               name="sidebar_bg" value="<?php echo htmlspecialchars($colors['sidebar_bg']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['sidebar_bg']); ?>" 
                                               id="sidebar_bg_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="sidebar_text" class="form-label">Sidebar Text</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="sidebar_text" 
                                               name="sidebar_text" value="<?php echo htmlspecialchars($colors['sidebar_text']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['sidebar_text']); ?>" 
                                               id="sidebar_text_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="sidebar_active" class="form-label">Sidebar Active Item</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="sidebar_active" 
                                               name="sidebar_active" value="<?php echo htmlspecialchars($colors['sidebar_active']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['sidebar_active']); ?>" 
                                               id="sidebar_active_text" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="header_bg" class="form-label">Header Background</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="header_bg" 
                                               name="header_bg" value="<?php echo htmlspecialchars($colors['header_bg']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['header_bg']); ?>" 
                                               id="header_bg_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="header_text" class="form-label">Header Text</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="header_text" 
                                               name="header_text" value="<?php echo htmlspecialchars($colors['header_text']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['header_text']); ?>" 
                                               id="header_text_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="body_bg" class="form-label">Body Background</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="body_bg" 
                                               name="body_bg" value="<?php echo htmlspecialchars($colors['body_bg']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['body_bg']); ?>" 
                                               id="body_bg_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="text_color" class="form-label">Text Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="text_color" 
                                               name="text_color" value="<?php echo htmlspecialchars($colors['text_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['text_color']); ?>" 
                                               id="text_color_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="link_color" class="form-label">Link Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="link_color" 
                                               name="link_color" value="<?php echo htmlspecialchars($colors['link_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['link_color']); ?>" 
                                               id="link_color_text" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Alert Colors</h5>
                                <div class="mb-3">
                                    <label for="success_color" class="form-label">Success Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="success_color" 
                                               name="success_color" value="<?php echo htmlspecialchars($colors['success_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['success_color']); ?>" 
                                               id="success_color_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="success_text" class="form-label">Success Text Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="success_text" 
                                               name="success_text" value="<?php echo htmlspecialchars($colors['success_text']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['success_text']); ?>" 
                                               id="success_text_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="danger_color" class="form-label">Danger Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="danger_color" 
                                               name="danger_color" value="<?php echo htmlspecialchars($colors['danger_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['danger_color']); ?>" 
                                               id="danger_color_text" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="warning_color" class="form-label">Warning Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="warning_color" 
                                               name="warning_color" value="<?php echo htmlspecialchars($colors['warning_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['warning_color']); ?>" 
                                               id="warning_color_text" readonly>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="info_color" class="form-label">Info Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="info_color" 
                                               name="info_color" value="<?php echo htmlspecialchars($colors['info_color']); ?>">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($colors['info_color']); ?>" 
                                               id="info_color_text" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Admin Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                   value="<?php echo htmlspecialchars($settings['admin_email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                            <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                <option value="0" <?php echo $settings['maintenance_mode'] == 0 ? 'selected' : ''; ?>>Disabled</option>
                                <option value="1" <?php echo $settings['maintenance_mode'] == 1 ? 'selected' : ''; ?>>Enabled</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Update color text inputs when color pickers change
const colorInputs = document.querySelectorAll('input[type="color"]');
colorInputs.forEach(input => {
    input.addEventListener('input', function(e) {
        document.getElementById(this.id + '_text').value = e.target.value;
    });
});
</script>

<?php include '../includes/footer.php'; ?> 