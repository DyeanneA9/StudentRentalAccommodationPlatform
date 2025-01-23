<?php
include("config.php");
include("Authenticate.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $waitingListID = isset($_POST['waitingListID']) ? intval($_POST['waitingListID']) : null;
    $userID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : null;
    $moveInDate = isset($_POST['moveInDate']) ? trim($_POST['moveInDate']) : null;
    $moveOutDate = isset($_POST['moveOutDate']) ? trim($_POST['moveOutDate']) : null;

    if (!$waitingListID || !$userID || !$moveInDate || !$moveOutDate) {
        die("Invalid request. Missing required data.");
    }

    // Fetch waiting list details
    $query = "SELECT wl.*, p.TotalTenants, p.PropertyAddress 
              FROM waiting_list wl 
              JOIN property p ON wl.PropertyID = p.PropertyID 
              WHERE wl.WaitingListID = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $waitingListID);
    $stmt->execute();
    $result = $stmt->get_result();
    $waitingList = $result->fetch_assoc();

    if (!$waitingList) {
        die("Waiting list not found.");
    }

    $requiredTenants = $waitingList['TotalTenants'];
    $propertyID = $waitingList['PropertyID'];
    $propertyAddress = htmlspecialchars($waitingList['PropertyAddress']);

    // Validate duplicate bookings
    $checkBookingQuery = "SELECT * FROM waiting_list WHERE UserID = ? AND PropertyID = ?";
    $checkBookingStmt = $conn->prepare($checkBookingQuery);
    $checkBookingStmt->bind_param("ii", $userID, $propertyID);
    $checkBookingStmt->execute();
    if ($checkBookingStmt->get_result()->num_rows > 0) {
        die("You have already booked for this property.");
    }

    // Calculate next SubmissionOrder
    $submissionOrderQuery = "SELECT IFNULL(MAX(SubmissionOrder), 0) + 1 AS NextSubmissionOrder 
                             FROM waiting_list 
                             WHERE PropertyID = ?";
    $submissionOrderStmt = $conn->prepare($submissionOrderQuery);
    $submissionOrderStmt->bind_param("i", $propertyID);
    $submissionOrderStmt->execute();
    $submissionOrderResult = $submissionOrderStmt->get_result();
    $nextSubmissionOrder = $submissionOrderResult->fetch_assoc()['NextSubmissionOrder'];

    // Add the booking to the waiting list
    $insertQuery = "INSERT INTO waiting_list (UserID, PropertyID, MoveInDate, MoveOutDate, SubmissionOrder)
                    VALUES (?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("iissi", $userID, $propertyID, $moveInDate, $moveOutDate, $nextSubmissionOrder);
    $insertStmt->execute();

    // Notify the user about joining the waiting list
    $notificationMessage = "You have successfully joined the waiting list for the property located at ". $propertyAddress . ".";
    $notificationType = "Join Waiting List";
    $notificationQuery = "INSERT INTO notification (UserID, SenderID, NotificationType, NotificationMessage, Date) 
                          VALUES (?, ?, ?, ?, CURDATE())";
    $notificationStmt = $conn->prepare($notificationQuery);
    $notificationStmt->bind_param("iiss", $userID, $userID, $notificationType, $notificationMessage);
    $notificationStmt->execute();

    // Notify the first user that someone joined the waiting list
    if ($nextSubmissionOrder === 0) { // Only notify the first user if the current user is not the first
        $firstUserQuery = "SELECT UserID 
                           FROM waiting_list 
                           WHERE PropertyID = ? 
                           ORDER BY SubmissionOrder ASC LIMIT 1";
        $firstUserStmt = $conn->prepare($firstUserQuery);
        $firstUserStmt->bind_param("i", $propertyID);
        $firstUserStmt->execute();
        $firstUserResult = $firstUserStmt->get_result();
        $firstUser = $firstUserResult->fetch_assoc();

        if ($firstUser) {
            $firstUserID = $firstUser['UserID'];
            $notificationMessage = "Someone has successfully joined the waiting list for the property located at " . $propertyAddress . ".";
            $notificationType = "Waiting List Update";
            $notificationStmt = $conn->prepare($notificationQuery);
            $notificationStmt->bind_param("iiss", $firstUserID, $userID, $notificationType, $notificationMessage);
            $notificationStmt->execute();
        }
    }

    // Update total number of people
    $updateNumOfPeopleQuery = "UPDATE waiting_list 
                               SET NumOfPeople = (SELECT COUNT(*) FROM waiting_list WHERE PropertyID = ?)
                               WHERE PropertyID = ?";
    $updateNumOfPeopleStmt = $conn->prepare($updateNumOfPeopleQuery);
    $updateNumOfPeopleStmt->bind_param("ii", $propertyID, $propertyID);
    $updateNumOfPeopleStmt->execute();

    // Redirect to success page
    header("Location: JoinWaitingList.php?id=" . $waitingListID . "&success=1");
    exit();
}
?>
