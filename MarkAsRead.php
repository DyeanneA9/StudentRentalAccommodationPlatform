<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $NotificationID = isset($_POST['NotificationID']) ? intval($_POST['NotificationID']) : null;

    if ($NotificationID) {
        // Check if notification exists and belongs to the logged-in user
        $checkQuery = "SELECT NotificationID FROM notification WHERE NotificationID = ? AND UserID = ?";
        $stmtCheck = $conn->prepare($checkQuery);
        if (!$stmtCheck) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare check query']);
            exit();
        }
        $stmtCheck->bind_param("ii", $NotificationID, $_SESSION['UserID']);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows === 0) {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'Notification not found']);
            $stmtCheck->close();
            exit();
        }
        $stmtCheck->close();

        // Mark the notification as read
        $query = "UPDATE notification SET IsRead = 1 WHERE NotificationID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to prepare the statement']);
            exit();
        }
        $stmt->bind_param("i", $NotificationID);
        $stmt->execute();
        $stmt->close();

        // Fetch the updated unread count
        $unreadCount = 0;
        $countQuery = "SELECT COUNT(*) AS unread_count FROM notification WHERE UserID = ? AND IsRead = 0";
        $stmtUnread = $conn->prepare($countQuery);
        if ($stmtUnread) {
            $stmtUnread->bind_param("i", $_SESSION['UserID']);
            $stmtUnread->execute();
            $stmtUnread->bind_result($unreadCount);
            $stmtUnread->fetch();
            $stmtUnread->close();
        } else {
            error_log("Failed to prepare unread count query: " . $conn->error);
        }

        // Return JSON for AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true, 'unreadCount' => $unreadCount]);
            exit();
        }

        // Redirect for non-AJAX requests
        header("Location: Notification.php");
        exit();
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'NotificationID is required']);
        exit();
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}
