<?php
// config.php
// Contains all configuration constants for the application.
// Please handle these credentials with care and never share them publicly.

// --- Paystack Configuration (Test) ---
// Your Paystack API keys
define('PAYSTACK_PUBLIC_KEY', 'pk_test_e664f87f1d1e05528d0e24cd0fab1fc2c444c830');
define('PAYSTACK_SECRET_KEY', 'sk_test_fea7e5ee68479c054dc28decf1873d9c639adb03');

// Paystack callback URL. This must be a public URL for Paystack to redirect to.
// Use Ngrok to get a public URL for your local server.
// REMEMBER to update this URL every time you start Ngrok.
define('PAYSTACK_CALLBACK_URL', 'https://0a87ac2d25aa.ngrok-free.app/verify.php');

// --- Email Configuration ---
// Your email provider's SMTP details
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'only.legendcode@gmail.com'); // Your Gmail address
define('SMTP_PASS', 'hccb xnxd svvj qzen'); // Your Gmail App Password

// Recipient and sender details
define('ADMIN_EMAIL', 'abrahamadejumo2003@gmail.com');
define('SENDER_NAME', '3rd Brigade Headquarters');

// --- Database Configuration ---
// Your local database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'cadetportal');
