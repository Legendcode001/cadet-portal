<?php
// Paystack API Configuration
// Replace the placeholders with your actual Paystack API keys.

// You can find these on your Paystack Dashboard > Settings > API Keys & Webhooks
// Use TEST keys for development and LIVE keys for production.

define('PAYSTACK_SECRET_KEY', 'sk_test_fea7e5ee68479c054dc28decf1873d9c639adb03');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_e664f87f1d1e05528d0e24cd0fab1fc2c444c830');

// This is the URL that Paystack will redirect to after a successful transaction.
// It should point to your paystack_processor.php file.
// For local testing, you must use a tool like ngrok to get a public URL.
define('PAYSTACK_CALLBACK_URL', ' https://708e5a01475a.ngrok-free.app/cadetportal/paystack_processor.php');
