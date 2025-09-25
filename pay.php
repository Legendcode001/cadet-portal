<?php
include 'db.php';
include 'paystack_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $amount = $_POST['amount'] * 100; // Paystack uses kobo

    // Save user record with "Pending" status
    $stmt = $conn->prepare("INSERT INTO applications (full_name, email, phone, amount, payment_status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("sssd", $fullName, $email, $phone, $_POST['amount']);
    $stmt->execute();
    $lastId = $conn->insert_id;

    // Generate transaction reference
    $tx_ref = "TXN_" . time() . "_" . $lastId;

    // Redirect to Paystack
    $callback_url = "http://yourdomain.com/verify.php?ref=$tx_ref&id=$lastId";

    $postdata = [
        'email' => $email,
        'amount' => $amount,
        'reference' => $tx_ref,
        'callback_url' => $callback_url
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/initialize");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $res = json_decode($result, true);

    if ($res['status']) {
        header("Location: " . $res['data']['authorization_url']);
        exit;
    } else {
        echo "Error initializing payment.";
    }
}
