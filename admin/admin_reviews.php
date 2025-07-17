<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle review actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_review'])) {
        $reviewId = (int)$_POST['review_id'];
        $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
        $stmt->execute([$reviewId]);
        $_SESSION['message'] = "Review approved successfully!";
    } 
    elseif (isset($_POST['reject_review'])) {
        $reviewId = (int)$_POST['review_id'];
        $stmt = $pdo->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$reviewId]);
        $_SESSION['message'] = "Review rejected successfully!";
    } 
    elseif (isset($_POST['feature_review'])) {
        $reviewId = (int)$_POST['review_id'];
        $stmt = $pdo->prepare("UPDATE reviews SET is_featured = TRUE WHERE id = ?");
        $stmt->execute([$reviewId]);
        $_SESSION['message'] = "Review featured successfully!";
    } 
    elseif (isset($_POST['unfeature_review'])) {
        $reviewId = (int)$_POST['review_id'];
        $stmt = $pdo->prepare("UPDATE reviews SET is_featured = FALSE WHERE id = ?");
        $stmt->execute([$reviewId]);
        $_SESSION['message'] = "Review unfeatured successfully!";
    }
    header("Location: admin_reviews.php");
    exit;
}

// Get all reviews
try {
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check for success message
$successMessage = '';
if (isset($_SESSION['message'])) {
    $successMessage = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews | SereneTripsLK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        .admin-header {
            background: linear-gradient(to right, #b602b6, #750587);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: var(--dark-color);
            color: white;
            font-weight: 500;
        }
        
        tr:hover {
            background-color: rgba(0,0,0,0.02);
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
        
        .status-approved {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }
        
        .status-rejected {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
        
        .featured {
            color: var(--warning-color);
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            margin-right: 5px;
            cursor: pointer;
            border: none;
        }
        
        .btn-approve {
            background-color: var(--secondary-color);
        }
        
        .btn-reject {
            background-color: var(--danger-color);
        }
        
        .btn-feature {
            background-color: var(--warning-color);
        }
        
        .rating {
            color: var(--warning-color);
        }
        
        .logout-btn {
            color: white;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="dashboard.php" style="color: white; text-decoration: none; font-size: 1.2rem;" title="Back to Dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2>Reviews</h2>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    
    <div class="admin-container">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>
        
        <table id="reviewsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Rating</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?= $review['id'] ?></td>
                        <td><?= htmlspecialchars($review['name']) ?><br>
                            <small><?= htmlspecialchars($review['email']) ?></small>
                        </td>
                        <td class="rating">
                            <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                        </td>
                        <td><?= htmlspecialchars($review['message']) ?></td>
                        <td>
                            <span class="status status-<?= strtolower($review['status']) ?>">
                                <?= ucfirst($review['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?= $review['is_featured'] ? '<span class="featured">★ Featured</span>' : 'No' ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($review['created_at'])) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                <?php if ($review['status'] !== 'approved'): ?>
                                    <button type="submit" name="approve_review" class="btn btn-approve">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                <?php endif; ?>
                                <?php if ($review['status'] !== 'rejected'): ?>
                                    <button type="submit" name="reject_review" class="btn btn-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                <?php endif; ?>
                                <?php if ($review['status'] === 'approved'): ?>
                                    <?php if (!$review['is_featured']): ?>
                                        <button type="submit" name="feature_review" class="btn btn-feature">
                                            <i class="fas fa-star"></i> Feature
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="unfeature_review" class="btn">
                                            <i class="fas fa-star"></i> Unfeature
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </form>
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
            $('#reviewsTable').DataTable({
                "order": [[0, "desc"]],
                "responsive": true,
                "columnDefs": [
                    { "responsivePriority": 1, "targets": 0 },
                    { "responsivePriority": 2, "targets": -1 },
                    { "responsivePriority": 3, "targets": 2 }
                ]
            });
        });
    </script>
</body>
</html>