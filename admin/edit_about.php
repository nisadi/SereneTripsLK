<?php
require_once 'config.php';

// Check if user is admin
if (!isLoggedIn() || !isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    // Handle file upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/about/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            } else {
                $errorMessage = "Sorry, there was an error uploading your file.";
            }
        } else {
            $errorMessage = "File is not an image.";
        }
    }
    
    if (empty($errorMessage)) {
        try {
            if ($imagePath) {
                $stmt = $pdo->prepare("UPDATE about_us SET title = ?, content = ?, image_path = ? WHERE id = 1");
                $stmt->execute([$title, $content, $imagePath]);
            } else {
                $stmt = $pdo->prepare("UPDATE about_us SET title = ?, content = ? WHERE id = 1");
                $stmt->execute([$title, $content]);
            }
            $successMessage = "About page updated successfully!";
        } catch (PDOException $e) {
            $errorMessage = "Database error: " . $e->getMessage();
        }
    }
}

// Get current about content
try {
    $stmt = $pdo->query("SELECT * FROM about_us LIMIT 1");
    $aboutContent = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$aboutContent) {
        // Insert default content if none exists
        $defaultContent = [
            'title' => 'About SereneTripsLK',
            'content' => 'Welcome to SereneTripsLK, your premier travel companion in Sri Lanka.',
            'image_path' => 'images/about-us.jpg'
        ];
        $stmt = $pdo->prepare("INSERT INTO about_us (title, content, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$defaultContent['title'], $defaultContent['content'], $defaultContent['image_path']]);
        $aboutContent = $defaultContent;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Page | SereneTripsLK Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
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
        
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .image-preview {
            max-width: 100%;
            height: auto;
            margin-top: 15px;
            border-radius: 5px;
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
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            <h2>Edit About</h2>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    
    <div class="admin-container">
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?= htmlspecialchars($aboutContent['title']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="form-control" required><?= htmlspecialchars($aboutContent['content']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Featured Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <?php if ($aboutContent['image_path']): ?>
                        <p>Current Image:</p>
                        <img src="<?= htmlspecialchars($aboutContent['image_path']) ?>" alt="Current About Us Image" class="image-preview">
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="update_about" class="btn">
                    <i class="fas fa-save"></i> Update About Page
                </button>
                <a href="dashboard.php" class="btn" style="background-color: #95a5a6; margin-left: 10px;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </form>
        </div>
    </div>
</body>
</html>