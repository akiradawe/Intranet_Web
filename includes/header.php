<?php
// Debug database connection
error_log('Database connection status: ' . ($db ? 'Connected' : 'Not connected'));

// Get site settings
$query = "SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('site_name', 'site_logo')";
$result = $db->query($query);
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get color settings
$colorQuery = "SELECT setting_key, setting_value FROM color_settings";
$colorResult = $db->query($colorQuery);
$colors = [];

// Debug: Check if query executed successfully
if (!$colorResult) {
    error_log('Error executing color settings query: ' . $db->error);
} else {
    while ($row = $colorResult->fetch_assoc()) {
        $colors[$row['setting_key']] = $row['setting_value'];
    }
}

// Debug: Print color settings
error_log('Color Settings: ' . print_r($colors, true));

// Set default colors if none are loaded
if (empty($colors)) {
    $colors = [
        'primary_color' => '#0d6efd',
        'secondary_color' => '#6c757d',
        'success_color' => '#198754',
        'success_text' => '#ffffff',
        'danger_color' => '#dc3545',
        'warning_color' => '#ffc107',
        'info_color' => '#0dcaf0',
        'body_bg' => '#f8f9fa',
        'text_color' => '#212529',
        'link_color' => '#0d6efd',
        'header_bg' => '#212529',
        'header_text' => '#ffffff',
        'sidebar_bg' => '#f8f9fa',
        'sidebar_text' => '#333333',
        'sidebar_active' => '#0d6efd',
        'footer_bg' => '#f8f9fa',
        'footer_text' => '#6c757d'
    ];
    error_log('Using default colors');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name'] ?? 'IRCAD Africa Intranet'); ?></title>

    
    <!-- Standard favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>/public/uploads/logo-square.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>/public/uploads/logo-square.png">
    
    <!-- Apple Touch Icon (for iOS devices) -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>/public/uploads/logo-square.png">

    <!-- Web App Manifest (for progressive web apps) -->
    <link rel="manifest" href="<?php echo SITE_URL; ?>/public/uploads/logo-square.png">  
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --bs-primary: <?php echo $colors['primary_color']; ?>;
            --bs-secondary: <?php echo $colors['secondary_color']; ?>;
            --bs-success: <?php echo $colors['success_color']; ?>;
            --bs-success-text: <?php echo $colors['success_text']; ?>;
            --bs-danger: <?php echo $colors['danger_color']; ?>;
            --bs-warning: <?php echo $colors['warning_color']; ?>;
            --bs-info: <?php echo $colors['info_color']; ?>;
            --bs-body-bg: <?php echo $colors['body_bg']; ?>;
            --bs-body-color: <?php echo $colors['text_color']; ?>;
            --bs-link-color: <?php echo $colors['link_color']; ?>;
            --bs-header-bg: <?php echo $colors['header_bg']; ?>;
            --bs-header-text: <?php echo $colors['header_text']; ?>;
            --bs-sidebar-bg: <?php echo $colors['sidebar_bg']; ?>;
            --bs-sidebar-text: <?php echo $colors['sidebar_text']; ?>;
            --bs-sidebar-active: <?php echo $colors['sidebar_active']; ?>;
            --bs-footer-bg: <?php echo $colors['footer_bg']; ?>;
            --bs-footer-text: <?php echo $colors['footer_text']; ?>;
        }

        /* Force background colors */
        body {
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color) !important;
            min-height: 100vh;
        }

        .navbar {
            background-color: var(--bs-header-bg) !important;
        }

        .navbar-brand, .navbar-nav .nav-link {
            color: var(--bs-header-text) !important;
        }

        .sidebar {
            background-color: var(--bs-sidebar-bg) !important;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: var(--bs-sidebar-text) !important;
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link.active {
            color: var(--bs-sidebar-active) !important;
            background-color: rgba(0, 0, 0, 0.1) !important;
        }

        .sidebar .nav-link:hover {
            color: var(--bs-sidebar-active) !important;
            background-color: rgba(0, 0, 0, 0.05) !important;
        }

        .sidebar .nav-link i {
            color: var(--bs-sidebar-text) !important;
            margin-right: 0.5rem;
            width: 1.25rem;
            text-align: center;
        }

        .sidebar .nav-link.active i,
        .sidebar .nav-link:hover i {
            color: var(--bs-sidebar-active) !important;
        }

        a {
            color: var(--bs-link-color);
        }

        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .btn-primary:hover {
            background-color: <?php echo adjustBrightness($colors['primary_color'] ?? '#0d6efd', -10); ?>;
            border-color: <?php echo adjustBrightness($colors['primary_color'] ?? '#0d6efd', -10); ?>;
        }

        .btn-outline-primary {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .btn-outline-primary:hover {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .text-primary {
            color: var(--bs-primary) !important;
        }

        .bg-primary {
            background-color: var(--bs-primary) !important;
        }

        .border-primary {
            border-color: var(--bs-primary) !important;
        }

        .btn-secondary {
            background-color: var(--bs-secondary);
            border-color: var(--bs-secondary);
        }

        .btn-secondary:hover {
            background-color: <?php echo adjustBrightness($colors['secondary_color'] ?? '#6c757d', -10); ?>;
            border-color: <?php echo adjustBrightness($colors['secondary_color'] ?? '#6c757d', -10); ?>;
        }

        .btn-outline-secondary {
            color: var(--bs-secondary);
            border-color: var(--bs-secondary);
        }

        .btn-outline-secondary:hover {
            background-color: var(--bs-secondary);
            border-color: var(--bs-secondary);
        }

        .text-secondary {
            color: var(--bs-secondary) !important;
        }

        .bg-secondary {
            background-color: var(--bs-secondary) !important;
        }

        .border-secondary {
            border-color: var(--bs-secondary) !important;
        }

        .alert-success {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
            color: var(--bs-success-text) !important;
        }

        .alert-danger {
            background-color: var(--bs-danger);
            border-color: var(--bs-danger);
        }

        .alert-warning {
            background-color: var(--bs-warning);
            border-color: var(--bs-warning);
        }

        .alert-info {
            background-color: var(--bs-info);
            border-color: var(--bs-info);
        }

        .badge-success {
            background-color: var(--bs-success);
        }

        .badge-danger {
            background-color: var(--bs-danger);
        }

        .badge-warning {
            background-color: var(--bs-warning);
        }

        .badge-info {
            background-color: var(--bs-info);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand img {
            height: 40px;
            width: auto;
        }
        .sidebar-sticky {
            position: sticky;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
        }

        /* User dropdown styles */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-dropdown .dropdown-toggle {
            color: var(--bs-header-text) !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Remove default Bootstrap dropdown arrow */
        .dropdown-toggle::after {
            display: none !important;
        }

        .user-dropdown .dropdown-menu {
            min-width: 200px;
            padding: 0.5rem 0;
        }

        .user-dropdown .dropdown-item {
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-dropdown .dropdown-item i {
            width: 1.25rem;
            text-align: center;
        }

        .user-dropdown .dropdown-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .user-dropdown .dropdown-divider {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="<?php echo SITE_URL; ?>">
            <?php if (isset($settings['site_logo']) && $settings['site_logo'] !== 'default-logo.png'): ?>
                <img src="<?php echo SITE_URL; ?>/public/uploads/logos/<?php echo htmlspecialchars($settings['site_logo']); ?>" 
                     alt="<?php echo htmlspecialchars($settings['site_name']); ?>">
            <?php endif; ?>
            <span><?php echo htmlspecialchars($settings['site_name'] ?? 'IRCAD Africa Intranet'); ?></span>
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="w-100"></div>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link" href="<?php echo SITE_URL; ?>/my-account/">
                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['username']); ?>
                </a>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Content will be inserted here -->
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 