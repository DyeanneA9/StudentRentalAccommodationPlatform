<?php
include("config.php"); 

// Super admin credentials
$name = 'Super Admin';
$email = 'superadmin@gmail.com';
$password = password_hash('SRAPSuperadmin2024', PASSWORD_BCRYPT); // Hash the password
$role = 'super_admin';

// Insert super admin into the database
$stmt = $conn->prepare("
    INSERT INTO users (name, email, password, role) 
    VALUES (?, ?, ?, ?)
");

if ($stmt) {
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if ($stmt->execute()) {
        echo "Super admin inserted successfully!";
    } else {
        echo "Error executing statement: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>
