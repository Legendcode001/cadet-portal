<?php
// payment.php
// Displays the payment button and handles payment initiation.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection and Paystack keys
require_once 'paystack_config.php'; // Make sure this file exists and has PAYSTACK_PUBLIC_KEY
$host = "localhost";
$user = "root";
$pass = "";
$db = "cadetportal";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get the application ID from the URL
$applicationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$applicationId) {
    die("No application ID provided.");
}

// Fetch the applicant's details from the database
$sql = "SELECT firstname, lastname, email FROM applications WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $applicationId);
$stmt->execute();
$result = $stmt->get_result();
$applicant = $result->fetch_assoc();
$stmt->close();

if (!$applicant) {
    die("Applicant not found.");
}

$conn->close();

// Default amount for the application fee
$amount = 10000; // Amount in Naira

// Generate a random secure reference (keeps applicationId inside but adds randomness)
$reference = "APP" . $applicationId . "-" . strtoupper(bin2hex(random_bytes(4)));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Paystack inline script -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <link rel="icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full text-center">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Application Fee</h1>
        <p class="text-gray-600 mb-6">₦<?php echo number_format($amount, 2); ?></p>

        <p class="text-gray-700 mb-4">
            Hello, <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($applicant['firstname'] . " " . $applicant['lastname']); ?></span>.
            Your application has been saved. Please proceed to payment to finalize it.
        </p>

        <button id="pay-button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow transition duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 w-full">
            Pay Now
        </button>

        <p class="text-gray-500 text-sm mt-4">Powered by Paystack</p>
    </div>

    <script>
        const payButton = document.getElementById('pay-button');

        payButton.addEventListener('click', () => {
            const handler = PaystackPop.setup({
                key: '<?php echo 'pk_test_e664f87f1d1e05528d0e24cd0fab1fc2c444c830'; ?>',
                email: '<?php echo htmlspecialchars($applicant['email']); ?>',
                amount: <?php echo $amount * 100; ?>, // Amount in kobo
                currency: 'NGN',
                ref: '<?php echo $reference; ?>', // Random secure reference
                metadata: {
                    application_id: '<?php echo $applicationId; ?>' // Pass application_id in metadata
                },
                callback: function(response) {
                    console.log("callback success");
                    window.location.href = `paystack_processor.php?reference=${response.reference}&application_id=<?php echo $applicationId; ?>`;

                },
                onSuccess: (response) => {
                    // Redirect to the processor script with the reference and application_id
                    // Ensure application_id is explicitly passed here
                    //window.location.href = `paystack_processor.php?reference=${response.reference}&application_id=<?php echo $applicationId; ?>`;
                    console.log("Successful Pyment==");
                },
                onClose: () => {
                    console.log('Payment pop-up closed. Please complete your payment to proceed.');
                },
            });
            handler.openIframe();
        });
    </script>
</body>

</html>