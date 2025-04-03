<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Set current page for sidebar
$current_page = 'internal-links';

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
                <h1 class="h2">Internal Links</h1>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Link
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <?php
            // Get all links grouped by category
            $query = "SELECT * FROM internal_links ORDER BY category, title";
            $result = $db->query($query);
            
            if ($result->num_rows > 0) {
                $links_by_category = [];
                while ($row = $result->fetch_assoc()) {
                    $category = $row['category'] ?: 'Uncategorized';
                    if (!isset($links_by_category[$category])) {
                        $links_by_category[$category] = [];
                    }
                    $links_by_category[$category][] = $row;
                }

                // Display links by category
                foreach ($links_by_category as $category => $links) {
                    echo '<div class="card mb-4">';
                    echo '<div class="card-header bg-light">';
                    echo '<h5 class="mb-0">' . htmlspecialchars($category) . '</h5>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    echo '<div class="row">';
                    
                    foreach ($links as $link) {
                        echo '<div class="col-md-4 mb-3">';
                        echo '<div class="card h-100">';
                        echo '<div class="card-body">';
                        echo '<div class="d-flex justify-content-between align-items-start">';
                        echo '<h5 class="card-title"><i class="' . htmlspecialchars($link['icon']) . '"></i> ' . htmlspecialchars($link['title']) . '</h5>';
                        if ($_SESSION['user']['role'] === 'admin') {
                            echo '<div class="btn-group">';
                            echo '<a href="edit.php?id=' . $link['id'] . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>';
                            echo '<a href="delete.php?id=' . $link['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Are you sure you want to delete this link?\')"><i class="fas fa-trash"></i></a>';
                            echo '</div>';
                        }
                        echo '</div>';
                        if (!empty($link['description'])) {
                            echo '<p class="card-text">' . htmlspecialchars($link['description']) . '</p>';
                        }
                        echo '<a href="' . htmlspecialchars($link['url']) . '" class="btn btn-primary" target="_blank">Visit Link</a>';
                        echo '</div></div></div>';
                    }
                    
                    echo '</div></div></div>';
                }
            } else {
                echo '<div class="alert alert-info">No internal links available.</div>';
            }
            ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 
 
 