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

// Check if file was uploaded
if (!isset($_FILES['profile_picture'])) {
    $_SESSION['error'] = 'No file was uploaded';
    header('Location: index.php');
    exit;
}

if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = array(
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    );
    $_SESSION['error'] = 'Upload error: ' . ($uploadErrors[$_FILES['profile_picture']['error']] ?? 'Unknown error');
    header('Location: index.php');
    exit;
}

$file = $_FILES['profile_picture'];
$userId = $_SESSION['user']['id'];

// Debug information
error_log('Upload attempt for user ' . $userId);
error_log('File details: ' . print_r($file, true));

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) {
    $_SESSION['error'] = 'Only image files are allowed! (Received type: ' . $file['type'] . ')';
    header('Location: index.php');
    exit;
}

// Validate file size (5MB limit)
$maxSize = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $maxSize) {
    $_SESSION['error'] = 'File size must be less than 5MB (Received size: ' . round($file['size'] / 1024 / 1024, 2) . 'MB)';
    header('Location: index.php');
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = dirname(dirname(__FILE__)) . '/public/uploads/profile-pictures';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $_SESSION['error'] = 'Failed to create upload directory';
        error_log('Failed to create upload directory: ' . $uploadDir);
        header('Location: index.php');
        exit;
    }
    error_log('Created upload directory: ' . $uploadDir);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = time() . '-' . rand(100000000, 999999999) . '.' . $extension;
$filepath = $uploadDir . '/' . $filename;

error_log('Attempting to move file to: ' . $filepath);

// Delete old profile picture if exists
$query = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['profile_picture']) {
    $oldFilepath = $uploadDir . '/' . $user['profile_picture'];
    if (file_exists($oldFilepath)) {
        if (!unlink($oldFilepath)) {
            error_log('Failed to delete old profile picture: ' . $oldFilepath);
        }
    }
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    $_SESSION['error'] = 'Failed to upload profile picture. Error: ' . error_get_last()['message'];
    error_log('Failed to move uploaded file. Error: ' . print_r(error_get_last(), true));
    header('Location: index.php');
    exit;
}

error_log('File successfully moved to: ' . $filepath);

// Update database
$updateQuery = "UPDATE users SET profile_picture = ? WHERE id = ?";
$updateStmt = $db->prepare($updateQuery);
$updateStmt->bind_param('si', $filename, $userId);

if (!$updateStmt->execute()) {
    $_SESSION['error'] = 'Failed to update profile picture in database: ' . $db->error;
    error_log('Database update failed: ' . $db->error);
    // Try to delete the uploaded file since database update failed
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    header('Location: index.php');
    exit;
}

// Update session user data
$_SESSION['user']['profile_picture'] = $filename;
$_SESSION['success'] = 'Profile picture updated successfully';
error_log('Profile picture update completed successfully');

header('Location: index.php');
exit; 
 
 