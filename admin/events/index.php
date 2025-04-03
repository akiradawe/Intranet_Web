<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configurations first
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

// Start session after configurations are loaded
//session_start();

// Check authentication and role
require_once __DIR__ . '/../../includes/auth_check.php';
if (!in_array($_SESSION['user']['role'], ['admin', 'editor'])) {
    header('Location: ' . SITE_URL);
    exit();
}

// Handle event deletion
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $deleteQuery = "DELETE FROM events WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bind_param('i', $id);
    $deleteStmt->execute();
    header('Location: ' . SITE_URL . '/admin/events/');
    exit();
}

// Get all events
$query = "SELECT e.*, u.full_name 
          FROM events e 
          LEFT JOIN users u ON e.user_id = u.id 
          ORDER BY e.event_date DESC";
$result = $db->query($query);
$events = $result->fetch_all(MYSQLI_ASSOC);

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
                <h1 class="h2">Manage Events</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?php echo SITE_URL; ?>/calendar/" class="btn btn-secondary me-2">
                        <i class="fas fa-calendar"></i> View Calendar
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/events/create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Event
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                                        <td><?php echo date('F j, Y', strtotime($event['event_date'])); ?></td>
                                        <td>
                                            <?php
                                            $typeClass = '';
                                            switch ($event['event_type']) {
                                                case 'holiday':
                                                    $typeClass = 'text-success';
                                                    break;
                                                case 'meeting':
                                                    $typeClass = 'text-primary';
                                                    break;
                                                default:
                                                    $typeClass = 'text-secondary';
                                            }
                                            ?>
                                            <span class="<?php echo $typeClass; ?>">
                                                <i class="fas fa-<?php echo $event['event_type'] === 'holiday' ? 'flag' : ($event['event_type'] === 'meeting' ? 'users' : 'calendar-alt'); ?>"></i>
                                                <?php echo ucfirst($event['event_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['description']); ?></td>
                                        <td><?php echo htmlspecialchars($event['full_name'] ?? 'System'); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo SITE_URL; ?>/admin/events/edit.php?id=<?php echo $event['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this event?');">
                                                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 
 