<?php
session_start();
include("config.php"); 

// Check if property ID and photo are provided
if (isset($_GET['id']) && isset($_GET['photo'])) {
    $propertyID = $_GET['id'];
    $photo = urldecode($_GET['photo']); // Decode the photo path

    // Construct the correct path to the photo file
    $photoPath = (file_exists($photo)) ? $photo : "uploads/" . $photo;

    // Check if the photo exists on the server
    if (file_exists($photoPath)) {
        // Attempt to delete the photo from the server
        if (unlink($photoPath)) {
            // Fetch current photos for the property
            $sql = "SELECT Photo FROM property WHERE PropertyID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $propertyID);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $propertyData = $result->fetch_assoc();
                    $photos = $propertyData['Photo'];

                    // Convert the photo string into an array and remove the deleted photo
                    $photoArray = json_decode($photos, true);
                    $photoArray = array_values(array_filter($photoArray, fn($image) => $image !== $photo));

                    // Re-encode the photo array back to a string
                    $updatedPhotos = json_encode($photoArray);

                    // Update the property record with the new list of photos
                    $updateSql = "UPDATE property SET Photo = ? WHERE PropertyID = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("si", $updatedPhotos, $propertyID);

                    if ($updateStmt->execute()) {
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

// Redirect back to the property edit page
header("Location: EditProperty.php?id=" . $propertyID);
exit();
?>
