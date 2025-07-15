<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get all bookings with package details
$stmt = $pdo->query("
    SELECT b.*, p.title as package_title, p.price as package_price 
    FROM bookings b
    JOIN packages p ON b.package_id = p.id
    ORDER BY b.booking_date DESC
");
$bookings = $stmt->fetchAll();

// Calculate totals
$total_bookings = count($bookings);
$total_revenue = array_reduce($bookings, function($carry, $booking) {
    return $carry + ($booking['package_price'] * $booking['guests']);
}, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .admin-header {
            background: #8e44ad;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
        }
        .stat-card h3 {
            margin-top: 0;
            color: #555;
        }
        .stat-card p {
            font-size: 24px;
            font-weight: bold;
            color: #8e44ad;
            margin-bottom: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #8e44ad;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-pending {
            color: #f39c12;
        }
        .status-confirmed {
            color: #2ecc71;
        }
        .status-cancelled {
            color: #e74c3c;
        }
        .logout-btn {
            color: white;
            text-decoration: none;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-confirm {
            background: #2ecc71;
        }
        .btn-cancel {
            background: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h2>Admin Panel - Bookings</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="admin-container">
        <div class="stats">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?= $total_bookings ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p>$<?= number_format($total_revenue, 2) ?></p>
            </div>
        </div>
        
        <table id="bookingsTable">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Package</th>
                    <th>Customer</th>
                    <th>Dates</th>
                    <th>Guests</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $booking['id'] ?></td>
                        <td><?= htmlspecialchars($booking['package_title']) ?></td>
                        <td>
                            <?= htmlspecialchars($booking['name']) ?><br>
                            <small><?= htmlspecialchars($booking['email']) ?></small>
                        </td>
                        <td>
                            <?= date('M j, Y', strtotime($booking['arrival_date'])) ?> - 
                            <?= date('M j, Y', strtotime($booking['leaving_date'])) ?>
                        </td>
                        <td><?= $booking['guests'] ?></td>
                        <td>$<?= number_format($booking['package_price'] * $booking['guests'], 2) ?></td>
                        <td class="status-<?= strtolower($booking['status']) ?>">
                            <?= ucfirst($booking['status']) ?>
                        </td>
                        <td>
                            <?php if ($booking['status'] == 'pending'): ?>
                                <a href="update_booking.php?id=<?= $booking['id'] ?>&status=confirmed" class="action-btn btn-confirm">Confirm</a>
                                <a href="update_booking.php?id=<?= $booking['id'] ?>&status=cancelled" class="action-btn btn-cancel">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#bookingsTable').DataTable({
                "order": [[0, "desc"]]
            });
        });
    </script>
</body>
</html>