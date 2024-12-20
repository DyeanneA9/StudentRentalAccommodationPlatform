<?php
include("Auth.php");
include("config.php");

// Ensure user is logged in
$userID = $_SESSION['UserID'];

// Check if the PropertyID is set in the query string
if (isset($_GET['id'])) {
    $propertyID = $_GET['id'];

    // Prepare the query to delete the saved property from the saved_properties table
    $sql = "DELETE FROM saved_property WHERE UserID = ? AND PropertyID = ?";

    // Prepare and execute the query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $userID, $propertyID);
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['message'] = "Property has been unsaved successfully!";
        } else {
            // Set error message
            $_SESSION['message'] = "Error: Could not unsave the property.";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Error: Could not prepare the query.";
    }
} else {
    $_SESSION['message'] = "Error: No property selected.";
}

// Redirect back to the saved properties page
header("Location: SaveProperty.php");
exit();
?>
