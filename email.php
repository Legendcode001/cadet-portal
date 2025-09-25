<?php
// email.php
// Sends an application and payment confirmation email.

require 'config.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Global email sender setup
function setupMailer(): PHPMailer
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port = SMTP_PORT;
    $mail->setFrom(SMTP_USER, SENDER_NAME);
    return $mail;
}

/**
 * Email template builder for a professional, military-style email.
 *
 * @param string $title The subject or main title of the email.
 * @param string $message The main body content, expected to be HTML.
 * @param string $footerNote A customizable note for the email footer.
 * @return string The full HTML email template.
 */
function buildEmailTemplate($title, $message, $footerNote = 'Thank you for trusting the 3rd Brigade Hq Program.')
{
    // Using a placeholder for a military-style logo.
    $logoUrl = "./img/cadetlogo_prev_ui.png";
    return "
        <div style='font-family: Arial, sans-serif; background-color: #e8e8e8; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.2); border: 1px solid #c0c0c0;'>
                <div style='background-color: #2d4739; color: #ffffff; text-align: center; padding: 20px; border-bottom: 5px solid #0C450B;'>
                    <img src='$logoUrl' alt='Cadet Logo' style='width: 70px; height: auto; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.3);'>
                    <h1 style='margin: 15px 0 0; font-size: 28px; font-weight: bold;'>3rd Brigade Headquarters</h1>
                    <p style='margin: 5px 0 0; font-size: 16px; font-style: italic;'>$title</p>
                </div>
                <div style='padding: 30px 25px; color: #333333;'>
                    <p style='font-size: 16px; line-height: 1.6;'>$message</p>
                </div>
                <div style='background-color: #3d5c4e; padding: 20px 25px; text-align: center; font-size: 13px; color: #ffffff;'>
                    $footerNote
                </div>
            </div>
        </div>
    ";
}

/**
 * Sends a confirmation email to the user.
 *
 * @param array $application An associative array containing the user's application data.
 * @return void
 */
function sendReceiptToUser(array $application)
{
    // Directly use the data from the $application array, no database query needed.
    $fullName = htmlspecialchars($application['fullname']);
    $email = htmlspecialchars($application['email']);
    $amount = htmlspecialchars($application['amount_paid']);
    $reference = htmlspecialchars($application['application_id']);
    $id = htmlspecialchars($application['id']);

    $message = "
        <p>Dear Cadet <strong>$fullName</strong>,</p>
        <p>This email serves as an official confirmation of your application payment. Your application has been successfully recorded and is now under review by our command staff.</p>
        <div style='border: 1px solid #cccccc; padding: 15px; margin: 20px 0; background-color: #f9f9f9; border-radius: 6px;'>
            <h3 style='margin-top: 0; color: #0C450B;'>Payment Report</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'><strong>Amount Paid:</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'>₦" . number_format((float)$amount, 2) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'><strong>Reference ID:</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'>$reference</td>
                </tr>
                <tr>
                    <td style='padding: 8px;'><strong>Application ID:</strong></td>
                    <td style='padding: 8px;'>$id</td>
                </tr>
            </table>
        </div>
        <p>You can download your official receipt from your portal. We thank you for your commitment and look forward to your future contributions.</p>
        <p><strong>Dismissed.</strong></p>
    ";

    $mail = setupMailer();
    try {
        $mail->addAddress($email, $fullName);
        $mail->isHTML(true);
        $mail->Subject = "Official Application Confirmation - " . $reference;
        $mail->Body = buildEmailTemplate("Official Confirmation", $message);
        $mail->send();
    } catch (Exception $e) {
        error_log("User email error: " . $mail->ErrorInfo);
    }
}

/**
 * Sends a new application notification email to the administrator.
 *
 * @param array $application An associative array containing the user's application data.
 * @return void
 */
function sendNotificationToAdmin(array $application)
{
    // Directly use the data from the $application array
    $fullName = htmlspecialchars($application['fullname']);
    $email = htmlspecialchars($application['email']);
    $phone = htmlspecialchars($application['phone']);
    $amount = htmlspecialchars($application['amount_paid']);
    $reference = htmlspecialchars($application['application_id']);

    $adminEmail = ADMIN_EMAIL;

    $message = "
        <p>A new payment has been processed and verified for a Cadet Portal application. Immediate action may be required.</p>
        <div style='border: 1px solid #cccccc; padding: 15px; margin: 20px 0; background-color: #f9f9f9; border-radius: 6px;'>
            <h3 style='margin-top: 0; color: #0C450B;'>New Payment Alert</h3>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'><strong>Cadet Name:</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'>" . $fullName . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'><strong>Email:</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'>" . $email . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'><strong>Phone:</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'>" . $phone . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'><strong>Amount Paid:</strong></td>
                    <td style='padding: 8px; border-bottom: 1px solid #dddddd;'>₦" . number_format((float)$amount, 2) . "</td>
                </tr>
                <tr>
                    <td style='padding: 8px;'><strong>Transaction Reference:</strong></td>
                    <td style='padding: 8px;'>" . $reference . "</td>
                </tr>
            </table>
        </div>
        <p>Please log in to the admin dashboard to verify and process this new application.</p>
    ";

    $mail = setupMailer();
    try {
        $mail->addAddress($adminEmail, "Admin");
        $mail->isHTML(true);
        $mail->Subject = "New Payment Received - " . $reference;
        $mail->Body = buildEmailTemplate("New Payment Received", $message, "This is an automated notification from the 3rd Brigade Headquarters Portal.");
        $mail->send();
    } catch (Exception $e) {
        error_log("Admin email error: " . $mail->ErrorInfo);
    }
}
