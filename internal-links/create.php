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
            
            $query = "INSERT INTO internal_links (title, url, description, category, icon, bg_color) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bind_param('ssssss', $title, $url, $description, $category, $icon, $bg_color);
            
            if ($stmt->execute()) {
                header('Location: ' . SITE_URL . '/internal-links/');
                exit();
            } else {
                $error = 'Failed to create link';
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
                <h1 class="h2">Create Internal Link</h1>
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
                    <input type="text" class="form-control" id="title" name="title" required>
                    <div class="invalid-feedback">
                        Please provide a title.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="url" class="form-label">URL</label>
                    <input type="url" class="form-control" id="url" name="url" required>
                    <div class="invalid-feedback">
                        Please provide a valid URL.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control" id="category" name="category" required>
                    <div class="invalid-feedback">
                        Please provide a category.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="icon" class="form-label">Icon (Font Awesome class)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-icons"></i></span>
                        <input type="text" class="form-control" id="icon" name="icon" placeholder="fas fa-link">
                    </div>
                    <small class="text-muted">Leave empty to use the default link icon</small>
                </div>

                <div class="mb-3">
                    <label for="bg_color" class="form-label">Background Color</label>
                    <select class="form-select" id="bg_color" name="bg_color">
                        <option value="bg-primary">Primary Blue</option>
                        <option value="bg-success">Success Green</option>
                        <option value="bg-danger">Danger Red</option>
                        <option value="bg-warning">Warning Yellow</option>
                        <option value="bg-info">Info Cyan</option>
                        <option value="bg-dark">Dark</option>
                        <option value="bg-brown">Brown</option>
                        <option value="bg-coral">Coral</option>
                        <option value="bg-maroon">Maroon</option>
                        <option value="bg-teal">Teal</option>
                    </select>
                    <small class="text-muted">Choose a background color for the quick link box</small>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Create Link</button>
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