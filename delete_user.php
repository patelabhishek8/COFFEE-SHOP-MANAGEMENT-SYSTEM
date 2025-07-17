<?php
include('../PHP/db_connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}

$id = (int)$_GET['id'];

// we can delete users from admin panel
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../HTML/admin_panel.php?section=users&success=User deleted successfully");
} else {
    echo "Error deleting user: " . $stmt->error;
}

$stmt->close();
$conn->close();
exit();
?>