<?php
session_start();
include '../PHP/db_connect.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['vp_email'])) {
    $booking_id = filter_var($_POST['booking_id'], FILTER_VALIDATE_INT);
    $action = $_POST['action'];
    $user_email = htmlspecialchars(trim($_POST['user_email']));
    $business_name = htmlspecialchars(trim($_POST['business_name']));
    $booking_date = htmlspecialchars(trim($_POST['booking_date']));

    if (!$booking_id || !in_array($action, ['confirm', 'cancel'])) {
        echo "<script>alert('Invalid request.'); window.location.href = '../HTML/vp_panel.php';</script>";
        exit();
    }

    // Update booking status
    $status = $action === 'confirm' ? 'confirmed' : 'canceled';
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        // Send email to user
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'spot2host@gmail.com';
            $mail->Password = 'uziqmufedzoxpvnk';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('spot2host@gmail.com', 'Spot2Host');
            $mail->addAddress($user_email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Booking ' . ucfirst($status);
            $mail->Body = "Your booking for <strong>$business_name</strong> on <strong>$booking_date</strong> has been $status.";
            if ($action === 'cancel') {
                $mail->Body .= "<p>Your money will be refunded to you within 1 week.</p>";
            }

            $mail->send();
            echo "<script>alert('Booking $status and email sent.'); window.location.href = '../HTML/vp_panel.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Booking $status, but email failed: {$mail->ErrorInfo}'); window.location.href = '../HTML/vp_panel.php';</script>";
        }
    } else {
        echo "<script>alert('Failed to update booking.'); window.location.href = '../HTML/vp_panel.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header('Location: ../HTML/vp_login.php');
    exit();
}
?>