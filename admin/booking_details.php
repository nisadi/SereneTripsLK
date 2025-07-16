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

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    header('Location: bookings.php');
    exit;
}

$bookingId = $_GET['id'];

try {
    // Get booking details with package information
    $stmt = $pdo->prepare("
        SELECT b.*, p.title as package_title, p.price as package_price, 
               p.description as package_description, p.duration as package_duration
        FROM bookings b
        JOIN packages p ON b.package_id = p.id
        WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header('Location: bookings.php');
        exit;
    }

    // Calculate total price
    $totalPrice = $booking['package_price'] * $booking['guests'];

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details #<?= htmlspecialchars($bookingId) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #8e44ad;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f5f5;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--light-bg);
            color: var(--text-color);
        }
        
        .admin-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .booking-details {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 2rem;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
        .booking-title {
            margin: 0;
            color: var(--primary-color);
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }
        
        .status-confirmed {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }
        
        .status-cancelled {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .detail-section {
            margin-bottom: 1.5rem;
        }
        
        .detail-section h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .detail-item {
            margin-bottom: 0.8rem;
            display: flex;
        }
        
        .detail-label {
            font-weight: 500;
            min-width: 120px;
            color: #666;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 4px;
            color: var(--white);
            text-decoration: none;
            font-size: 0.9rem;
            margin-right: 10px;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn-back {
            background: var(--primary-color);
        }
        
        .btn-confirm {
            background: var(--success-color);
        }
        
        .btn-cancel {
            background: var(--danger-color);
        }
        
        .package-description {
            line-height: 1.6;
            color: #555;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 0 1rem;
            }
            
            .booking-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="bookings.php" style="color: white; text-decoration: none; font-size: 1.2rem;" title="Back to Bookings">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2>Booking Details</h2>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="admin-container">
        <div class="booking-details">
            <div class="booking-header">
                <h1 class="booking-title">Booking #<?= htmlspecialchars($booking['id']) ?></h1>
                <span class="status status-<?= strtolower($booking['status']) ?>">
                    <?= ucfirst($booking['status']) ?>
                </span>
            </div>
            
            <div class="details-grid">
                <div>
                    <div class="detail-section">
                        <h3>Customer Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['name']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['email']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['phone']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Address:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['address']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Special Requests:</span>
                            <span class="detail-value"><?= !empty($booking['special_requests']) ? htmlspecialchars($booking['special_requests']) : 'None' ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Booking Dates</h3>
                        <div class="detail-item">
                            <span class="detail-label">Arrival:</span>
                            <span class="detail-value"><?= date('F j, Y', strtotime($booking['arrival_date'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Departure:</span>
                            <span class="detail-value"><?= date('F j, Y', strtotime($booking['leaving_date'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value">
                                <?php
                                $arrival = new DateTime($booking['arrival_date']);
                                $leaving = new DateTime($booking['leaving_date']);
                                $interval = $arrival->diff($leaving);
                                echo $interval->format('%a nights');
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="detail-section">
                        <h3>Package Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Package:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['package_title']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Price per guest:</span>
                            <span class="detail-value">$<?= number_format($booking['package_price'], 2) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Number of guests:</span>
                            <span class="detail-value"><?= htmlspecialchars($booking['guests']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total Price:</span>
                            <span class="detail-value">$<?= number_format($totalPrice, 2) ?></span>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Package Description</h3>
                        <p class="package-description"><?= htmlspecialchars($booking['package_description']) ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Booking Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Booking Date:</span>
                            <span class="detail-value"><?= date('F j, Y H:i', strtotime($booking['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                <a href="bookings.php" class="action-btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Bookings
                </a>
                
                <?php if ($booking['status'] == 'pending'): ?>
                    <div>
                        <a href="update_booking.php?id=<?= $booking['id'] ?>&status=confirmed" class="action-btn btn-confirm">
                            <i class="fas fa-check"></i> Confirm Booking
                        </a>
                        <a href="update_booking.php?id=<?= $booking['id'] ?>&status=cancelled" class="action-btn btn-cancel">
                            <i class="fas fa-times"></i> Cancel Booking
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Confirmation for status changes
        document.querySelectorAll('.btn-confirm, .btn-cancel').forEach(button => {
            button.addEventListener('click', function(e) {
                const action = this.classList.contains('btn-confirm') ? 'confirm' : 'cancel';
                if (!confirm(`Are you sure you want to ${action} this booking?`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>