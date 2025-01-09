<?php
session_start();
include("config.php");

if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw JSON input and decode it
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['UserID']) && is_numeric($input['UserID'])) {
        $userID = intval($input['UserID']); 

        $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");
        $stmt->bind_param("i", $userID); 

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found or already deleted.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close(); 
?>
