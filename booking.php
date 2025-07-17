<?php
include '../PHP/db_connect.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $listing_id = filter_var($_POST['listing_id'], FILTER_VALIDATE_INT);
    $price = htmlspecialchars(trim($_POST['price']));
    $occasion = htmlspecialchars(trim($_POST['occasion']));
    $booking_date = htmlspecialchars(trim($_POST['date']));
    $booking_time = htmlspecialchars(trim($_POST['time']));
    $guests_range = htmlspecialchars(trim($_POST['guests_range']));
    $user_name = htmlspecialchars(trim($_POST['fullname']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    // Validate inputs
    if (!$listing_id || !$price || !$occasion || !$booking_date || !$booking_time || !$guests_range || !$user_name || !$phone || !$email) {
        echo "<script>alert('Invalid or missing data. Please try again.'); window.location.href = '../Venue_project/HTML/detail_page.php?venue_id=$listing_id';</script>";
        exit();
    }

    // Insert into bookings table
    $sql = "INSERT INTO bookings (listing_id, occasion, booking_date, booking_time, guests_range, user_name, phone, email, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssssss", $listing_id, $occasion, $booking_date, $booking_time, $guests_range, $user_name, $phone, $email, $price);
    
    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        // Redirect to payment page
        header("Location: ../HTML/payment.php?booking_id=$booking_id");
        exit();
    } else {
        echo "<script>alert('Failed to save booking. Please try again.'); window.location.href = '../HTML/detail_page.php?venue_id=$listing_id';</script>";
        exit();
    }
} else {
    header('Location: ../HTML/home.php');
    exit();
}
?>