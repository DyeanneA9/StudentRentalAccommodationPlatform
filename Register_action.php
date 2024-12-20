<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $contactNo = trim($_POST['contactNo']);
    $role = trim($_POST['UserRole']);

    // Check if the email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: Login.php?error=email_exists");
        exit();
    }

    // Validate the uploaded file
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $fileInfo = pathinfo($_FILES['profile_image']['name']);
        $fileExt = strtolower($fileInfo['extension']);

        if (!in_array($fileExt, $allowedExtensions) || $_FILES['profile_image']['size'] > $maxSize) {
            header("Location: Register.php?error=invalid_file");
            exit();
        }

        $newFileName = uniqid('profile_', true) . '.' . $fileExt;
        move_uploaded_file($_FILES['profile_image']['tmp_name'], "uploads/$newFileName");

        // Hash the password and insert user data
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, photo, contactNo, UserRole) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $newFileName, $contactNo, $role);

        if ($stmt->execute()) {
            header("Location: Register.php?success=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        header("Location: Register.php?error=upload_error");
        exit();
    }
}
