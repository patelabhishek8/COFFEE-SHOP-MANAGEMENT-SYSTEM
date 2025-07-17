<?php
// Include database connection
include('../PHP/db_connect.php');

// Fetch dashboard metrics
// Total Users
$sql_users = "SELECT COUNT(*) FROM users";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_row()[0];

// Total Venue Partners (from venue_partners table)
$sql_partners = "SELECT COUNT(*) FROM venue_partners";
$result_partners = $conn->query($sql_partners);
$total_partners = $result_partners->fetch_row()[0];

// Total Bookings
$sql_bookings = "SELECT COUNT(*) FROM bookings";
$result_bookings = $conn->query($sql_bookings);
$total_bookings = $result_bookings->fetch_row()[0];

// Fetch data for sections
// Users
$users = [];
$sql = "SELECT id, full_name, phone, email FROM users";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Venue Partners (from venue_partners, joined with listing)
$partners = [];
$sql = "SELECT vp.id, vp.email AS partner_email, l.business_name, l.contact_person, l.mobile, l.email AS listing_email, l.city, l.state 
        FROM venue_partners vp 
        JOIN listing l ON vp.listing_id = l.id";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $partners[] = $row;
}

// Bookings
$bookings = [];
$sql = "SELECT b.id, b.listing_id, b.booking_date, b.user_name, b.email, b.price, l.business_name 
        FROM bookings b 
        JOIN listing l ON b.listing_id = l.id";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

// Feedback
$feedbacks = [];
$sql = "SELECT f.id, f.listing_id, f.email, f.rating, f.comment, f.created_at, l.business_name 
        FROM feedback f 
        JOIN listing l ON f.listing_id = l.id 
        ORDER BY l.business_name, f.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../CSS/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="#dashboard" class="active">Dashboard</a></li>
                <li><a href="#users">Users</a></li>
                <li><a href="#partners">Venue Partners</a></li>
                <li><a href="#bookings">Bookings</a></li>
                <li><a href="#feedback">Feedback</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1 id="section-title">Dashboard</h1>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <p style="color: green; margin-bottom: 20px;"><?php echo htmlspecialchars($_GET['success']); ?></p>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <section id="dashboard" class="content-section active">
                <div class="dashboard-cards">
                    <div class="card">
                        <h3>Total Users</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                    <div class="card">
                        <h3>Total Venue Partners</h3>
                        <p><?php echo $total_partners; ?></p>
                    </div>
                    <div class="card">
                        <h3>Total Bookings</h3>
                        <p><?php echo $total_bookings; ?></p>
                    </div>
                </div>
            </section>

            <!-- Users Section -->
            <section id="users" class="content-section">
                <h2>Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <a href="../PHP/delete_user.php?id=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- Venue Partners Section -->
            <section id="partners" class="content-section">
                <h2>Venue Partners</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Contact Person</th>
                            <th>Mobile</th>
                            <th>Listing Email</th>
                            <th>Partner Email</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partners as $partner): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($partner['id']); ?></td>
                                <td><?php echo htmlspecialchars($partner['business_name']); ?></td>
                                <td><?php echo htmlspecialchars($partner['contact_person']); ?></td>
                                <td><?php echo htmlspecialchars($partner['mobile']); ?></td>
                                <td><?php echo htmlspecialchars($partner['listing_email']); ?></td>
                                <td><?php echo htmlspecialchars($partner['partner_email']); ?></td>
                                <td><?php echo htmlspecialchars($partner['city'] . ', ' . $partner['state']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- Bookings Section -->
            <section id="bookings" class="content-section">
                <h2>Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Venue</th>
                            <th>Booked By</th>
                            <th>Email</th>
                            <th>Price</th>
                            <th>Booking Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['business_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td>₹<?php echo htmlspecialchars($booking['price']); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <!-- Feedback Section -->
            <section id="feedback" class="content-section">
                <h2>Feedback</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Venue</th>
                            <th>Email</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['business_name']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['rating']); ?> ★</td>
                                <td><?php echo htmlspecialchars($feedback['comment']); ?></td>
                                <td><?php echo htmlspecialchars($feedback['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <script>
        // Sidebar navigation
        const links = document.querySelectorAll('.sidebar a');
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
                sectionTitle.textContent = targetId.charAt(0).toUpperCase() + targetId.slice(1);

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
</body>
</html>