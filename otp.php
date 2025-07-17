<?php
session_start();
include '../PHP/db_connect.php';

// Load PHPMailer properly
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    // Sending OTP
    if (!empty($email) && empty($otp)) {
        // Checks email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Invalid email format";
            exit();
        }

        // Check if user exists in db.
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Email not registered";
            exit();
        }

        // Generate OTP for reset passoword.
        $generatedOtp = rand(100000, 999999);
        $_SESSION['otp'] = $generatedOtp;
        $_SESSION['email'] = $email;

        // Save OTP to database
        $update = $conn->prepare("UPDATE users SET otp = ?, otp_verified = 0 WHERE email = ?");
        $update->bind_param("is", $generatedOtp, $email);
        $update->execute();

        // Send OTP in user email for reset
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'spot2host@gmail.com';
        $mail->Password = 'uziqmufedzoxpvnk'; // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('spot2host@gmail.com', 'Spot2Host');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Spot2Host OTP for Password Reset';
        $mail->Body = "<p>Your OTP is: <strong>$generatedOtp</strong></p>";

        if ($mail->send()) {
            echo "OTP Sent to your email!";
        } else {
            echo "Failed to send OTP. Mailer Error: " . $mail->ErrorInfo;
        }
        exit();
    }

    // Verifying OTP 
    if (!empty($otp)) {
        if (!isset($_SESSION['otp']) || !isset($_SESSION['email'])) {
            echo "Session expired. Please request a new OTP.";
            exit();
        }

        if ($otp == $_SESSION['otp']) {
            $email = $_SESSION['email'];
            $update = $conn->prepare("UPDATE users SET otp_verified = 1 WHERE email = ?");
            $update->bind_param("s", $email);
            $update->execute();

            echo "OTP Verified";
        } else {
            echo "Invalid OTP";
        }
        exit();
    }
}
?>
