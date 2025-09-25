<?php
// paystack_processor.php
// Handles the redirect from Paystack after a payment is made.

error_reporting(E_ALL); // <--- ADD THIS LINE for better debugging
ini_set('display_errors', 1); // <--- ADD THIS LINE for better debugging

// Paystack_processor
//require 'paystack_config.php'; // Make sure this file exists and has PAYSTACK_SECRET_KEY
require 'db.php';             // Make sure this file properly initializes $conn
require 'email.php';          // Include the email functions

// Check if reference is provided
if (!isset($_GET['reference'])) {
    die("No transaction reference supplied.");
}

$reference = $_GET['reference'];

// Ensure we get the application ID from the URL parameter we explicitly set
// This is crucial for linking the payment to the correct application
$application_id = $_GET['application_id'] ?? null;

if (!$application_id) {
    die("No application ID supplied in the redirect. Payment could not be processed.");
}

// Verify payment via Paystack API
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Tell cURL where to find the CA certificate bundle
curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '\my_cacert.pem');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Content-Type: application/json"
]);
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    die("cURL error during Paystack verification: " . $error_msg);
}
curl_close($ch);
$result = json_decode($response, true);
//echo "" . var_dump($result) . "";

// Debugging: Log the full Paystack response
// error_log("Paystack Verification Response: " . print_r($result, true));

// Check if the verification was successful and the payment was a success
if (isset($result['status']) && $result['status'] === true && isset($result['data']['status']) && $result['data']['status'] === 'success') {
    $amount_paid = $result['data']['amount'] / 100; // Convert kobo to Naira
    $email       = $result['data']['customer']['email'];
    $paystack_transaction_id = $result['data']['id']; // Store Paystack's transaction ID for reference

    // Ensure $conn is available from db.php and is a mysqli object
    if (!isset($conn) || !($conn instanceof mysqli)) {
        die("Database connection not established in db.php.");
    }

    // Use a transaction to ensure data integrity
    $conn->begin_transaction();
    //echo "" . var_dump($result) . "";
    //die();


    try {
        // Prepare data for the `applications` table update
        print("1");
        $new_status = "paid";
        print("2");

        // Update the application status in the database
        $update_sql = "UPDATE applications SET payment_status = ?, amount = ?, transaction_reference=? WHERE id = ?";
        print("3");

        $update_stmt = $conn->prepare($update_sql);
        print("4");

        $update_stmt->bind_param("sdsi", $new_status, $amount_paid, $reference, $application_id);
        print("5");

        $update_stmt->execute();
        print("6");

        $update_stmt->close();

        // Prepare data for the `payments` table insertion
        $payment_status = "success";
        $payment_sql = "INSERT INTO payments (application_id, reference, paystack_transaction_id, amount, email, status) VALUES (?, ?, ?, ?, ?, ?)";
        $payment_stmt = $conn->prepare($payment_sql);

        $payment_stmt->bind_param("issdis", $application_id, $reference, $paystack_transaction_id, $amount_paid, $email, $payment_status);
        $payment_stmt->execute();

        $payment_stmt->close();

        // Get the full application data to pass to the email functions
        $application_sql = "SELECT * FROM applications WHERE id = ?";
        $application_stmt = $conn->prepare($application_sql);

        $application_stmt->bind_param("i", $application_id);
        $application_stmt->execute();

        $application_result = $application_stmt->get_result();
        $application_data = $application_result->fetch_assoc();
        $application_stmt->close();

        if ($application_data) {
            // Send email to the user with the payment receipt
            sendReceiptToUser($application_data);
            // Send a notification email to the admin
            sendNotificationToAdmin($application_data);
        }

        // Commit the transaction
        $conn->commit();

        // Redirect to receipt
        header("Location: receipt.php?reference=" . urlencode($reference) . "&id=" . urlencode($application_id));
        exit; // <--- THIS IS THE CRUCIAL ADDITION
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        error_log("Payment processing failed: " . $e->getMessage());
        print("Payment processing failed: " . $e->getMessage());
        // For security, avoid echoing raw database errors to the user
        die("An error occurred while processing your payment. Please contact support.");
    }
} else {
    // Log detailed failure for debugging
    error_log("Paystack verification failed for reference: " . $reference . " Response: " . print_r($result, true));
    die("Payment verification failed. Please try again or contact support.");
}

$conn->close();
