<?php
session_start();
include("config.php");

$userType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;

if ($userType !== 'homeowner') {
    // Redirect if not a homeowner
    header("Location: Login.php");
    exit();
}

// Check if property ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $propertyID = $_GET['id'];

    // Prepare SQL statement to delete the property
    $sql = "DELETE FROM property WHERE PropertyID = ?";

    // Prepare and bind
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $propertyID); // "i" for integer
        
        // Execute the query
        if ($stmt->execute()) {
            // Redirect back to the property listing page after successful deletion
            header("Location: HomeownerProfile.php?message=Property+deleted+successfully");
            exit();
        } else {
            // Handle failure
            echo "Error deleting property: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
} else {
    // If no property ID is provided, redirect to the property listing page
    header("Location: HomeownerProfile.php?error=No+property+ID+provided");
    exit();
}
?>
