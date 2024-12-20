<?php
include("Auth.php");
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $NotificationID = isset($_POST['NotificationID']) ? intval($_POST['NotificationID']) : null;

    if ($NotificationID) {
        $query = "DELETE FROM notification WHERE NotificationID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $NotificationID);
        $stmt->execute();
    }
    header("Location: Notification.php");
    exit();
}
?>
