<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin
requireLogin();
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/internal-links/');
    exit();
}

// Set current page for sidebar
$current_page = 'internal-links';

// Get link ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get link data
$query = "SELECT * FROM internal_links WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/internal-links/');
    exit();
}

$link = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $title = sanitizeInput($_POST['title']);
        $url = sanitizeInput($_POST['url']);
        $description = sanitizeInput($_POST['description']);
        $category = sanitizeInput($_POST['category']);
        $icon = sanitizeInput($_POST['icon']);
        $bg_color = sanitizeInput($_POST['bg_color']);
        
        if (empty($title) || empty($url) || empty($category)) {
            $error = 'Title, URL and category are required';
        } else {
            // Format icon class
            $icon = $icon ?: 'fas fa-link';
            // Format bg_color
            $bg_color = $bg_color ?: 'bg-primary';
            
            $query = "UPDATE internal_links SET title = ?, url = ?, description = ?, category = ?, icon = ?, bg_color = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param('ssssssi', $title, $url, $description, $category, $icon, $bg_color, $id);
            
            if ($stmt->execute()) {
                header('Location: ' . SITE_URL . '/internal-links/');
                exit();
            } else {
                $error = 'Failed to update link';
            }
            
            $stmt->close();
        }
    }
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
                <h1 class="h2">Edit Internal Link</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Links
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($link['title']); ?>" required>
                    <div class="invalid-feedback">
                        Please provide a title.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="url" class="form-label">URL</label>
                    <input type="url" class="form-control" id="url" name="url" value="<?php echo htmlspecialchars($link['url']); ?>" required>
                    <div class="invalid-feedback">
                        Please provide a valid URL.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($link['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($link['category']); ?>" required>
                    <div class="invalid-feedback">
                        Please provide a category.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="icon" class="form-label">Icon (Font Awesome class)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="<?php echo htmlspecialchars($link['icon']); ?>"></i></span>
                        <input type="text" class="form-control" id="icon" name="icon" value="<?php echo htmlspecialchars($link['icon']); ?>" placeholder="fas fa-link">
                    </div>
                    <small class="text-muted">Leave empty to use the default link icon</small>
                </div>

                <div class="mb-3">
                    <label for="bg_color" class="form-label">Background Color</label>
                    <select class="form-select" id="bg_color" name="bg_color">
                        <option value="bg-primary" <?php echo ($link['bg_color'] === 'bg-primary') ? 'selected' : ''; ?>>Primary Blue</option>
                        <option value="bg-success" <?php echo ($link['bg_color'] === 'bg-success') ? 'selected' : ''; ?>>Success Green</option>
                        <option value="bg-danger" <?php echo ($link['bg_color'] === 'bg-danger') ? 'selected' : ''; ?>>Danger Red</option>
                        <option value="bg-warning" <?php echo ($link['bg_color'] === 'bg-warning') ? 'selected' : ''; ?>>Warning Yellow</option>
                        <option value="bg-info" <?php echo ($link['bg_color'] === 'bg-info') ? 'selected' : ''; ?>>Info Cyan</option>
                        <option value="bg-dark" <?php echo ($link['bg_color'] === 'bg-dark') ? 'selected' : ''; ?>>Dark</option>
                        <option value="bg-brown" <?php echo ($link['bg_color'] === 'bg-brown') ? 'selected' : ''; ?>>Brown</option>
                        <option value="bg-coral" <?php echo ($link['bg_color'] === 'bg-coral') ? 'selected' : ''; ?>>Coral</option>
                        <option value="bg-maroon" <?php echo ($link['bg_color'] === 'bg-maroon') ? 'selected' : ''; ?>>Maroon</option>
                        <option value="bg-teal" <?php echo ($link['bg_color'] === 'bg-teal') ? 'selected' : ''; ?>>Teal</option>
                    </select>
                    <small class="text-muted">Choose a background color for the quick link box</small>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Update Link</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include '../includes/footer.php'; ?> 