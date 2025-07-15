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
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$packageId]);
$package = $stmt->fetch();

if (!$package) {
    header('Location: index.php');
    exit;
}

// Decode includes JSON
$package['includes'] = json_decode($package['includes'], true);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package['title'] = trim($_POST['title'] ?? '');
    $package['short_description'] = trim($_POST['short_description'] ?? '');
    $package['full_description'] = trim($_POST['full_description'] ?? '');
    
    // Process includes
    $includes = [];
    foreach ($_POST['includes'] as $item) {
        if (!empty(trim($item))) {
            $includes[] = trim($item);
        }
    }
    $package['includes'] = $includes;
    
    // Validate
    if (empty($package['title'])) {
        $errors['title'] = 'Title is required';
    }
    if (empty($package['short_description'])) {
        $errors['short_description'] = 'Short description is required';
    }
    if (empty($package['full_description'])) {
        $errors['full_description'] = 'Full description is required';
    }
    if (count($includes) === 0) {
        $errors['includes'] = 'At least one package inclusion is required';
    }
    
    // Handle file upload if new image is provided
    $image_path = $package['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('package_') . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // Delete old image if it exists
            if ($image_path && file_exists('../' . $image_path)) {
                unlink('../' . $image_path);
            }
            $image_path = 'uploads/' . $file_name;
        } else {
            $errors['image'] = 'Failed to upload image';
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE packages SET 
            title = ?, 
            short_description = ?, 
            full_description = ?, 
            image_path = ?, 
            includes = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $package['title'],
            $package['short_description'],
            $package['full_description'],
            $image_path,
            json_encode($package['includes']),
            $packageId
        ]);
        
        $_SESSION['success_message'] = 'Package updated successfully';
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 100px;
        }
        .error {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
        }
        .btn-submit {
            background: #2ecc71;
            color: white;
        }
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
        .include-item {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .include-item input {
            flex: 1;
        }
        .btn-add-include {
            background: #3498db;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        .current-image {
            max-width: 200px;
            margin-top: 0.5rem;
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h2>Edit Package</h2>
        <a href="index.php" class="logout-btn">Back to Packages</a>
    </div>

    <div class="admin-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Package Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($package['title']) ?>">
                <?php if (isset($errors['title'])): ?>
                    <div class="error"><?= $errors['title'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image">Package Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <?php if ($package['image_path']): ?>
                    <img src="../<?= htmlspecialchars($package['image_path']) ?>" alt="Current image" class="current-image">
                    <small>Leave blank to keep current image</small>
                <?php endif; ?>
                <?php if (isset($errors['image'])): ?>
                    <div class="error"><?= $errors['image'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="short_description">Short Description</label>
                <textarea id="short_description" name="short_description"><?= htmlspecialchars($package['short_description']) ?></textarea>
                <?php if (isset($errors['short_description'])): ?>
                    <div class="error"><?= $errors['short_description'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="full_description">Full Description</label>
                <textarea id="full_description" name="full_description"><?= htmlspecialchars($package['full_description']) ?></textarea>
                <?php if (isset($errors['full_description'])): ?>
                    <div class="error"><?= $errors['full_description'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Package Includes</label>
                <div id="includes-container">
                    <?php foreach ($package['includes'] as $index => $item): ?>
                        <div class="include-item">
                            <input type="text" name="includes[]" value="<?= htmlspecialchars($item) ?>">
                            <button type="button" class="btn btn-delete" onclick="removeInclude(this)">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['includes'])): ?>
                    <div class="error"><?= $errors['includes'] ?></div>
                <?php endif; ?>
                <button type="button" class="btn-add-include" onclick="addInclude()">Add Inclusion</button>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-submit">Update Package</button>
                <a href="index.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function addInclude() {
            const container = document.getElementById('includes-container');
            const div = document.createElement('div');
            div.className = 'include-item';
            div.innerHTML = `
                <input type="text" name="includes[]" placeholder="Included item">
                <button type="button" class="btn btn-delete" onclick="removeInclude(this)">×</button>
            `;
            container.appendChild(div);
        }
        
        function removeInclude(button) {
            const container = document.getElementById('includes-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            }
        }
    </script>
</body>
</html>