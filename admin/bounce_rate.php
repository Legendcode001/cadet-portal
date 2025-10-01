<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bounce Rate Explained & Analytics</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js for a professional chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom styles using Inter font and dark mode theme */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #030712;
            /* gray-950 equivalent */
            color: #e5e7eb;
            /* gray-200 equivalent */
        }

        /* Fix for chart container height: Use responsive height, min height on mobile */
        .chart-container {
            height: 320px;
            width: 100%;
            position: relative;
        }

        /* Add subtle glow/shadow to the main container */
        .card-glow {
            box-shadow: 0 0 40px rgba(52, 211, 153, 0.08);
            /* Green 400 glow */
        }
    </style>
</head>

<body class="antialiased min-h-screen">

    <!-- Main Content Area -->
    <div class="container mx-auto p-4 sm:p-8 md:p-12 max-w-4xl">
        <header class="text-center mb-10">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-green-400 tracking-tight">Understanding Bounce Rate</h1>
            <p class="text-md sm:text-lg text-gray-400 mt-2">Metrics to help you improve your portal's performance.</p>
        </header>

        <!-- Back Link -->
        <a href="#" onclick="history.back(); return false;"
            class="flex items-center text-green-400 hover:text-green-300 transition-colors duration-200 mb-6 font-semibold rounded-lg p-2 max-w-fit">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>

        <div class="bg-gray-800 rounded-xl card-glow p-6 sm:p-8 border border-gray-700">
            <h2 class="text-3xl font-bold text-gray-100 mb-4 border-b border-gray-700 pb-2">Bounce Rate Overview</h2>

            <p class="text-gray-400 mb-6">
                Bounce rate is the percentage of single-page sessions on your website. A "bounce" occurs when a user visits
                one page and then exits without triggering any other interactions (like clicking an internal link, filling a
                form, or navigating to another page).
            </p>

            <!-- Current Rate Card -->
            <div class="mb-8 p-6 bg-gray-700 rounded-xl shadow-lg flex flex-col items-center justify-center">
                <p class="text-gray-300 text-lg">Current Bounce Rate:</p>
                <strong class="text-gray-100 text-6xl font-extrabold mt-1">
                    <span id="current-rate" class="text-green-400">...</span>%
                </strong>
                <!-- Performance Status Indicator -->
                <p id="rate-performance" class="text-xl font-semibold mt-2 px-3 py-1 rounded-full"></p>
                <!-- Status Message Area -->
                <p class="text-sm text-gray-400 mt-3">
                    <span class="inline-block w-2 h-2 rounded-full mr-1 animate-pulse" id="status-indicator"
                        style="background-color: #fcd34d;"></span>
                    <span id="status-message">Loading data...</span>
                </p>
                <p id="api-message" class="text-sm mt-2 font-mono text-red-400"></p>
            </div>

            <!-- Manual Update Section -->
            <div class="mt-8 pt-4 border-t border-gray-700">
                <h3 class="text-xl font-semibold text-gray-200 mb-3">Manually Update Bounce Rate</h3>
                <div class="flex flex-col sm:flex-row gap-4 items-stretch">
                    <input type="number" id="new-rate-input" min="0" max="100" step="0.1" placeholder="Enter new rate (0-100)"
                        class="flex-grow p-3 bg-gray-900 border border-gray-600 rounded-lg text-gray-100 focus:ring-green-500 focus:border-green-500 transition duration-150">
                    <button id="update-rate-button"
                        class="w-full sm:w-auto px-6 py-3 bg-green-500 hover:bg-green-600 text-gray-900 font-bold rounded-lg shadow-lg transition duration-200 active:scale-95">
                        Update Rate
                    </button>
                </div>
            </div>
            <!-- End Manual Update Section -->

            <h2 class="text-2xl font-bold text-gray-200 mb-4 mt-8">Weekly Bounce Rate Trend</h2>

            <!-- Chart Container -->
            <div class="chart-container">
                <canvas id="bounceRateChart"></canvas>
            </div>

            <!-- Interpretation Section -->
            <div class="mt-8 pt-6 border-t border-gray-700">
                <h3 class="text-xl font-semibold text-gray-100 mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-green-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    How to Reduce Bounce Rate
                </h3>
                <ul class="space-y-3 text-gray-400 list-disc list-inside ml-4">
                    <li>**Improve Page Load Speed:** Users leave slow pages quickly. Aim for a load time under 3 seconds.
                    </li>
                    <li>**Enhance Content Quality:** Ensure the page content directly matches the user's expectation (e.g.,
                        the search result title they clicked).</li>
                    <li>**Mobile Optimization:** Check that the design, readability, and interaction elements work perfectly
                        on all mobile devices.</li>
                    <li>**Clear Calls to Action (CTAs):** Provide obvious next steps, links, or internal navigation to
                        encourage users to explore further.</li>
                    <li>**Optimize Readability:** Use subheadings, short paragraphs, and bullet points to break up long
                        blocks of text.</li>
                </ul>
            </div>

        </div>
    </div>

    <!-- Firebase SDKs and Logic -->
    <script type="module">
        // Import necessary Firebase modules
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import {
            getAuth,
            signInAnonymously,
            signInWithCustomToken
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import {
            getFirestore,
            onSnapshot,
            setDoc,
            doc,
            getDoc,
            setLogLevel,
            updateDoc // ADDED: For manual data update
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        // Set log level for debugging
        setLogLevel('Debug');

        /**
         * Fallback configuration used when environment variables are missing.
         */
        const MOCK_FIREBASE_CONFIG = {
            apiKey: "MOCK_KEY_TO_ALLOW_INIT",
            authDomain: "mock-project.firebaseapp.com",
            projectId: "mock-project-id", // The projectId must be set for initializeApp()
            storageBucket: "mock-project.appspot.com",
            messagingSenderId: "123456789012",
            appId: "1:123456789012:web:abcdef123456"
        };

        // --- Mock/Initial Data for Fallback ---
        const initialData = {
            weeklyData: [{
                    week: 'Week 1',
                    rate: 61.2
                },
                {
                    week: 'Week 2',
                    rate: 58.0
                },
                {
                    week: 'Week 3',
                    rate: 55.4
                },
                {
                    week: 'Week 4',
                    rate: 54.5
                }
            ],
            currentRate: 54.5,
            lastUpdated: new Date().toISOString()
        };
        // --- End Mock/Initial Data ---

        // Determine configuration based on environment availability
        const envConfigRaw = typeof __firebase_config !== 'undefined' ? __firebase_config : null;
        let firebaseConfig;

        try {
            firebaseConfig = envConfigRaw ? JSON.parse(envConfigRaw) : MOCK_FIREBASE_CONFIG;
            if (!firebaseConfig.projectId) {
                firebaseConfig = MOCK_FIREBASE_CONFIG;
            }
        } catch (e) {
            console.warn("JSON parsing failed for __firebase_config. Using mock configuration.", e);
            firebaseConfig = MOCK_FIREBASE_CONFIG;
        }

        const appId = typeof __app_id !== 'undefined' ? __app_id : 'demo-app';
        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        const auth = getAuth(app);


        /**
         * Authenticates the user using a custom token or anonymously.
         */
        const signIn = async () => {
            try {
                await new Promise(resolve => setTimeout(resolve, 50));

                if (typeof __initial_auth_token !== 'undefined') {
                    await signInWithCustomToken(auth, __initial_auth_token);
                } else {
                    await signInAnonymously(auth);
                }
                console.log("Firebase Sign-In Successful.");
            } catch (error) {
                console.error("Firebase Auth Error: Failed to sign in.", error);
                document.getElementById('current-rate').textContent = 'AUTH FAIL';
            }
        };

        /**
         * Seeds demonstration data if the document doesn't exist.
         */
        const seedData = async () => {
            const analyticsRef = doc(db, `artifacts/${appId}/public/data/analytics/bounceRate`);
            try {
                const docSnap = await getDoc(analyticsRef);
                if (!docSnap.exists()) {
                    console.log("Seeding initial data for Bounce Rate.");
                    // Use initialData defined above for seeding
                    await setDoc(analyticsRef, initialData);
                }
            } catch (error) {
                // This is the likely point of failure if security rules block WRITE
                console.error("Error seeding data (Check Firestore Write Rules):", error);
            }
        };

        let bounceChart;
        const bounceRateDocRef = doc(db, `artifacts/${appId}/public/data/analytics/bounceRate`);

        // UI element references
        const currentRateElement = document.getElementById('current-rate');
        const statusMessageElement = document.getElementById('status-message');
        const statusIndicatorElement = document.getElementById('status-indicator');
        const performanceElement = document.getElementById('rate-performance');
        const apiMessageElement = document.getElementById('api-message');
        const updateButton = document.getElementById('update-rate-button');
        const rateInput = document.getElementById('new-rate-input');


        /**
         * Determines the performance status based on the bounce rate value.
         * @param {number} rate - The current bounce rate percentage.
         * @returns {{status: string, color: string}}
         */
        const getPerformanceStatus = (rate) => {
            let status = {
                status: 'Unknown',
                color: 'bg-gray-500 text-white'
            }; // Default

            if (rate <= 40) {
                status = {
                    status: 'Excellent',
                    color: 'bg-green-500 text-white'
                }; // Green
            } else if (rate <= 55) {
                status = {
                    status: 'Average',
                    color: 'bg-yellow-500 text-gray-900'
                }; // Yellow
            } else if (rate <= 70) {
                status = {
                    status: 'Needs Attention',
                    color: 'bg-orange-500 text-white'
                }; // Orange
            } else {
                status = {
                    status: 'Poor',
                    color: 'bg-red-500 text-white'
                }; // Red
            }

            return status;
        };


        /**
         * Updates the UI (rate, chart, status message, and performance rating).
         * @param {object} data - The data object containing currentRate and weeklyData.
         * @param {string} message - The status message to display.
         * @param {string} color - Tailwind color hex for the indicator (e.g., '#10b981').
         */
        const updateUI = (data, message, color) => {
            const rate = data.currentRate;

            // Update main rate and set color based on rate (dynamic)
            currentRateElement.textContent = rate !== undefined ? rate.toFixed(1) : 'N/A';
            currentRateElement.className = 'text-gray-100 text-6xl font-extrabold mt-1'; // Reset class

            // Update performance status
            const {
                status: performanceStatus,
                color: performanceColor
            } = getPerformanceStatus(rate);
            performanceElement.textContent = performanceStatus;
            performanceElement.className = `${performanceColor} text-xl font-semibold mt-2 px-3 py-1 rounded-full`;

            // Update chart data
            const weeklyData = data.weeklyData || [];
            const labels = weeklyData.map(item => item.week);
            const rates = weeklyData.map(item => item.rate);

            if (bounceChart) {
                bounceChart.data.labels = labels;
                bounceChart.data.datasets[0].data = rates;
                bounceChart.update();
            }

            // Update status message and indicator color
            statusMessageElement.textContent = message;
            statusIndicatorElement.style.backgroundColor = color;
        };

        /**
         * Handles the manual update of the bounce rate in Firestore.
         */
        const updateBounceRate = async () => {
            const newRateValue = parseFloat(rateInput.value);

            if (isNaN(newRateValue) || newRateValue < 0 || newRateValue > 100) {
                apiMessageElement.textContent = 'Error: Rate must be between 0 and 100.';
                apiMessageElement.style.color = '#f87171'; // Red-400
                rateInput.focus();
                return;
            }

            // Clear previous message
            apiMessageElement.textContent = 'Updating...';
            apiMessageElement.style.color = '#34d399'; // Green-400

            // Create a copy of the weekly data, rotating it to include the new rate
            // This logic assumes you are using the mock data structure for an example update
            let newWeeklyData = [...initialData.weeklyData];

            // 1. Drop the oldest week (Week 1)
            newWeeklyData.shift();

            // 2. Rename the remaining weeks
            newWeeklyData.forEach((item, index) => {
                item.week = `Week ${index + 2}`;
            });

            // 3. Add the new week's data as the latest (Week 4 becomes Week 5, new data is Week 4)
            // To keep the chart small (4 weeks), we'll shift the labels back:
            newWeeklyData = [{
                week: 'Week 2',
                rate: newWeeklyData[0].rate
            }, {
                week: 'Week 3',
                rate: newWeeklyData[1].rate
            }, {
                week: 'Week 4',
                rate: newWeeklyData[2].rate
            }, {
                week: 'Week 5',
                rate: newRateValue
            }, ];

            // To keep the chart labels fixed at 4, we perform a simplified shift:
            const lastData = newWeeklyData[3];
            newWeeklyData = [{
                    week: 'Week 1',
                    rate: initialData.weeklyData[1].rate
                },
                {
                    week: 'Week 2',
                    rate: initialData.weeklyData[2].rate
                },
                {
                    week: 'Week 3',
                    rate: initialData.weeklyData[3].rate
                },
                {
                    week: 'Week 4',
                    rate: newRateValue
                }
            ];

            const updatedFields = {
                currentRate: newRateValue,
                weeklyData: newWeeklyData,
                lastUpdated: new Date().toISOString()
            };

            try {
                await updateDoc(bounceRateDocRef, updatedFields);
                apiMessageElement.textContent = `Successfully updated to ${newRateValue.toFixed(1)}%!`;
                apiMessageElement.style.color = '#34d399'; // Green-400
                rateInput.value = ''; // Clear input on success
            } catch (error) {
                console.error("Error updating document (Check Firestore Write Rules):", error);
                apiMessageElement.textContent = `ERROR: Failed to update. Check console for details.`;
                apiMessageElement.style.color = '#f87171'; // Red-400
            } finally {
                // Clear message after a short delay
                setTimeout(() => {
                    apiMessageElement.textContent = '';
                }, 4000);
            }
        };


        // --- Main Execution Logic ---
        window.addEventListener('DOMContentLoaded', async () => {

            const chartCanvas = document.getElementById('bounceRateChart');
            if (!chartCanvas || !currentRateElement || !statusMessageElement || !statusIndicatorElement) {
                console.error("Error: Required HTML elements not found.");
                return;
            }

            // Authentication and Data Seeding
            await signIn();
            await seedData();

            // Setup chart
            const ctx = chartCanvas.getContext('2d');
            bounceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Bounce Rate (%)',
                        data: [],
                        borderColor: 'rgb(52, 211, 153)', // green-400
                        backgroundColor: 'rgba(52, 211, 153, 0.2)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: 'rgb(52, 211, 153)',
                        pointBorderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                            labels: {
                                color: '#e2e8f0',
                            }
                        },
                        title: {
                            display: true,
                            text: 'Last 4 Weeks Bounce Rate Trend',
                            color: '#cbd5e1',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 30, // Adjusted min for better visual range with performance tiers
                            max: 100,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#94a3b8',
                                callback: (value) => value + "%"
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });

            // Attach update handler
            updateButton.addEventListener('click', updateBounceRate);

            // --- Real-time data listener for the chart ---
            onSnapshot(bounceRateDocRef, (docSnap) => {
                if (docSnap.exists()) {
                    const data = docSnap.data();
                    // Live data found!
                    updateUI(data, 'Live Data from Firestore', '#10b981'); // Green-500
                    // Update initialData state for correct rotation logic in updateBounceRate
                    initialData.currentRate = data.currentRate;
                    initialData.weeklyData = data.weeklyData;
                    console.log("Bounce rate data updated from Firestore.");

                } else {
                    // Document missing, fall back to mock data
                    updateUI(initialData, 'No live data found. Displaying mock data.', '#fbbf24'); // Amber-500
                    console.log("No bounce rate data found in Firestore! Displaying mock data.");
                }
            }, (error) => {
                // Permission Denied error
                console.error("Error fetching bounce rate (Check Firestore Read Rules):", error);
                updateUI(initialData, 'Permission denied. Displaying mock data.', '#ef4444'); // Red-500
            });

            // Initially display mock data while waiting for the snapshot
            updateUI(initialData, 'Loading data...', '#fcd34d'); // Yellow-400
        });
    </script>
</body>

</html>