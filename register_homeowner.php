<?php
include("config.php"); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['homeownerName']);
    $email = trim($_POST['homeownerEmail']);
    $phone = trim($_POST['homeownerPhone']);
    $phoneWithPrefix = "+60" . $phone; 
    $password = trim($_POST['homeownerPassword']);
    $gender = trim($_POST['gender']);
    $security_question = isset($_POST['security_question_1']) ? trim($_POST['security_question_1']) : null;
    $security_answer = isset($_POST['security_answer_1']) ? trim($_POST['security_answer_1']) : null;
    $alt_security_question = isset($_POST['security_question_2']) ? trim($_POST['security_question_2']) : null;
    $alt_security_answer = isset($_POST['security_answer_2']) ? trim($_POST['security_answer_2']) : null;

    // Validate mandatory fields
    $errors = [];
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($phone)) {
        $errors[] = "Phone Number is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if (empty($gender)) {
        $errors[] = "Gender is required."; 
    }
    if (empty($security_question)) {
        $errors[] = "Security Question is required.";
    }
    if (empty($security_answer)) {
        $errors[] = "Answer to Security Question is required.";
    }
    if (empty($alt_security_question)) {
        $errors[] = "Alternative Security Question is required.";
    }
    if (empty($alt_security_answer)) {
        $errors[] = "Answer to Alternative Security Question is required.";
    }

    // Check for existing email in the database
    $checkEmailSql = "SELECT email FROM users WHERE email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        $errors[] = "Email is already registered.";
    }
    $checkEmailStmt->close();

    // If validation fails, show errors
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
        exit;
    }

    // Hash the password and security answers
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $hashed_security_answer = password_hash($security_answer, PASSWORD_BCRYPT);
    $hashed_alt_security_answer = password_hash($alt_security_answer, PASSWORD_BCRYPT);

    // Insert homeowner data into the database
    $sql = "INSERT INTO users (name, email, phone_number, password, role, user_type, gender, security_question, security_answer, alternative_question, alternative_answer, is_approved) VALUES (?, ?, ?, ?, 'user', 'homeowner', ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "sssssssss", 
            $name, 
            $email, 
            $phoneWithPrefix,
            $hashedPassword,
            $gender,
            $security_question, 
            $hashed_security_answer, 
            $alt_security_question, 
            $hashed_alt_security_answer);

            if ($stmt->execute()) {
                header("Location: register.php?success=1");
                exit();
            } else {
                header("Location: register.php?error=1");
                exit();
            }

        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }

    $conn->close();
}
?>
