<?php
include("config.php");
include("Auth.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyID = intval($_POST['propertyID']);
    $userID = $_SESSION['UserID']; 
    $bookingType = $_POST['bookingType'];
    $numPeople = ($bookingType === "group") ? (isset($_POST['numPeople']) ? intval($_POST['numPeople']) : 0) : 1; // Default to 1 for "Myself"
    $moveInDate = $_POST['moveInDate'];
    $moveOutDate = $_POST['moveOutDate'];
    $monthlyRent = floatval($_POST['MonthlyRent']);
    $currentDateTime = date('Y-m-d H:i:s');

    // Fetch property details
    $query = "SELECT * FROM property WHERE PropertyID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();

    if (!$property) {
        echo "Error: Property not found.";
        exit;
    }

    $totalTenantsRequired = $property['TotalTenants'];
    $propertyAddress = htmlspecialchars($property['PropertyAddress']);
    $monthlyRent = floatval($property['PropertyPrice']); // Monthly rent from property table
    $ownerID = $property['UserID']; // Fetch the property owner's UserID

    // Calculate the security deposit (2 + 1 months rent)
    $securityDeposit = ($monthlyRent * 2) + $monthlyRent; 
    $totalRent = $monthlyRent + $securityDeposit; // Include security deposit into total rent

    // Check tenant fulfillment
    if ($numPeople < $totalTenantsRequired) {
        // Waiting list handling
        $checkWaitingListQuery = "SELECT * FROM waiting_list WHERE PropertyID = ?";
        $checkWaitingListStmt = $conn->prepare($checkWaitingListQuery);
        $checkWaitingListStmt->bind_param("i", $propertyID);
        $checkWaitingListStmt->execute();
        $waitingListResult = $checkWaitingListStmt->get_result();

        if ($waitingListResult->num_rows > 0) {
            $updateWaitingListQuery = "UPDATE waiting_list SET NumOfPeople = NumOfPeople + 1 WHERE PropertyID = ?";
            $updateWaitingListStmt = $conn->prepare($updateWaitingListQuery);
            $updateWaitingListStmt->bind_param("i", $propertyID);
            $updateWaitingListStmt->execute();
        } else {
            $waitingListQuery = "INSERT INTO waiting_list (PropertyID, UserID, NumOfPeople, MoveInDate, MoveOutDate, CreatedAt) 
                                VALUES (?, ?, 1, ?, ?, NOW())";
            $waitingStmt = $conn->prepare($waitingListQuery);
            $waitingStmt->bind_param("iiss", $propertyID, $userID, $moveInDate, $moveOutDate);
            $waitingStmt->execute();
        }

        // Send notification to user
        $notificationMessage = "You have successfully joined the waiting list for the property located at " . $propertyAddress . ".";
        $notificationType = "Join Waiting List";

        $notificationQuery = "INSERT INTO notification (UserID, SenderID, NotificationType, NotificationMessage, Date) 
                            VALUES (?, ?, ?, ?, ?)";
        $notificationStmt = $conn->prepare($notificationQuery);
        $notificationStmt->bind_param("iisss", $userID, $userID, $notificationType, $notificationMessage, $currentDateTime);
        $notificationStmt->execute();

        echo "The property has been added to the waiting list.";
        exit;

    } elseif ($numPeople == $totalTenantsRequired) {
        // Send notification to user
        $notificationMessage = "Your booking for the property located at " . $propertyAddress . " has been confirmed.";
        $notificationType = "Booking Agreement";

        $notificationQuery = "INSERT INTO notification (UserID, SenderID, NotificationType, NotificationMessage, Date) 
                            VALUES (?, ?, ?, ?, ?)";
        $notificationStmt = $conn->prepare($notificationQuery);
        $notificationStmt->bind_param("iisss", $userID, $userID, $notificationType, $notificationMessage, $currentDateTime);
        $notificationStmt->execute();

        // Insert booking details
        $bookingQuery = "INSERT INTO booking (PropertyID, UserID, BookingType, NumOfPeople, MoveInDate, MoveOutDate, MonthlyRent, TotalRent) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $bookingStmt = $conn->prepare($bookingQuery);
        $bookingStmt->bind_param(
            "iisiissi", 
            $propertyID, 
            $userID, 
            $bookingType, 
            $numPeople, 
            $moveInDate, 
            $moveOutDate, 
            $monthlyRent, 
            $totalRent
        );
        $bookingStmt->execute();

        // Send notification to the property owner
        $ownerNotificationMessage = "Your property located at " . $propertyAddress . " has been fully booked. Please confirm the booking";
        $ownerNotificationType = "Booking Confirmation";

        $ownerNotificationQuery = "INSERT INTO notification (UserID, SenderID, NotificationType, NotificationMessage, Date) 
                                   VALUES (?, ?, ?, ?, ?)";
        $ownerNotificationStmt = $conn->prepare($ownerNotificationQuery);
        $ownerNotificationStmt->bind_param("iisss", $ownerID, $userID, $ownerNotificationType, $ownerNotificationMessage, $currentDateTime);
        $ownerNotificationStmt->execute();

        echo "You have successfully filled in the booking form. Your booking is confirmed.";
        exit;
    }
}
?>