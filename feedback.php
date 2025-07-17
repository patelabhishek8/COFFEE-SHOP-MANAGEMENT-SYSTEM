<?php
// Include database connection
include('../PHP/db_connect.php');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Get form data
$listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

// Validate inputs
if ($listing_id <= 0 || empty($email) || $rating < 1 || $rating > 5 || empty($comment)) {
    die("Invalid input. Please fill all fields correctly.");
}

// Check if listing_id exists in listing table
$sql = "SELECT COUNT(*) FROM listing WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed (listing check): " . $conn->error);
}
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$stmt->bind_result($listing_count);
$stmt->fetch();
$stmt->close();

if ($listing_count == 0) {
    $conn->close();
    die("Invalid venue ID. Venue does not exist.");
}

// Check if email exists in users table
$sql = "SELECT COUNT(*) FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $conn->close();
    die("Prepare failed (email check): " . $conn->error);
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($email_count);
$stmt->fetch();
$stmt->close();

if ($email_count == 0) {
    $conn->close();
    die("Email doesn't exist.");
}

// Email and listing_id are valid, proceed to store feedback
$sql = "INSERT INTO feedback (listing_id, email, rating, comment) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $conn->close();
    die("Prepare failed (insert): " . $conn->error);
}

$stmt->bind_param("isis", $listing_id, $email, $rating, $comment);
if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    die("Insert failed: " . $stmt->error);
}

// Cleanup before redirect
$stmt->close();
$conn->close();

// Success: Redirect back to the venue detail page
header("Location: ../HTML/detail_page.php?venue_id=$listing_id&success=Feedback submitted successfully");
exit();
?>