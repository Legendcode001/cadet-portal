<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unique Visitors</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js for a professional chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #030712;
            /* gray-950 equivalent */
            color: #e5e7eb;
            /* gray-200 equivalent */
        }

        /* Add subtle glow/shadow to the main container */
        .card-glow {
            box-shadow: 0 0 40px rgba(239, 68, 68, 0.08);
            /* Red 500 glow */
        }

        .chart-container {
            height: 320px;
            width: 100%;
            position: relative;
        }
    </style>
</head>

<body class="antialiased min-h-screen">

    <div class="container mx-auto p-4 sm:p-8 md:p-12 max-w-4xl">
        <header class="text-center mb-10">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-red-500 tracking-tight">Unique Visitors</h1>
            <p class="text-md sm:text-lg text-gray-400 mt-2">Tracking the daily traffic to your portal.</p>
        </header>

        <a href="#" onclick="history.back(); return false;"
            class="flex items-center text-red-400 hover:text-red-300 transition-colors duration-200 mb-6 font-semibold rounded-lg p-2 max-w-fit">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>

        <div class="bg-gray-800 rounded-xl card-glow p-6 sm:p-8 border border-gray-700">

            <!-- Today's Count & Status -->
            <div class="mb-8 p-6 bg-gray-700 rounded-xl shadow-lg flex flex-col items-center justify-center">
                <p class="text-gray-300 text-lg">Unique Visitors Today:</p>
                <strong class="text-gray-100 text-6xl font-extrabold mt-1">
                    <span id="today-count" class="text-red-400">...</span>
                </strong>
                <!-- Status Message Area -->
                <p class="text-sm text-gray-400 mt-3">
                    <span class="inline-block w-2 h-2 rounded-full mr-1 animate-pulse" id="status-indicator"
                        style="background-color: #fcd34d;"></span>
                    <span id="status-message">Loading data...</span>
                </p>
                <p id="api-message" class="text-sm mt-2 font-mono text-green-400"></p>
            </div>


            <h2 class="text-2xl font-bold text-gray-200 mb-4">What are Unique Visitors?</h2>
            <p class="text-gray-400 mb-6">
                A unique visitor is an individual who has visited your website at least once during a specific reporting
                period. This metric helps you understand the size of your audience and your site's reach.
            </p>

            <!-- Manual Update Section -->
            <div class="mt-8 pt-4 border-t border-gray-700">
                <h3 class="text-xl font-semibold text-gray-200 mb-3">Manually Update Today's Visitor Count</h3>
                <div class="flex flex-col sm:flex-row gap-4 items-stretch">
                    <input type="number" id="new-count-input" min="0" step="1" placeholder="Enter new visitor count"
                        class="flex-grow p-3 bg-gray-900 border border-gray-600 rounded-lg text-gray-100 focus:ring-red-500 focus:border-red-500 transition duration-150">
                    <button id="update-count-button"
                        class="w-full sm:w-auto px-6 py-3 bg-red-500 hover:bg-red-600 text-gray-900 font-bold rounded-lg shadow-lg transition duration-200 active:scale-95">
                        Update Count
                    </button>
                </div>
            </div>
            <!-- End Manual Update Section -->

            <h2 class="text-2xl font-bold text-gray-200 mb-4 mt-8">Daily Unique Visitors Trend</h2>
            <div class="chart-container">
                <canvas id="visitorsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Firebase SDKs and Logic -->
    <script type="module">
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
            updateDoc // ADDED for manual updates
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        setLogLevel('Debug');

        // --- Mock/Initial Data for Fallback ---
        const initialData = {
            dailyData: [{
                    date: 'Mon',
                    count: 65
                },
                {
                    date: 'Tue',
                    count: 59
                },
                {
                    date: 'Wed',
                    count: 80
                },
                {
                    date: 'Thu',
                    count: 81
                },
                {
                    date: 'Fri',
                    count: 56
                },
                {
                    date: 'Sat',
                    count: 55
                },
                {
                    date: 'Sun',
                    count: 40
                }
            ],
            todayCount: 65,
            lastUpdated: new Date().toISOString()
        };

        const MOCK_FIREBASE_CONFIG = {
            apiKey: "MOCK_KEY_TO_ALLOW_INIT",
            authDomain: "mock-project.firebaseapp.com",
            projectId: "mock-project-id",
            storageBucket: "mock-project.appspot.com",
            messagingSenderId: "123456789012",
            appId: "1:123456789012:web:abcdef123456"
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
        let app;
        let db;
        let auth;
        let visitorsChart;
        let visitorsDocRef;

        // UI element references
        const todayCountElement = document.getElementById('today-count');
        const statusMessageElement = document.getElementById('status-message');
        const statusIndicatorElement = document.getElementById('status-indicator');
        const apiMessageElement = document.getElementById('api-message');
        const updateButton = document.getElementById('update-count-button');
        const countInput = document.getElementById('new-count-input');

        /**
         * Updates the UI (count, chart, and status message).
         * @param {object} data - The data object containing todayCount and dailyData.
         * @param {string} message - The status message to display.
         * @param {string} color - Tailwind color hex for the indicator (e.g., '#10b981').
         */
        const updateUI = (data, message, color) => {
            const count = data.todayCount !== undefined ? data.todayCount : 'N/A';

            // 1. Update the main count display
            todayCountElement.textContent = count;

            // 2. Update chart data
            const dailyData = data.dailyData || [];
            const labels = dailyData.map(item => item.date);
            const counts = dailyData.map(item => item.count);

            if (visitorsChart) {
                visitorsChart.data.labels = labels;
                visitorsChart.data.datasets[0].data = counts;
                visitorsChart.update();
            }

            // 3. Update status message and indicator color
            statusMessageElement.textContent = message;
            statusIndicatorElement.style.backgroundColor = color;
        };

        /**
         * Seeds demonstration data if the document doesn't exist.
         */
        const seedData = async () => {
            try {
                const docSnap = await getDoc(visitorsDocRef);
                if (!docSnap.exists()) {
                    console.log("Seeding initial data for Unique Visitors.");
                    // Use initialData defined above for seeding
                    await setDoc(visitorsDocRef, initialData);
                }
            } catch (error) {
                // This is the likely point of failure if security rules block WRITE
                console.error("Error seeding data (Check Firestore Write Rules):", error);
            }
        };

        /**
         * Handles the manual update of the visitor count in Firestore.
         */
        const updateVisitorCount = async () => {
            const newCountValue = parseInt(countInput.value, 10);

            if (isNaN(newCountValue) || newCountValue < 0) {
                apiMessageElement.textContent = 'Error: Count must be a non-negative number.';
                apiMessageElement.style.color = '#f87171'; // Red-400
                countInput.focus();
                return;
            }

            // Clear previous message
            apiMessageElement.textContent = 'Updating...';
            apiMessageElement.style.color = '#34d399'; // Green-400

            // Create a copy of the daily data, rotating it to include the new count
            // This logic assumes you are using the mock data structure for an example update
            let newDailyData = [...initialData.dailyData];

            // To keep the chart size fixed, we perform a shift (dropping Mon, adding new value as Sun)
            newDailyData = [{
                    date: 'Mon',
                    count: newDailyData[1].count
                },
                {
                    date: 'Tue',
                    count: newDailyData[2].count
                },
                {
                    date: 'Wed',
                    count: newDailyData[3].count
                },
                {
                    date: 'Thu',
                    count: newDailyData[4].count
                },
                {
                    date: 'Fri',
                    count: newDailyData[5].count
                },
                {
                    date: 'Sat',
                    count: newDailyData[6].count
                },
                {
                    date: 'Sun',
                    count: newCountValue
                } // New value goes to Sunday
            ];

            const updatedFields = {
                todayCount: newCountValue,
                dailyData: newDailyData,
                lastUpdated: new Date().toISOString()
            };

            try {
                await updateDoc(visitorsDocRef, updatedFields);
                apiMessageElement.textContent = `Successfully updated today's count to ${newCountValue}!`;
                apiMessageElement.style.color = '#34d399'; // Green-400
                countInput.value = ''; // Clear input on success

                // Update initialData state for correct rotation logic on subsequent updates
                initialData.dailyData = newDailyData;
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
            app = initializeApp(firebaseConfig);
            db = getFirestore(app);
            auth = getAuth(app);
            visitorsDocRef = doc(db, `artifacts/${appId}/public/data/analytics/visitors`);

            // Authentication
            try {
                if (typeof __initial_auth_token !== 'undefined') {
                    await signInWithCustomToken(auth, __initial_auth_token);
                } else {
                    await signInAnonymously(auth);
                }
            } catch (error) {
                console.error("Firebase Auth Error:", error);
            }

            // Setup Chart
            const ctx = document.getElementById('visitorsChart').getContext('2d');
            visitorsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: initialData.dailyData.map(d => d.date),
                    datasets: [{
                        label: 'Unique Visitors',
                        data: initialData.dailyData.map(d => d.count),
                        borderColor: 'rgb(239, 68, 68)', // red-500
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        hoverBackgroundColor: 'rgba(239, 68, 68, 0.9)',
                        borderWidth: 1,
                        borderRadius: 5,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Visitors This Week',
                            color: '#cbd5e1',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Count: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#94a3b8',
                                precision: 0 // Ensure whole numbers
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });

            // Initially display mock data while waiting for the snapshot
            updateUI(initialData, 'Loading data...', '#fcd34d'); // Yellow-400

            // Seed data after auth, but don't block the UI thread
            await seedData();

            // Attach update handler
            updateButton.addEventListener('click', updateVisitorCount);

            // --- Real-time data listener ---
            onSnapshot(visitorsDocRef, (docSnap) => {
                if (docSnap.exists()) {
                    const data = docSnap.data();
                    // Live data found!
                    updateUI(data, 'Live Data from Firestore', '#10b981'); // Green-500
                    // IMPORTANT: Update initialData state for correct rotation logic in updateVisitorCount
                    initialData.todayCount = data.todayCount;
                    initialData.dailyData = data.dailyData;
                    console.log("Visitor data updated from Firestore.");
                } else {
                    // Document missing, fall back to mock data
                    updateUI(initialData, 'No live data found. Displaying mock data.', '#fbbf24'); // Amber-500
                    console.log("No visitor data found in Firestore! Displaying mock data.");
                }
            }, (error) => {
                // Permission Denied or other Read error
                console.error("Error fetching visitor data (Check Firestore Read Rules):", error);
                updateUI(initialData, 'Permission denied. Displaying mock data.', '#ef4444'); // Red-500
            });
        });
    </script>
</body>

</html>