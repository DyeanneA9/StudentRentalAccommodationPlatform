<?php
include("config.php"); // Include MySQLi connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Verify the token and check if it has expired
    $stmt = $conn->prepare('SELECT Email FROM password_reset WHERE Token = ? AND ExpiresAt > NOW()');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $email = $result->fetch_assoc()['Email'];

    if ($email) {
        // Update the user's password
        $stmt = $conn->prepare('UPDATE User SET Password = ? WHERE Email = ?');
        $stmt->bind_param('ss', $newPassword, $email);
        $stmt->execute();

        // Delete the used token
        $stmt = $conn->prepare('DELETE FROM password_reset WHERE Token = ?');
        $stmt->bind_param('s', $token);
        $stmt->execute();

        echo 'Your password has been successfully reset.';
    } else {
        echo 'Invalid or expired token.';
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
