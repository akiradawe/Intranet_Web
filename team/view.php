<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Get team member ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get team member data
$query = "SELECT id, username, full_name, email, role, phone, mobile_phone, job_title, department, profile_picture, bio, birth_date 
          FROM users 
          WHERE id = ? AND status = 'active'";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/team/');
    exit();
}

$teamMember = $result->fetch_assoc();

// Set current page for sidebar
$current_page = 'team';

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
                <h1 class="h2">Team Member Profile</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?php echo SITE_URL; ?>/team/" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Team
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <?php if (!empty($teamMember['profile_picture'])): ?>
                                <img src="<?php echo SITE_URL; ?>/public/uploads/profile-pictures/<?php echo htmlspecialchars($teamMember['profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($teamMember['full_name'] ?: $teamMember['username']); ?>" 
                                     class="rounded-circle mb-3" 
                                     style="width: 200px; height: 200px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTIwIDEyMCI+CiAgPHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMjAiIGZpbGw9IiMwMDdiZmYiLz4KICA8dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iNDBweCI+CiAgICA8dHNwYW4gZHk9Ii0uM2VtIj7wn5GpPC90c3Bhbj4KICA8L3RleHQ+Cjwvc3ZnPg=='">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                     style="width: 200px; height: 200px;">
                                    <i class="fas fa-user fa-4x"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h2 class="h4 mb-1"><?php echo htmlspecialchars($teamMember['full_name'] ?: $teamMember['username']); ?></h2>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($teamMember['job_title'] ?: 'Team Member'); ?></p>
                            
                            <div class="d-grid gap-2">
                                <a href="mailto:<?php echo htmlspecialchars($teamMember['email']); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-2"></i>Send Email
                                </a>
                                <?php if ($teamMember['phone']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($teamMember['phone']); ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-phone me-2"></i>Call Office
                                    </a>
                                <?php endif; ?>
                                <?php if ($teamMember['mobile_phone']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($teamMember['mobile_phone']); ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-mobile-alt me-2"></i>Call Mobile
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="card-title h5 mb-4">About</h3>
                            <?php if (!empty($teamMember['bio'])): ?>
                                <div class="bio-content">
                                    <?php echo nl2br(htmlspecialchars($teamMember['bio'])); ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No bio available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title h5 mb-4">Contact Information</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted d-block">Email</label>
                                        <a href="mailto:<?php echo htmlspecialchars($teamMember['email']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($teamMember['email']); ?>
                                        </a>
                                    </div>
                                    
                                    <?php if ($teamMember['phone']): ?>
                                        <div class="mb-3">
                                            <label class="text-muted d-block">Office Phone</label>
                                            <a href="tel:<?php echo htmlspecialchars($teamMember['phone']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($teamMember['phone']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($teamMember['mobile_phone']): ?>
                                        <div class="mb-3">
                                            <label class="text-muted d-block">Mobile Phone</label>
                                            <a href="tel:<?php echo htmlspecialchars($teamMember['mobile_phone']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($teamMember['mobile_phone']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($teamMember['birth_date']): ?>
                                        <div class="mb-3">
                                            <label class="text-muted d-block">Birthday</label>
                                            <i class="fas fa-birthday-cake text-danger me-2"></i>
                                            <?php 
                                            if ($_SESSION['user']['role'] === 'admin') {
                                                echo date('F j, Y', strtotime($teamMember['birth_date']));
                                            } else {
                                                echo date('F j', strtotime($teamMember['birth_date']));
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted d-block">Department</label>
                                        <?php echo htmlspecialchars($teamMember['department']); ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="text-muted d-block">Role</label>
                                        <?php echo htmlspecialchars(ucfirst($teamMember['role'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.bio-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #2c3e50;
}
.bio-content p {
    margin-bottom: 1rem;
}
.bio-content p:last-child {
    margin-bottom: 0;
}
</style>

<?php include '../includes/footer.php'; ?> 
 
 