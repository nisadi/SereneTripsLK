<?php
// Simple input sanitization
function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = clean_input($_POST["name"]);
    $email    = clean_input($_POST["email"]);
    $phone    = clean_input($_POST["phone"]);
    $address  = clean_input($_POST["address"]);
    $location = clean_input($_POST["location"]);
    $guests   = clean_input($_POST["guests"]);
    $arrivals = clean_input($_POST["arrivals"]);
    $leaving  = clean_input($_POST["leaving"]);
} else {
    // Redirect if accessed directly without form submission
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking Confirmation</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url('https://wallpapercat.com/w/full/7/8/8/639817-3072x2051-desktop-hd-sri-lanka-background.jpg') no-repeat center center/cover;
      color: white;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 30px;
    }

    .confirmation-box {
      background: rgba(0, 0, 0, 0.7);
      padding: 40px;
      border-radius: 15px;
      max-width: 600px;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }

    h2 {
      font-size: 2rem;
      color: #ffd700;
      text-align: center;
      margin-bottom: 20px;
    }

    p {
      margin: 10px 0;
      font-size: 18px;
      line-height: 1.5;
    }

    .highlight {
      color: #f4b400;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div class="confirmation-box">
    <h2>Booking Confirmation</h2>
    <p><span class="highlight">Name:</span> <?php echo $name; ?></p>
    <p><span class="highlight">Email:</span> <?php echo $email; ?></p>
    <p><span class="highlight">Phone:</span> <?php echo $phone; ?></p>
    <p><span class="highlight">Address:</span> <?php echo $address; ?></p>
    <p><span class="highlight">Destination:</span> <?php echo $location; ?></p>
    <p><span class="highlight">Guests:</span> <?php echo $guests; ?></p>
    <p><span class="highlight">Arrival Date:</span> <?php echo $arrivals; ?></p>
    <p><span class="highlight">Leaving Date:</span> <?php echo $leaving; ?></p>
  </div>

</body>
</html>
