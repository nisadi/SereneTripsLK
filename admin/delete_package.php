<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$packageId = $_GET['id'];

// First get the package to delete its image
$stmt = $pdo->prepare("SELECT image_path FROM packages WHERE id = ?");
$stmt->execute([$packageId]);
$package = $stmt->fetch();

if ($package) {
    // Delete the image file if it exists
    if ($package['image_path'] && file_exists('../' . $package['image_path'])) {
        unlink('../' . $package['image_path']);
    }
    
    // Delete the package from database
    $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
    $stmt->execute([$packageId]);
    
    $_SESSION['success_message'] = 'Package deleted successfully';
}

header('Location: dashboard.php');
exit;
?>