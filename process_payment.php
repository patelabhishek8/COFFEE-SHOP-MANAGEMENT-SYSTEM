<?php
include '../PHP/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_to_pay'])) {
    // Collect and sanitize form data
    $booking_id = filter_var($_POST['booking_id'], FILTER_VALIDATE_INT);
    $amount = htmlspecialchars(trim($_POST['amount']));
    $payment_method = htmlspecialchars(trim($_POST['payment_method']));
    $card_holder_name = isset($_POST['card_holder_name']) ? htmlspecialchars(trim($_POST['card_holder_name'])) : null;
    $card_number = isset($_POST['card_number']) ? htmlspecialchars(trim($_POST['card_number'])) : null;
    $expiry_date = isset($_POST['expiry_date']) ? htmlspecialchars(trim($_POST['expiry_date'])) : null;
    $cvv = isset($_POST['cvv']) ? htmlspecialchars(trim($_POST['cvv'])) : null;
    $upi_id = isset($_POST['upi_id']) ? htmlspecialchars(trim($_POST['upi_id'])) : null;

    // Validate inputs
    if (!$booking_id || !$amount || !$payment_method) {
        echo "<script>alert('Invalid or missing data. Please try again.'); window.location.href = '../PHP/payment.php?booking_id=$booking_id';</script>";
        exit();
    }

    if ($payment_method === 'DEBIT' || $payment_method === 'CREDIT') {
        if (empty($card_holder_name) || empty($card_number) || empty($expiry_date) || empty($cvv)) {
            echo "<script>alert('Please fill all card details.'); window.location.href = '../PHP/payment.php?booking_id=$booking_id';</script>";
            exit();
        }
    } elseif ($payment_method === 'UPI') {
        if (empty($upi_id)) {
            echo "<script>alert('Please enter a UPI ID.'); window.location.href = '../PHP/payment.php?booking_id=$booking_id';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid payment method.'); window.location.href = '../PHP/payment.php?booking_id=$booking_id';</script>";
        exit();
    }

    // Insert into payments table
    $sql = "INSERT INTO payments (booking_id, payment_method, card_holder_name, card_number, expiry_date, cvv, upi_id, amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssss", $booking_id, $payment_method, $card_holder_name, $card_number, $expiry_date, $cvv, $upi_id, $amount);
    
    if ($stmt->execute()) {
        // Update booking status to 'submitted'
        $update_sql = "UPDATE bookings SET status = 'submitted' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $booking_id);
        $update_stmt->execute();
        $update_stmt->close();

        $stmt->close();
        $conn->close();
        echo "<script>alert('Payment submitted successfully. Booking sent to VP panel.'); window.location.href = '../HTML/home.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to process payment. Please try again.'); window.location.href = '../PHP/payment.php?booking_id=$booking_id';</script>";
        exit();
    }
} else {
    header('Location: ../HTML/home.php');
    exit();
}
?>