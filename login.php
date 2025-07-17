<?php
session_start();
include("../PHP/db_connect.php");

// Load PHPMailer manually
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Send welcome email to new users
function sendWelcomeEmail($email, $full_name) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'spot2host@gmail.com';
        $mail->Password   = 'uziqmufedzoxpvnk'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('spot2host@gmail.com', 'Spot2Host');
        $mail->addAddress($email, $full_name);

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Spot2Host!';
        $mail->Body    = "
            <h3>Hi $full_name,</h3>
            <p>Welcome to <strong>Spot2Host</strong>! You have successfully registered.</p>
            <p><strong>Your Email:</strong> $email</p>
            <p>We’re excited to have you onboard! Explore and enjoy the platform.</p>
            <br>
            <p>Regards,<br>Spot2Host Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        
    }
}

//                              SIGNUP 
//  This code saved web page data in databse.Ilke full anem ,email ,password
if (isset($_POST['signup'])) {
    $full_name = htmlspecialchars(trim($_POST['full_name']));//htmlspecalchar prevent attack from hacker
    $phone = htmlspecialchars(trim($_POST['phone'])); 
    $email = trim($_POST['email']); //Trim is used to remove spaces 
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        header("Location: ../HTML/login.html?error=PasswordMismatch");
        exit();
    }
    // Checks if email aleready exist in databse or not.
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        header("Location: ../HTML/login.html?error=EmailExists");
        exit();
    }
    //Hashed pass encrypts the passowrd for saftey before saving.
    //bind and prepare are to prevent sql injection
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $phone, $email, $hashed_password);
    
    //This send email if saved succesfully
    if ($stmt->execute()) {
        sendWelcomeEmail($email, $full_name);
        header("Location: ../HTML/login.html?signup=success");
    } else {
        header("Location: ../HTML/login.html?error=SignupFailed");
    }
    exit();
}

//                              LOGIN 
//This code check isuser exist in db to login.if yesh then redirected to home page
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
            header("Location: ../HTML/home.php");
        } else {
            header("Location: ../HTML/login.html?error=InvalidPassword");
        }
    } else {
        header("Location: ../HTML/login.html?error=UserNotFound");
    }
    exit();
}

//                      RESET PASSWORD 
//Checks if email exist in db for reset.
if (isset($_POST['reset_password'])) {
    if (!isset($_SESSION['email'])) {
        header("Location: ../HTML/login.html?error=SessionExpired");
        exit();
    }
//Enters new pass and confrim pass to reset
    $email = $_SESSION['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        header("Location: ../HTML/login.html?error=PasswordMismatch");
        exit();
    }
// Saves new pass with encryption
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        unset($_SESSION['otp']);
        unset($_SESSION['email']);
        header("Location: ../HTML/login.html?reset=success");
    } else {
        header("Location: ../HTML/login.html?reset=failed");
    }
    exit();
}
?>
