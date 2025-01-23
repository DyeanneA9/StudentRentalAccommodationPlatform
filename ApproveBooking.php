<?php
include("config.php");
include("Authenticate.php");

if (isset($_GET['id'])) {
    $bookingID = intval($_GET['id']);

    // Approve the booking
    $sql = "UPDATE booking SET is_approved = 1 WHERE BookingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingID);

    if ($stmt->execute()) {
        // Fetch booking details to notify the tenant
        $query = "SELECT b.UserID AS TenantID, p.PropertyAddress 
                  FROM booking b
                  INNER JOIN property p ON b.PropertyID = p.PropertyID
                  WHERE b.BookingID = ?";
        $fetchStmt = $conn->prepare($query);
        $fetchStmt->bind_param("i", $bookingID);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();

        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            $tenantID = $booking['TenantID'];
            $propertyAddress = $booking['PropertyAddress'];

            // Send notification to the tenant
            $notificationQuery = "INSERT INTO notification (UserID, SenderID, NotificationType, NotificationMessage, Date) VALUES (?, ?, ?, ?, ?)";
            $notificationStmt = $conn->prepare($notificationQuery);
            $notificationMessage = "The property owner has approved your booking at $propertyAddress.";
            $notificationType = "Booking Approved";
            $currentDateTime = date('Y-m-d H:i:s');
            $senderID = $_SESSION['UserID'];

            $notificationStmt->bind_param("iisss", $tenantID, $senderID, $notificationType, $notificationMessage, $currentDateTime);
            $notificationStmt->execute();
            $notificationStmt->close();
        }

        $fetchStmt->close();

        // Redirect with success message
        header("Location: HomeownerProfile.php?tab=confirm&success=Booking approved successfully");
    } else {
        // Redirect with error message
        header("Location: HomeownerProfile.php?tab=confirm&error=Failed to approve booking");
    }

    $stmt->close();
} else {
    // Invalid booking ID
    header("Location: HomeownerProfile.php?tab=confirm&error=Invalid booking ID");
}

?>
