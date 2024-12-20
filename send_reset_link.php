<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Autoload PHPMailer using Composer

$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to use
    $mail->SMTPAuth = true;
    $mail->Username = 'your_email@gmail.com'; // Your Gmail address
    $mail->Password = 'your_email_password'; // Your Gmail password or App Password
    $mail->SMTPSecure = 'tls'; // Enable TLS encryption
    $mail->Port = 587; // TCP port for TLS

    // Sender and recipient
    $mail->setFrom('your_email@gmail.com', 'Your Name');
    $mail->addAddress($email); // Recipient email address

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = 'Click the following link to reset your password: <a href="' . $resetLink . '">' . $resetLink . '</a>';

    // Send the email
    $mail->send();
    echo 'Password reset link has been sent to your email.';
} catch (Exception $e) {
    echo 'Failed to send the email. Mailer Error: ' . $mail->ErrorInfo;
}
