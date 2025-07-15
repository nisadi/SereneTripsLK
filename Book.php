<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use correct path to config.php
require_once __DIR__ . '/admin/config.php';

// Get package_id from URL if exists
$package_id = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;

try {
    // Get all packages (removed status check)
    $stmt = $pdo->prepare("SELECT id, title FROM packages");
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
    <title>Book Now</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Book.css">
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
  
        <form action="book_form.php" method="post" class="book-form">
            <div class="flex">
                <div class="inputBox">
                    <label for="package_id">Package:</label>
                    <select id="package_id" name="package_id" required>
                        <option value="">-- Select Package --</option>
                        <?php foreach ($packages as $package): ?>
                            <option value="<?php echo $package['id']; ?>" <?php echo $package['id'] == $package_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($package['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="inputBox">
                    <label for="name">Name :</label>
                    <input type="text" id="name" placeholder="Enter your name" name="name" required>
                </div>
                <div class="inputBox">
                    <label for="email">Email :</label>
                    <input type="email" id="email" placeholder="Enter your email" name="email" required>
                </div>
                <div class="inputBox">
                    <label for="phone">Phone :</label>
                    <input type="tel" id="phone" placeholder="Enter your number" name="phone" required>
                </div>
                <div class="inputBox">
                    <label for="address">Address :</label>
                    <input type="text" id="address" placeholder="Enter your address" name="address" required>
                </div>
                <div class="inputBox">
                    <label for="guests">How many :</label>
                    <input type="number" id="guests" placeholder="Number of guests" name="guests" min="1" required>
                </div>
                <div class="inputBox">
                    <label for="arrival_date">Arrival Date:</label>
                    <input type="date" id="arrival_date" name="arrival_date" required>
                </div>
                <div class="inputBox">
                    <label for="leaving_date">Leaving Date:</label>
                    <input type="date" id="leaving_date" name="leaving_date" required>
                </div>
            </div>
            <input type="submit" value="Submit Booking" class="btn" name="submit">
        </form>
    </section>
</body>
</html>