<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyID = intval($_POST['PropertyID']);
    $userID = $_SESSION['UserID'];

    if ($propertyID && $userID) {
        $sql = "SELECT name, role FROM users WHERE UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $adminName = $user['name'];
        $adminRole = $user['role'];
        $stmt->close();

        // Approve the property
        $sql = "UPDATE property SET is_approved = 1, rejection_reason = NULL WHERE PropertyID = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $propertyID);

            if ($stmt->execute()) {
                if ($adminRole === 'super_admin') {
                    $action = "You (Super Admin) approved property ID $propertyID";
                } else {
                    $action = "$adminName approved property ID $propertyID";
                }

                // Log the approval in the activities table
                $logSql = "INSERT INTO activities (UserID, action, created_at) VALUES (?, ?, NOW())";
                $logStmt = $conn->prepare($logSql);
                $logStmt->bind_param("is", $userID, $action);
                $logStmt->execute();
                $logStmt->close();

                // Redirect back to the dashboard with success message
                header("Location: PropertyApproval.php?status=success&message=Property%20approved%20successfully.");
                exit();
            } else {
                // Handle failure (SQL execution)
                header("Location: PropertyApproval.php?status=error&message=Failed%20to%20approve%20property.");
                exit();
            }
        } else {
            // SQL error handling
            header("Location: PropertyApproval.php?status=error&message=Error%20approving%20property.");
            exit();
        }
    } else {
        header("Location: PropertyApproval.php?status=error&message=Invalid%20property%20ID.");
        exit();
    }
}
?>
