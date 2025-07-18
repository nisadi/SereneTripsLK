<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$package = [
    'title' => '',
    'price' => '',
    'short_description' => '',
    'full_description' => '',
    'includes' => ["Item 1", "Item 2", "Item 3"]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package['title'] = trim($_POST['title'] ?? '');
    $package['price'] = trim($_POST['price'] ?? '');
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
    if (empty($package['price']) || !is_numeric($package['price'])) {
        $errors['price'] = 'Valid price is required';
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
    
    // Handle file upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('package_') . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_path = 'uploads/' . $file_name;
        } else {
            $errors['image'] = 'Failed to upload image';
        }
    } else {
        $errors['image'] = 'Image is required';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO packages 
            (title, price, short_description, full_description, image_path, includes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $package['title'],
            $package['price'],
            $package['short_description'],
            $package['full_description'],
            $image_path,
            json_encode($package['includes'])
        ]);
        
        $_SESSION['success_message'] = 'Package added successfully';
        header('Location: dashboard.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Package</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #8e44ad;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --gray-color: #95a5a6;
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
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1.5rem;
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
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        .form-group input[type="number"] {
            width: 200px;
        }
        
        .form-group textarea {
            min-height: 100px;
        }
        
        .error {
            color: var(--danger-color);
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
            transition: all 0.2s;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .btn-submit {
            background: var(--success-color);
            color: var(--white);
        }
        
        .btn-cancel {
            background: var(--gray-color);
            color: var(--white);
        }
        
        .btn-add-include {
            background: var(--info-color);
            color: var(--white);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        
        .include-item {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .include-item input {
            flex: 1;
        }
        
        .btn-delete {
            background: var(--danger-color);
            color: var(--white);
            padding: 0 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .logout-btn {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
            text-decoration: none;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h2>Add New Package</h2>
        <a href="dashboard.php" class="logout-btn">
            <i class="fas fa-arrow-left"></i> Back to Packages
        </a>
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
                <label for="price">Price (USD)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($package['price']) ?>">
                <?php if (isset($errors['price'])): ?>
                    <div class="error"><?= $errors['price'] ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image">Package Image</label>
                <input type="file" id="image" name="image" accept="image/*">
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
                            <button type="button" class="btn-delete" onclick="removeInclude(this)">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['includes'])): ?>
                    <div class="error"><?= $errors['includes'] ?></div>
                <?php endif; ?>
                <button type="button" class="btn-add-include" onclick="addInclude()">Add Inclusion</button>
            </div>
            
            <div class="form-group button-group">
                <button type="submit" class="btn btn-submit">Save Package</button>
                <a href="dashboard.php" class="btn btn-cancel">Cancel</a>
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
                <button type="button" class="btn-delete" onclick="removeInclude(this)">×</button>
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