<?php
// verify.php
include 'db.php';
include 'paystack_config.php';
include 'email.php';

if (!isset($_GET['reference'])) {
    die("Error: No transaction reference provided.");
}

$reference = $_GET['reference'];

// Find application by application_id
$stmt = $conn->prepare("SELECT * FROM applications WHERE application_id = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$res = $stmt->get_result();
$application = $res->fetch_assoc();
$stmt->close();

if (!$application) {
    die("Error: Invalid application reference.");
}

// If already paid, skip verification
if ($application['payment_status'] === 'Paid') {
    header("Location: receipt.php?id=" . urlencode($application['id']));
    exit();
}

// Verify with Paystack
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . rawurlencode($reference));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Cache-Control: no-cache"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    die("cURL Error: " . $err);
}

$res = json_decode($result, true);

if ($res && $res['status'] && $res['data']['status'] === 'success') {
    $amount_paid = $res['data']['amount'] / 100;

    // Update DB
    $stmt = $conn->prepare("UPDATE applications 
                            SET payment_status = 'Paid', transaction_reference = ?, amount = ? 
                            WHERE application_id = ?");
    $stmt->bind_param("sds", $reference, $amount_paid, $reference);

    if ($stmt->execute()) {
        // Send emails
        sendReceiptToUser($application);
        sendNotificationToAdmin($application);

        // Redirect to receipt
        header("Location: receipt.php?id=" . urlencode($application['id']));
        exit();
    } else {
        die("Database update failed: " . $stmt->error);
    }
} else {
    header("Location: payment_failed.php?reason=" . urlencode($res['message'] ?? 'Payment verification failed.'));
    exit();
}
