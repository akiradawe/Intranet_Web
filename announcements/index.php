<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Set current page for sidebar
$current_page = 'announcements';

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
                <h1 class="h2">Announcements</h1>
                <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'editor'): ?>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Announcement
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <?php
            // Get announcements with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $per_page = 10;
            $offset = ($page - 1) * $per_page;

            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM announcements";
            $count_result = $db->query($count_query);
            $total_announcements = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_announcements / $per_page);

            // Get announcements
            $query = "SELECT a.*, u.username 
                     FROM announcements a 
                     LEFT JOIN users u ON a.user_id = u.id 
                     ORDER BY created_at DESC 
                     LIMIT ? OFFSET ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param('ii', $per_page, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>

            <div class="row">
                <?php while ($announcement = $result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($announcement['image_path'])): ?>
                                <img src="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($announcement['image_path']); ?>" 
                                     class="card-img-top" alt="Announcement Image"
                                     style="height: 400px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                <div class="card-text announcement-content">
                                    <?php echo $announcement['content']; ?>
                                </div>
                                
                                <?php
                                // Get attachments for this announcement
                                $attachments_query = "SELECT * FROM attachments WHERE announcement_id = ?";
                                $attachments_stmt = $db->prepare($attachments_query);
                                $attachments_stmt->bind_param('i', $announcement['id']);
                                $attachments_stmt->execute();
                                $attachments_result = $attachments_stmt->get_result();
                                
                                if ($attachments_result->num_rows > 0):
                                ?>
                                    <div class="mt-3">
                                        <h6 class="mb-2">Attachments:</h6>
                                        <div class="list-group">
                                            <?php while ($attachment = $attachments_result->fetch_assoc()): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-paperclip me-2"></i>
                                                        <span class="me-3"><?php echo htmlspecialchars($attachment['filename']); ?></span>
                                                        <a href="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($attachment['filename']); ?>" 
                                                           class="btn btn-sm btn-outline-primary me-2" 
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
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        Posted by <?php echo htmlspecialchars($announcement['username']); ?> on 
                                        <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?>
                                    </small>
                                    <div class="btn-group">
                                        <a href="view.php?id=<?php echo $announcement['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i> Read More
                                        </a>
                                        <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'editor'): ?>
                                            <a href="edit.php?id=<?php echo $announcement['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="delete.php" method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="id" value="<?php echo $announcement['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this announcement?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 