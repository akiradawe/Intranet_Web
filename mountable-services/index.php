<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Set current page for sidebar
$current_page = 'mountable-services';

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
                <h1 class="h2">Mountable Services</h1>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                        <i class="fas fa-plus"></i> Add New Service
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <?php
                // Get all mountable services
                $query = "SELECT * FROM mountable_services ORDER BY name";
                $result = $db->query($query);
                
                if ($result->num_rows > 0) {
                    while ($service = $result->fetch_assoc()) {
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas <?php echo htmlspecialchars($service['icon']); ?> me-2"></i>
                                        <?php echo htmlspecialchars($service['name']); ?>
                                    </h5>
                                    <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                    
                                    <div class="mt-3">
                                        <h6>Mounting Instructions:</h6>
                                        
                                        <!-- Windows Instructions -->
                                        <div class="mb-3">
                                            <h6 class="text-primary">
                                                <i class="fab fa-windows me-2"></i>Windows
                                            </h6>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($service['windows_command']); ?>" readonly>
                                                <button class="btn btn-outline-primary copy-btn" data-command="<?php echo htmlspecialchars($service['windows_command']); ?>">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Press Windows + R, paste the command, and press Enter</small>
                                        </div>

                                        <!-- Mac/Linux Instructions -->
                                        <div>
                                            <h6 class="text-primary">
                                                <i class="fas fa-desktop me-2"></i>Mac/Linux
                                            </h6>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($service['mac_linux_command']); ?>" readonly>
                                                <button class="btn btn-outline-primary copy-btn" data-command="<?php echo htmlspecialchars($service['mac_linux_command']); ?>">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Open Finder/File Manager and press Cmd/Ctrl + K, paste the command</small>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?php echo $service['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $service['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this service?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            No mountable services found. <?php if ($_SESSION['user']['role'] === 'admin'): ?>Click the "Add New Service" button to create one.<?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </main>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Mountable Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="create.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Windows Command</label>
                        <input type="text" class="form-control" name="windows_command" required>
                        <small class="text-muted">Example: \\\\server\\share</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mac/Linux Command</label>
                        <input type="text" class="form-control" name="mac_linux_command" required>
                        <small class="text-muted">Example: smb://server/share</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" class="form-control" name="icon" value="fa-network-wired">
                        <small class="text-muted">Example: fa-network-wired, fa-folder, fa-server</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy button functionality
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const command = this.dataset.command;
            navigator.clipboard.writeText(command).then(() => {
                // Show feedback
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?> 
 
 