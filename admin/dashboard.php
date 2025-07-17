<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Fetch all packages
$stmt = $pdo->query("SELECT * FROM packages ORDER BY created_at DESC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Packages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .admin-header {
            background: linear-gradient(to right, #b602b6, #750587);
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
        .package-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .package-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .package-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .package-content {
            padding: 1.5rem;
        }
        .package-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-edit {
            background: #3498db;
            color: white;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .btn-add {
            background: #2ecc71;
            color: white;
            margin-bottom: 1.5rem;
            display: inline-block;
            padding: 0.8rem 1.5rem;
        }
        .btn-bookings {
            background: #8e44ad;
            color: white;
            margin-bottom: 1.5rem;
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin-left: 1rem;
        }
        .logout-btn {
            color: white;
            text-decoration: none;
        }
        .admin-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h2>Admin Panel</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="admin-container">
        <div class="admin-actions">
            <a href="add_package.php" class="btn btn-add">
                <i class="fas fa-plus"></i> Add New Package
            </a>
            <a href="bookings.php" class="btn btn-bookings">
                <i class="fas fa-calendar-check"></i> View Bookings
            </a>
        </div>
        
        <div class="package-grid">
            <?php foreach ($packages as $package): ?>
                <div class="package-card">
                    <img src="../<?= htmlspecialchars($package['image_path']) ?>" alt="<?= htmlspecialchars($package['title']) ?>">
                    <div class="package-content">
                        <h3><?= htmlspecialchars($package['title']) ?></h3>
                        <p><?= substr(htmlspecialchars($package['short_description']), 0, 100) ?>...</p>
                        <div class="package-actions">
                            <a href="edit_package.php?id=<?= $package['id'] ?>" class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_package.php?id=<?= $package['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this package?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>