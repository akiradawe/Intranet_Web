<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../config/config.php';

// Debug database connection
error_log('Database connection status: ' . ($db ? 'Connected' : 'Not connected'));

// Check if user is logged in and is admin
requireLogin();
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . SITE_URL);
    exit();
}

// Get all users
$query = "SELECT id, username, full_name, email, role, status, created_at 
          FROM users 
          ORDER BY created_at DESC";
$result = $db->query($query);

// Debug query result
if (!$result) {
    error_log('Error executing users query: ' . $db->error);
    die('Error executing users query: ' . $db->error);
}

$users = $result->fetch_all(MYSQLI_ASSOC);

// Debug users data
error_log('Users data: ' . print_r($users, true));

// Set current page for sidebar
$current_page = 'users';

// Include header
include '../includes/header.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">User Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'editor' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($user['role'] ?? 'user'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'inactive'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'] ?? 'now')); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-user" 
                                                data-bs-toggle="modal" data-bs-target="#userModal"
                                                data-user='<?php echo json_encode($user); ?>'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                                            <form action="delete.php" method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">User Details</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Username:</strong> <span id="modal-username"></span></p>
                            <p><strong>Name:</strong> <span id="modal-name"></span></p>
                            <p><strong>Email:</strong> <span id="modal-email"></span></p>
                            <p><strong>Role:</strong> <span id="modal-role"></span></p>
                            <p><strong>Department:</strong> <span id="modal-department"></span></p>
                            <p><strong>Job Title:</strong> <span id="modal-job-title"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Phone:</strong> <span id="modal-phone"></span></p>
                            <p><strong>Mobile:</strong> <span id="modal-mobile"></span></p>
                            <p><strong>Bio:</strong> <span id="modal-bio"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle view user button click
        const viewButtons = document.querySelectorAll('.view-user');
        
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userData = JSON.parse(this.dataset.user);
                
                // Update modal content
                document.getElementById('modal-username').textContent = userData.username;
                document.getElementById('modal-name').textContent = userData.full_name;
                document.getElementById('modal-email').textContent = userData.email;
                document.getElementById('modal-role').textContent = userData.role.charAt(0).toUpperCase() + userData.role.slice(1);
                document.getElementById('modal-department').textContent = userData.department;
                document.getElementById('modal-job-title').textContent = userData.job_title;
                document.getElementById('modal-phone').textContent = userData.phone || 'N/A';
                document.getElementById('modal-mobile').textContent = userData.mobile || 'N/A';
                document.getElementById('modal-bio').textContent = userData.bio || 'No bio available';
            });
        });

        // Listen for modal close event
        document.getElementById('userModal').addEventListener('hidden.bs.modal', function () {
            // Remove the modal backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            
            // Remove any extra styles Bootstrap might have added
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            
            // Refresh the page
            window.location.reload();
        });
    });
    </script>
</main>

<?php include '../includes/footer.php'; ?> 