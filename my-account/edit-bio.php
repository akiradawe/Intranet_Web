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
$bio = trim($_POST['bio']);

// Validate bio length
if (strlen($bio) > 1000) {
    $_SESSION['error'] = 'Bio cannot exceed 1000 characters';
    header('Location: index.php');
    exit;
}

// Update bio in database
$query = "UPDATE users SET bio = ? WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('si', $bio, $userId);

if ($stmt->execute()) {
    // Update session user data
    $_SESSION['user']['bio'] = $bio;
    $_SESSION['success'] = 'Bio updated successfully';
} else {
    $_SESSION['error'] = 'Failed to update bio';
}

header('Location: index.php');
exit; 
 
 