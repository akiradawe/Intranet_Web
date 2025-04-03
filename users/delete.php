<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . SITE_URL);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: index.php');
    exit;
}

$userId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Prevent deleting yourself
if ($userId === $_SESSION['user']['id']) {
    $_SESSION['error'] = 'You cannot delete your own account';
    header('Location: index.php');
    exit;
}

// Prevent deleting the last admin
$query = "SELECT COUNT(*) as adminCount FROM users WHERE role = 'admin'";
$result = $db->query($query);
$adminCount = $result->fetch_assoc()['adminCount'];

if ($adminCount <= 1) {
    // Check if the user to be deleted is an admin
    $checkQuery = "SELECT role FROM users WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bind_param('i', $userId);
    $checkStmt->execute();
    $userRole = $checkStmt->get_result()->fetch_assoc()['role'];

    if ($userRole === 'admin') {
        $_SESSION['error'] = 'Cannot delete the last admin user';
        header('Location: index.php');
        exit;
    }
}

// Delete the user
$deleteQuery = "DELETE FROM users WHERE id = ?";
$deleteStmt = $db->prepare($deleteQuery);
$deleteStmt->bind_param('i', $userId);

if ($deleteStmt->execute()) {
    $_SESSION['success'] = 'User deleted successfully';
} else {
    $_SESSION['error'] = 'Failed to delete user: ' . $db->error;
}

header('Location: index.php');
exit; 
 
 