<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'only.legendcode@gmail.com';
    $mail->Password = 'hccb xnxd svvj qzen'; // App password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('abrahamadejumo2003@gmail.com', 'Test Mail');
    $mail->addAddress('abrahamadejumo12@gmail.com', 'Your Name');

    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test';
    $mail->Body = '<h1>Hello!</h1><p>This is a test email.</p>';

    $mail->send();
    echo '✅ Email sent successfully!';
} catch (Exception $e) {
    echo "❌ Email failed: {$mail->ErrorInfo}";
}
