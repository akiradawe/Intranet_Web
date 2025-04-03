<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Get announcement ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get announcement data
$query = "SELECT a.*, u.username 
          FROM announcements a 
          LEFT JOIN users u ON a.user_id = u.id 
          WHERE a.id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/announcements/');
    exit();
}

$announcement = $result->fetch_assoc();

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
                <h1 class="h2">Announcement Details</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="card">
                <?php if (!empty($announcement['image_path'])): ?>
                    <img src="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($announcement['image_path']); ?>" 
                         class="card-img-top" alt="Announcement Image"
                         style="max-height: 500px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body">
                    <h2 class="card-title mb-4"><?php echo htmlspecialchars($announcement['title']); ?></h2>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="text-muted">
                                <i class="fas fa-user me-1"></i> Posted by <?php echo htmlspecialchars($announcement['username']); ?>
                            </span>
                            <span class="text-muted ms-3">
                                <i class="fas fa-calendar me-1"></i> <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?>
                            </span>
                        </div>
                        <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] === $announcement['user_id'])): ?>
                            <div class="btn-group">
                                <a href="edit.php?id=<?php echo $announcement['id']; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?php echo $announcement['id']; ?>" 
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this announcement?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="announcement-content mb-4">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>

                    <?php if (!empty($announcement['attachments'])): ?>
                        <div class="attachments-section">
                            <h5 class="mb-3">Attachments</h5>
                            <div class="list-group">
                                <?php 
                                $attachments = json_decode($announcement['attachments'], true);
                                foreach ($attachments as $attachment): 
                                ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-paperclip me-2"></i>
                                            <span><?php echo htmlspecialchars($attachment['filename']); ?></span>
                                        </div>
                                        <div class="btn-group">
                                            <a href="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($attachment['filename']); ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($attachment['filename']); ?>" 
                                               class="btn btn-sm btn-outline-success" 
                                               download>
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.announcement-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #2c3e50;
}
.card-img-top {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}
.attachments-section .list-group-item {
    transition: background-color 0.2s;
}
.attachments-section .list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<?php include '../includes/footer.php'; ?> 