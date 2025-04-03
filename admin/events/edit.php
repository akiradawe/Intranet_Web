<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configurations first
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Start session after configurations are loaded
session_start();

// Check authentication and role
require_once __DIR__ . '/../../includes/auth_check.php';
if (!in_array($_SESSION['user']['role'], ['admin', 'editor'])) {
    header('Location: ' . SITE_URL);
    exit();
}

// Get event ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get event data
$query = "SELECT * FROM events WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/calendar/');
    exit();
}

$event = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = trim($_POST['event_date']);
    $event_type = trim($_POST['event_type']);

    // Validate required fields
    if (empty($title) || empty($event_date) || empty($event_type)) {
        $error = "Please fill in all required fields.";
    } else {
        // Update event
        $updateQuery = "UPDATE events SET 
            title = ?, 
            description = ?,
            event_date = ?,
            event_type = ?
            WHERE id = ?";
        
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bind_param('ssssi', 
            $title,
            $description,
            $event_date,
            $event_type,
            $id
        );

        if ($updateStmt->execute()) {
            header('Location: ' . SITE_URL . '/calendar/');
            exit();
        } else {
            $error = "Error updating event.";
        }
    }
}

// Set current page for sidebar
$current_page = 'calendar';

// Include header
require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Include sidebar -->
        <?php require_once '../../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Event</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?php echo SITE_URL; ?>/calendar/" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Calendar
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="event_date" class="form-label">Event Date *</label>
                            <input type="date" class="form-control" id="event_date" name="event_date" 
                                   value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="event_type" class="form-label">Event Type *</label>
                            <select class="form-select" id="event_type" name="event_type" required>
                                <option value="">Select Event Type</option>
                                <option value="holiday" <?php echo $event['event_type'] === 'holiday' ? 'selected' : ''; ?>>Holiday</option>
                                <option value="meeting" <?php echo $event['event_type'] === 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                                <option value="other" <?php echo $event['event_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 
 
 