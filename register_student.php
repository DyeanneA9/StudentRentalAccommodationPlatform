<?php
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['studentEmail']);
    $phone = trim($_POST['studentPhone']);
    $phoneWithPrefix = "+60" . $phone; 
    $password = trim($_POST['studentPassword']);
    $university = trim($_POST['universitySelect']);
    $gender = trim($_POST['studentGender']);
    $security_question = trim($_POST['security_question_1']);
    $security_answer = trim($_POST['security_answer_1']);
    $alt_security_question = trim($_POST['security_question_2']);
    $alt_security_answer = trim($_POST['security_answer_2']);
    $student_id_file = ($_FILES['studentIDUpload']) ? $_FILES['studentIDUpload'] : null;;

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
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (empty($university)) {
        $errors[] = "University is required.";
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

    // Validate file upload (Student ID)
    if (!empty($_FILES['studentIDUpload']) && $_FILES['studentIDUpload']['error'] === UPLOAD_ERR_OK) {
        $student_id_file = $_FILES['studentIDUpload'];
    
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($student_id_file['type'], $allowed_types)) {
            $errors[] = "Student ID must be an image (JPEG/PNG).";
        } else {
            // Save the file
            $student_id_path = 'uploads/' . basename($student_id_file['name']);
            if (move_uploaded_file($student_id_file['tmp_name'], $student_id_path)) {
                // File uploaded successfully
            } else {
                $errors[] = "Failed to upload the Student ID.";
            }
        }
    } elseif (empty($_FILES['studentIDUpload'])) {
        $student_id_path = null; // No file uploaded
    } else {
        $errors[] = "An error occurred during the file upload.";
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
        exit; // Stop execution if validation fails
    }

    // Hash the password and security answers
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $hashed_security_answer = password_hash($security_answer, PASSWORD_BCRYPT);
    $hashed_alt_security_answer = password_hash($alt_security_answer, PASSWORD_BCRYPT);

    // Prepare and execute MySQLi query
    $stmt = $conn->prepare("
        INSERT INTO users 
        (name, email, phone_number, password, role, user_type, university, gender, security_question, security_answer, alternative_question, alternative_answer, student_id, is_approved) 
        VALUES (?, ?, ?, ?, 'user', 'student', ?, ?, ?, ?, ?, ?, ?, 0)
    ");

    if ($stmt) {
        $stmt->bind_param(
            "sssssssssss",
            $name,
            $email,
            $phoneWithPrefix,
            $hashed_password,
            $university,
            $gender,
            $security_question,
            $hashed_security_answer,
            $alt_security_question,
            $hashed_alt_security_answer,
            $student_id_path
        );

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
