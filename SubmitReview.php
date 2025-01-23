<?php
include("config.php");
include("Authenticate.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyID = intval($_POST['propertyID']);
    $userID = $_SESSION['UserID'] ?? null; 
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['feedback']);
    $date = date('Y-m-d'); 

    // Validate inputs
    if (empty($propertyID) || empty($userID) || $rating < 1 || $rating > 5 || empty($comment)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid input. Please provide all required fields.']);
        exit;
    }

    // Check if the user already submitted a review for this property
    $checkQuery = "SELECT ReviewID FROM review WHERE UserID = ? AND PropertyID = ?";
    $stmtCheck = $conn->prepare($checkQuery);
    if (!$stmtCheck) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmtCheck->bind_param("ii", $userID, $propertyID);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'You have already submitted a review for this property.']);
        exit;
    }
    $stmtCheck->close();

    // Insert the review into the database
    $insertQuery = "INSERT INTO review (PropertyID, UserID, Rating, Comment, Date) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($insertQuery);
    if (!$stmtInsert) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters and execute the query
    $stmtInsert->bind_param("iiiss", $propertyID, $userID, $rating, $comment, $date);
    if ($stmtInsert->execute()) {
        // Success response: Redirect to the property details page
        header('Location: PropertyDetails.php?id=' . $propertyID);
        exit;
    } else {
        // Error response
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit the review. Please try again.']);
    }
    $stmtInsert->close();
} else {
    // If the request method is not POST
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
