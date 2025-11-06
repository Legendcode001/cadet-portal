<?php
// ===========================================
// LOGIN AUTHENTICATION LOGIC
// ===========================================
session_start();

// --- INITIALIZE DYNAMIC CREDENTIALS AND MOCK DATA ---
// We use $_SESSION to simulate a database where credentials and settings are stored.
// This allows settings.php to modify them.
if (!isset($_SESSION['user_data'])) {
    $_SESSION['user_data'] = [
        'username' => 'admin',
        // In a real app, this would be a hash (password_hash('secret456', PASSWORD_DEFAULT)).
        // We use plain text 'secret456' for simplicity in this PHP demo.
        'password_hash' => 'secret456',
        'dashboard_title' => 'Main Dashboard Overview',
        'widget_text' => 'Total Sales Figures (Editable Frontend Item)'
    ];
}

$login_error = '';
/*
// Check if already logged in and redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: login.php');
    exit;
}
*/

// Handle POST request for login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Check input against the dynamic session data
    if (
        $username === $_SESSION['user_data']['username'] &&
        $password === $_SESSION['user_data']['password_hash']
    ) {

        // Successful login
        $_SESSION['logged_in'] = true;
        // Redirect to clear POST data and load dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        $login_error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Required</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#4f46e5',
                        /* Indigo */
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        /* Custom shadow for a slight glowing effect */
        .glow-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1), 0 0 40px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>

<body class="bg-gray-50 font-sans min-h-screen flex items-center justify-center p-4">

    <!-- LOGIN SCREEN -->
    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-2xl shadow-2xl border-t-8 border-primary glow-shadow transform transition duration-500 hover:scale-[1.01]">
        <div class="text-center">
            <!-- Lock Icon -->
            <svg class="mx-auto h-12 w-12 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h2 class="mt-4 text-3xl font-extrabold text-gray-900">
                Sign in to Admin Dashboard
            </h2>
            <p class="mt-2 text-sm text-gray-500">
                Secure access required.
            </p>
        </div>

        <?php if ($login_error): ?>
            <!-- Error Message Box -->
            <div class="p-3 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg" role="alert">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form class="space-y-6" action="login.php" method="POST">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input id="username" name="username" type="text" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary transition duration-150"
                    placeholder="admin">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary transition duration-150"
                    placeholder="secret456">
            </div>

            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-lg font-semibold text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-primary focus:ring-opacity-50 transition duration-200 transform hover:-translate-y-0.5">
                Log In
            </button>
            <p class="text-xs text-gray-400 text-center pt-2">
                Demo Credentials: **<?php echo htmlspecialchars($_SESSION['user_data']['username'] ?? 'admin'); ?>** / **secret456**
            </p>
        </form>
    </div>

</body>

</html>