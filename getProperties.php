<?php
include("config.php");

function getPropertiesByOwner($userID) {
    global $conn;

    // SQL query to retrieve properties that belong to the given owner (userID)
    $sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, Images, Occupants, UserID 
            FROM properties 
            WHERE UserID = ?";  // Ensure properties are filtered by UserID

    $stmt = $conn->prepare($sql);  // Use prepared statements to avoid SQL injection
    $stmt->bind_param("i", $userID);  // Bind the UserID as an integer
    $stmt->execute();
    $result = $stmt->get_result();

    $properties = [];

    // Store the fetched properties in an array
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Adjust the image paths
            $images = explode(',', $row['Images']);
    

            // Prefix each image with the user folder (but only if not already prefixed)
            foreach ($images as &$image) {
                $image = trim($image);
            }

            $row['Images'] = implode(', ', $images);  // Rejoin the images as a comma-separated string
            $properties[] = $row;
        }
    }

    $stmt->close();  // Close the prepared statement

    return $properties;  // Return the properties array
}


function getAllAvailableProperties() {
    global $conn;  // Global database connection

    // SQL query to fetch all properties where the status is 'available'
    $sql = "SELECT PropertyID, PropertyType, PropertyAddress, PropertyPrice, Images, Occupants, UserID 
            FROM properties 
            WHERE PropertyStatus = 'available'";  // Only fetch available properties

    $stmt = $conn->prepare($sql);  // Use prepared statements for security
    $stmt->execute();
    $result = $stmt->get_result();

    $properties = [];

    // Store the fetched properties in an array
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Adjust the image paths
            $images = explode(',', $row['Images']);
        
            // Prefix each image with the user folder
            foreach ($images as &$image) {
                $image = trim($image);
            }

            $row['Images'] = implode(', ', $images);  // Rejoin the images as a comma-separated string
            $properties[] = $row;
        }
    }

    $stmt->close();  // Close the prepared statement

    return $properties;  // Return the properties array
}


function getAllProperties() {
    global $conn;
    $query = "SELECT * FROM properties";
    $result = $conn->query($query);
    $properties = [];

    if ($result && $result->num_rows > 0) {
        while ($property = $result->fetch_assoc()) {
            // Adjust the image paths
            $images = explode(',', $property['Images']);

            // Prefix each image with the user folder
            foreach ($images as &$image) {
                $image = trim($image);
            }

            $property['Images'] = implode(', ', $images);  // Rejoin the images as a comma-separated string
            $properties[] = $property;
        }
    }

    return $properties;
}
?>

