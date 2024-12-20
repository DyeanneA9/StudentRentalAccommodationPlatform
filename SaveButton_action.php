<?php
session_start();
include("config.php");

// Check if user is logged in and user type is 'student'
if (isset($_SESSION['UserID']) && $_SESSION['user_type'] == 'student') {
    $userID = $_SESSION['UserID'];
    $propertyID = isset($_GET['id']) ? $_GET['id'] : null;

    if ($propertyID) {
        // Check if the property is already saved by this student
        $sql = "SELECT * FROM saved_property WHERE UserID = ? AND PropertyID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userID, $propertyID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Property is already saved, inform the user
            $_SESSION['message'] = "Property already saved!";
        } else {
            // Save the property for the student
            $sql = "INSERT INTO saved_property (UserID, PropertyID) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $userID, $propertyID);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Property saved successfully!";
            } else {
                $_SESSION['message'] = "Error saving property. Please try again.";
            }
        }

        $stmt->close();
    } else {
        $_SESSION['message'] = "No property ID provided.";
    }
} else {
    $_SESSION['message'] = "You must be logged in as a student to save properties.";
}

header("Location: Dashboard.php"); // Redirect back to the Dashboard
exit();
?>
