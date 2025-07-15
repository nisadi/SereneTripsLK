<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home - SereneTripsLK</title>

  <!-- FontAwesome + Swiper + CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="style.css" />
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

  <section class="home">
    <div class="swiper home-slider">
      <div class="swiper-wrapper">
        
        <div class="swiper-slide slide">
            <video autoplay muted loop playsinline>
            <source src="https://media.istockphoto.com/id/2037434383/video/serene-aerial-view-of-woman-hiking-near-beautiful-waterfall-on-sri-lanka.mp4?b=1&s=mp4-640x640-is&k=20&c=FeARCVLr12bZ2B254L7yw8Vrm-DD-pxyceZBcqCB-TY=" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
          <div class="content">
            <span>explore, discover, travel</span>
            <h3>travel around the world</h3>
            <a href="package.php" class="btn">discover more</a>
          </div>
        </div>

        <div class="swiper-slide slide">
            <video autoplay muted loop playsinline>
            <source src="https://ak.picdn.net/shutterstock/videos/3656583357/preview/stock-footage-multiple-tourists-walk-by-the-ruins-of-the-ancient-stone-fortress-on-the-top-of-the-picturesque.mp4" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
          <div class="content">
            <span>explore, discover, travel</span>
            <h3>make your tour worthwhile</h3>
            <a href="package.php" class="btn">discover more</a>
          </div>
        </div>

        <div class="swiper-slide slide">
          <video autoplay muted loop playsinline>
            <source src="https://videos.pexels.com/video-files/32168722/13717613_2560_1440_30fps.mp4" type="video/mp4" />
            Your browser does not support the video tag.
          </video>
          <div class="content">
            <span>Explore, Discover, Travel</span>
            <h3>Travel Around the World</h3>
            <a href="package.php" class="btn">Discover More</a>
          </div>
        </div>

      </div>

      <!-- Swiper buttons -->
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
  </section>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>