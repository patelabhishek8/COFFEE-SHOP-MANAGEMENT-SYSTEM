<?php
session_start();
include '../PHP/db_connect.php';

$show_password_fields = false;
$email_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_email'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $email_error = 'Invalid email.';
        } else {
            // Check if email exists in listing
            $sql = "SELECT id FROM listing WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $email_error = 'Email not found.';
            } else {
                $listing = $result->fetch_assoc();
                // Check if VP has a password
                $sql_vp = "SELECT id FROM venue_partners WHERE email = ?";
                $stmt_vp = $conn->prepare($sql_vp);
                $stmt_vp->bind_param("s", $email);
                $stmt_vp->execute();
                $vp_result = $stmt_vp->get_result();

                if ($vp_result->num_rows === 0) {
                    // No password, show password fields
                    $show_password_fields = true;
                    $_SESSION['vp_email'] = $email;
                    $_SESSION['listing_id'] = $listing['id'];
                } else {
                    // Password exists, show login
                    $show_password_fields = false;
                }
                $stmt_vp->close();
            }
            $stmt->close();
        }
    } elseif (isset($_POST['set_password'])) {
        $email = $_SESSION['vp_email'];
        $listing_id = $_SESSION['listing_id'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $email_error = 'Passwords do not match.';
            $show_password_fields = true;
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Insert into venue_partners
            $sql = "INSERT INTO venue_partners (email, password, listing_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $email, $hashed_password, $listing_id);

            if ($stmt->execute()) {
                unset($_SESSION['vp_email']);
                unset($_SESSION['listing_id']);
                echo "<script>alert('Password set successfully. Please log in.'); window.location.href = '../HTML/vp_login.php';</script>";
                exit();
            } else {
                $email_error = 'Failed to set password.';
                $show_password_fields = true;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['login'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$email) {
            $email_error = 'Invalid email.';
        } else {
            // Check password
            $sql = "SELECT password, listing_id FROM venue_partners WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $email_error = 'No account found. Please set a password.';
            } else {
                $vp = $result->fetch_assoc();
                if (password_verify($password, $vp['password'])) {
                    $_SESSION['vp_email'] = $email;
                    $_SESSION['listing_id'] = $vp['listing_id'];
                    header('Location: ../HTML/vp_panel.php');
                    exit();
                } else {
                    $email_error = 'Incorrect password.';
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VP Login</title>
    <link href="../CSS/vp_login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h2>Venue Partner Login</h2>
        <form action="../HTML/vp_login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <?php if ($email_error): ?>
                    <div class="error"><?php echo $email_error; ?></div>
                <?php endif; ?>
            </div>
            <?php if ($show_password_fields): ?>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" name="set_password">Set Password</button>
            <?php else: ?>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password">
                </div>
                <button type="submit" name="check_email">Check Email</button>
                <button type="submit" name="login">Login</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>