<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Check if user is logged in and is admin
requireLogin();
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . SITE_URL . '/mountable-services/');
    exit();
}

// Get service ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Delete the service
$query = "DELETE FROM mountable_services WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Service deleted successfully';
} else {
    $_SESSION['error'] = 'Failed to delete service';
}

$stmt->close();

// Redirect back to the services list
header('Location: ' . SITE_URL . '/mountable-services/');
exit(); 
 
 