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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $business_hours = trim($_POST['business_hours']);
    
    try {
        $stmt = $pdo->prepare("UPDATE contact_info SET address = ?, phone = ?, email = ?, business_hours = ? WHERE id = 1");
        $stmt->execute([$address, $phone, $email, $business_hours]);
        $successMessage = "Contact information updated successfully!";
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
    }
}

// Get current contact info
try {
    $stmt = $pdo->query("SELECT * FROM contact_info LIMIT 1");
    $contactInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$contactInfo) {
        // Insert default contact info if none exists
        $defaultContact = [
            'address' => '123 Travel Street, Colombo 01, Sri Lanka',
            'phone' => '+94 11 234 5678',
            'email' => 'info@serenetripslk.com',
            'business_hours' => 'Monday - Friday: 9:00 AM to 6:00 PM<br>Saturday: 9:00 AM to 1:00 PM<br>Sunday: Closed'
        ];
        $stmt = $pdo->prepare("INSERT INTO contact_info (address, phone, email, business_hours) VALUES (?, ?, ?, ?)");
        $stmt->execute([$defaultContact['address'], $defaultContact['phone'], $defaultContact['email'], $defaultContact['business_hours']]);
        $contactInfo = $defaultContact;
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
    <title>Edit Contact Info | SereneTripsLK Admin</title>
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
            background-color: var(--dark-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .admin-container {
            max-width: 800px;
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
            min-height: 100px;
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
        <h2>Edit Contact Information</h2>
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
            <form method="POST">
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" required><?= htmlspecialchars($contactInfo['address']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" 
                           value="<?= htmlspecialchars($contactInfo['phone']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($contactInfo['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="business_hours">Business Hours</label>
                    <textarea id="business_hours" name="business_hours" class="form-control" required><?= htmlspecialchars($contactInfo['business_hours']) ?></textarea>
                </div>
                
                <button type="submit" name="update_contact" class="btn">
                    <i class="fas fa-save"></i> Update Contact Info
                </button>
                <a href="dashboard.php" class="btn" style="background-color: #95a5a6; margin-left: 10px;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </form>
        </div>
    </div>
</body>
</html>