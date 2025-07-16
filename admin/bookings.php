<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper path to config.php
require_once __DIR__ . '/../admin/config.php';

// Check if user is logged in and has admin privileges
if (!isLoggedIn() || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check for success message from update_booking.php
$success_message = '';
if (isset($_SESSION['message'])) {
    $success_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

try {
    // Get all bookings with package details using prepared statement
    $stmt = $pdo->prepare("
        SELECT b.*, p.title as package_title, p.price as package_price 
        FROM bookings b
        JOIN packages p ON b.package_id = p.id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $total_bookings = count($bookings);
    $total_revenue = array_reduce($bookings, function($carry, $booking) {
        return $carry + ($booking['package_price'] * $booking['guests']);
    }, 0);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bookings Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
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
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
            color: #666;
        }
        
        .stat-card p {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
            color: var(--primary-color);
        }
        
        .data-table-container {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--primary-color);
            color: var(--white);
            font-weight: 500;
        }
        
        tr:hover {
            background-color: rgba(142, 68, 173, 0.05);
        }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
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
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 4px;
            color: var(--white);
            text-decoration: none;
            font-size: 0.85rem;
            margin-right: 5px;
            transition: all 0.2s;
            min-width: 30px;
            height: 30px;
            cursor: pointer;
        }
        
        .action-btn i {
            pointer-events: none;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn-confirm {
            background: var(--success-color);
        }
        
        .btn-cancel {
            background: var(--danger-color);
        }
        
        .btn-view {
            background: var(--primary-color);
        }
        
        .logout-btn {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .logout-btn:hover {
            text-decoration: underline;
        }
        
        .customer-info {
            line-height: 1.4;
        }
        
        .customer-name {
            font-weight: 500;
        }
        
        .customer-email {
            font-size: 0.85rem;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: white;
            background-color: var(--success-color);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 0 1rem;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="dashboard.php" style="color: white; text-decoration: none; font-size: 1.2rem;" title="Back to Dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2>Bookings</h2>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <div class="admin-container">
        <?php if (!empty($success_message)): ?>
            <div class="alert">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Bookings</h3>
                <p><?= number_format($total_bookings) ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p>$<?= number_format($total_revenue, 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Bookings</h3>
                <p><?= number_format(array_reduce($bookings, function($count, $booking) {
                    return $booking['status'] === 'pending' ? $count + 1 : $count;
                }, 0)) ?></p>
            </div>
        </div>
        
        <div class="data-table-container">
            <table id="bookingsTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <td><?= htmlspecialchars($booking['id']) ?></td>
                            <td><?= htmlspecialchars($booking['package_title']) ?></td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name"><?= htmlspecialchars($booking['name']) ?></div>
                                    <div class="customer-email"><?= htmlspecialchars($booking['email']) ?></div>
                                    <div class="customer-phone"><?= htmlspecialchars($booking['phone']) ?></div>
                                </div>
                            </td>
                            <td>
                                <?= date('M j, Y', strtotime($booking['arrival_date'])) ?><br>
                                <small>to</small><br>
                                <?= date('M j, Y', strtotime($booking['leaving_date'])) ?>
                            </td>
                            <td><?= htmlspecialchars($booking['guests']) ?></td>
                            <td>$<?= number_format($booking['package_price'] * $booking['guests'], 2) ?></td>
                            <td>
                                <span class="status status-<?= strtolower($booking['status']) ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="booking_details.php?id=<?= $booking['id'] ?>" class="action-btn btn-view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="update_booking.php?id=<?= $booking['id'] ?>&status=confirmed" class="action-btn btn-confirm" title="Confirm Booking">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="update_booking.php?id=<?= $booking['id'] ?>&status=cancelled" class="action-btn btn-cancel" title="Cancel Booking">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#bookingsTable').DataTable({
                "order": [[0, "desc"]],
                "responsive": true,
                "columnDefs": [
                    { "responsivePriority": 1, "targets": 0 },
                    { "responsivePriority": 2, "targets": 7 },
                    { "responsivePriority": 3, "targets": 2 }
                ],
                "language": {
                    "lengthMenu": "Show _MENU_ bookings per page",
                    "zeroRecords": "No bookings found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ bookings",
                    "infoEmpty": "No bookings available",
                    "infoFiltered": "(filtered from _MAX_ total bookings)",
                    "search": "Search bookings:",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });
            
            // Ensure action buttons work even if DataTable interferes
            $(document).on('click', '.action-btn', function(e) {
                // You can add confirmation for certain actions
                if ($(this).hasClass('btn-cancel')) {
                    if (!confirm('Are you sure you want to cancel this booking?')) {
                        e.preventDefault();
                        return false;
                    }
                }
                if ($(this).hasClass('btn-confirm')) {
                    if (!confirm('Are you sure you want to confirm this booking?')) {
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            });
        });
    </script>
</body>
</html>