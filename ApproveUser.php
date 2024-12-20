<?php
session_start();
include("config.php");

// Ensure the user is logged in and is either a super admin or regular admin
if (!isset($_SESSION['UserID']) || !in_array($_SESSION['role'], ['super_admin', 'admin'])) {
    header("Location: Login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['UserID'])) {
    $userID = intval($_POST['UserID']);

    // Approve the user
    $sql = "UPDATE users SET is_approved = 1 WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userID);
        if ($stmt->execute()) {
            $message = "User approved successfully!";
            if ($_SESSION['role'] === 'super_admin') {
                header("Location: SuperAdminDashboard.php?message=" . urlencode($message));
            } elseif ($_SESSION['role'] === 'admin') {
                header("Location: AdminDashboard.php?message=" . urlencode($message));
            }
            exit();
        } else {
            die("Error: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
} else {
    header("Location: AdminDashboard.php?message=" . urlencode("Invalid request."));
    exit();
}
?>
