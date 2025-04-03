<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin or editor
requireLogin();
if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'editor') {
    header('Location: ' . SITE_URL . '/announcements/');
    exit();
}

// Verify CSRF token
if (!validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid request';
    header('Location: ' . SITE_URL . '/announcements/');
    exit();
}

// Get announcement ID
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// First, get the announcement data to check for image
$query = "SELECT image_path FROM announcements WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $announcement = $result->fetch_assoc();
    
    // Delete the image file if it exists
    if ($announcement['image_path']) {
        $image_path = 'public/uploads/' . $announcement['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Now delete the announcement
    $query = "DELETE FROM announcements WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Announcement deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete announcement';
    }
} else {
    $_SESSION['error'] = 'Announcement not found';
}

$stmt->close();

header('Location: ' . SITE_URL . '/announcements/');
exit(); 