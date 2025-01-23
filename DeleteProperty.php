<?php
session_start();
include("config.php");

// Ensure the user is a homeowner
if ($_SESSION['user_type'] !== 'homeowner') {
    header("Location: Login.php");
    exit();
}

// Check if property ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $propertyID = $_GET['id'];

    // Delete the property
    $sql = "DELETE FROM property WHERE PropertyID = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $propertyID); 
        
        // Execute the deletion
        if ($stmt->execute()) {
            // Redirect to the profile page with success message
            header("Location: HomeownerProfile.php?message=Property+deleted+successfully");
            exit();
        } else {
            echo "Error deleting property: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
} else {
    // If no property ID is provided, redirect to the profile page with an error message
    header("Location: HomeownerProfile.php?error=No+property+ID+provided");
    exit();
}
?>
