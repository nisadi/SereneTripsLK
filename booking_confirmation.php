<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if booking data exists
if (!isset($_SESSION['booking_data'])) {
    header("Location: Book.php");
    exit;
}

$booking = $_SESSION['booking_data'];
unset($_SESSION['booking_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - SereneTripsLK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #8e44ad;
            --success-color: #2ecc71;
            --text-color: #333;
            --light-bg: #f5f5f5;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://wallpapercat.com/w/full/7/8/8/639817-3072x2051-desktop-hd-sri-lanka-background.jpg') no-repeat center center/cover;
            color: var(--white);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .confirmation-container {
            background: rgba(0, 0, 0, 0.8);
            padding: 2rem;
            border-radius: 10px;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        
        .confirmation-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .confirmation-header h1 {
            color: var(--success-color);
            margin-bottom: 0.5rem;
        }
        
        .confirmation-header p {
            font-size: 1.2rem;
            margin-top: 0;
        }
        
        .confirmation-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-label {
            font-weight: bold;
            color: #f4b400;
            margin-bottom: 0.3rem;
            display: block;
        }
        
        .detail-value {
            font-size: 1.1rem;
        }
        
        .total-price {
            grid-column: span 2;
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #444;
            font-size: 1.3rem;
        }
        
        .total-price .amount {
            color: var(--success-color);
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .actions {
            text-align: center;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 5px;
            margin: 0 0.5rem;
            transition: all 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .confirmation-details {
                grid-template-columns: 1fr;
            }
            
            .total-price {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-header">
            <h1><i class="fas fa-check-circle"></i> Booking Confirmed!</h1>
            <p>Thank you for choosing SereneTripsLK</p>
        </div>
        
        <div class="confirmation-details">
            <div class="detail-group">
                <span class="detail-label">Booking ID</span>
                <div class="detail-value"><?= htmlspecialchars($booking['booking_id']) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Package</span>
                <div class="detail-value"><?= htmlspecialchars($booking['package_title']) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Name</span>
                <div class="detail-value"><?= htmlspecialchars($booking['name']) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Email</span>
                <div class="detail-value"><?= htmlspecialchars($booking['email']) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Phone</span>
                <div class="detail-value"><?= htmlspecialchars($booking['phone']) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Address</span>
                <div class="detail-value"><?= htmlspecialchars($booking['address']) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Arrival Date</span>
                <div class="detail-value"><?= date('F j, Y', strtotime($booking['arrival_date'])) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Leaving Date</span>
                <div class="detail-value"><?= date('F j, Y', strtotime($booking['leaving_date'])) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Number of Guests</span>
                <div class="detail-value"><?= htmlspecialchars($booking['guests']) ?></div>
            </div>
            
            <div class="detail-group">
                <span class="detail-label">Price per Guest</span>
                <div class="detail-value">$<?= number_format($booking['price'], 2) ?></div>
            </div>
            
            <div class="total-price">
                <span>Total Amount: </span>
                <span class="amount">$<?= number_format($booking['total'], 2) ?></span>
            </div>
        </div>
        
        <div class="actions">
            <a href="home.php" class="btn"><i class="fas fa-home"></i> Back to Home</a>
            <a href="package.php" class="btn"><i class="fas fa-suitcase"></i> View Packages</a>
        </div>
    </div>
</body>
</html>