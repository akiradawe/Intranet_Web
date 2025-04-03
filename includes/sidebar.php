<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard' || $current_page === 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'announcements' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/announcements/">
                    <i class="fas fa-bullhorn"></i> Announcements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'internal-links' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/internal-links/">
                    <i class="fas fa-link"></i> Internal Links
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'mountable-services' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/mountable-services/">
                    <i class="fas fa-cubes"></i> Mountable Services
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'team' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/team">
                    <i class="fas fa-users"></i> Team
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'calendar' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/calendar">
                    <i class="fas fa-calendar-alt"></i> Calendar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'my-account' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/my-account">
                    <i class="fas fa-user"></i> My Account
                </a>
            </li>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'users' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/users/">
                    <i class="fas fa-user-cog"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'site-settings' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/admin/site-settings.php">
                    <i class="fas fa-cog"></i> Site Settings
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <hr class="my-3">

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    background-color: var(--bs-sidebar-bg) !important;
}

.sidebar .nav-link {
    font-weight: 500;
    color: var(--bs-sidebar-text) !important;
    padding: 0.5rem 1rem;
    margin: 0.2rem 0;
    border-radius: 0.25rem;
    transition: all 0.3s ease;
}

.sidebar .nav-link:hover {
    color: var(--bs-sidebar-active) !important;
    background-color: rgba(0, 0, 0, 0.05);
}

.sidebar .nav-link.active {
    color: var(--bs-sidebar-active) !important;
    background-color: rgba(0, 0, 0, 0.1);
}

.sidebar .nav-link i {
    margin-right: 0.5rem;
    width: 1.25rem;
    text-align: center;
}

.sidebar .nav-link:hover i {
    color: var(--bs-sidebar-active) !important;
}

.sidebar .nav-link.active i {
    color: var(--bs-sidebar-active) !important;
}

.sidebar hr {
    border-color: rgba(0, 0, 0, 0.1);
    margin: 1rem 0;
}
</style> 