<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug database connection
require_once '../config/database.php';
error_log('Database connection status: ' . ($db ? 'Connected' : 'Not connected'));

// Load configurations
require_once '../config/config.php';

// Debug session status
error_log('Session status: ' . session_status());
error_log('Session ID: ' . session_id());

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    error_log('User already logged in, redirecting to dashboard');
    header('Location: ' . SITE_URL);
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    error_log('Login attempt for username: ' . $username);
    
    try {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $db->prepare($query);
        
        if (!$stmt) {
            error_log('Error preparing statement: ' . $db->error);
            $error = 'Database error occurred';
        } else {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    error_log('Login successful for user: ' . $username);
                    // Set session
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                        'email' => $user['email']
                    ];
                    
                    // Redirect to dashboard
                    header('Location: ' . SITE_URL);
                    exit();
                } else {
                    error_log('Invalid password for user: ' . $username);
                    $error = 'Invalid username or password';
                }
            } else {
                error_log('User not found: ' . $username);
                $error = 'Invalid username or password';
            }
            
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
        $error = 'A database error occurred. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }
        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }
        .form-signin .form-floating:focus-within {
            z-index: 2;
        }
        .form-signin input[type="text"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
</head>
<body class="text-center">
    <main class="form-signin">
        <form method="POST" action="<?php echo SITE_URL; ?>/auth/login.php">
            <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            
            <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
        </form>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 