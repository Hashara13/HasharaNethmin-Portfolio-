<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

$config = include('config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Message = $_POST['Message'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['gmail_username'];
        $mail->Password   = $config['gmail_password']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($Email, $Name);
        $mail->addAddress('nethhashara@gmail.com');

        $mail->isHTML(true); 
        $mail->Subject = 'New message from Contact Form';
        $mail->Body    = "<strong>Name:</strong> $Name<br><strong>Email:</strong> $Email<br><strong>Message:</strong><br>$Message";
        $mail->AltBody = "Name: $Name\nEmail: $Email\nMessage: $Message";

        $mail->send();
        echo "Message has been sent";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
