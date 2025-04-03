<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin
requireLogin();
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/mountable-services/');
    exit();
}

// Set current page for sidebar
$current_page = 'mountable-services';

// Get service ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get service data
$query = "SELECT * FROM mountable_services WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/mountable-services/');
    exit();
}

$service = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $windows_command = sanitizeInput($_POST['windows_command']);
        $mac_linux_command = sanitizeInput($_POST['mac_linux_command']);
        $icon = sanitizeInput($_POST['icon']);
        
        if (empty($name) || empty($windows_command) || empty($mac_linux_command)) {
            $error = 'Name, Windows command, and Mac/Linux command are required';
        } else {
            // Format icon class
            $icon = $icon ?: 'fa-network-wired';
            
            $query = "UPDATE mountable_services SET name = ?, description = ?, windows_command = ?, mac_linux_command = ?, icon = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param('sssssi', $name, $description, $windows_command, $mac_linux_command, $icon, $id);
            
            if ($stmt->execute()) {
                header('Location: ' . SITE_URL . '/mountable-services/');
                exit();
            } else {
                $error = 'Failed to update service';
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
                <h1 class="h2">Edit Mountable Service</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Services
                    </a>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Service Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($service['name']); ?>" required>
                    <div class="invalid-feedback">
                        Please provide a service name.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($service['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="windows_command" class="form-label">Windows Command</label>
                    <input type="text" class="form-control" id="windows_command" name="windows_command" value="<?php echo htmlspecialchars($service['windows_command']); ?>" required>
                    <small class="text-muted">Example: \\\\server\\share</small>
                    <div class="invalid-feedback">
                        Please provide a Windows command.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="mac_linux_command" class="form-label">Mac/Linux Command</label>
                    <input type="text" class="form-control" id="mac_linux_command" name="mac_linux_command" value="<?php echo htmlspecialchars($service['mac_linux_command']); ?>" required>
                    <small class="text-muted">Example: smb://server/share</small>
                    <div class="invalid-feedback">
                        Please provide a Mac/Linux command.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="icon" class="form-label">Icon (Font Awesome class)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="<?php echo htmlspecialchars($service['icon']); ?>"></i></span>
                        <input type="text" class="form-control" id="icon" name="icon" value="<?php echo htmlspecialchars($service['icon']); ?>" placeholder="fa-network-wired">
                    </div>
                    <small class="text-muted">Leave empty to use the default network icon</small>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Update Service</button>
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
 
 