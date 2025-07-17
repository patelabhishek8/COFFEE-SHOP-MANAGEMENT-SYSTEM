<?php
include '../PHP/db_connect.php';

// Validate booking_id
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    header('Location: ../HTML/home.php');
    exit();
}
$booking_id = $_GET['booking_id'];

// Fetch booking details
$sql_booking = "SELECT user_name, email, price FROM bookings WHERE id = ?";
$stmt_booking = $conn->prepare($sql_booking);
$stmt_booking->bind_param("i", $booking_id);
$stmt_booking->execute();
$booking_result = $stmt_booking->get_result();

if ($booking_result->num_rows == 0) {
    echo "Booking not found!";
    exit();
}
$booking = $booking_result->fetch_assoc();
$user_name = htmlspecialchars($booking['user_name']);
$email = htmlspecialchars($booking['email']);
$price = htmlspecialchars($booking['price']);

// Close statement
$stmt_booking->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Venue Booking</title>
    <link rel="stylesheet" href="../CSS/payment.css">
</head>
<body>
    <div class="container">
        <form id="payment-form" action="../PHP/process_payment.php" method="POST">
            <h2>Venue Booking - Payment</h2>
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $price; ?>">

            <!-- Step 1: User Details -->
            <div class="section">
                <h3>Your Details</h3>
                <div class="inputBox">
                    <label>Full Name</label>
                    <input type="text" name="fullname" value="<?php echo $user_name; ?>" readonly>
                </div>
                <div class="inputBox">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $email; ?>" readonly>
                </div>
            </div>

            <!-- Step 2: Payment Method -->
            <div class="section">
                <h3>Payment Method</h3>
                <select name="payment_method" id="payment-method" onchange="togglePaymentFields()" required>
                    <option value="">Select Payment Method</option>
                    <option value="DEBIT">Debit Card</option>
                    <option value="CREDIT">Credit Card</option>
                    <option value="UPI">UPI</option>
                </select>

                <!-- Card Payment Fields -->
                <div id="card-details" class="hidden">
                    <div class="inputBox">
                        <label>Name on Card</label>
                        <input type="text" name="card_holder_name" placeholder="Cardholder Name">
                    </div>
                    <div class="inputBox">
                        <label>Card Number</label>
                        <input type="text" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                    </div>
                    <div class="flex">
                        <div class="inputBox">
                            <label>Exp Month/Year</label>
                            <input type="text" name="expiry_date" placeholder="MM/YY">
                        </div>
                        <div class="inputBox">
                            <label>CVV</label>
                            <input type="text" name="cvv" placeholder="123">
                        </div>
                    </div>
                </div>

                <!-- UPI Payment Field -->
                <div id="upi-details" class="hidden">
                    <div class="inputBox">
                        <label>UPI ID</label>
                        <input type="text" name="upi_id" placeholder="Enter your UPI ID">
                    </div>
                </div>
            </div>

            <!-- Step 3: Confirm Payment -->
            <div class="section">
                <h3>Review & Confirm</h3>
                <p><strong>Amount:</strong> ₹<?php echo $price; ?></p>
                <button type="submit" name="proceed_to_pay" class="submit-btn">Proceed to Pay</button>
            </div>
        </form>
    </div>

    <script>
        function togglePaymentFields() {
            const method = document.getElementById("payment-method").value;
            document.getElementById("card-details").classList.add("hidden");
            document.getElementById("upi-details").classList.add("hidden");

            if (method === "DEBIT" || method === "CREDIT") {
                document.getElementById("card-details").classList.remove("hidden");
            } else if (method === "UPI") {
                document.getElementById("upi-details").classList.remove("hidden");
            }
        }
    </script>
</body>
</html>