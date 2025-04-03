<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configurations first
require_once '../config/config.php';
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Validate month and year
if ($month < 1 || $month > 12) {
    $month = (int)date('m');
}
if ($year < 2000 || $year > 2100) {
    $year = (int)date('Y');
}

// Get first day of the month
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay);

// Get month name
$monthName = date('F', $firstDay);

// Get events for the current month
$query = "SELECT * FROM events 
          WHERE MONTH(event_date) = ? AND YEAR(event_date) = ?
          ORDER BY event_date ASC";
$stmt = $db->prepare($query);
$stmt->bind_param('ii', $month, $year);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);

// Set current page for sidebar
$current_page = 'calendar';

// Get all users' birthdays for the current month
$birthdayQuery = "SELECT id, full_name, profile_picture, birth_date 
                 FROM users 
                 WHERE MONTH(birth_date) = ? 
                 AND status = 'active'";
$birthdayStmt = $db->prepare($birthdayQuery);
$birthdayStmt->bind_param('i', $month);
$birthdayStmt->execute();
$birthdays = $birthdayStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug: Print birthdays
echo "<!-- Debug: Birthdays for current month -->";
echo "<!-- Number of birthdays: " . count($birthdays) . " -->";
foreach ($birthdays as $birthday) {
    echo "<!-- Birthday: " . htmlspecialchars($birthday['full_name'] ?? '') . " on " . ($birthday['birth_date'] ?? '') . " -->";
}

// Combine events and birthdays
$allEvents = array_merge($events, array_map(function($birthday) {
    return [
        'id' => 'birthday_' . ($birthday['id'] ?? ''),
        'title' => ($birthday['full_name'] ?? '') . "'s Birthday",
        'description' => 'Birthday',
        'event_date' => $birthday['birth_date'] ?? '',
        'event_type' => 'birthday',
        'user_id' => $birthday['id'] ?? '',
        'full_name' => $birthday['full_name'] ?? '',
        'profile_picture' => $birthday['profile_picture'] ?? ''
    ];
}, $birthdays));

// Debug: Print combined events
echo "<!-- Debug: Combined events -->";
echo "<!-- Total number of events (including birthdays): " . count($allEvents) . " -->";

// Calculate previous and next month/year
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Include sidebar -->
        <?php require_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Calendar</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if (in_array($_SESSION['user']['role'], ['admin', 'editor'])): ?>
                        <a href="<?php echo SITE_URL; ?>/admin/events/" class="btn btn-sm btn-primary me-2">
                            <i class="fas fa-cog"></i> Manage Events
                        </a>
                    <?php endif; ?>
                    <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-chevron-left"></i> Previous Month
                    </a>
                    <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-sm btn-outline-secondary">
                        Next Month <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="text-center mb-4"><?php echo $monthName . ' ' . $year; ?></h2>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sunday</th>
                                    <th>Monday</th>
                                    <th>Tuesday</th>
                                    <th>Wednesday</th>
                                    <th>Thursday</th>
                                    <th>Friday</th>
                                    <th>Saturday</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $day = 1;
                                $total_days = $daysInMonth + $dayOfWeek;
                                $rows = ceil($total_days / 7);

                                for ($i = 0; $i < $rows; $i++) {
                                    echo '<tr>';
                                    for ($j = 0; $j < 7; $j++) {
                                        if ($i === 0 && $j < $dayOfWeek) {
                                            echo '<td class="bg-light"></td>';
                                            continue;
                                        }
                                        if ($day > $daysInMonth) {
                                            echo '<td class="bg-light"></td>';
                                            continue;
                                        }

                                        $currentDay = $day;
                                        $hasEvents = false;
                                        $eventList = [];
                                        
                                        // Check for events on this day
                                        foreach ($allEvents as $event) {
                                            if (!empty($event['event_date'])) {
                                                $eventDate = strtotime($event['event_date']);
                                                if ($eventDate && date('j', $eventDate) == $currentDay) {
                                                    $hasEvents = true;
                                                    $eventList[] = $event;
                                                }
                                            }
                                        }
                                        
                                        if ($hasEvents) {
                                            echo '<td class="table-primary position-relative">';
                                        } else {
                                            echo '<td class="position-relative">';
                                        }
                                        echo $currentDay;
                                        
                                        // Display events for this day
                                        if ($hasEvents) {
                                            echo '<div class="event-list mt-1">';
                                            foreach ($eventList as $event) {
                                                if ($event['event_type'] === 'birthday') {
                                                    echo '<div class="event-item birthday-event" data-bs-toggle="tooltip" data-bs-placement="top" title="' . htmlspecialchars($event['title'] ?? '') . '">';
                                                    echo '<i class="fas fa-birthday-cake text-danger"></i>';
                                                    if ($_SESSION['user']['role'] === 'admin') {
                                                        echo ' ' . htmlspecialchars($event['full_name'] ?? '');
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    echo '<div class="event-item">';
                                                    // Add different icons for different event types
                                                    switch ($event['event_type'] ?? '') {
                                                        case 'holiday':
                                                            echo '<i class="fas fa-flag text-success"></i> ';
                                                            break;
                                                        case 'meeting':
                                                            echo '<i class="fas fa-users text-primary"></i> ';
                                                            break;
                                                        default:
                                                            echo '<i class="fas fa-calendar-alt text-primary"></i> ';
                                                    }
                                                    echo htmlspecialchars($event['title'] ?? '');
                                                    echo '</div>';
                                                }
                                            }
                                            echo '</div>';
                                        }
                                        
                                        echo '</td>';
                                        $day++;
                                    }
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 