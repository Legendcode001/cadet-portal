<?php
// =================== DATABASE CONNECTION ===================
$host = "localhost";
$user = "root";
$pass = "";
$db   = "cadetportal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// =================== PAYMENT PROCESSING ===================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve and sanitize application ID
    $applicationId = $_POST['application_id'] ?? null;

    if (!$applicationId) {
        // Redirect if no ID is found
        header("Location: apply.php");
        exit();
    }

    // =================== FAKE PAYMENT (simulation) ===================
    // In production, integrate a real payment gateway here.

    // Update application payment status
    $sql  = "UPDATE applications SET payment_status = 'paid' WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $applicationId);

    if ($stmt->execute()) {
        // Payment successful, show confirmation + redirect
        echo '

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel=" icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
            <link rel=" icon" href="favicon-16x16.png" sizes="16x16" type="image/png">
            <link rel=" icon" href="favicon-32x32.png" sizes="32x32" type="image/png">
            <link rel=" icon" href="favicon-64x64.png" sizes="64x64" type="image/png">
            <title>Payment Successful</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f8f9fa;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    text-align: center;
                }
                .container {
                    background: #fff;
                    padding: 40px 50px;
                    border-radius: 12px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    max-width: 450px;
                }
                h1 {
                    color: #28a745;
                    margin-bottom: 15px;
                }
                p {
                    color: #555;
                    margin-bottom: 25px;
                }
                .loader {
                    border: 6px solid #e9ecef;
                    border-radius: 50%;
                    border-top: 6px solid #28a745;
                    width: 60px;
                    height: 60px;
                    animation: spin 1s linear infinite;
                    margin: 20px auto;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>✅ Payment Successful!</h1>
                <p>You can now print your application form.</p>
                <div class="loader"></div>
                <p><h5>Redirecting to your receipt...</h5></p>
            </div>

            <script>
                // Redirect after 2 seconds
                setTimeout(function() {
                    window.location.href = "application_receipt.php?id=' . $applicationId . '";
                }, 2000);
            </script>
        </body>
        </html>
        ';
    } else {
        echo "❌ Error updating record: " . $stmt->error;
    }

    $stmt->close();
} else {
    // Redirect if accessed without POST
    header("Location: apply.php");
    exit();
}

$conn->close();
