<?php
// Define your email address here where you want to receive messages.
// IMPORTANT: Replace 'youremail@example.com' with your actual email address.
$to_email = "nccnoyonigeria@gmail.com";

$status_message = "";
$status_type = ""; // 'success' or 'error'

// PHPMailer Library Integration
// IMPORTANT: You must install PHPMailer via Composer first.
// Run: composer require phpmailer/phpmailer
require 'src/PHPMailer.php';
require 'src/Exception.php';
require 'src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// A simple honeypot field to trap spambots.
$honeypot_field = isset($_POST['my_field']) ? $_POST['my_field'] : '';

// Check if the form was submitted and the honeypot field is empty
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($honeypot_field)) {
    // Sanitize and validate input data.
    $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8');

    // Validate that the required fields are not empty
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $status_message = "Please fill in all required fields.";
        $status_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Validate email format
        $status_message = "Invalid email format. Please enter a valid email address.";
        $status_type = "error";
    } else {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings for sending email
            $mail->isSMTP();

            // TODO: Replace with your actual SMTP server and credentials
            $mail->Host       = 'smtp.gmail.com'; // Example: Gmail SMTP server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'nccnoyonigeria@gmail.com';
            $mail->Password   = 'ccgm pxpg wigg qdct';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587; // Or 465 for SSL

            // Recipients
            $mail->setFrom($email, $name);
            $mail->addAddress($to_email, '3BG-Command Headquarters');


            // Content
            $mail->isHTML(true); // *** IMPORTANT: Set to true for HTML body ***
            $mail->Subject = "COMMUNICATION REPORT: " . $subject;

            // --- HTML EMAIL TEMPLATE (Military Design) ---

            // Use line breaks for HTML email compatibility
            $html_message = str_replace("\n", "<br>", $message);

            $mail->Body = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>' . htmlspecialchars($mail->Subject) . '</title>
                <style>
                    /* Basic Reset and Web Fonts */
                    body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #1a1a1a; color: #f0f0f0; }
                    .container { max-width: 600px; margin: 20px auto; background-color: #2c2c2c; border: 4px solid #b8860b; border-radius: 6px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
                    .header { background-color: #0d0d0d; padding: 15px 20px; text-align: center; border-bottom: 2px solid #b8860b; }
                    .header h1 { margin: 0; font-size: 20px; color: #e0e0e0; letter-spacing: 2px; text-transform: uppercase; }
                    .content { padding: 20px; }
                    .field-block { background-color: #1a1a1a; border: 1px solid #444; padding: 12px; margin-bottom: 10px; border-left: 5px solid #b8860b; border-radius: 3px; }
                    .field-label { font-weight: bold; color: #b8860b; display: block; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; }
                    .field-data { font-size: 14px; color: #e0e0e0; }
                    .message-box { background-color: #121212; border: 1px solid #b8860b; padding: 15px; border-radius: 4px; }
                    .footer { text-align: center; padding: 10px 20px; font-size: 10px; color: #888; border-top: 1px solid #444; margin-top: 20px; }
                    .footer a { color: #b8860b; text-decoration: none; }
                </style>
            </head>
            <body>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #1a1a1a;">
                    <tr>
                        <td align="center">
                            <div class="container">
                                <!-- Header: Mission/Report Status -->
                                <div class="header">
                                    <h1>INCOMING CONTACT TRANSMISSION</h1>
                                    <p style="color:#e0e0e0; font-size:10px; margin-top:5px;">CONFIDENTIALITY LEVEL: HIGH</p>
                                </div>
                                
                                <div class="content">
                                    <p style="color:#b8860b; font-size:12px; margin-bottom:15px; text-transform:uppercase;">Originator Data Log:</p>

                                    <!-- Name Field -->
                                    <div class="field-block">
                                        <span class="field-label">Sender Name (Operator)</span>
                                        <span class="field-data">' . $name . '</span>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="field-block">
                                        <span class="field-label">Digital Identifier (Email)</span>
                                        <span class="field-data"><a href="mailto:' . $email . '" style="color: #6a9c9b; text-decoration: none;">' . $email . '</a></span>
                                    </div>
                                    
                                    <!-- Subject Field -->
                                    <div class="field-block">
                                        <span class="field-label">Subject Line (Mission Brief)</span>
                                        <span class="field-data">' . $subject . '</span>
                                    </div>

                                    <!-- Message Body -->
                                    <div style="margin-top:20px;">
                                        <p class="field-label" style="font-size: 13px; color:#b8860b;">MESSAGE CONTENT (RAW FEED)</p>
                                        <div class="message-box">
                                            <p style="font-size: 14px; line-height: 1.6; color: #f0f0f0;">' . $html_message . '</p>
                                        </div>
                                    </div>

                                </div>
                                
                                <!-- Footer: Command Info -->
                                <div class="footer">
                                    <p>--- End of Transmission from 3BG Digital Network ---</p>
                                    <p>3BG-Command Headquarters | <a href="mailto:' . $to_email . '">Digital Dispatch Center</a></p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ';

            $mail->send();
            $status_message = "Thank you! Your message has been sent successfully. NC4A📌";
            $status_type = "success";
        } catch (Exception $e) {
            $status_message = "Sorry, something went wrong and your message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $status_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&family=Playfair+Display&family=Roboto&display=swap"
        rel="stylesheet">
    <link rel="icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
    <!-- Tailwind CSS for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for professional icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* 🔥 Full-screen loader styles */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader-icon {
            width: 200px;
            height: auto;
            animation: spin 3s linear infinite;
        }

        .new {
            font-size: 13px;
        }

        @keyframes spin {
            100% {
                transform: rotate(300deg);
            }
        }
    </style>
</head>

<body>
    <!-- 🔥 Loader HTML -->
    <div id="page-loader">
        <img src="./img/cadetlogo_prev_ui.png" alt="Loading..." class="loader-icon">
    </div>

    <header>
        <div class="logo">
            <a href="index.html"><img src="./img/cadetlogo_prev_ui.png" alt="Cadet Logo"></a>
        </div>
        <div class="logo-right">
            <a href="about.html"><img src="./img/image.png" alt="3rd Brigade Logo"></a>
        </div>
        <nav>
            <ul class="new">
                <li><a href="index.html">Home</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="careers.html">Careers</a></li>
                <li><a href="user_dashboard.php">DashBoard</a></li>
                <li><a href="news.php">News</a></li>
            </ul>
        </nav>
    </header>

    <main class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-900 text-gray-200">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold text-center text-white mb-8">Contact 3BG-Command Headquarters</h1>
            <p class="text-center text-gray-400 mb-12">
                We are dedicated to serving our community. Please use the form below for general inquiries, or find our
                contact information and location.
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div class="bg-gray-800 p-8 rounded-xl shadow-lg">
                    <h2 class="text-2xl font-semibold text-white mb-6">Send Us a Message</h2>

                    <!-- Display status message here -->
                    <?php if (!empty($status_message)): ?>
                        <div class="mb-4 p-4 rounded-md <?php echo ($status_type === 'success') ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
                            <?php echo htmlspecialchars($status_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="contact.php" method="POST" class="space-y-6">
                        <!-- Honeypot field for spam protection -->
                        <div style="display:none;">
                            <label for="my_field">My Field</label>
                            <input type="text" id="my_field" name="my_field">
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-400">Full Name</label>
                            <input type="text" id="name" name="name" required
                                class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-4 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-400">Email Address</label>
                            <input type="email" id="email" name="email" required
                                class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-4 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-400">Subject</label>
                            <input type="text" id="subject" name="subject" required
                                class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-4 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-400">Your Message</label>
                            <textarea id="message" name="message" rows="5" required
                                class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-4 text-white placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            Submit Inquiry
                        </button>
                    </form>
                </div>

                <!-- Contact Details and Map -->
                <div class="space-y-8">
                    <!-- Contact Details -->
                    <div class="bg-gray-800 p-8 rounded-xl shadow-lg">
                        <h2 class="text-2xl font-semibold text-white mb-6">General Information</h2>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="font-bold text-gray-300">Headquarters Address</h3>
                                    <p class="text-gray-400">123 Adifase High School Rd, <br> Ibadan, Oyo State</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-envelope text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="font-bold text-gray-300">Email</h3>
                                    <p class="text-gray-400">
                                        <a href="mailto:nccnoyonigeria@gmail.com">nccnoyonigeria@gmail.com </a>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-phone-alt text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="font-bold text-gray-300">Phone</h3>
                                    <p class="text-gray-400">+234 813 859 4224</p>
                                    <p class="text-gray-400">+234 806 045 9583</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-clock text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="font-bold text-gray-300">Response Time</h3>
                                    <p class="text-gray-400">We aim to respond to all inquiries within 2-3 business
                                        days.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Map Embed Placeholder -->
                    <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg">
                        <div class="w-full h-80 bg-gray-700 flex items-center justify-center">
                            <i class="fas fa-globe-americas text-blue-500 text-6xl opacity-70"></i>
                            <!-- A real map iframe would go here. Example: -->
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.7080870073196!2d3.821422671970485!3d7.386572896252759!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10398c828a0fde01%3A0x354a11e0ae22876d!2sAdifase%20High%20School!5e0!3m2!1sen!2sng!4v1758107955913!5m2!1sen!2sng" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-links">
            <div><a href="contact.php">Contact Us</a></div>
            <div><a href="faqs.html">FAQs</a></div>
            <div><a href="gallery.html">Gallery</a></div>
            <div><a href="privacy.html">Privacy Policy</a></div>
            <div><a href="developer.html">About the Developer</a></div>
            <div><a href="https://www.facebook.com" target="_blank">Facebook</a></div>
            <div><a href="https://www.instagram.com" target="_blank">Instagram</a></div>
            <div><a href="https://www.tiktok.com" target="_blank">TikTok</a></div>
            <div><a href="mailto:nccnoyonigeria@gmail.com" target="_blank">Gmail</a></div>
        </div>
        <p>&copy; 2024 3rd Brigade Headquarters. All Rights Reserved.</p>
    </footer>
</body>

</html>