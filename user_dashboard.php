<?php
// user_dashboard.php
// Uses: db.php (should create $conn), config.php (PAYSTACK_PUBLIC_KEY, PAYSTACK_CALLBACK_URL)

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php';
require 'config.php';

$application = null;
$app_id_message = "";
$status_message = "";
$status_color_class = "text-yellow-600"; // Default status color
$payment_feedback_message = "";
$payment_feedback_color_class = "";

// =================== APPLICATION ID CHECK ===================
if (isset($_GET['app_id'])) {
    $app_id = $conn->real_escape_string($_GET['app_id']);
    $sql = "SELECT * FROM applications WHERE application_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $app_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $application = $result->fetch_assoc();
        $app_id_message = "Your Application ID: <strong class='text-green-500'>" . htmlspecialchars($application['application_id']) . "</strong>";

        $payment_status = $application['payment_status'] ?? 'pending';
        if (strtolower($payment_status) == 'paid') {
            $status_message = "Payment Received. Application is now Under Review.";
            $status_color_class = "text-green-600";
        } else {
            $status_message = "Payment Pending. Please complete payment to finalize your application.";
            $status_color_class = "text-yellow-600";
        }
    } else {
        // keep $application null if not found
    }
    if ($stmt) $stmt->close();
}

// =================== PAYMENT STATUS FEEDBACK ===================
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    switch ($status) {
        case 'payment_success':
            $payment_feedback_message = "Payment Successful! Thank you for your payment. Your application is now being processed.";
            $payment_feedback_color_class = "bg-green-100 text-green-700 border-green-400";
            break;
        case 'payment_failed':
            $payment_feedback_message = "Payment Failed. There was an issue processing your payment. Please try again.";
            $payment_feedback_color_class = "bg-red-100 text-red-700 border-red-400";
            break;
        case 'update_failed':
            $payment_feedback_message = "An error occurred. We received your payment but there was a problem updating our records. Please contact support.";
            $payment_feedback_color_class = "bg-red-100 text-red-700 border-red-400";
            break;
    }
}

// Helper: build passport URL from different possible DB column names
function buildPassportUrl($row)
{
    // prefer explicit path fields if available
    $candidates = ['passport_path', 'passport_filename', 'passport'];
    foreach ($candidates as $col) {
        if (!empty($row[$col])) {
            $val = $row[$col];
            // If it looks like a full URL or absolute path, return as-is
            if (preg_match('#^https?://#i', $val) || strpos($val, '/') === 0) {
                return $val;
            }
            // if DB stored "uploads/..." already, allow that
            if (stripos($val, 'uploads/') === 0) {
                return $val;
            }
            // otherwise prefix with uploads/
            return 'uploads/' . ltrim($val, '/');
        }
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Application - 3rd Brigade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
    <style>
        body {
            font-family: "Inter", system-ui, sans-serif;
            background: linear-gradient(135deg, #1b2d1b, #3a5a40, #2d4739);
            background-size: cover;
            background-attachment: fixed;
        }

        .logo {
            position: fixed;
            top: 1rem;
            left: 1rem;
        }

        .content-box {
            position: relative;
            background: #fff;
            color: #000;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            max-width: 890px;
            margin: 50px auto;
            z-index: 1;
            overflow: hidden;
        }

        .content-box::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 340px;
            height: 250px;
            background: url('img/ncclogo.png') no-repeat center center;
            background-size: 150%;
            opacity: 0.09;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>

<body class="text-gray-900">
    <div class="logo">
        <a href="index.html">
            <img src="./img/cadetlogo_prev_ui.png" alt="Cadet Logo" class="w-16 h-auto">
        </a>
    </div>

    <div class="max-w-4xl mx-auto p-4 md:p-8">
        <div class="content-box">
            <header class="mb-6">
                <h1 class="text-3xl font-bold text-center text-slate-800">My Application Dashboard</h1>
                <p class="text-center text-gray-600 mt-2">View the details and status of your application below.</p>
                <p class="text-center text-gray-500 text-sm mt-1"><?php echo $app_id_message; ?></p>
            </header>

            <?php if (!empty($payment_feedback_message)) : ?>
                <div class="mb-6 p-4 rounded-lg border-l-4 <?php echo $payment_feedback_color_class; ?>" role="alert">
                    <p class="font-bold">Payment Status</p>
                    <p class="text-sm"><?php echo htmlspecialchars($payment_feedback_message); ?></p>
                </div>
            <?php endif; ?>

            <div class="mb-8 p-6 bg-gray-100 rounded-lg shadow-inner">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Check Application Status</h2>
                <form action="user_dashboard.php" method="GET" class="flex flex-col sm:flex-row gap-4">
                    <input
                        type="text"
                        name="app_id"
                        placeholder="Enter your Application ID (e.g., 3BH-123456)"
                        required
                        class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                        Check Status
                    </button>
                </form>
                <div class="text-center mt-4 text-sm text-gray-600">
                    <p>Don't have an Application ID? <a href="apply.php" class="text-blue-600 hover:underline font-medium">Create a new application here.</a></p>
                </div>
            </div>

            <?php if ($application) : ?>
                <!-- Application Status Section -->
                <div class="p-6 bg-gray-50 rounded-lg border border-gray-200 text-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Application Status</h2>
                    <p class="text-lg font-bold <?php echo $status_color_class; ?>">
                        <?php echo htmlspecialchars($status_message); ?>
                    </p>

                    <!-- Paystack Payment Button (only for pending payments) -->
                    <?php if (strtolower($application['payment_status'] ?? '') !== 'paid') : ?>
                        <button
                            id="payButton"
                            class="mt-4 w-full sm:w-auto px-8 py-3 bg-blue-600 text-white rounded-lg font-bold shadow-md hover:bg-blue-700 transition-transform duration-200 ease-in-out active:scale-95">
                            Pay Now (₦10,000)
                        </button>
                    <?php else: ?>
                        <a href="application_receipt.php?id=<?php echo urlencode($application['application_id'] ?? $application['id']); ?>" target="_blank" class="mt-4 inline-block px-6 py-3 bg-green-600 text-white rounded-lg font-bold shadow-md hover:bg-green-700">
                            View / Print Receipt
                        </a>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-700 mb-3">Personal Details</h2>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li><strong>Name:</strong> <?php echo htmlspecialchars(trim(($application['firstname'] ?? '') . ' ' . ($application['lastname'] ?? '') . ' ' . ($application['surname'] ?? ''))); ?></li>
                            <li><strong>Date of Birth:</strong> <?php echo htmlspecialchars($application['dob'] ?? 'N/A'); ?></li>
                            <li><strong>Age:</strong> <?php echo htmlspecialchars($application['age'] ?? 'N/A'); ?></li>
                            <li><strong>Sex:</strong> <?php echo htmlspecialchars($application['sex'] ?? 'N/A'); ?></li>
                            <li><strong>Marital Status:</strong> <?php echo htmlspecialchars($application['marital_status'] ?? 'N/A'); ?></li>
                            <li><strong>Nationality:</strong> <?php echo htmlspecialchars($application['nationality'] ?? 'N/A'); ?></li>
                            <li><strong>Religion:</strong> <?php echo htmlspecialchars($application['religion'] ?? 'N/A'); ?></li>
                            <li><strong>Hometown:</strong> <?php echo htmlspecialchars($application['hometown'] ?? 'N/A'); ?></li>
                            <li><strong>State of Origin:</strong> <?php echo htmlspecialchars($application['state'] ?? 'N/A'); ?></li>
                            <li><strong>LGA:</strong> <?php echo htmlspecialchars($application['lga'] ?? 'N/A'); ?></li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="text-2xl font-semibold text-gray-700 mb-3">Contact & Qualifications</h2>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone'] ?? 'N/A'); ?></li>
                            <li><strong>Email:</strong> <?php echo htmlspecialchars($application['email'] ?? 'N/A'); ?></li>
                            <li><strong>Address:</strong> <?php echo htmlspecialchars($application['address'] ?? 'N/A'); ?></li>
                            <li><strong>Qualifications:</strong> <?php echo htmlspecialchars($application['qualifications'] ?? 'N/A'); ?></li>
                            <li><strong>Medical Challenges:</strong> <?php echo htmlspecialchars($application['challenges'] ?? 'N/A'); ?></li>
                        </ul>

                        <div class="mt-6">
                            <h2 class="text-2xl font-semibold text-gray-700 mb-3">Application Files</h2>
                            <ul class="space-y-2 text-gray-600 text-sm">
                                <?php
                                $passportUrl = buildPassportUrl($application);
                                if (!empty($passportUrl)) : ?>
                                    <li><strong>Passport:</strong> <a href="<?php echo htmlspecialchars($passportUrl); ?>" target="_blank" class="text-blue-600 hover:underline">View Passport</a></li>
                                <?php else : ?>
                                    <li><strong>Passport:</strong> <span class="text-red-500">Not Uploaded</span></li>
                                <?php endif; ?>
                                <?php if (!empty($application['other_file_path'])): ?>
                                    <li><strong>Other file:</strong> <a href="<?php echo htmlspecialchars($application['other_file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Download</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t pt-6 text-center">
                    <p class="text-sm text-gray-500">This is a summary of the data you provided. The application status will be updated here after review.</p>
                </div>
            <?php else : ?>
                <div class="text-center p-8 bg-gray-50 rounded-lg">
                    <h2 class="text-xl font-semibold text-gray-700">No application found.</h2>
                    <p class="mt-2 text-gray-500">
                        It looks like you haven't created an application yet, or the ID is incorrect.
                    </p>
                    <a href="apply.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition">
                        Create New Application
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="mt-8 text-center text-gray-400 text-sm">
        © <?php echo date("Y"); ?> 3rd Brigade Headquarters. All Rights Reserved.
    </footer>

    <!-- Paystack JavaScript Library -->
    <script src="https://js.paystack.co/v1/inline.js"></script>

    <script>
        // Only run this code if an application was found and payment is pending.
        <?php if ($application && strtolower($application['payment_status'] ?? '') !== 'paid') : ?>
            const payButton = document.getElementById('payButton');
            // Unique reference format: application_id + random suffix
            const paystackRef = '<?php echo htmlspecialchars($application['application_id'] ?? $application['id']); ?>-' + Math.floor(Math.random() * 100000);

            payButton && payButton.addEventListener('click', () => {
                // Paystack amount is in kobo, so we multiply by 100
                const amountInKobo = 10000 * 100; // ₦10,000

                const handler = PaystackPop.setup({
                    key: "<?php echo PAYSTACK_PUBLIC_KEY; ?>",
                    email: "<?php echo htmlspecialchars($application['email'] ?? ''); ?>",
                    amount: amountInKobo,
                    ref: paystackRef, // unique reference
                    // Let Paystack append its own reference param to the callback; callback will receive ?reference=<ref>
                    callback_url: "<?php echo PAYSTACK_CALLBACK_URL; ?>",
                    onClose: () => {
                        console.log('Payment window closed.');
                    },
                    metadata: {
                        application_id: "<?php echo htmlspecialchars($application['application_id'] ?? $application['id']); ?>"
                    }
                });

                handler.openIframe();
            });
        <?php endif; ?>
    </script>
</body>

</html>
<?php
$conn->close();
?>