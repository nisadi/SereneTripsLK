<?php
require_once __DIR__ . '/admin/config.php';

// Fetch all packages from database
$stmt = $pdo->query("SELECT * FROM packages ORDER BY created_at DESC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages - SereneTripsLK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
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

    <section class="packages">
        <h1 class="heading">Our Packages</h1>
        
        <div class="box-container">
            <?php foreach(array_chunk($packages, 4) as $packageRow): ?>
                <?php foreach($packageRow as $package): ?>
                    <div class="box">
                        <div class="image">
                            <img src="<?= htmlspecialchars($package['image_path']) ?>" alt="<?= htmlspecialchars($package['title']) ?>">
                        </div>
                        <div class="content">
                            <h3><?= htmlspecialchars($package['title']) ?></h3>
                            <p class="short-desc"><?= htmlspecialchars($package['short_description']) ?></p>
                            <div class="full-desc" style="display: none;">
                                <p><?= htmlspecialchars($package['full_description']) ?></p>
                                <div class="package-details">
                                    <h4>Package Includes:</h4>
                                    <ul>
                                        <?php 
                                        $includes = json_decode($package['includes'], true);
                                        foreach($includes as $item): 
                                        ?>
                                            <li><?= htmlspecialchars($item) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <button class="read-more-btn">Read More</button>
                            <a href="book.php?package=<?= $package['id'] ?>" class="btn">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const readMoreBtns = document.querySelectorAll('.read-more-btn');
            
            readMoreBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const content = this.parentElement;
                    const fullDesc = content.querySelector('.full-desc');
                    const shortDesc = content.querySelector('.short-desc');
                    
                    if (fullDesc.style.display === 'none') {
                        fullDesc.style.display = 'block';
                        shortDesc.style.display = 'none';
                        this.textContent = 'Read Less';
                    } else {
                        fullDesc.style.display = 'none';
                        shortDesc.style.display = 'block';
                        this.textContent = 'Read More';
                    }
                });
            });
        });
    </script>
</body>
</html>