<?php
require_once 'config.php';

// Helper function to get or create default data
function getOrCreateData($pdo, $table, $defaultData) {
    try {
        $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            $columns = implode(', ', array_keys($defaultData));
            $placeholders = ':' . implode(', :', array_keys($defaultData));
            $stmt = $pdo->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
            $stmt->execute($defaultData);
            $data = $defaultData;
        }
        return $data;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Get about us content
$aboutContent = getOrCreateData($pdo, 'about_us', [
    'title' => 'About SereneTripsLK',
    'content' => '<p>Welcome to SereneTripsLK, your premier travel companion in Sri Lanka. We specialize in creating unforgettable travel experiences that showcase the beauty and culture of this amazing island.</p>
                  <p>Our team of travel experts has years of experience crafting personalized itineraries that cater to all types of travelers, from adventure seekers to those looking for relaxation.</p>
                  <p>We pride ourselves on our excellent customer service and attention to detail, ensuring every trip with us is seamless and memorable.</p>',
    'image_path' => 'images/about-us.jpg'
]);

// Get contact information
$contactInfo = getOrCreateData($pdo, 'contact_info', [
    'address' => '123 Travel Street, Colombo 01, Sri Lanka',
    'phone' => '+94 11 234 5678',
    'email' => 'info@serenetripslk.com',
    'business_hours' => 'Monday - Friday: 9:00 AM to 6:00 PM<br>Saturday: 9:00 AM to 1:00 PM<br>Sunday: Closed'
]);

// Get approved reviews
$reviews = [];
try {
    $stmt = $pdo->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT 10");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle review submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $reviewText = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($reviewText) || $rating < 1 || $rating > 5) {
        $message = '<div class="alert alert-danger">Please fill all fields correctly. Rating must be between 1 and 5.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (name, email, rating, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $rating, $reviewText]);
            $message = '<div class="alert alert-success">Thank you for your review! It will be visible after approval by our team.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Error submitting your review. Please try again later.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us & Contact | SereneTripsLK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="about.css">
</head>
<body>
    <!-- Header -->
    <section class="header">
        <a href="home.php" class="logo">SereneTripsLK</a>
        <nav class="navbar">
            <a href="home.php">home</a>
            <a href="about.php">about</a>
            <a href="package.php">package</a>
            <a href="book.php">book</a>
        </nav>
        <div id="menu-btn" class="fas fa-bars"></div>
    </section>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>About SereneTripsLK</h1>
            <p>Discover our story and connect with us for your next unforgettable journey in Sri Lanka</p>
            <a href="#contact" class="btn">Contact Us</a>
        </div>
    </section>

    <!-- About Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Our Story</h2>
            <div class="about-content">
                <div class="about-image">
                    <img src="uploads/about1.jpg" alt="About SereneTripsLK">
                </div>
                <div class="about-text">
                    <h2><?= htmlspecialchars($aboutContent['title']) ?></h2>
                    <?= $aboutContent['content'] ?>
                </div>
            </div>

            <!-- Reviews Section -->
            <h2 class="section-title">What Our Travelers Say</h2>
            <div class="reviews-container">
                <?php if ($reviews): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="review-rating">
                                    <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                                </div>
                                <div>
                                    <div class="review-author"><?= htmlspecialchars($review['name']) ?></div>
                                    <div class="review-date"><?= date('F j, Y', strtotime($review['created_at'])) ?></div>
                                </div>
                            </div>
                            <p><?= htmlspecialchars($review['message']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; font-size: 18px; color: #666; padding: 40px;">
                        No reviews yet. Be the first to share your experience with SereneTripsLK!
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section" style="background-color: #f0f4f8;">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-container">
                <!-- Contact Info -->
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <div class="contact-details">
                        <?php
                        $contactItems = [
                            ['icon' => 'fas fa-map-marker-alt', 'title' => 'Address', 'content' => $contactInfo['address']],
                            ['icon' => 'fas fa-phone-alt', 'title' => 'Phone', 'content' => $contactInfo['phone']],
                            ['icon' => 'fas fa-envelope', 'title' => 'Email', 'content' => $contactInfo['email']],
                            ['icon' => 'fas fa-clock', 'title' => 'Business Hours', 'content' => $contactInfo['business_hours']]
                        ];
                        
                        foreach ($contactItems as $item): ?>
                            <div class="contact-item">
                                <div class="contact-icon"><i class="<?= $item['icon'] ?>"></i></div>
                                <div>
                                    <h4><?= $item['title'] ?></h4>
                                    <p><?= $item['title'] === 'Business Hours' ? $item['content'] : htmlspecialchars($item['content']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="social-links">
                        <?php
                        $socialLinks = [
                            ['icon' => 'fab fa-facebook-f', 'url' => 'https://www.facebook.com/SereneTripsLK'],
                            ['icon' => 'fab fa-twitter', 'url' => 'https://www.twitter.com/SereneTripsLK'],
                            ['icon' => 'fab fa-instagram', 'url' => 'https://www.instagram.com/SereneTripsLK'],
                            ['icon' => 'fab fa-linkedin-in', 'url' => 'https://www.linkedin.com/company/SereneTripsLK']
                        ];
                        
                        foreach ($socialLinks as $link): ?>
                            <a href="<?= $link['url'] ?>" target="_blank"><i class="<?= $link['icon'] ?>"></i></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Review Form -->
                <div class="contact-form">
                    <h3>Share Your Experience</h3>
                    <?= $message ?>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Your Rating</label>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $i === 1 ? 'required' : '' ?>>
                                    <label for="star<?= $i ?>">★</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="message">Your Review</label>
                            <textarea id="message" name="message" class="form-control" required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <ul class="footer-links">
                <li><a href="home.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="package.php">Package</a></li>
                <li><a href="book.php">Book</a></li>
            </ul>
            <div class="social-links">
                <a href="https://www.facebook.com/SereneTripsLK" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.twitter.com/SereneTripsLK" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://www.instagram.com/SereneTripsLK" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.linkedin.com/company/SereneTripsLK" target="_blank"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p class="copyright">&copy; <?= date('Y') ?> SereneTripsLK. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>