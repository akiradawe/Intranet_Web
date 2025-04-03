<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in
requireLogin();

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user']['id'];

// Get current profile picture
$query = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['profile_picture']) {
    // Delete file from filesystem
    $filepath = '../public/uploads/profile-pictures/' . $user['profile_picture'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    // Update database
    $updateQuery = "UPDATE users SET profile_picture = NULL WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bind_param('i', $userId);
    
    if ($updateStmt->execute()) {
        // Update session user data
        $_SESSION['user']['profile_picture'] = null;
        $_SESSION['success'] = 'Profile picture deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete profile picture from database';
    }
}

header('Location: index.php');
exit; 
 
 