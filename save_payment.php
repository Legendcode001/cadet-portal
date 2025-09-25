<?php
// save_payment.php
// This script saves the application data and initiates the Paystack payment process.

include 'db.php';
include 'paystack_config.php';

// Set a more secure session cookie
session_set_cookie_params([
    'lifetime' => 3600, // 1 hour
    'path' => '/',
    'domain' => $_SERVER['SERVER_NAME'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve POST data
    $fullname = $_POST['fullname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $amount = 10000; // Hardcoded amount for application fee in Naira

    // Generate a unique and unpredictable reference for the transaction
    $application_id = '3BH-' . bin2hex(random_bytes(16));

    // Save initial application with a 'pending' status
    $stmt = $conn->prepare("INSERT INTO applications (fullname, email, phone, amount_paid, payment_status, application_id) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("sssis", $fullname, $email, $phone, $amount, $application_id);

    if ($stmt->execute()) {
        // Initialize Paystack transaction with the unique application ID as the reference
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'email' => $email,
                'amount' => $amount * 100, // Paystack expects amount in kobo
                'reference' => $application_id, // Use our unique ID as the reference
                // Use a secure callback URL to our verification script
                'callback_url' => "http://localhost/cadetportal/verify.php?reference=" . urlencode($application_id)
            ]),
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer " . PAYSTACK_SECRET_KEY,
                "content-type: application/json",
                "cache-control: no-cache"
            ],
        ));

        $response = curl_exec($curl);
        $res = json_decode($response, true);
        curl_close($curl);

        if ($res['status']) {
            $authUrl = $res['data']['authorization_url'];
            header("Location: $authUrl");
            exit();
        } else {
            echo "Payment initialization failed: " . htmlspecialchars($res['message']);
        }
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    // If the form wasn't submitted via POST, redirect them back.
    header("Location: index.html");
    exit();
}
