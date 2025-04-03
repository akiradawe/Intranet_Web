<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin or editor
requireLogin();
if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'editor') {
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit();
}

// Set current page for sidebar
$current_page = 'announcements';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user']['id'];

    // Validate required fields
    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        // Handle image upload
        $image_path = '';
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

        // Handle file attachments
        $attachments = [];
        if (isset($_FILES['attachments'])) {
            $upload_dir = '../public/uploads/';
            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['attachments']['name'][$key];
                    $file_type = $_FILES['attachments']['type'][$key];
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
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

        // Insert announcement into database
        $query = "INSERT INTO announcements (title, content, image_path, user_id, created_at) 
                 VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssi", $title, $content, $image_path, $user_id);
        
        if ($stmt->execute()) {
            $announcement_id = $db->insert_id;
            
            // Insert attachments if any
            if (!empty($attachments)) {
                $query = "INSERT INTO attachments (announcement_id, filename, filepath, filetype, created_at) 
                         VALUES (?, ?, ?, ?, NOW())";
                $stmt = $db->prepare($query);
                
                foreach ($attachments as $attachment) {
                    $stmt->bind_param("isss", 
                        $announcement_id, 
                        $attachment['filename'], 
                        $attachment['filepath'], 
                        $attachment['filetype']
                    );
                    $stmt->execute();
                }
            }
            
            $_SESSION['success'] = "Announcement created successfully.";
            header('Location: index.php');
            exit();
        } else {
            $error = "Error creating announcement.";
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
                <h1 class="h2">Create New Announcement</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($_FILES['image'])): ?>
                <div class="alert alert-info">
                    <h5>Debug Information:</h5>
                    <pre><?php print_r($_FILES['image']); ?></pre>
                    <p>Upload Error Code: <?php echo $_FILES['image']['error']; ?></p>
                    <p>PHP Error: <?php echo error_get_last()['message'] ?? 'No PHP error'; ?></p>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Featured Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPEG, PNG, GIF</div>
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                            <div class="form-text">You can select multiple files</div>
                        </div>

                        <div class="text-end">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Announcement</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 