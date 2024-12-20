<?php
include("config.php"); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['adminName']);
    $email = trim($_POST['adminEmail']);
    $phone = trim($_POST['adminPhone']);
    $password = trim($_POST['adminPassword']);

    // Validate inputs
    $errors = [];
    if (empty($name)) {
        $errors[] = "Full name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    // Check for validation errors
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
        exit; // Stop execution if validation fails
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert admin into the database
    $sql = "INSERT INTO users (name, email, phone_number, password, role, is_approved) VALUES (?, ?, ?, ?, 'admin', 1)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Admin added successfully!</p>";
            header("Location: ManageUsers.php"); 
            exit();
        } else {
            echo "<p style='color: red;'>Error adding admin: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>Error preparing statement: " . $conn->error . "</p>";
    }

    $conn->close(); 
}
?>
