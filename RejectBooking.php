<?php
include("Auth.php");
include("config.php");

if (isset($_GET['id'])) {
    $bookingID = intval($_GET['id']);

    $sql = "UPDATE booking SET is_approved = -1 WHERE BookingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingID);

    if ($stmt->execute()) {
        header("Location: HomeownerProfile.php?tab=confirm&success=Booking rejected successfully");
    } else {
        header("Location: HomeownerProfile.php?tab=confirm&error=Failed to reject booking");
    }

    $stmt->close();
} else {
    header("Location: HomeownerProfile.php?tab=confirm&error=Invalid booking ID");
}
