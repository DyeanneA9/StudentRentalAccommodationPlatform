<?php
session_start();
include("config.php");

if (!isset($_SESSION['UserID']) || $_SESSION['role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate the input
    if (isset($input['UserID']) && is_numeric($input['UserID'])) {
        $userID = intval($input['UserID']); 

        // Prevent super_admin from deleting themselves
        if ($userID == $_SESSION['UserID']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
            exit();
        }

        // Step 1: Check if the user exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE UserID = ?");
        $checkStmt->bind_param("i", $userID);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        // If user doesn't exist
        if ($count === 0) {
            echo json_encode(['success' => false, 'message' => 'User not found or already deleted.']);
            exit();
        }

        // Delete the user from the database
        $stmtDeleteUser = $conn->prepare("DELETE FROM users WHERE UserID = ?");
        $stmtDeleteUser->bind_param("i", $userID);

        if ($stmtDeleteUser->execute()) {
            if ($stmtDeleteUser->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user due to a server error.']);
        }

        $stmtDeleteUser->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
