<?php
include("config.php"); 
session_start();

$userID = $_SESSION['UserID'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $bookingID = intval($_GET['id']); 

    // Check if the booking exists and belongs to the user
    $query = "SELECT b.BookingID, b.PropertyID, p.UserID AS OwnerID, p.PropertyType, p.PropertyAddress 
              FROM booking b 
              INNER JOIN property p ON b.PropertyID = p.PropertyID 
              WHERE b.BookingID = ? AND b.UserID = ? AND b.is_approved = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $bookingID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        $propertyID = $booking['PropertyID'];
        $ownerID = $booking['OwnerID'];
        $propertyType = $booking['PropertyType'];
        $propertyAddress = $booking['PropertyAddress'];

        // Delete the booking
        $deleteQuery = "DELETE FROM booking WHERE BookingID = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $bookingID);

        if ($deleteStmt->execute()) {
            // Notify the property owner
            $notificationQueryOwner = "INSERT INTO notification (UserID, SenderID, NotificationType, NotificationMessage, Date) VALUES (?, ?, ?, ?, ?)";
            $notificationStmtOwner = $conn->prepare($notificationQueryOwner);
            $notificationMessageOwner = "Your property at '$propertyAddress' that was awaiting approval has been canceled by the tenant.";
            $notificationTypeOwner = "Booking Canceled";
            $currentDateTime = date('Y-m-d H:i:s');
            $notificationStmtOwner->bind_param("iisss", $ownerID, $userID, $notificationTypeOwner, $notificationMessageOwner, $currentDateTime);
            $notificationStmtOwner->execute();
            $notificationStmtOwner->close();

            // Notify the tenant
            $notificationQueryTenant = "INSERT INTO notification (UserID, SenderID, NotificationType, NotificationMessage, Date) VALUES (?, ?, ?, ?, ?)";
            $notificationStmtTenant = $conn->prepare($notificationQueryTenant);
            $notificationMessageTenant = "Your booking for the property at '$propertyAddress' has been successfully canceled.";
            $notificationTypeTenant = "Booking Canceled";
            $notificationStmtTenant->bind_param("iisss", $userID, $ownerID, $notificationTypeTenant, $notificationMessageTenant, $currentDateTime);
            $notificationStmtTenant->execute();
            $notificationStmtTenant->close();

            // Redirect with success message
            header("Location: StudentProfile.php?message=Booking canceled successfully");
        } else {
            // Redirect with error message
            header("Location: StudentProfile.php?error=Failed to cancel booking");
        }

        $deleteStmt->close();
    } else {
        // No booking found or unauthorized
        header("Location: StudentProfile.php?error=Booking not found or unauthorized action");
    }

    $stmt->close();
} else {
    // Invalid request
    header("Location: StudentProfile.php?error=Invalid request");
}
exit;
?>
