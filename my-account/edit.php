<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Set current page for sidebar
$current_page = 'my-account';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get user data
$userId = $_SESSION['user']['id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . SITE_URL . '/auth/login.php');
    exit;
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request';
    } else {
        $name = trim($_POST['name']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $mobile_phone = trim($_POST['mobile_phone']);
        $job_title = trim($_POST['job_title']);
        $department = trim($_POST['department']);
        $birth_date = trim($_POST['birth_date']);
        $password = trim($_POST['password'] ?? '');

        // Validate required fields
        if (empty($name) || empty($email)) {
            $error = 'Name and email are required';
        } else {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address';
            } else {
                // Check if email is already taken by another user
                $checkQuery = "SELECT id FROM users WHERE email = ? AND id != ?";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bind_param('si', $email, $userId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows > 0) {
                    $error = 'This email address is already in use';
                } else {
                    // Update user profile
                    if (!empty($password)) {
                        $query = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, mobile_phone = ?, job_title = ?, department = ?, birth_date = ?, password = ? WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt->bind_param('sssssssssi', $name, $full_name, $email, $phone, $mobile_phone, $job_title, $department, $birth_date, $hashed_password, $userId);
                    } else {
                        $query = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, mobile_phone = ?, job_title = ?, department = ?, birth_date = ? WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->bind_param('ssssssssi', $name, $full_name, $email, $phone, $mobile_phone, $job_title, $department, $birth_date, $userId);
                    }

                    if ($stmt->execute()) {
                        // Update session user data
                        $_SESSION['user']['username'] = $name;
                        $_SESSION['user']['full_name'] = $full_name;
                        $_SESSION['user']['email'] = $email;
                        $_SESSION['user']['phone'] = $phone;
                        $_SESSION['user']['mobile_phone'] = $mobile_phone;
                        $_SESSION['user']['job_title'] = $job_title;
                        $_SESSION['user']['department'] = $department;
                        $_SESSION['user']['birth_date'] = $birth_date;

                        $_SESSION['success'] = 'Profile updated successfully';
                        header('Location: index.php');
                        exit;
                    } else {
                        $error = 'An error occurred while updating your profile: ' . $db->error;
                    }
                }
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
                <h1 class="h2">Edit Profile</h1>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form action="edit.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name'] ?: ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="mobile_phone" class="form-label">Mobile Phone</label>
                            <input type="tel" class="form-control" id="mobile_phone" name="mobile_phone" 
                                   value="<?php echo htmlspecialchars($user['mobile_phone']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="birth_date" class="form-label">Birthday</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="job_title" class="form-label">Job Title</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" 
                                   value="<?php echo htmlspecialchars($user['job_title']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Select Department</option>
                                <option value="Administration" <?php echo $user['department'] === 'Administration' ? 'selected' : ''; ?>>Administration</option>
                                <option value="Research and Development" <?php echo $user['department'] === 'Research and Development' ? 'selected' : ''; ?>>Research and Development</option>
                                <option value="Medical" <?php echo $user['department'] === 'Medical' ? 'selected' : ''; ?>>Medical</option>
                                <option value="Engineering" <?php echo $user['department'] === 'Engineering' ? 'selected' : ''; ?>>Engineering</option>
                                <option value="Marketing" <?php echo $user['department'] === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                <option value="Parttime and Intern" <?php echo $user['department'] === 'Parttime and Intern' ? 'selected' : ''; ?>>Parttime and Intern</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 
 
 