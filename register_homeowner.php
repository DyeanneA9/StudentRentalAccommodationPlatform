<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['homeownerName']);
    $email = trim($_POST['homeownerEmail']);
    $phone = trim($_POST['homeownerPhone']);

    // Remove leading '0' from the phone number if present
    if (strpos($phone, '0') === 0) {
        $phone = substr($phone, 1);
    }
    $phoneWithPrefix = "+60" . $phone;

    $password = trim($_POST['homeownerPassword']);
    $gender = trim($_POST['gender']);
    $security_question = isset($_POST['security_question_1']) ? trim($_POST['security_question_1']) : null;
    $security_answer = isset($_POST['security_answer_1']) ? trim($_POST['security_answer_1']) : null;
    $alt_security_question = isset($_POST['security_question_2']) ? trim($_POST['security_question_2']) : null;
    $alt_security_answer = isset($_POST['security_answer_2']) ? trim($_POST['security_answer_2']) : null;

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
    } elseif (!preg_match('/^[0-9]{9,10}$/', $phone)) {
        $errors[] = "Invalid phone number format. Please enter 9 to 10 digits.";
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
    if (!$checkEmailStmt) {
        die("Error preparing email check statement: " . $conn->error);
    }
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        // If email already exists, add error message to the errors array
        $_SESSION['errorMessages'] = "Email is already registered.";  // Store error in session
    }
    $checkEmailStmt->close();

    // If validation fails, show errors using modal
    if (!empty($errors)) {
        $_SESSION['errorMessages'] = implode("\\n", $errors);  // Store all errors in session
        header("Location: Register.php?error=1"); // Redirect to register page
        exit;
    }

    // Hash the password and security answers
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $hashed_security_answer = password_hash($security_answer, PASSWORD_BCRYPT);
    $hashed_alt_security_answer = password_hash($alt_security_answer, PASSWORD_BCRYPT);

    // Insert homeowner data into the database
    $sql = "INSERT INTO users (name, email, phone_number, password, role, user_type, gender, security_question, security_answer, alternative_question, alternative_answer, is_approved) 
            VALUES (?, ?, ?, ?, 'user', 'homeowner', ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

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
        $hashed_alt_security_answer
    );

    if ($stmt->execute()) {
        // Success - Redirect to Register.php with success query parameter
        header("Location: Register.php?success=1");
        exit();
    } else {
        // Failure - Log error and redirect to Register.php with error query parameter
        error_log("Database error: " . $stmt->error);
        header("Location: Register.php?error=1");
        exit();
    }
    
    $stmt->close();
    $conn->close();
}
?>
