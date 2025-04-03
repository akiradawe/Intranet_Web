<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Set current page for sidebar
$current_page = 'my-account';

// Get complete user data
$userId = $_SESSION['user']['id'];
$query = "SELECT id, username, full_name, email, role, phone, mobile_phone, job_title, bio, profile_picture, birth_date, department, created_at FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /login');
    exit;
}

$user = $result->fetch_assoc();

// Update session with complete user data
$_SESSION['user'] = array_merge($_SESSION['user'], $user);

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
                <h1 class="h2">My Account</h1>
                <a href="edit.php" class="btn btn-outline">
                    <i class="fas fa-edit me-2"></i>Edit Profile
                </a>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <?php if ($user['profile_picture']): ?>
                                <img src="<?php echo SITE_URL; ?>/public/uploads/profile-pictures/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>" 
                                     class="rounded-circle mb-3" 
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTIwIDEyMCI+CiAgPHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMjAiIGZpbGw9IiMwMDdiZmYiLz4KICA8dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iNDBweCI+CiAgICA8dHNwYW4gZHk9Ii0uM2VtIj7wn5GpPC90c3Bhbj4KICA8L3RleHQ+Cjwvc3ZnPg=='">
                                <form action="delete-picture.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete your profile picture?')">
                                        <i class="fas fa-trash me-2"></i>Delete Picture
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                     style="width: 150px; height: 150px;">
                                    <i class="fas fa-user fa-4x"></i>
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

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php 
                                    echo $_SESSION['success'];
                                    unset($_SESSION['success']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form id="uploadForm" action="upload-picture.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" name="profile_picture" id="profile_picture" accept="image/*">
                                    <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-camera me-2"></i>Upload Picture
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Profile Information</h5>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Username</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Full Name</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['full_name'] ?: 'Not set'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Email</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Phone</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['phone'] ?: 'Not set'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Mobile Phone</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['mobile_phone'] ?: 'Not set'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Job Title</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['job_title'] ?: 'Not set'); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Role</div>
                                <div class="col-sm-9"><?php echo htmlspecialchars($user['role']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3 text-muted">Birthday</div>
                                <div class="col-sm-9"><?php if ($user['birth_date']): ?><i class="fas fa-birthday-cake text-danger me-2"></i><?php echo date('F j, Y', strtotime($user['birth_date'])); ?><?php endif; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">About Me</h5>
                            <form action="edit-bio.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="mb-3">
                                    <textarea name="bio" class="form-control" rows="5" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?: ''); ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Bio
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Account Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="edit.php" class="btn btn-primary">
                                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                                </a>
                                <a href="change-password.php" class="btn btn-outline">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.querySelector('form[action="edit-bio.php"]').addEventListener('submit', function(e) {
    const bio = this.querySelector('textarea[name="bio"]').value.trim();
    if (bio.length > 1000) {
        e.preventDefault();
        alert('Bio cannot exceed 1000 characters');
    }
});
</script>

<?php include '../includes/footer.php'; ?> 