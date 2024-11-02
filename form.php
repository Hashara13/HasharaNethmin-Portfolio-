<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
    'gmail_username' => $_ENV['GMAIL_USERNAME'] ?? '',
    'gmail_password' => $_ENV['GMAIL_PASSWORD'] ?? '',
    'to_email' => $_ENV['TO_EMAIL'] ?? ''
];

$name = $email = $message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_input(INPUT_POST, 'Name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'Message', FILTER_SANITIZE_STRING);

    if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input. Please check your form and try again.']);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['gmail_username'];
        $mail->Password   = $config['gmail_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($config['gmail_username'], 'Contact Form');
        $mail->addAddress($config['to_email']);
        $mail->addReplyTo($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'New message from Contact Form';
        $mail->Body    = "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
        ";
        $mail->AltBody = "New Contact Form Submission\n\nName: $name\nEmail: $email\n\nMessage:\n$message";

        $mail->send();
        echo json_encode('Message has been sent successfully. Thank you for contacting !');
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
    }
} else {
    header("Location: index.html");
    exit();
}