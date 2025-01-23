<?php
include("config.php");
include("Navigation.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    // Check if the email format is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    // Check if the email exists in the database
    $sql_check = "SELECT UserID FROM users WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        $_SESSION["modal_message"] = "This email is not registered in the system.";
        $_SESSION["modal_type"] = "error";
        $stmt_check->close();
        $conn->close();
        header("Location: Forgot_password.php");
        exit;
    }

    $stmt_check->close();

    // Generate reset token and hash
    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 3600); // Token expires in 1 hour

    // Update database with token and expiry
    $sql_update = "UPDATE users SET reset_token_hash = ?, reset_token_expired = ? WHERE email = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sss", $token_hash, $expiry, $email);
    $stmt_update->execute();

    if ($conn->affected_rows > 0) {
        // Send the email
        $mail = require __DIR__ . "/mailer.php";
        $mail->setFrom("dyeanneangel02@gmail.com");
        $mail->addAddress($email);
        $mail->Subject = "Password Reset";
        $mail->Body = <<<END
        Click <a href="http://localhost/StudentRentalAccommodationPlatform/reset_password.php?token=$token">here</a> 
        to reset your password.
        END;

        try {
            $mail->send();
            $_SESSION["modal_message"] = "Message sent, please check your inbox.";
            $_SESSION["modal_type"] = "success";
        } catch (Exception $e) {
            $_SESSION["modal_message"] = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
            $_SESSION["modal_type"] = "error";
        }
    } else {
        $_SESSION["modal_message"] = "Error updating reset token. Please try again.";
        $_SESSION["modal_type"] = "error";
    }

     $stmt_update->close();
    $conn->close();
    header("Location: Forgot_password.php");
    exit;
} else {
    $_SESSION["modal_message"] = "Invalid request method.";
    $_SESSION["modal_type"] = "error";
    header("Location: Forgot_password.php");
    exit;
}
