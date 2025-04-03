<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Set current page for sidebar
$current_page = 'team';

// Get all active users
$query = "SELECT id, username, full_name, email, role, phone, mobile_phone, job_title, department, profile_picture, bio FROM users WHERE status = 'active' ORDER BY department, full_name";
$result = $db->query($query);

if (!$result) {
    $error = 'Failed to load team members';
    $departments = [];
} else {
    // Group users by department
    $departments = [
        'Administration' => [],
        'Research and Development' => [],
        'Medical' => [],
        'Engineering' => [],
        'Marketing' => [],
        'Parttime and Intern' => []
    ];

    while ($user = $result->fetch_assoc()) {
        if (isset($departments[$user['department']])) {
            $departments[$user['department']][] = $user;
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
                <h1 class="h2">Our Team</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php foreach ($departments as $department => $users): ?>
                <?php if (!empty($users)): ?>
                    <div class="department-section mb-4">
                        <div class="department-header d-flex align-items-center mb-3 pb-2 border-bottom">
                            <h2 class="h4 mb-0"><?php echo htmlspecialchars($department); ?></h2>
                            <span class="badge bg-primary ms-2"><?php echo count($users); ?> members</span>
                        </div>
                        
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($users as $teamMember): ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <?php if ($teamMember['profile_picture']): ?>
                                                <img src="<?php echo SITE_URL; ?>/public/uploads/profile-pictures/<?php echo htmlspecialchars($teamMember['profile_picture']); ?>" 
                                                     alt="<?php echo htmlspecialchars($teamMember['full_name'] ?: $teamMember['username']); ?>" 
                                                     class="rounded-circle mb-3" 
                                                     style="width: 120px; height: 120px; object-fit: cover;"
                                                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTIwIDEyMCI+CiAgPHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMjAiIGZpbGw9IiMwMDdiZmYiLz4KICA8dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iNDBweCI+CiAgICA8dHNwYW4gZHk9Ii0uM2VtIj7wn5GpPC90c3Bhbj4KICA8L3RleHQ+Cjwvc3ZnPg=='">
                                            <?php else: ?>
                                                <i class="fas fa-user fa-4x mb-3 text-primary"></i>
                                            <?php endif; ?>
                                            
                                            <h3 class="card-title h5 mb-1">
                                                <?php echo htmlspecialchars($teamMember['full_name'] ?: $teamMember['username']); ?>
                                            </h3>
                                            <p class="card-subtitle text-muted mb-3">
                                                <?php echo htmlspecialchars($teamMember['job_title'] ?: 'Team Member'); ?>
                                            </p>

                                            <div class="d-grid gap-2">
                                                <?php if ($teamMember['email']): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($teamMember['email']); ?>" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($teamMember['email']); ?>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($teamMember['phone']): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($teamMember['phone']); ?>" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($teamMember['phone']); ?>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($teamMember['mobile_phone']): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($teamMember['mobile_phone']); ?>" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-mobile-alt me-2"></i><?php echo htmlspecialchars($teamMember['mobile_phone']); ?>
                                                    </a>
                                                <?php endif; ?>

                                                <a href="view.php?id=<?php echo $teamMember['id']; ?>" class="btn btn-primary d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-user-circle me-2"></i>View Profile
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </main>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Team Member Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="profile-picture-container mb-3">
                            <img id="modalProfilePicture" src="" alt="Profile Picture" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h3 id="modalName" class="mb-1"></h3>
                        <p id="modalTitle" class="text-muted mb-3"></p>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">Contact Information</h6>
                            <div id="modalContactInfo"></div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="text-primary">Department</h6>
                            <p id="modalDepartment"></p>
                        </div>
                        
                        <div>
                            <h6 class="text-primary">About</h6>
                            <div id="modalBio" class="bio-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openProfileModal(button) {
    const modal = document.getElementById('profileModal');
    const profilePicture = document.getElementById('modalProfilePicture');
    
    // Set profile picture
    const profilePicturePath = button.dataset.profilePicture;
    if (profilePicturePath) {
        profilePicture.src = `/public/uploads/profile-pictures/${profilePicturePath}`;
    } else {
        profilePicture.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTIwIDEyMCI+CiAgPHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMjAiIGZpbGw9IiMwMDdiZmYiLz4KICA8dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iNDBweCI+CiAgICA8dHNwYW4gZHk9Ii0uM2VtIj7wn5GpPC90c3Bhbj4KICA8L3RleHQ+Cjwvc3ZnPg==';
    }
    profilePicture.onerror = function() {
        this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjAiIGhlaWdodD0iMTIwIiB2aWV3Qm94PSIwIDAgMTIwIDEyMCI+CiAgPHJlY3Qgd2lkdGg9IjEyMCIgaGVpZ2h0PSIxMjAiIGZpbGw9IiMwMDdiZmYiLz4KICA8dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiIGZvbnQtc2l6ZT0iNDBweCI+CiAgICA8dHNwYW4gZHk9Ii0uM2VtIj7wn5GpPC90c3Bhbj4KICA8L3RleHQ+Cjwvc3ZnPg==';
    };

    // Set other information
    document.getElementById('modalName').textContent = button.dataset.name;
    document.getElementById('modalTitle').textContent = button.dataset.title;
    document.getElementById('modalDepartment').textContent = button.dataset.department;
    
    // Set contact information
    const contactInfo = document.getElementById('modalContactInfo');
    contactInfo.innerHTML = `
        <div class="mb-2">
            <i class="fas fa-envelope text-primary me-2"></i>
            <a href="mailto:${button.dataset.email}" class="text-decoration-none">${button.dataset.email}</a>
        </div>
        ${button.dataset.phone ? `
            <div class="mb-2">
                <i class="fas fa-phone text-primary me-2"></i>${button.dataset.phone}
            </div>
        ` : ''}
        ${button.dataset.mobile ? `
            <div class="mb-2">
                <i class="fas fa-mobile-alt text-primary me-2"></i>${button.dataset.mobile}
            </div>
        ` : ''}
    `;
    
    // Handle bio content
    const bioElement = document.getElementById('modalBio');
    try {
        const bio = button.dataset.bio;
        if (bio && bio.trim() !== '') {
            bioElement.innerHTML = bio;
        } else {
            bioElement.innerHTML = '<p>No bio available.</p>';
        }
    } catch (error) {
        console.error('Error displaying bio:', error);
        bioElement.innerHTML = '<p>No bio available.</p>';
    }

    // Show the modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}
</script>

<?php include '../includes/footer.php'; ?> 