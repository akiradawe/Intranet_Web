<?php
// Session settings must be set before any output or session start
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Site configuration
define('SITE_URL', 'http://localhost/intranet');
define('SITE_NAME', 'IRCAD Africa Intranet');

// Database configuration
require_once 'database.php';

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Helper functions
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function isEditor() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'editor';
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Error handling
function displayError($message) {
    return '<div class="alert alert-danger" role="alert">' . htmlspecialchars($message) . '</div>';
}

function displaySuccess($message) {
    return '<div class="alert alert-success" role="alert">' . htmlspecialchars($message) . '</div>';
}

// Date formatting
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// File handling
function validateFileUpload($file) {
    $errors = [];
    
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds maximum limit of 5MB';
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_FILE_TYPES)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_FILE_TYPES);
    }
    
    return $errors;
}

function saveUploadedFile($file, $destination) {
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = UPLOAD_DIR . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}

/**
 * Adjust the brightness of a hex color
 * @param string $hex Hex color code
 * @param int $steps Steps to adjust (-255 to 255)
 * @return string Adjusted hex color code
 */
function adjustBrightness($hex, $steps) {
    // Convert hex to RGB
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // Adjust each component
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    // Convert back to hex
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
?> 