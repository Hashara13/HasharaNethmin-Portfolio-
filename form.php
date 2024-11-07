<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use SendGrid\Mail\Mail;

error_reporting(E_ALL);
ini_set('display_errors', 1);

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'error.log');
}

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    logError('Error loading .env file: ' . $e->getMessage());
    echo 'An error occurred while loading configuration.';
    exit;
}

$config = [
    'sendgrid_api_key' => $_ENV['SENDGRID_API_KEY'] ?? '',
    'from_email' => $_ENV['FROM_EMAIL'] ?? '',
    'to_email' => $_ENV['TO_EMAIL'] ?? ''
];

if (empty($config['sendgrid_api_key']) || empty($config['from_email']) || empty($config['to_email'])) {
    logError('Missing required configuration in .env file');
    echo 'Server configuration error. Please contact the administrator.';
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['Name'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['Email'] ?? '', FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['Message'] ?? '', ENT_QUOTES, 'UTF-8');

    if (!$name || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo 'Invalid input. Please check your form and try again.';
        exit;
    }

    $email_body = "
    <h2>New Contact Form Submission</h2>
    <p><strong>Name:</strong> {$name}</p>
    <p><strong>Email:</strong> {$email}</p>
    <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
    <br>
    <p>--</p>
    <p>This email was sent from our contact form.</p>
    <p>Company Address: 123 Example Street, Colombo, Sri Lanka</p>
";


    $email = new Mail();
    $email->setFrom($config['from_email'], "Contact Form");
    $email->setSubject("New message from Contact Form");
    $email->addTo($config['to_email']);
    $email->addContent("text/plain", strip_tags($email_body));
    $email->addContent("text/html", $email_body);

    $sendgrid = new \SendGrid($config['sendgrid_api_key']);

    try {
        $response = $sendgrid->send($email);
        logError('SendGrid API Response: ' . $response->statusCode() . ' - ' . $response->body());

        if ($response->statusCode() == 202) {
            echo "Message has been sent successfully via SendGrid. Thank you for contacting!";
        } else {
            echo 'Message could not be sent. Please try again later.';
        }
    } catch (Exception $e) {
        logError('SendGrid Error: ' . $e->getMessage());
        echo 'Message could not be sent. Please try again later.';
    }
} else {
    header("Location: index.html");
    exit();
}
