<?php
session_start();
include '../PHP/db_connect.php';

if (!isset($_SESSION['vp_email']) || !isset($_SESSION['listing_id'])) {
    header('Location: ../HTML/vp_login.php');
    exit();
}

$listing_id = $_SESSION['listing_id'];

// Fetch bookings for VP's venue
$sql = "SELECT b.id, b.user_name, b.email, b.booking_date, b.booking_time, b.occasion, b.guests_range, b.price, l.business_name
        FROM bookings b
        JOIN listing l ON b.listing_id = l.id
        WHERE b.listing_id = ? AND b.status = 'submitted'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$bookings_result = $stmt->get_result();

// Fetch ratings and reviews for VP's venue
$feedback_sql = "SELECT rating, comment, email, created_at 
                 FROM feedback 
                 WHERE listing_id = ? 
                 ORDER BY created_at DESC";
$feedback_stmt = $conn->prepare($feedback_sql);
$feedback_stmt->bind_param("i", $listing_id);
$feedback_stmt->execute();
$feedback_result = $feedback_stmt->get_result();

// Calculate average rating
$average_sql = "SELECT AVG(rating) as average_rating 
                FROM feedback 
                WHERE listing_id = ?";
$average_stmt = $conn->prepare($average_sql);
$average_stmt->bind_param("i", $listing_id);
$average_stmt->execute();
$average_result = $average_stmt->get_result();
$average_rating = 0;
if ($average_result->num_rows > 0) {
    $average_row = $average_result->fetch_assoc();
    $average_rating = round($average_row['average_rating'], 1); // Round to 1 decimal place
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venue Partner Panel</title>
    <link href="../CSS/vp_panel.css" rel="stylesheet">
</head>
<body>
    <div class="panel-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Venue Partner Panel</h2>
            <p class="email"><?php echo htmlspecialchars($_SESSION['vp_email']); ?></p>
            <ul>
                <li><a href="#bookings" class="active">Pending Bookings</a></li>
                <li><a href="#ratings">Ratings & Reviews</a></li>
                <li><a href="../PHP/vp_logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1 id="section-title">Pending Bookings</h1>
            </header>

            <!-- Pending Bookings Section -->
            <section id="bookings" class="content-section active">
                <h2>Pending Bookings</h2>
                <?php if ($bookings_result->num_rows > 0): ?>
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Venue</th>
                                <th>User Name</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Occasion</th>
                                <th>Guests</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $bookings_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['business_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['occasion']); ?></td>
                                    <td><?php echo htmlspecialchars($row['guests_range']); ?></td>
                                    <td>₹<?php echo htmlspecialchars($row['price']); ?></td>
                                    <td>
                                        <form action="../PHP/vp_process_action.php" method="POST">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="user_email" value="<?php echo $row['email']; ?>">
                                            <input type="hidden" name Comunicado="business_name" value="<?php echo $row['business_name']; ?>">
                                            <input type="hidden" name="booking_date" value="<?php echo $row['booking_date']; ?>">
                                            <button type="submit" name="action" value="confirm" class="btn-confirm">Confirm</button>
                                            <button type="submit" name="action" value="cancel" class="btn-cancel">Cancel</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No pending bookings.</p>
                <?php endif; ?>
            </section>

            <!-- Ratings & Reviews Section -->
            <section id="ratings" class="content-section">
                <h2>Ratings & Reviews</h2>
                <p><strong>Average Rating:</strong> <?php echo $average_rating > 0 ? $average_rating . ' ★' : 'No ratings yet'; ?></p>
                <?php if ($feedback_result->num_rows > 0): ?>
                    <table class="ratings-table">
                        <thead>
                            <tr>
                                <th>User Email</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['rating']); ?> ★</td>
                                    <td><?php echo htmlspecialchars($feedback['comment']); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No ratings or reviews yet.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script>
        // Sidebar navigation
        const links = document.querySelectorAll('.sidebar a:not(.logout-link)');
        const sections = document.querySelectorAll('.content-section');
        const sectionTitle = document.getElementById('section-title');

        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);

                // Update active link
                links.forEach(l => l.classList.remove('active'));
                link.classList.add('active');

                // Show target section
                sections.forEach(section => {
                    section.classList.remove('active');
                    if (section.id === targetId) {
                        section.classList.add('active');
                    }
                });

                // Update section title
                sectionTitle.textContent = targetId === 'bookings' ? 'Pending Bookings' : 'Ratings & Reviews';

                // Update URL hash
                window.location.hash = targetId;
            });
        });

        // On page load, check URL hash and activate the correct section
        window.addEventListener('load', () => {
            const hash = window.location.hash.substring(1);
            if (hash) {
                links.forEach(link => {
                    const targetId = link.getAttribute('href').substring(1);
                    if (targetId === hash) {
                        link.click();
                    }
                });
            }
        });
    </script>

    <?php
    $stmt->close();
    $feedback_stmt->close();
    $average_stmt->close();
    $conn->close();
    ?>
</body>
</html>