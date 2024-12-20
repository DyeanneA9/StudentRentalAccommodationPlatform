<?php
include("config.php"); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize the image path and property ID from the form submission
    $imagePath = filter_var($_POST['imagePath'], FILTER_SANITIZE_STRING);
    $propertyId = filter_var($_POST['propertyId'], FILTER_SANITIZE_NUMBER_INT);

    // Check if both parameters are valid
    if (!empty($imagePath) && !empty($propertyId)) {
        // Fetch the current images from the database
        $stmt = $pdo->prepare("SELECT Images FROM properties WHERE id = :propertyId");
        $stmt->execute([':propertyId' => $propertyId]);
        $property = $stmt->fetch();

        if ($property) {
            // Remove the image path from the Images field
            $images = explode(', ', $property['Images']);
            $updatedImages = array_filter($images, function($image) use ($imagePath) {
                return $image !== $imagePath;
            });
            $updatedImagesString = implode(', ', $updatedImages);

            // Update the database with the new Images field
            $updateStmt = $pdo->prepare("UPDATE properties SET Images = :updatedImages WHERE id = :propertyId");
            $updateStmt->execute([
                ':updatedImages' => $updatedImagesString,
                ':propertyId' => $propertyId
            ]);

            // Check if the update was successful
            if ($updateStmt->rowCount() > 0) {
                // Optionally, you can delete the physical image file from the server
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Deletes the file
                }
                echo "Success";
            } else {
                echo "Failed to update the database.";
            }
        } else {
            echo "Property not found.";
        }
    } else {
        echo "Invalid input.";
    }
}
?>
