<?php

require_once __DIR__ . '/php_mailer/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/php_mailer/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/php_mailer/vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


$mail = new PHPMailer(true);

// dyeanneangel02@

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'dyeanneangel02@gmail.com';
$mail->Password = 'bjbj ihtk aczq scyq'; // Use App Password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->isHtml(true);

return $mail;
