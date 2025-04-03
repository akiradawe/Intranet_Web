<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';

// Set current page for sidebar highlighting
$current_page = 'dashboard';

// Get user data
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>

            <!-- Welcome Message -->
            <div class="alert alert-success mb-4">
                Welcome back, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!
            </div>

            <!-- Upcoming Events and Birthdays Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt text-primary"></i> 
                                This Month's Upcoming Events
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get current month's events
                            $current_month = (int)date('m');
                            $eventsQuery = "SELECT e.*, u.full_name 
                                          FROM events e 
                                          LEFT JOIN users u ON e.user_id = u.id 
                                          WHERE MONTH(e.event_date) = ? 
                                          ORDER BY e.event_date ASC";
                            $eventsStmt = $db->prepare($eventsQuery);
                            $eventsStmt->bind_param('i', $current_month);
                            $eventsStmt->execute();
                            $events = $eventsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                            if (count($events) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($events as $event): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <?php
                                                    switch ($event['event_type']) {
                                                        case 'holiday':
                                                            echo '<i class="fas fa-flag text-success"></i>';
                                                            break;
                                                        case 'meeting':
                                                            echo '<i class="fas fa-users text-primary"></i>';
                                                            break;
                                                        default:
                                                            echo '<i class="fas fa-calendar-alt text-secondary"></i>';
                                                    }
                                                    ?>
                                                    <span class="ms-2">
                                                        <?php echo htmlspecialchars($event['title']); ?>
                                                        <small class="text-muted">
                                                            (<?php echo date('M j', strtotime($event['event_date'])); ?>)
                                                        </small>
                                                    </span>
                                                </div>
                                                <?php if (in_array($_SESSION['user']['role'], ['admin', 'editor'])): ?>
                                                    <a href="<?php echo SITE_URL; ?>/admin/events/edit.php?id=<?php echo $event['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">No events scheduled for this month.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-birthday-cake text-danger"></i> 
                                This Month's Upcoming Birthdays
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get current month's birthdays
                            $birthdaysQuery = "SELECT id, full_name, profile_picture, birth_date 
                                             FROM users 
                                             WHERE MONTH(birth_date) = ? 
                                             AND status = 'active' 
                                             ORDER BY DAY(birth_date) ASC";
                            $birthdaysStmt = $db->prepare($birthdaysQuery);
                            $birthdaysStmt->bind_param('i', $current_month);
                            $birthdaysStmt->execute();
                            $birthdays = $birthdaysStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                            if (count($birthdays) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($birthdays as $birthday): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <?php if ($birthday['profile_picture']): ?>
                                                    <img src="<?php echo SITE_URL; ?>/public/uploads/profile-pictures/<?php echo htmlspecialchars($birthday['profile_picture']); ?>" 
                                                         alt="<?php echo htmlspecialchars($birthday['full_name']); ?>" 
                                                         class="rounded-circle me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div><?php echo htmlspecialchars($birthday['full_name']); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo date('F j', strtotime($birthday['birth_date'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">No birthdays this month.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bullhorn me-2"></i>ANNOUNCEMENTS
                        </h5>
                        <a href="announcements/" class="btn btn-light btn-sm">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $query = "SELECT a.*, u.username 
                             FROM announcements a 
                             LEFT JOIN users u ON a.user_id = u.id 
                             ORDER BY created_at DESC 
                             LIMIT 4";
                    $result = $db->query($query);
                    
                    if ($result->num_rows > 0):
                    ?>
                        <div id="announcementsCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php 
                                $first = true;
                                while ($announcement = $result->fetch_assoc()): 
                                ?>
                                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <div class="card h-100">
                                                    <?php if (!empty($announcement['image_path'])): ?>
                                                        <img src="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($announcement['image_path']); ?>" 
                                                             class="card-img-top" alt="Announcement Image"
                                                             style="height: 400px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                                        <p class="card-text">
                                                            <?php echo substr(strip_tags($announcement['content']), 0, 300); ?>...
                                                        </p>
                                                        
                                                        <?php if (!empty($announcement['attachments'])): ?>
                                                            <div class="attachments-section mb-3">
                                                                <h6 class="text-muted mb-2">Attachments:</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php 
                                                                    $attachments = json_decode($announcement['attachments'], true);
                                                                    foreach ($attachments as $attachment): 
                                                                    ?>
                                                                        <div class="attachment-item">
                                                                            <a href="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($attachment['filename']); ?>" 
                                                                               class="btn btn-sm btn-outline-primary" 
                                                                               target="_blank">
                                                                                <i class="fas fa-file me-1"></i> View
                                                                            </a>
                                                                            <a href="<?php echo SITE_URL; ?>/public/uploads/<?php echo htmlspecialchars($attachment['filename']); ?>" 
                                                                               class="btn btn-sm btn-outline-success" 
                                                                               download>
                                                                                <i class="fas fa-download me-1"></i> Download
                                                                            </a>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                                            <small class="text-muted">
                                                                Posted by <?php echo htmlspecialchars($announcement['username']); ?> on 
                                                                <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?>
                                                            </small>
                                                            <a href="announcements/view.php?id=<?php echo $announcement['id']; ?>" 
                                                               class="btn btn-primary btn-sm">
                                                                Read More
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                $first = false;
                                endwhile; 
                                ?>
                            </div>
                            <?php if ($result->num_rows > 1): ?>
                                <div class="carousel-controls position-absolute top-50 start-0 end-0 translate-middle-y d-flex justify-content-between px-4">
                                    <button class="btn btn-light rounded-circle carousel-control-prev" type="button" data-bs-target="#announcementsCarousel" data-bs-slide="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="btn btn-light rounded-circle carousel-control-next" type="button" data-bs-target="#announcementsCarousel" data-bs-slide="next">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No announcements yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">QUICK LINKS</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php
                        $query = "SELECT * FROM internal_links ORDER BY category, title";
                        $result = $db->query($query);
                        
                        if ($result->num_rows > 0):
                            while ($link = $result->fetch_assoc()):
                                // Use the icon from database or fallback to default
                                $icon = !empty($link['icon']) ? $link['icon'] : 'fa-link';
                                $bg_color = !empty($link['bg_color']) ? $link['bg_color'] : 'bg-primary';
                        ?>
                            <div class="col-md-2 col-sm-4 col-6">
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" class="text-decoration-none" target="_blank">
                                    <div class="quick-link-box <?php echo $bg_color; ?> text-white text-center p-3 rounded">
                                        <i class="<?php echo $icon; ?> fa-2x mb-2"></i>
                                        <div><?php echo htmlspecialchars($link['title']); ?></div>
                                    </div>
                                </a>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="col-12">
                                <p class="text-muted mb-0">No quick links available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Mountable Services -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-network-wired me-2"></i>AVAILABLE SERVICES
                        </h5>
                        <a href="mountable-services/" class="btn btn-light btn-sm">
                            View More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $query = "SELECT * FROM mountable_services ORDER BY name LIMIT 6";
                    $result = $db->query($query);
                    
                    if ($result->num_rows > 0):
                    ?>
                        <div class="row g-3">
                            <?php while ($service = $result->fetch_assoc()): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas <?php echo htmlspecialchars($service['icon'] ?: 'fa-network-wired'); ?> me-2"></i>
                                                <?php echo htmlspecialchars($service['name']); ?>
                                            </h5>
                                            <p class="card-text small"><?php echo htmlspecialchars($service['description']); ?></p>
                                            
                                            <div class="mt-3">
                                                <h6 class="text-muted">Mounting Instructions:</h6>
                                                
                                                <!-- Windows Instructions -->
                                                <div class="mb-3">
                                                    <h6 class="text-primary">
                                                        <i class="fab fa-windows me-2"></i>Windows
                                                    </h6>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($service['windows_command']); ?>" readonly>
                                                        <button class="btn btn-outline-primary copy-btn" data-command="<?php echo htmlspecialchars($service['windows_command']); ?>">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted">Press Windows + R, paste the command, and press Enter</small>
                                                </div>

                                                <!-- Mac/Linux Instructions -->
                                                <div>
                                                    <h6 class="text-primary">
                                                        <i class="fas fa-desktop me-2"></i>Mac/Linux
                                                    </h6>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($service['mac_linux_command']); ?>" readonly>
                                                        <button class="btn btn-outline-primary copy-btn" data-command="<?php echo htmlspecialchars($service['mac_linux_command']); ?>">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted">Open Finder/File Manager and press Cmd/Ctrl + K, paste the command</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No services available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.quick-link-box {
    transition: transform 0.2s;
    cursor: pointer;
}
.quick-link-box:hover {
    transform: translateY(-5px);
}
.bg-brown {
    background-color: #8B4513;
}
.bg-coral {
    background-color: #FF6F61;
}
.bg-maroon {
    background-color: #800000;
}
.bg-teal {
    background-color: #008080;
}
.carousel-item img {
    filter: brightness(0.7);
}
.carousel-caption {
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    padding: 20px;
}
.carousel-controls {
    z-index: 10;
}
.carousel-controls .btn {
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(248, 249, 250, 0.9);
    border: 1px solid #dee2e6;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.carousel-controls .btn:hover {
    background-color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.carousel-controls .btn i {
    font-size: 1.2em;
    color: #495057;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy button functionality
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const command = this.dataset.command;
            navigator.clipboard.writeText(command).then(() => {
                // Show feedback
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 