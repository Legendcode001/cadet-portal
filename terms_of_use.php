<?php
// PHP file for server-side processing, though the content is purely static HTML in this case.
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3BG Terms of Use | Command Agreement</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <!-- Assuming these external files exist for consistent portal styling -->
    <link rel="icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Base styling based on the military theme - MUST match privacy_policy.php */
        body {
            font-family: 'Roboto', sans-serif;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #0d0d0d;
            color: #fff;
            border-bottom: 2px solid #b8860b;
        }

        .logo img,
        .logo-right img {
            height: 50px;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.2s;
        }

        nav a:hover {
            color: #b8860b;
        }

        footer {
            background-color: #0d0d0d;
            color: #888;
            padding: 20px 0;
            text-align: center;
            border-top: 2px solid #333;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .footer-links a {
            color: #b8860b;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-200">

    <!-- HEADER (Copied for consistency) -->
    <header>
        <div class="logo">
            <a href="index.html"><img src="./img/cadetlogo_prev_ui.png" alt="Cadet Logo"></a>
        </div>
        <div class="logo-right">
            <a href="about.html"><img src="./img/image.png" alt="3rd Brigade Logo"></a>
        </div>

    </header>

    <main class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">

            <!-- Title Block: Mimicking a Classified Report Header -->
            <div class="bg-red-800 text-white p-4 mb-8 border-b-4 border-yellow-500 rounded-t-lg">
                <p class="text-xs tracking-widest uppercase font-mono text-center">3rd Brigade Headquarters - Digital
                    Command Center</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-center mt-2 tracking-wider">
                    OPERATIONAL DIRECTIVE 002-B
                </h1>
                <h2 class="text-xl font-semibold text-center italic mt-1">
                    USER COMMAND AGREEMENT (TERMS OF USE)
                </h2>
            </div>

            <div class="bg-gray-800 p-8 md:p-12 rounded-lg shadow-2xl border border-gray-700">
                <p class="text-sm font-mono text-right text-yellow-500 mb-6 border-b border-gray-600 pb-2">
                    // **Effective Date:** 18 October 2024
                </p>

                <!-- Section 1: Acceptance -->
                <section class="mb-10 p-6 border-l-4 border-blue-500 bg-gray-900 rounded-md">
                    <h3 class="text-2xl font-bold text-blue-400 mb-4 uppercase tracking-wider">1. Acceptance of Command
                        (Agreement)</h3>
                    <p class="text-gray-400 mb-4">By accessing and utilizing the 3rd Brigade Headquarters Portal (the "Portal"), you hereby confirm your unconditional acceptance of the terms and conditions outlined in this User Command Agreement. If you do not agree to these terms, you are directed to immediately cease use of the Portal.</p>
                </section>

                <!-- Section 2: User Conduct -->
                <section class="mb-10 p-6 border-l-4 border-blue-500 bg-gray-900 rounded-md">
                    <h3 class="text-2xl font-bold text-blue-400 mb-4 uppercase tracking-wider">2. Rules of Engagement
                        (User Conduct)</h3>
                    <p class="text-gray-400 mb-6">Users of this Portal must adhere to strict military-grade conduct protocols:</p>

                    <ul class="list-disc list-inside space-y-2 ml-4 text-gray-300">
                        <li>**Prohibited Behavior:** Users are strictly forbidden from engaging in unauthorized access attempts, data manipulation, disruptive communication (spamming), or the distribution of malicious code.</li>
                        <li>**Content Integrity:** All information provided by the user must be accurate and truthful. Impersonation of command staff or other cadets is a severe breach of this Agreement.</li>
                        <li>**Legal Compliance:** All activities on the Portal must comply with all local, state, and federal laws and the official standing directives of the Headquarters.</li>
                    </ul>
                </section>

                <!-- Section 3: Intellectual Property -->
                <section class="mb-10 p-6 border-l-4 border-blue-500 bg-gray-900 rounded-md">
                    <h3 class="text-2xl font-bold text-blue-400 mb-4 uppercase tracking-wider">3. Ownership and Copyright
                        (Intellectual Property)</h3>
                    <p class="text-gray-400 mb-4">All content, including text, graphics, logos, images, and code (excluding user-submitted content) is the exclusive property of the 3rd Brigade Headquarters or its licensors and is protected by copyright law.</p>
                    <p class="text-gray-400">Unauthorized reproduction, modification, distribution, or public display of Portal content is strictly prohibited without explicit written permission from the Headquarters Command.</p>
                </section>

                <!-- Section 4: Limitation of Liability -->
                <section class="mb-10 p-6 border-l-4 border-blue-500 bg-gray-900 rounded-md">
                    <h3 class="text-2xl font-bold text-blue-400 mb-4 uppercase tracking-wider">4. Disclaimer of Warranty
                        (Liability)</h3>
                    <p class="text-gray-400 mb-4">The Portal is provided "as is," without any warranties, express or implied. The Headquarters does not guarantee the accuracy, completeness, or reliability of any information or functionality provided herein.</p>
                    <p class="text-gray-400">The Headquarters will not be liable for any damages (direct, indirect, incidental, or consequential) resulting from the use or inability to use the Portal.</p>
                </section>

                <!-- Section 5: Termination -->
                <section class="p-6 border-l-4 border-blue-500 bg-gray-900 rounded-md">
                    <h3 class="text-2xl font-bold text-blue-400 mb-4 uppercase tracking-wider">5. Termination Protocol
                        (Suspension of Access)</h3>
                    <p class="text-gray-400 mb-6">The Headquarters reserves the right to suspend or terminate a user's access to the Portal, without prior notice, if the user violates any of the directives outlined in this Agreement or engages in conduct deemed harmful to the Portal or the organization's reputation.</p>
                </section>

            </div>
        </div>
    </main>

    <!-- FOOTER (Updated to link to new PHP files) -->
    <footer>
        <div class="footer-links">
            <div><a href="contact.php">Contact Us</a></div>
            <div><a href="faqs.html">FAQs</a></div>
            <div><a href="gallery.html">Gallery</a></div>
            <div><a href="privacy_policy.php">Privacy Policy</a></div>
            <div><a href="terms_of_use.php">Terms of Use</a></div>
            <div><a href="developer.html">About the Developer</a></div>
            <div><a href="https://www.facebook.com" target="_blank">Facebook</a></div>
            <div><a href="https://www.instagram.com" target="_blank">Instagram</a></div>
            <div><a href="https://www.tiktok.com" target="_blank">TikTok</a></div>
            <div><a href="mailto:nccnoyonigeria@gmail.com" target="_blank">Gmail</a></div>
        </div>
        <p>&copy; 2024 3rd Brigade Headquarters. All Rights Reserved.</p>
    </footer>
</body>

</html>