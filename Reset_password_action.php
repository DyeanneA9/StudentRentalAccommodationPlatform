<?php
include("config.php");

// Ensure the token is provided in the POST request
if (empty($_POST["token"])) {
    die("Error: Token not provided.");
}

$token = $_POST["token"];
$password = $_POST["password"] ?? null;
$password_confirmation = $_POST["password_confirmation"] ?? null;

// Validate if passwords match
if ($password !== $password_confirmation) {
    die("Error: Passwords do not match.");
}

// Hash the token for secure lookup
$token_hash = hash("sha256", $token);

// Query to find the user by token hash
$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verify if the user exists
if ($user === null) {
    die("Error: Token not found.");
}

// Check if the token has expired
if (strtotime($user["reset_token_expired"]) <= time()) {
    die("Error: Token has expired.");
}

// Validate password requirements
if (strlen($password) < 8) {
    die("Error: Password must be at least 8 characters.");
}

if (!preg_match("/[a-z]/i", $password)) {
    die("Error: Password must contain at least one letter.");
}

if (!preg_match("/[0-9]/", $password)) {
    die("Error: Password must contain at least one number.");
}

// Hash the new password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Update the user's password and clear the reset token
$sql = "UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expired = NULL WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $password_hash, $user["UserID"]);
$stmt->execute();

// Confirm password update
if ($stmt->affected_rows > 0) {
    echo "Password updated successfully. You can now log in.";
} else {
    echo "Error updating password. Please try again.";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>