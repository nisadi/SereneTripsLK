<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper path to config.php
require_once __DIR__ . '/../admin/config.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check for required parameters
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    $_SESSION['message'] = "Invalid request parameters!";
    header('Location: bookings.php');
    exit;
}

$bookingId = $_GET['id'];
$newStatus = $_GET['status'];

// Validate status
$allowedStatuses = ['confirmed', 'cancelled'];
if (!in_array($newStatus, $allowedStatuses)) {
    $_SESSION['message'] = "Invalid status provided!";
    header('Location: bookings.php');
    exit;
}

try {
    // First check if booking exists
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        $_SESSION['message'] = "Booking not found!";
        header('Location: bookings.php');
        exit;
    }

    // Update booking status
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $bookingId]);
    
    // Set success message
    $_SESSION['message'] = "Booking #$bookingId has been " . ucfirst($newStatus) . " successfully!";
    header('Location: bookings.php');
    exit;
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>