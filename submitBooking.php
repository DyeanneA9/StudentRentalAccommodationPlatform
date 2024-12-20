<?php
session_start();
include("config.php");

// Retrieve form data
$userID = $_SESSION['UserID'];
$propertyID = $_POST['propertyID'];
$NumOfOccupants = $_POST['NumOfOccupants'];
$StartDate = $_POST['StartDate'];
$EndDate = $_POST['EndDate'];

// Prepare an SQL statement to insert booking data
$sql = "INSERT INTO bookings (UserID, PropertyID, NumOfOccupants, StartDate, EndDate) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Check if the statement was prepared successfully
if (!$stmt) {
    die("Error in SQL preparation: " . $conn->error);
}

$stmt->bind_param("iiiss", $userID, $propertyID, $NumOfOccupants, $StartDate, $EndDate);

// Execute the statement and check if successful
if ($stmt->execute()) {
    echo "Booking has been successfully submitted!";

    // Proceed to create and send the notification
    sendNotification($userID, $propertyID, $conn);
} else {
    echo "Error: Could not save booking. Please try again " . $stmt->error;
}



// Close the statement and connection
$stmt->close();
$conn->close();

// Function to get homeowner ID and send notification
function sendNotification($senderID, $propertyID, $conn) {
    // Get the homeowner's user ID (Adjust this based on your database structure)
    $homeownerID = getHomeownerID($propertyID, $conn);

    // Prepare the notification message
    $message = "You have a new booking request for Property ID: " . $propertyID;

    // Insert the notification for the homeowner
    $notifSQL = "INSERT INTO notifications (UserID, SenderID, NotificationMessage, Date) VALUES (?, ?, ?, CURDATE())";
    $notifStmt = $conn->prepare($notifSQL);

    if (!$notifStmt) {
        die("Error in SQL preparation: " . $conn->error);
    }

    $notifStmt->bind_param("iis", $homeownerID, $senderID, $message);
    $notifStmt->execute();
    $notifStmt->close();
}


// Function to get homeowner ID based on the property
function getHomeownerID($propertyID, $conn) {
    $query = "SELECT UserID FROM properties WHERE propertyID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $propertyID);
    $stmt->execute();
    $stmt->bind_result($homeownerID);
    $stmt->fetch();
    $stmt->close();
    return $homeownerID;
}
?>
