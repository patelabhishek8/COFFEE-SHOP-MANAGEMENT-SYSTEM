<?php
include('../PHP/db_connect.php');

// checing  venue_id.
if (isset($_GET['venue_id']) && is_numeric($_GET['venue_id'])) {
    $venue_id = $_GET['venue_id'];
} else {
    header('Location: ../HTML/home.php');
    exit();
}

// Fetching venue details from the listing table.
$sql = "SELECT * FROM listing WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$venue_result = $stmt->get_result();

if ($venue_result->num_rows > 0) {
    $venue = $venue_result->fetch_assoc();

    // Escape all values safely
    $business_name = htmlspecialchars($venue['business_name']);
    $pincode = htmlspecialchars($venue['pincode']);
    $street = htmlspecialchars($venue['street']);
    $city = htmlspecialchars($venue['city']);
    $state = htmlspecialchars($venue['state']);
    $contact_person = htmlspecialchars($venue['contact_person']);
    $mobile = htmlspecialchars($venue['mobile']);
    $email = htmlspecialchars($venue['email']);
    $price = htmlspecialchars($venue['price']);
    $operating_days = htmlspecialchars($venue['operating_days']);
    $opening_time = htmlspecialchars($venue['opening_time']);
    $closing_time = htmlspecialchars($venue['closing_time']);
    $venue_types = htmlspecialchars($venue['venue_types']);
    $facilities = htmlspecialchars($venue['facilities']);
} else {
    echo "Venue not found!";
    exit();
}

// Fetch images
$sql_images = "SELECT photo_path FROM listing_photos WHERE listing_id = ?";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->bind_param("i", $venue_id);
$stmt_images->execute();
$images_result = $stmt_images->get_result();

$images = [];
while ($row = $images_result->fetch_assoc()) {
    $images[] = $row['photo_path'];
}

// Close DB connections
$stmt->close();
$stmt_images->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Venue Details - <?php echo $business_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/detail_page.css">
</head>
<body>

 <!-- Header -->
 <header class="d-flex justify-content-between align-items-center p-3">
    <div class="logo">
        <img src="../IMAGES/logo.jpg" alt="Venue Management Logo" />
    </div>
    <nav class="nav-menu">
        <a href="../HTML/home.php">Home</a>
        <a href="../HTML/Topvenue.html">Top Venues</a>
        <a href="../HTML/Aboutus.html">About</a>
    </nav>
    
    <button class="btn" onclick="location.href='listing.html'">Listing</button>
    <button class="btn secondary" onclick="location.href='login.html'">Login/Signup</button>
  </header>

<!-- Image Slider -->
<div class="slider-container">
    <div class="slider">
        <?php foreach ($images as $img): ?>
            <div class="slide">
                <img src="<?php echo htmlspecialchars($img); ?>" alt="Venue Image">
            </div>
        <?php endforeach; ?>
    </div>
    <button class="prev" onclick="prevSlide()">‹</button>
    <button class="next" onclick="nextSlide()">›</button>
</div>

<!-- Venue Information -->
<div class="venue-container">
    <div class="venue-details">
        <h2><?php echo $business_name; ?></h2>
        <p><strong>Address:</strong> <?php echo $street . ', ' . $city . ', ' . $state . ' - ' . $pincode; ?></p>
        <p><strong>Contact Person:</strong> <?php echo $contact_person; ?></p>
        <p><strong>Mobile:</strong> <?php echo $mobile; ?></p>
        <p><strong>Email:</strong> <?php echo $email; ?></p>
        <p><strong>Price:</strong> ₹<?php echo $price; ?></p>
        <p><strong>Operating Days:</strong> <?php echo $operating_days; ?></p>
        <p><strong>Opening Time:</strong> <?php echo $opening_time; ?></p>
        <p><strong>Closing Time:</strong> <?php echo $closing_time; ?></p>
        <p><strong>Venue Types:</strong> <?php echo $venue_types; ?></p>
        <p><strong>Facilities:</strong> <?php echo $facilities; ?></p>

        <!-- Review Section -->
        <div class="rating-section">
            <h3>Rate & Review</h3>
            <?php if (isset($_GET['success'])): ?>
                <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php endif; ?>
            <form action="../PHP/feedback.php" method="POST">
                <input type="hidden" name="listing_id" value="<?php echo $venue_id; ?>">
                <div class="star-rating">
                    <input type="radio" name="rating" id="star1" value="1" required /><label for="star1">★</label>
                    <input type="radio" name="rating" id="star2" value="2" /><label for="star2">★</label>
                    <input type="radio" name="rating" id="star3" value="3" /><label for="star3">★</label>
                    <input type="radio" name="rating" id="star4" value="4" /><label for="star4">★</label>
                    <input type="radio" name="rating" id="star5" value="5" /><label for="star5">★</label>
                </div>
                <input type="email" name="email" placeholder="Enter your email..." required />
                <input type="text" name="comment" placeholder="Write your comment..." required />
                <button type="submit">Submit Review</button>
            </form>
        </div>
    </div>

    <!-- Booking Section -->
    <div class="booking-section">
        <h3>Check Availability</h3>
        <!-- Booking Form -->
        <form action="../PHP/booking.php" method="POST">
            <!-- Pass the correct venue ID -->
            <input type="hidden" name="listing_id" value="<?php echo $venue_id; ?>">
            <input type="hidden" name="price" value="<?php echo $price; ?>">

            <label for="occasion">Occasion:</label>
            <select name="occasion" id="occasion" required>
                <option value="Wedding">Wedding</option>
                <option value="Party">Party</option>
                <option value="Conference">Conference</option>
            </select>

            <label for="date">Date:</label>
            <input type="date" name="date" id="date" required>

            <label for="time">Time:</label>
            <input type="time" name="time" id="time" required>

            <label for="guests_range">No. of Guests:</label>
            <select name="guests_range" id="guests_range" required>
                <option value="1-50">1-50 Guests</option>
                <option value="51-100">51-100 Guests</option>
                <option value="101-200">101-200 Guests</option>
            </select>

            <label for="fullname">Your Name:</label>
            <input type="text" name="fullname" id="fullname" required>

            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <button type="submit">Check Availability</button>
        </form>
    </div>
</div>

<script src="../JS/venue-detail.js"></script>
<script src="../JS/imageslider.js"></script>
</body>
</html>