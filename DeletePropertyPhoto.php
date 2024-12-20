<?php
session_start();
include("config.php"); 

if (isset($_GET['id']) && isset($_GET['photo'])) {
    $propertyID = $_GET['id'];
    $photo = urldecode($_GET['photo']); 

    // Construct the correct path to the photo file
    $photoPath = $photo; // Adjust based on how your paths are stored
    if (!file_exists($photoPath)) {
        $photoPath = "uploads/" . $photo; // Add uploads directory if necessary
    }

    // Check if the photo exists on the server
    if (file_exists($photoPath)) {
        // Delete the photo file from the server
        if (unlink($photoPath)) {
            // If the file is deleted, proceed to update the database

            // Get the current photos for the property
            $sql = "SELECT Photo FROM property WHERE PropertyID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $propertyID);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $propertyData = $result->fetch_assoc();
                    $photos = $propertyData['Photo'];

                    // Convert the photo string into an array
                    $photoArray = json_decode($photos, true);

                    // Remove the deleted photo from the array
                    if (($key = array_search($photo, $photoArray)) !== false) {
                        unset($photoArray[$key]);
                    }

                    // Re-encode the photo array back to a string
                    $updatedPhotos = json_encode(array_values($photoArray));

                    // Update the property record with the new list of photos
                    $updateSql = "UPDATE property SET Photo = ? WHERE PropertyID = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("si", $updatedPhotos, $propertyID);

                    if ($updateStmt->execute()) {
                        // Set a session variable to show success message
                        $_SESSION['delete_success'] = "Photo deleted successfully!";
                    } else {
                        $_SESSION['delete_error'] = "Error updating the property photos in the database.";
                    }
                } else {
                    $_SESSION['delete_error'] = "Property not found.";
                }
            } else {
                $_SESSION['delete_error'] = "Error retrieving property details: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $_SESSION['delete_error'] = "Error deleting the photo from the server.";
        }
    } else {
        $_SESSION['delete_error'] = "Photo file does not exist.";
    }
} else {
    $_SESSION['delete_error'] = "Error: Property ID or Photo not provided.";
}

header("Location: EditProperty.php?id=" . $propertyID); // Redirect back to Edit Property page
exit();
?>
