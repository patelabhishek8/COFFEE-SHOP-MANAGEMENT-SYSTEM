<?php
include '../PHP/db_connect.php';

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect listing data.
    //implode() takes an array like from multiple checkbox inputs and converts it into a single 
    // string, with values separated by a comma.
    $business_name = $_POST['business_name'];
    $pincode = $_POST['pincode'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $contact_person = $_POST['contact_person'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $price = $_POST['price'];
    $operating_days = implode(',', $_POST['days'] ?? []);
    $opening_time = $_POST['opening_time'];
    $closing_time = $_POST['closing_time'];
    $venue_types = implode(',', $_POST['venue_types'] ?? []);
    $facilities = implode(',', $_POST['facilities'] ?? []);
    $membership = $_POST['membership'];

    // Insert listing data
    $stmt = $conn->prepare("INSERT INTO listing (business_name, pincode, street, city, state, contact_person, mobile, email, price, operating_days, opening_time, closing_time, venue_types, facilities, membership) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssssss", $business_name, $pincode, $street, $city, $state, $contact_person, $mobile, $email, $price, $operating_days, $opening_time, $closing_time, $venue_types, $facilities, $membership);
    $stmt->execute();
    $listing_id = $stmt->insert_id;
    $stmt->close();

    // Upload images (limit 5)
    $image_paths = [];
    if (!empty($_FILES['venue_photos']['name'][0])) {
        $count = 0;
        foreach ($_FILES['venue_photos']['tmp_name'] as $index => $tmp_name) {
            if ($count >= 5) break;

            $original_name = $_FILES['venue_photos']['name'][$index];
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $filename = uniqid("venue_") . "." . $extension;
            $target_dir = "../uploads/";

            // Image type validation
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp', 'svg', 'avi', 'mp4', 'mov', 'wmv', 'avif'];
            if (!in_array(strtolower($extension), $allowed_types)) {
                echo "<script>alert('Invalid image type. Allowed types: JPG, JPEG, PNG, GIF, BMP, TIFF, WebP, SVG, AVI, MP4, MOV, WMV, AVIF'); window.location.href = '../HTML/listing.html';</script>";
                exit();
            }

            // Image size validation (limit to 20MB)
            if ($_FILES['venue_photos']['size'][$index] > 20 * 1024 * 1024) { // 20MB
                echo "<script>alert('Image size is too large. Maximum allowed size is 20MB.'); window.location.href = '../HTML/listing.html';</script>";
                exit();
            }

            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $image_paths[] = $target_file;

                $stmt = $conn->prepare("INSERT INTO listing_photos (listing_id, photo_path) VALUES (?, ?)");
                $stmt->bind_param("is", $listing_id, $target_file);
                $stmt->execute();
                $stmt->close();

                $count++;
            } else {
                echo "<script>alert('Failed to upload image.'); window.location.href = '../HTML/listing.html';</script>";
                exit();
            }
        }
    }

    // Send confirmation email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'spot2host@gmail.com';
        $mail->Password = 'uziqmufedzoxpvnk'; // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('spot2host@gmail.com', 'Spot2Host');
        $mail->addAddress($email, $contact_person);
        $mail->isHTML(true);
        $mail->Subject = 'Your Venue Listing Submission';

        // Embed uploaded images
        $image_html = '';
        foreach ($image_paths as $i => $img_path) {
            $cid = 'image' . $i;
            $mail->addEmbeddedImage($img_path, $cid);
            $image_html .= "<img src='cid:$cid' style='width:150px;height:auto;margin:5px;border:1px solid #ccc;' />";
        }

        $mail->Body = "
            <h3>Hi $contact_person,</h3>
            <p>Your venue has been successfully listed on <b>Spot2Host</b>. Below are your submitted details:</p>
            <ul>
                <li><b>Business:</b> $business_name</li>
                <li><b>Address:</b> $street, $city, $state - $pincode</li>
                <li><b>Mobile:</b> $mobile</li>
                <li><b>Email:</b> $email</li>
                <li><b>Price:</b> ₹$price</li>
                <li><b>Days:</b> $operating_days</li>
                <li><b>Time:</b> $opening_time to $closing_time</li>
                <li><b>Venue Types:</b> $venue_types</li>
                <li><b>Facilities:</b> $facilities</li>
                <li><b>Membership:</b> $membership</li>
            </ul>
            <h4>Uploaded Photos:</h4>
            <div>$image_html</div>
            <p>Thanks for listing with us!<br>– Team Spot2Host</p>
        ";

        $mail->send();
        echo "<script>alert('Listing submitted and email sent successfully!'); window.location.href = '../HTML/listing.html';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Listing saved, but email sending failed.'); window.location.href = '../HTML/listing.html';</script>";
    }
}
?>
