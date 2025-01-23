<?php
include("config.php");
session_start();

if (isset($_SESSION['UserID'])) {
    $UserID = $_SESSION['UserID'];
    $unreadCount = 0;

    $query = "SELECT COUNT(*) AS unread_count FROM notification WHERE UserID = ? AND IsRead = 0";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $UserID);
        $stmt->execute();
        $stmt->bind_result($unreadCount);
        $stmt->fetch();
        $stmt->close();
    }

    echo json_encode(['unreadCount' => $unreadCount]);
    exit();
} else {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}
?>