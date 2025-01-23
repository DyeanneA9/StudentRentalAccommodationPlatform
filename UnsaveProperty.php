<?php
include("config.php");
include("Authenticate.php");

$userID = $_SESSION['UserID'];

if (isset($_GET['id'])) {
    $propertyID = $_GET['id'];

    //delete the saved property from the saved_properties table
    $sql = "DELETE FROM saved_property WHERE UserID = ? AND PropertyID = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $userID, $propertyID);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Property has been unsaved successfully!";
        } else {
            $_SESSION['message'] = "Error: Could not unsave the property.";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Error: Could not prepare the query.";
    }
} else {
    $_SESSION['message'] = "Error: No property selected.";
}

header("Location: SaveProperty.php");
exit();
?>
