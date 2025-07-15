<?php
require_once __DIR__ . '/admin/config.php';

// Initialize variables
$errors = [];
$booking_data = [];

// Process form when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate and sanitize inputs
        $package_id = filter_input(INPUT_POST, 'package_id', FILTER_VALIDATE_INT);
        if (!$package_id) {
            $errors[] = "Please select a valid package";
        }

        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
        $guests = filter_input(INPUT_POST, 'guests', FILTER_VALIDATE_INT, ['min_range' => 1]);
        $arrival_date = filter_input(INPUT_POST, 'arrival_date', FILTER_SANITIZE_STRING);
        $leaving_date = filter_input(INPUT_POST, 'leaving_date', FILTER_SANITIZE_STRING);

        // Validate dates
        if (!strtotime($arrival_date) || !strtotime($leaving_date)) {
            $errors[] = "Invalid date format";
        } elseif (strtotime($arrival_date) > strtotime($leaving_date)) {
            $errors[] = "Leaving date must be after arrival date";
        }

        // Check if package exists
        $stmt = $pdo->prepare("SELECT id, title, price FROM packages WHERE id = ?");
        $stmt->execute([$package_id]);
        $package = $stmt->fetch();

        if (!$package) {
            $errors[] = "Selected package not found";
        }

        // If no errors, proceed with booking
        if (empty($errors)) {
            $pdo->beginTransaction();

            // Insert booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings 
                (package_id, name, email, phone, address, guests, arrival_date, leaving_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $package_id,
                $name,
                $email,
                $phone,
                $address,
                $guests,
                $arrival_date,
                $leaving_date
            ]);

            $booking_id = $pdo->lastInsertId();
            $pdo->commit();

            // Prepare data for confirmation page
            $booking_data = [
                'booking_id' => $booking_id,
                'package_title' => $package['title'],
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'guests' => $guests,
                'arrival_date' => $arrival_date,
                'leaving_date' => $leaving_date,
                'price' => $package['price'],
                'total' => $package['price'] * $guests
            ];
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errors[] = "Database error: " . $e->getMessage();
    }
} else {
    // Not a POST request, redirect
    header("Location: Book.php");
    exit;
}

// If errors, redirect back with error messages
if (!empty($errors)) {
    session_start();
    $_SESSION['booking_errors'] = $errors;
    header("Location: Book.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('https://wallpapercat.com/w/full/7/8/8/639817-3072x2051-desktop-hd-sri-lanka-background.jpg') no-repeat center center/cover;
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }
        .confirmation-box {
            background: rgba(0, 0, 0, 0.7);
            padding: 40px;
            border-radius: 15px;
            max-width: 600px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        h2 {
            font-size: 2rem;
            color: #ffd700;
            text-align: center;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
            color: #f4b400;
        }
        .info-value {
            flex: 1;
        }
        .total-row {
            font-size: 1.2em;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #f4b400;
        }
        .success-message {
            color: #2ecc71;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h2>Booking Confirmation</h2>
        <div class="success-message">Your booking has been successfully submitted!</div>
        
        <div class="booking-info">
            <div class="info-row">
                <div class="info-label">Booking ID:</div>
                <div class="info-value"><?= htmlspecialchars($booking_data['booking_id']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Package:</div>
                <div class="info-value"><?= htmlspecialchars($booking_data['package_title']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value"><?= htmlspecialchars($booking_data['name']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?= htmlspecialchars($booking_data['email']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value"><?= htmlspecialchars($booking_data['phone']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value"><?= htmlspecialchars($booking_data['address']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Guests:</div>
                <div class="info-value"><?= htmlspecialchars($booking_data['guests']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Arrival Date:</div>
                <div class="info-value"><?= date('F j, Y', strtotime($booking_data['arrival_date'])) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Leaving Date:</div>
                <div class="info-value"><?= date('F j, Y', strtotime($booking_data['leaving_date'])) ?></div>
            </div>
            <div class="info-row total-row">
                <div class="info-label">Total Price:</div>
                <div class="info-value">$<?= number_format($booking_data['total'], 2) ?></div>
            </div>
        </div>
    </div>
</body>
</html>