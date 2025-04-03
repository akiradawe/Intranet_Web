<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Debug database connection
error_log('Database connection status: ' . ($db ? 'Connected' : 'Not connected'));

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . SITE_URL);
    exit;
}

// Set current page for sidebar
$current_page = 'users';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = trim($_POST['role']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $department = trim($_POST['department']);
        $job_title = trim($_POST['job_title']);
        $phone = trim($_POST['phone']);
        $mobile_phone = trim($_POST['mobile_phone']);
        $bio = trim($_POST['bio']);
        $birth_date = trim($_POST['birth_date']);

        // Validate required fields
        if (empty($username) || empty($password) || empty($role) || empty($full_name) || empty($email) || empty($department) || empty($job_title)) {
            $error = 'Username, password, role, full name, email, department, and job title are required';
        } else {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } else {
                // Check if username or email already exists
                $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
                $checkStmt = $db->prepare($checkQuery);
                if (!$checkStmt) {
                    $error = 'Database error: ' . $db->error;
                } else {
                    $checkStmt->bind_param('ss', $username, $email);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();

                    if ($checkResult->num_rows > 0) {
                        $error = 'Username or email already exists';
                    } else {
                        // Hash password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                        // Insert new user
                        $insertQuery = "INSERT INTO users (username, password, role, full_name, email, department, job_title, phone, mobile_phone, bio, birth_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                        $insertStmt = $db->prepare($insertQuery);
                        
                        if (!$insertStmt) {
                            $error = 'Database error: ' . $db->error;
                        } else {
                            $insertStmt->bind_param('sssssssssss', 
                                $username,
                                $hashedPassword,
                                $role,
                                $full_name,
                                $email,
                                $department,
                                $job_title,
                                $phone,
                                $mobile_phone,
                                $bio,
                                $birth_date
                            );

                            if ($insertStmt->execute()) {
                                $_SESSION['success'] = 'User created successfully';
                                header('Location: index.php');
                                exit;
                            } else {
                                $error = 'Failed to create user: ' . $db->error;
                                error_log('Create user error: ' . $db->error);
                            }
                        }
                    }
                }
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Create New User</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading">Error!</h4>
            <p><?php echo $error; ?></p>
            <?php if (isset($db->error)): ?>
                <hr>
                <p class="mb-0">Database Error: <?php echo $db->error; ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="create.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="user">User</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="birth_date" class="form-label">Birthday</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date">
                </div>

                <div class="mb-3">
                    <label for="department" class="form-label">Department *</label>
                    <select class="form-select" id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Administration">Administration</option>
                        <option value="Research and Development">Research and Development</option>
                        <option value="Medical">Medical</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Parttime and Intern">Parttime and Intern</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="job_title" class="form-label">Job Title *</label>
                    <input type="text" class="form-control" id="job_title" name="job_title" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" name="phone">
                </div>

                <div class="mb-3">
                    <label for="mobile_phone" class="form-label">Mobile Phone</label>
                    <input type="tel" class="form-control" id="mobile_phone" name="mobile_phone">
                </div>

                <div class="mb-3">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?> 
 
 