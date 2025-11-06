<?php
// ===========================================
// SETTINGS UPDATE LOGIC
// ===========================================
session_start();

// Security Check: If not logged in, redirect to login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$settings_message = ['type' => '', 'text' => ''];
$user_data = $_SESSION['user_data']; // Get current data

// Handle Settings Update POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Inputs
    $new_username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $current_password = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    $dashboard_title = filter_input(INPUT_POST, 'dashboard_title', FILTER_SANITIZE_STRING);
    $widget_text = filter_input(INPUT_POST, 'widget_text', FILTER_SANITIZE_STRING);

    $success = true;

    // 2. Validate Current Password (MANDATORY)
    if ($current_password !== $user_data['password_hash']) {
        $settings_message = ['type' => 'error', 'text' => 'Error: Current password is incorrect. No changes were saved.'];
        $success = false;
    }

    if ($success) {
        // 3. Update Username
        if (!empty($new_username) && $new_username !== $user_data['username']) {
            $_SESSION['user_data']['username'] = $new_username;
        }

        // 4. Update Password
        if (!empty($new_password)) {
            // In a real app, use password_hash() here.
            $_SESSION['user_data']['password_hash'] = $new_password;
        }

        // 5. Update Frontend/Dashboard Items
        $_SESSION['user_data']['dashboard_title'] = $dashboard_title;
        $_SESSION['user_data']['widget_text'] = $widget_text;

        $settings_message = ['type' => 'success', 'text' => 'Settings saved successfully! Changes are effective immediately.'];

        // Refresh data object after saving
        $user_data = $_SESSION['user_data'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#4f46e5', // Indigo
                        'accent': '#10b981', // Emerald
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        /* Custom Nav Link Styles */
        .nav-link {
            @apply flex items-center px-4 py-3 text-base font-medium rounded-xl transition-all duration-200 ease-in-out;
        }

        .nav-link.active {
            @apply bg-primary text-white shadow-lg shadow-indigo-200/50 transform translate-x-1;
        }

        .nav-link:not(.active) {
            @apply text-gray-600 hover:bg-gray-100 hover:text-gray-800;
        }

        /* Settings Section Heading Style */
        .setting-section-title {
            @apply text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex">

    <!-- 1. SIDEBAR NAVIGATION -->
    <aside class="w-64 bg-white border-r border-gray-200 shadow-xl p-6 flex flex-col">
        <div class="text-3xl font-extrabold text-primary mb-10 mt-2">
            Admin <span class="text-gray-900">Portal</span>
        </div>

        <nav class="flex-1 space-y-2">
            <a href="dashboard.php" class="nav-link">
                <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-10v10a1 1 0 001 1h3M12 20v-6"></path>
                </svg>
                Dashboard
            </a>
            <div class="nav-link active">
                <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Settings
            </div>
        </nav>

        <div class="pt-6 border-t border-gray-200">
            <a href="dashboard.php?action=logout" class="nav-link text-red-500 hover:bg-red-50 hover:text-red-700">
                <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- 2. MAIN CONTENT AREA -->
    <main class="flex-1 p-8 overflow-y-auto">
        <header class="mb-8 border-b border-gray-200 pb-4 flex justify-between items-center">
            <h1 class="text-4xl font-bold text-gray-800">
                Settings
            </h1>
            <div class="text-sm text-gray-600 bg-white p-2 rounded-lg shadow-sm">
                Current User: <span class="font-semibold text-primary"><?php echo htmlspecialchars($user_data['username']); ?></span>
            </div>
        </header>

        <!-- SETTINGS FORM -->
        <div class="bg-white rounded-xl shadow-2xl p-6 md:p-10 border border-gray-200">

            <?php if ($settings_message['text']):
                $color = $settings_message['type'] === 'success' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300';
            ?>
                <div class="p-4 mb-8 text-sm font-medium rounded-lg border <?php echo $color; ?>" role="alert">
                    <?php echo htmlspecialchars($settings_message['text']); ?>
                </div>
            <?php endif; ?>

            <form action="settings.php" method="POST" class="space-y-12">

                <!-- 1. ACCOUNT & SECURITY SETTINGS (Change Username/Password) -->
                <div class="pb-8">
                    <h2 class="setting-section-title">
                        <svg class="h-7 w-7 mr-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Account Security
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">Update your login credentials. You must provide your current password to confirm and save any changes.</p>

                    <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-6 sm:gap-x-8">

                        <div class="sm:col-span-6">
                            <label for="username" class="block text-sm font-medium text-gray-700">New Username</label>
                            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:ring-primary focus:border-primary">
                        </div>

                        <div class="sm:col-span-3">
                            <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (Optional)</label>
                            <input type="password" name="new_password" id="new_password" placeholder="Leave blank to keep current password"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:ring-primary focus:border-primary">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 opacity-0">Filler</label>
                            <!-- This is just to align the form fields -->
                            <div class="mt-1 block w-full py-2.5 px-3 h-[42px] text-sm text-gray-500"></div>
                        </div>

                        <div class="sm:col-span-6 border-t pt-6">
                            <label for="current_password" class="block text-sm font-medium text-gray-700">
                                <span class="text-red-500 font-bold">*</span> Current Password (Mandatory to Save)
                            </label>
                            <input type="password" name="current_password" id="current_password" required placeholder="Verify your current password"
                                class="mt-1 block w-full border-red-300 ring-1 ring-red-100 rounded-lg shadow-sm py-2.5 px-3 focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>
                </div>

                <!-- 2. FRONTEND CONTENT EDITING -->
                <div class="pt-8 border-t border-gray-100 pb-8">
                    <h2 class="setting-section-title">
                        <svg class="h-7 w-7 mr-3 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Dashboard Content Editing
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">Customize the visible text and titles on your main dashboard page.</p>

                    <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-6 sm:gap-x-8">
                        <div class="sm:col-span-6">
                            <label for="dashboard_title" class="block text-sm font-medium text-gray-700">Main Dashboard Title</label>
                            <input type="text" name="dashboard_title" id="dashboard_title" value="<?php echo htmlspecialchars($user_data['dashboard_title']); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:ring-primary focus:border-primary">
                        </div>
                        <div class="sm:col-span-6">
                            <label for="widget_text" class="block text-sm font-medium text-gray-700">Editable Widget Text</label>
                            <input type="text" name="widget_text" id="widget_text" value="<?php echo htmlspecialchars($user_data['widget_text']); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm py-2.5 px-3 focus:ring-primary focus:border-primary">
                            <p class="mt-1 text-xs text-gray-400">This text appears in the green widget on the main dashboard.</p>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="pt-5 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-md text-sm font-semibold rounded-lg text-white bg-accent hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-accent transition transform hover:scale-[1.01]">
                        <svg class="h-5 w-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Save All Settings
                    </button>
                </div>
            </form>
        </div>

    </main>
</body>

</html>