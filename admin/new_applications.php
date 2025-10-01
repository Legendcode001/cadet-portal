<?php
// CRITICAL: Display errors for debugging (Keep these while troubleshooting the SQL)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ----------------------------------------------------
// 1. PHP Database Connection and Query Logic (USING MySQLi)
// ----------------------------------------------------
$host = 'localhost';
$db   = 'cadetportal';
$user = 'root'; // <-- CHECK THIS
$pass = '';     // <-- CHECK THIS

$applications = [];
$db_error = null;

// Attempt to establish connection
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    $db_error = "Connection failed: " . $conn->connect_error;
} else {
    // SQL Query: Pulling names from 'applications' and status/date from 'payments'
    $sql = "
         SELECT 
             a.id, 
             a.surname, 
             a.firstname, 
             a.lastname,
             COALESCE(p.status, 'Pending Review') AS application_status, 
             -- FINAL GUESS FOR DATE COLUMN NAME: Try 'date_submitted'
             p.payment_date AS date_applied, 
             COALESCE(p.status, 'N/A') AS payment_status
         FROM 
             applications a
         LEFT JOIN 
             payments p ON a.id = p.application_id
         ORDER BY 
             p.payment_date DESC;
    ";

    $result = $conn->query($sql);

    if ($result === FALSE) {
        $db_error = "Query failed: " . $conn->error;
    } else {
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
        if (is_object($result)) {
            $result->free();
        }
    }
    $conn->close();
}

if ($db_error !== null) {
    error_log("Database Error: " . $db_error);
    $applications = [];
    // If you run this code and still get the 'No applications found...' message,
    // look in your PHP error log (or uncomment the line below) for the exact
    // column error (e.g., 'Unknown column date_submitted').
    // echo "Debug Error: " . $db_error; 
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Applications</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* This is from your original code block */
            @apply bg-gray-950 text-gray-200;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        thead th,
        tbody td {
            text-align: left;
            padding: 1rem;
        }
    </style>
</head>

<body class="antialiased">

    <div class="container mx-auto p-4 sm:p-8 md:p-12">
        <header class="text-center mb-10">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-400 tracking-tight flex items-center justify-center">
                New Applications
            </h1>
            <p class="text-md sm:text-lg text-gray-400 mt-2">A detailed list of all submitted 3rd Brigade applications.</p>
        </header>

        <a href="#" onclick="history.back()"
            class="flex items-center text-blue-400 hover:text-blue-500 transition-colors duration-200 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>

        <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-sm font-semibold text-gray-400 uppercase tracking-wider">
                            Applicant</th>
                        <th scope="col" class="px-6 py-3 text-sm font-semibold text-gray-400 uppercase tracking-wider">
                            Application Status</th>
                        <th scope="col" class="px-6 py-3 text-sm font-semibold text-gray-400 uppercase tracking-wider">
                            Payment Status</th>
                        <th scope="col" class="px-6 py-3 text-sm font-semibold text-gray-400 uppercase tracking-wider">
                            Date Applied</th>
                    </tr>
                </thead>
                <tbody id="applications-table-body" class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (!empty($applications)): ?>
                        <?php foreach ($applications as $app):
                            // PHP status and color logic
                            $app_status = htmlspecialchars($app['application_status'] ?? 'N/A');
                            $app_color = match ($app_status) {
                                'Accepted' => 'bg-green-500',
                                'Rejected' => 'bg-red-500',
                                default => 'bg-yellow-500', // Pending Review
                            };

                            $payment_status = htmlspecialchars($app['payment_status'] ?? 'N/A');
                            $payment_color = match ($payment_status) {
                                'Paid' => 'bg-green-700',
                                'Unpaid' => 'bg-red-700',
                                default => 'bg-gray-500', // N/A
                            };
                        ?>
                            <tr class="hover:bg-gray-700 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-white font-medium">
                                    <?= htmlspecialchars($app['firstname'] . ' ' . $app['lastname'] . ' ' . $app['surname']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $app_color ?> text-white">
                                        <?= $app_status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $payment_color ?> text-white">
                                        <?= $payment_status ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    <?= htmlspecialchars(date('m/d/Y', strtotime($app['date_applied'] ?? 'now'))) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No applications found or a database error occurred.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>