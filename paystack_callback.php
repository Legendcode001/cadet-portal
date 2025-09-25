<?php
// =================== DATABASE CONNECTION & CONFIG ===================
require 'db.php';       // Contains your $conn = new mysqli(...) connection
require 'config.php';   // Contains your PAYSTACK_SECRET_KEY and other Paystack keys

// Function: Redirect to dashboard with status
function redirectToDashboard($status, $app_id = null)
{
    $url = "user_dashboard.php?status=" . urlencode($status);
    if ($app_id) {
        $url .= "&app_id=" . urlencode($app_id);
    }
    header("Location: " . $url);
    exit();
}

// =================== 1. GET PAYMENT REFERENCE FROM URL ===================
if (!isset($_GET['reference'])) {
    error_log("Paystack Callback: Missing 'reference' parameter.");
    redirectToDashboard('payment_failed');
}

$reference = $_GET['reference'];
$app_id = $reference; // In your payment.php, ref = applicationId directly

if (!$app_id) {
    error_log("Paystack Callback: Invalid reference format. Reference: " . $reference);
    redirectToDashboard('update_failed');
}

// =================== 2. VERIFY PAYMENT WITH PAYSTACK API ===================
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Cache-Control: no-cache"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    error_log("Paystack API call failed: " . $error_msg);
    curl_close($ch);
    redirectToDashboard('payment_failed', $app_id);
}
curl_close($ch);

$result = json_decode($response);

// =================== 3. CHECK VERIFICATION RESULT ===================
if ($http_code != 200 || !isset($result->data) || $result->data->status != 'success') {
    error_log("Paystack Callback: Verification failed. HTTP Code: " . $http_code . ". Response: " . $response);
    redirectToDashboard('payment_failed', $app_id);
}

// Payment was successful
$paystack_status = $result->data->status;
$transaction_id  = $result->data->reference;
$amount_paid     = $result->data->amount / 100; // convert from kobo to naira

// =================== 4. UPDATE DATABASE ===================
// Your applications table must have: id (int), payment_status, transaction_id, amount_paid
$sql = "UPDATE applications 
        SET payment_status = 'paid', transaction_id = ?, amount_paid = ? 
        WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Database preparation failed: " . $conn->error);
    redirectToDashboard('update_failed', $app_id);
}

$stmt->bind_param("sdi", $transaction_id, $amount_paid, $app_id);

if ($stmt->execute()) {
    // ✅ Redirect to receipt after successful payment
    header("Location: application_receipt.php?app_id=" . urlencode($app_id));
    exit();
} else {
    error_log("Database update failed for application ID: " . $app_id . " Error: " . $stmt->error);
    header("Location: user_dashboard.php?status=update_failed&app_id=" . urlencode($app_id));
    exit();
}

$stmt->close();
$conn->close();
