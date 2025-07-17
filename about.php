<?php
require_once 'config.php';

// Get about us content
$aboutContent = [];
try {
    $stmt = $pdo->query("SELECT * FROM about_us LIMIT 1");
    $aboutContent = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$aboutContent) {
        // Insert default content if none exists
        $defaultContent = [
            'title' => 'About SereneTripsLK',
            'content' => '<p>Welcome to SereneTripsLK, your premier travel companion in Sri Lanka. We specialize in creating unforgettable travel experiences that showcase the beauty and culture of this amazing island.</p>
                          <p>Our team of travel experts has years of experience crafting personalized itineraries that cater to all types of travelers, from adventure seekers to those looking for relaxation.</p>
                          <p>We pride ourselves on our excellent customer service and attention to detail, ensuring every trip with us is seamless and memorable.</p>',
            'image_path' => 'images/about-us.jpg'
        ];
        $stmt = $pdo->prepare("INSERT INTO about_us (title, content, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$defaultContent['title'], $defaultContent['content'], $defaultContent['image_path']]);
        $aboutContent = $defaultContent;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Get contact information
$contactInfo = [];
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

// Get approved and featured reviews
$reviews = [];
try {
    $stmt = $pdo->query("SELECT * FROM reviews WHERE status = 'approved' AND is_featured = TRUE ORDER BY created_at DESC LIMIT 5");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle review submission
$successMessage = '';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $rating = (int)$_POST['rating'];
    $message = trim($_POST['message']);

    // Validate inputs
    if (empty($name) || empty($email) || empty($message) || $rating < 1 || $rating > 5) {
        $errorMessage = 'Please fill all fields correctly. Rating must be between 1 and 5.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (name, email, rating, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $rating, $message]);
            $successMessage = 'Thank you for your review! It will be visible after approval by our team.';
        } catch (PDOException $e) {
            $errorMessage = 'Error submitting your review. Please try again later.';
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
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: var(--dark-color);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: var(--secondary-color);
        }
        
        .hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 100px 20px;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 20px;
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #27ae60;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-size: 36px;
            color: var(--dark-color);
        }
        
        .about-content {
            display: flex;
            align-items: center;
            gap: 40px;
            margin-bottom: 50px;
        }
        
        .about-image {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-text h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--dark-color);
        }
        
        .about-text p {
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .reviews-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .review-card {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .review-rating {
            color: var(--warning-color);
            margin-right: 15px;
            font-size: 20px;
        }
        
        .review-author {
            font-weight: bold;
            color: var(--dark-color);
        }
        
        .review-date {
            color: #777;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }
        
        .contact-info {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .contact-info h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--dark-color);
        }
        
        .contact-details {
            margin-bottom: 30px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .contact-icon {
            color: var(--primary-color);
            margin-right: 15px;
            font-size: 20px;
            margin-top: 3px;
        }
        
        .contact-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .contact-form h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--dark-color);
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
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .rating-stars {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .rating-stars input {
            display: none;
        }
        
        .rating-stars label {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .rating-stars input:checked ~ label {
            color: #ddd;
        }
        
        .rating-stars label:hover,
        .rating-stars input:checked + label,
        .rating-stars input:checked + label ~ label {
            color: var(--warning-color);
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
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 40px 0 20px;
            text-align: center;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            list-style: none;
            margin-bottom: 20px;
        }
        
        .footer-links li {
            margin: 0 15px;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--secondary-color);
        }
        
        .social-links {
            margin-bottom: 20px;
        }
        
        .social-links a {
            color: white;
            font-size: 20px;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: var(--secondary-color);
        }
        
        .copyright {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
        }
        
        @media (max-width: 768px) {
            .about-content {
                flex-direction: column;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .section-title {
                font-size: 30px;
            }
            
            nav {
                flex-direction: column;
            }
            
            .nav-links {
                margin-top: 20px;
            }
            
            .nav-links li {
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">SereneTripsLK</a>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="packages.php">Packages</a></li>
                    <li><a href="about_contact.php">About & Contact</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>About SereneTripsLK</h1>
            <p>Discover our story and connect with us for your next unforgettable journey in Sri Lanka</p>
            <a href="#contact" class="btn">Contact Us</a>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Our Story</h2>
            <div class="about-content">
                <div class="about-image">
                    <img src="<?= htmlspecialchars($aboutContent['image_path'] ?? 'images/about-us.jpg') ?>" alt="About SereneTripsLK">
                </div>
                <div class="about-text">
                    <h2><?= htmlspecialchars($aboutContent['title'] ?? 'About SereneTripsLK') ?></h2>
                    <?= $aboutContent['content'] ?? '<p>Welcome to SereneTripsLK, your premier travel companion in Sri Lanka.</p>' ?>
                </div>
            </div>

            <h2 class="section-title">What Our Travelers Say</h2>
            <div class="reviews-container">
                <?php if (!empty($reviews)): ?>
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
                    <p>No featured reviews yet. Be the first to share your experience!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="contact" class="section" style="background-color: #f0f4f8;">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4>Address</h4>
                                <p><?= htmlspecialchars($contactInfo['address']) ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <h4>Phone</h4>
                                <p><?= htmlspecialchars($contactInfo['phone']) ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4>Email</h4>
                                <p><?= htmlspecialchars($contactInfo['email']) ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h4>Business Hours</h4>
                                <p><?= $contactInfo['business_hours'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <h3>Share Your Experience</h3>
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success"><?= $successMessage ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger"><?= $errorMessage ?></div>
                    <?php endif; ?>
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
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1">★</label>
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

    <footer>
        <div class="container">
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="packages.php">Packages</a></li>
                <li><a href="about_contact.php">About & Contact</a></li>
                <li><a href="gallery.php">Gallery</a></li>
                <li><a href="terms.php">Terms & Conditions</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
            </ul>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <p class="copyright">&copy; <?= date('Y') ?> SereneTripsLK. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>