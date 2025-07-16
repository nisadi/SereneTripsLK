<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use correct path to config.php
require_once __DIR__ . '/admin/config.php';

// Get package_id from URL if exists
$package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

try {
    // Get all active packages with prices
    $stmt = $pdo->prepare("SELECT id, title, price FROM packages WHERE status = 'active'");
    $stmt->execute();
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error loading packages: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Now - SereneTripsLK</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Book.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        .error-message {
            color: #d9534f;
            background-color: #f2dede;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ebccd1;
        }
        .inputBox.invalid input,
        .inputBox.invalid select {
            border-color: #d9534f;
        }
        .inputBox.invalid label {
            color: #d9534f;
        }
    </style>
</head>
<body style="background-image: url('https://wallpapercat.com/w/full/7/8/8/639817-3072x2051-desktop-hd-sri-lanka-background.jpg'); background-repeat: no-repeat; background-size: cover; background-position: center; margin: 0;">

    <section class="header">
        <a href="home.php" class="logo">SereneTripsLK</a>
        <nav class="navbar">
            <a href="home.php">home</a>
            <a href="about.php">about</a>
            <a href="package.php">package</a>
            <a href="Book.php">book</a>
        </nav>
        <div id="menu-btn" class="fas fa-bars"></div>
    </section>

    <div class="heading">
      <h1>Book Now</h1>
    </div>

    <section class="booking">
        <h1 class="heading-title">book your trip!</h1>
        
        <?php if (isset($_SESSION['booking_errors'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
                <ul>
                    <?php foreach ($_SESSION['booking_errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['booking_errors']); ?>
        <?php endif; ?>
  
        <form action="book_form.php" method="post" class="book-form" id="bookingForm">
            <div class="flex">
                <div class="inputBox <?= isset($_SESSION['field_errors']['package_id']) ? 'invalid' : '' ?>">
                    <label for="package_id">Package:</label>
                    <select id="package_id" name="package_id" required>
                        <option value="">-- Select Package --</option>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?= $package['id'] ?>" 
                                <?= $package['id'] == $package_id ? 'selected' : '' ?>
                                data-price="<?= $package['price'] ?>">
                                <?= htmlspecialchars($package['title']) ?> - $<?= number_format($package['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="inputBox <?= isset($_SESSION['field_errors']['name']) ? 'invalid' : '' ?>">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" 
                           value="<?= isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : '' ?>" 
                           placeholder="Enter your name" required>
                </div>
                
                <div class="inputBox <?= isset($_SESSION['field_errors']['email']) ? 'invalid' : '' ?>">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" 
                           value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '' ?>" 
                           placeholder="Enter your email" required>
                </div>
                
                <div class="inputBox <?= isset($_SESSION['field_errors']['phone']) ? 'invalid' : '' ?>">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= isset($_SESSION['form_data']['phone']) ? htmlspecialchars($_SESSION['form_data']['phone']) : '' ?>" 
                           placeholder="Enter your phone number" required>
                </div>
                
                <div class="inputBox <?= isset($_SESSION['field_errors']['address']) ? 'invalid' : '' ?>">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" 
                           value="<?= isset($_SESSION['form_data']['address']) ? htmlspecialchars($_SESSION['form_data']['address']) : '' ?>" 
                           placeholder="Enter your address" required>
                </div>
                
                <div class="inputBox <?= isset($_SESSION['field_errors']['guests']) ? 'invalid' : '' ?>">
                    <label for="guests">Number of Guests:</label>
                    <input type="number" id="guests" name="guests" min="1" 
                           value="<?= isset($_SESSION['form_data']['guests']) ? htmlspecialchars($_SESSION['form_data']['guests']) : '' ?>" 
                           placeholder="How many guests" required>
                </div>
                
                <div class="inputBox <?= isset($_SESSION['field_errors']['arrival_date']) ? 'invalid' : '' ?>">
                    <label for="arrival_date">Arrival Date:</label>
                    <input type="date" id="arrival_date" name="arrival_date" 
                           value="<?= isset($_SESSION['form_data']['arrival_date']) ? htmlspecialchars($_SESSION['form_data']['arrival_date']) : '' ?>" 
                           required>
                </div>
                
                <div class="inputBox <?= isset($_SESSION['field_errors']['leaving_date']) ? 'invalid' : '' ?>">
                    <label for="leaving_date">Leaving Date:</label>
                    <input type="date" id="leaving_date" name="leaving_date" 
                           value="<?= isset($_SESSION['form_data']['leaving_date']) ? htmlspecialchars($_SESSION['form_data']['leaving_date']) : '' ?>" 
                           required>
                </div>
            </div>
            
            <input type="submit" value="Submit Booking" class="btn" name="submit">
        </form>
    </section>

    <script>
        // Client-side validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            let isValid = true;
            const today = new Date().toISOString().split('T')[0];
            
            // Validate dates
            const arrivalDate = document.getElementById('arrival_date').value;
            const leavingDate = document.getElementById('leaving_date').value;
            
            if (arrivalDate && leavingDate) {
                if (new Date(arrivalDate) > new Date(leavingDate)) {
                    alert('Leaving date must be after arrival date');
                    isValid = false;
                }
                
                if (new Date(arrivalDate) < new Date(today)) {
                    alert('Arrival date cannot be in the past');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Set min date for arrival to today
        document.getElementById('arrival_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
<?php
// Clear form data from session after displaying
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
if (isset($_SESSION['field_errors'])) {
    unset($_SESSION['field_errors']);
}
?>