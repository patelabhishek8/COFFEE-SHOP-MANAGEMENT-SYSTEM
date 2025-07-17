<?php
include '../PHP/db_connect.php';

// Fetch unique categories (venue_types) and cities
//DISTINCT ensures no duplicate categories are fetched
$categoryQuery = "SELECT DISTINCT venue_types FROM listing";
$categoryResult = $conn->query($categoryQuery);

$cityQuery = "SELECT DISTINCT city FROM listing";
$cityResult = $conn->query($cityQuery);

// Fetch all listings
$listingQuery = "SELECT * FROM listing ORDER BY created_at DESC";
$listingResult = $conn->query($listingQuery);

// Function to fetch the first photo from a listing and this photo is set as logo in venues
function getFirstPhoto($conn, $listing_id) {
    $photoQuery = "SELECT photo_path FROM listing_photos WHERE listing_id = $listing_id LIMIT 1";
    $photoResult = $conn->query($photoQuery);
    if ($photoResult && $photoResult->num_rows > 0) {
        $row = $photoResult->fetch_assoc();
        return $row['photo_path'];
    }
    return "../LISTINGIMAGE/default.png"; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="../CSS/home.css" />
</head>
<body>
  <!-- Header -->
  <header class="d-flex justify-content-between align-items-center p-3">
    <div class="logo">
        <img src="../IMAGES/logo.jpg" alt="Venue Management Logo" />
    </div>
    <nav class="nav-menu">
        <a href="../HTML/Topvenue.html">Top Venues</a>
        <a href="../HTML/Aboutus.html">About us</a> 
    </nav>

    <input type="text" class="form-control w-25" placeholder="Search venues..." id="searchInput"/>

    <button class="btn" onclick="location.href='listing.html'">Listing</button>
    <button class="btn secondary" onclick="location.href='login.html'">Login/Signup</button>
  </header>

  <!-- Filter Section -->
  <div class="container mt-3">
    <div class="d-flex sorting-section flex-wrap">
      <select class="form-select w-25 me-2 mb-2" id="categoryFilter">
        <option value="all">All Categories</option>
        <?php
        //this uniquecategories array is empy but later will types like banquet hall,outddoe etc.
        $uniqueCategories = [];
        if ($categoryResult && $categoryResult->num_rows > 0) {
            while ($row = $categoryResult->fetch_assoc()) {
                $categories = explode(',', $row['venue_types']);
                foreach ($categories as $cat) {
                    $cat = trim($cat);
                    if ($cat !== '') { 
                        $uniqueCategories[] = $cat;
                    }
                }
            }
            $uniqueCategories = array_unique($uniqueCategories);
            foreach ($uniqueCategories as $cat) {
                echo "<option value=\"$cat\">$cat</option>";
            }
        }
        ?>
      </select>

      <select class="form-select w-25 mb-2" id="cityFilter">
        <option value="all">All Cities</option>
        <?php
        if ($cityResult && $cityResult->num_rows > 0) {
            while ($row = $cityResult->fetch_assoc()) {
                $city = htmlspecialchars($row['city']);
                echo "<option value=\"$city\">$city</option>";
            }
        }
        ?>
      </select>
    </div>
  </div>

  <!-- Venue Listings -->
  <div class="container mt-4">
    <div class="row" id="venueContainer">
      <?php
      if ($listingResult && $listingResult->num_rows > 0) {
        while ($row = $listingResult->fetch_assoc()) {
          $photo = getFirstPhoto($conn, $row['id']);
          $venueTypes = htmlspecialchars($row['venue_types']);
          $address = htmlspecialchars($row['street'] . ", " . $row['city']);
          $contact = htmlspecialchars($row['mobile']);
          $business = htmlspecialchars($row['business_name']);

          // The corrected link to pass venue_id with added spacing class and inline check
          echo "<div class='col-md-6 venue mb-4 venue-spaced' 
                    data-category='$venueTypes' 
                    data-city='" . htmlspecialchars($row['city']) . "' 
                    style='margin-bottom: 40px;'>
                  <div class='border p-3 d-flex align-items-start' style='cursor:pointer;' 
                       onclick=\"window.location.href='../HTML/detail_page.php?venue_id=" . $row['id'] . "'\">
                    <img src='" . $photo . "' alt='Venue Image' class='img-thumbnail me-3' style='width:150px; height:auto;'/>
                    <div>
                      <h5>$business</h5>
                      <p><strong>Address:</strong> $address</p>
                      <p><strong>Category:</strong> $venueTypes</p>
                      <p><strong>Contact:</strong> $contact</p>
                    </div>
                  </div>
                </div>";
        }
      } else {
        echo "<p>No venues found.</p>";
      }
      ?>
    </div>
  </div>

  <!-- Footer Section -->
  <footer>
    <div class="footer-container" style="display: flex; justify-content: space-between">
      <!-- Contact Us Section -->
      <div class="footer-column">
        <h3>Contact Us</h3>
        <p>Email: <a href="mailto:coffeeshop@34.com">spot2host@gmail.com</a></p>
        <p>Phone: <a href="tel:+91 97263 23358">+91 97263 23358</a></p>
      </div>

      <!-- Social Media Section -->
      <div class="footer-column">
        <h3>Follow Us</h3>
        <div class="social-icons">
          <a href="https://www.youtube.com" target="_blank">
            <img src="../IMAGES/youtube.png" alt="YouTube" class="social-icon" />
          </a>
          <a href="https://www.facebook.com" target="_blank">
            <img src="../IMAGES/facebook.png" alt="Facebook" class="social-icon" />
          </a>
          <a href="https://www.instagram.com" target="_blank">
            <img src="../IMAGES/instagram.png" alt="Instagram" class="social-icon" />
          </a>
          <a href="https://www.twitter.com" target="_blank">
            <img src="../IMAGES/twitter.png" alt="Twitter" class="social-icon" />
          </a>
        </div>
      </div>

      <!-- About Section -->
      <div class="footer-column">
        <h3>Our Policies</h3>
        <ul class="policy-list">
          <li><a href="Termvisualize/Term_and_Condition.1.jpeg">Terms and Conditions</a></li>
          <li><a href="Privacy_policy.1.jpeg">Privacy Policy</a></li>
          <li><a href="Shipping_Policy.1.jpeg">Shipping Policy</a></li>
          <li><a href="Return_Policy.1.jpeg">Return Policy</a></li>
        </ul>
      </div>

      <!-- Created By Section -->
      <div class="footer-column">
        <h3>Credits</h3>
        <p>Created by Aman & Abhishek</p>
        <p>2025 Spot2host. All Rights Reserved</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../JS/home.js"></script>
 
</body>
</html>