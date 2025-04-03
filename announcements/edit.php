<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin or editor
requireLogin();
if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'editor') {
    header('Location: ' . SITE_URL . '/announcements/');
    exit();
}

// Set current page for sidebar
$current_page = 'announcements';

// Get announcement ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch announcement data
$query = "SELECT * FROM announcements WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/announcements/');
    exit();
}

$announcement = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $title = sanitizeInput($_POST['title']);
        $content = $_POST['content']; // Rich text content doesn't need sanitization
        
        if (empty($title) || empty($content)) {
            $error = 'Please fill in all required fields';
        } else {
            // Handle image upload
            $image_path = $announcement['image_path']; // Keep existing image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['image']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    $upload_dir = '../public/uploads/';
                    if (!file_exists($upload_dir)) {
                        if (!mkdir($upload_dir, 0777, true)) {
                            $error = "Failed to create upload directory.";
                        }
                    }
                    
                    if (!isset($error)) {
                        // Delete old image if exists
                        if ($announcement['image_path']) {
                            $old_image_path = $upload_dir . $announcement['image_path'];
                            if (file_exists($old_image_path)) {
                                unlink($old_image_path);
                            }
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $file_name = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_path = $file_name; // Store just the filename
                        } else {
                            $error = "Failed to move uploaded file. Error: " . error_get_last()['message'];
                        }
                    }
                } else {
                    $error = "Invalid file type. Only JPG, PNG, and GIF images are allowed.";
                }
            } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = array(
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
                );
                $error = "Upload error: " . $upload_errors[$_FILES['image']['error']];
            }
            
            // Handle image deletion
            if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
                if ($announcement['image_path']) {
                    $old_image_path = '../public/uploads/' . $announcement['image_path'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                    $image_path = null;
                }
            }

            // Handle file attachments
            $attachments = [];
            if (isset($_FILES['attachments'])) {
                $upload_dir = '../public/uploads/';
                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES['attachments']['name'][$key];
                        $file_type = $_FILES['attachments']['type'][$key];
                        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $new_file_name = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $new_file_name;
                        
                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $attachments[] = [
                                'filename' => $new_file_name,
                                'filepath' => $target_path,
                                'filetype' => $file_type
                            ];
                        }
                    }
                }
            }

            // Delete attachments if requested
            if (isset($_POST['delete_attachments']) && is_array($_POST['delete_attachments'])) {
                foreach ($_POST['delete_attachments'] as $attachment_id) {
                    // Get attachment info before deleting
                    $query = "SELECT filepath FROM attachments WHERE id = ? AND announcement_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param('ii', $attachment_id, $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($attachment = $result->fetch_assoc()) {
                        // Delete the file
                        if (file_exists($attachment['filepath'])) {
                            unlink($attachment['filepath']);
                        }
                        // Delete from database
                        $query = "DELETE FROM attachments WHERE id = ? AND announcement_id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->bind_param('ii', $attachment_id, $id);
                        $stmt->execute();
                    }
                }
            }
            
            if (!isset($error)) {
                $query = "UPDATE announcements SET title = ?, content = ?, image_path = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param('sssi', $title, $content, $image_path, $id);
                
                if ($stmt->execute()) {
                    header('Location: ' . SITE_URL . '/announcements/');
                    exit();
                } else {
                    $error = 'Failed to update announcement';
                }
                
                $stmt->close();
            }
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
                <h1 class="h2">Edit Announcement</h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Announcements
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($announcement['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($announcement['content']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Featured Image</label>
                            <?php if ($announcement['image_path']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($announcement['image_path']); ?>" 
                                         alt="Current announcement image" 
                                         class="img-thumbnail" 
                                         style="max-height: 200px;">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="delete_image" value="1" id="delete_image">
                                    <label class="form-check-label" for="delete_image">
                                        Delete current image
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/gif">
                            <div class="form-text">Supported formats: JPEG, PNG, GIF</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Attachments</label>
                            <?php
                            $query = "SELECT * FROM attachments WHERE announcement_id = ?";
                            $stmt = $db->prepare($query);
                            $stmt->bind_param('i', $id);
                            $stmt->execute();
                            $attachments_result = $stmt->get_result();
                            
                            if ($attachments_result->num_rows > 0):
                            ?>
                                <div class="list-group mb-2">
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
                                                   class="btn btn-sm btn-outline-success me-2" 
                                                   download>
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="delete_attachments[]" 
                                                       value="<?php echo $attachment['id']; ?>" 
                                                       id="delete_attachment_<?php echo $attachment['id']; ?>">
                                                <label class="form-check-label" for="delete_attachment_<?php echo $attachment['id']; ?>">
                                                    Delete
                                                </label>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No attachments</p>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">Add New Attachments</label>
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                            <div class="form-text">You can select multiple files</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 