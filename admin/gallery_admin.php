<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gallery Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .admin-card {
            background-color: #1a202c;
            /* gray-900 */
            border: 1px solid #2d3748;
            /* gray-800 */
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col items-center p-6">

    <!-- Header & Navigation -->
    <header class="w-full max-w-4xl bg-gray-800 shadow-xl rounded-xl p-6 mb-8">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i data-lucide="shield" class="w-8 h-8 text-red-500"></i>
                <h1 class="text-3xl font-bold text-white">Gallery Admin Center</h1>
            </div>
            <a href="gallery.html"
                class="flex items-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white font-medium transition-colors shadow-md transform hover:scale-[1.02]">
                <i data-lucide="images" class="w-5 h-5"></i>
                <span>View Gallery</span>
            </a>
        </div>
        <p id="auth-status" class="mt-2 text-sm text-yellow-400"></p>
    </header>

    <main id="admin-content" class="w-full max-w-4xl">
        <!-- Image Upload Form Card -->
        <section class="admin-card rounded-xl p-8 shadow-2xl">
            <h2 class="text-2xl font-semibold mb-6 text-red-400 border-b border-gray-700 pb-3">
                <i data-lucide="upload" class="w-6 h-6 inline-block mr-2"></i>Upload New Gallery Item
            </h2>

            <form id="upload-form" onsubmit="event.preventDefault(); handleImageUpload();">
                <!-- Image File Input (Simulated) -->
                <div class="mb-6">
                    <label for="image-file" class="block text-sm font-medium text-gray-300 mb-2">Select Image File</label>
                    <input type="file" id="image-file" required accept="image/*"
                        class="w-full text-sm text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-500 file:text-white
                        hover:file:bg-indigo-600 cursor-pointer">
                    <p class="mt-1 text-xs text-gray-500">Note: Actual file upload is simulated, but the file name is captured for metadata.</p>
                </div>

                <!-- Caption -->
                <div class="mb-4">
                    <label for="image-caption" class="block text-sm font-medium text-gray-300 mb-1">Caption / Description</label>
                    <input type="text" id="image-caption" required
                        class="w-full px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-red-500 focus:border-red-500"
                        placeholder="e.g., Tactical Field Training Day 3">
                </div>

                <div class="mb-6 flex space-x-4">
                    <!-- Year -->
                    <div class="flex-1">
                        <label for="image-year" class="block text-sm font-medium text-gray-300 mb-1">Year</label>
                        <input type="number" id="image-year" required min="2000" max="2100"
                            class="w-full px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-red-500 focus:border-red-500"
                            placeholder="e.g., 2025">
                    </div>
                    <!-- Category -->
                    <div class="flex-1">
                        <label for="image-category" class="block text-sm font-medium text-gray-300 mb-1">Category (e.g., Drilling, Ceremonies)</label>
                        <input type="text" id="image-category" required
                            class="w-full px-4 py-2 bg-gray-700 text-white border border-gray-600 rounded-lg focus:ring-red-500 focus:border-red-500"
                            placeholder="e.g., Drilling">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="submit-btn"
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg transform hover:scale-[1.02] disabled:opacity-50"
                        disabled>
                        <span id="submit-text" class="inline-flex items-center">
                            <i data-lucide="save" class="w-5 h-5 mr-2"></i> Add to Gallery
                        </span>
                    </button>
                </div>
            </form>

            <p id="upload-message" class="mt-4 text-center text-green-400 hidden"></p>
        </section>
    </main>

    <!-- JavaScript for Firebase and Admin Logic -->
    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import {
            getAuth,
            signInAnonymously,
            signInWithCustomToken,
            onAuthStateChanged
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import {
            getFirestore,
            collection,
            addDoc,
            serverTimestamp,
            setLogLevel
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        // --- Global Variables ---
        setLogLevel('Debug');
        let firebaseApp, db, auth;
        let isAdmin = false;

        // DOM Elements
        const authStatus = document.getElementById('auth-status');
        const uploadForm = document.getElementById('upload-form');
        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const uploadMessage = document.getElementById('upload-message');
        const imageFile = document.getElementById('image-file');
        const imageCaption = document.getElementById('image-caption');
        const imageYear = document.getElementById('image-year');
        const imageCategory = document.getElementById('image-category');

        // --- Configuration Setup ---
        const MOCK_FIREBASE_CONFIG = {
            apiKey: "MOCK_KEY_TO_ALLOW_INIT",
            authDomain: "mock-project.firebaseapp.com",
            projectId: "mock-project-id",
            storageBucket: "mock-project.appspot.com",
            appId: "1:123456789012:web:abcdef123456"
        };

        const envConfigRaw = typeof __firebase_config !== 'undefined' ? __firebase_config : null;
        let firebaseConfig;

        try {
            firebaseConfig = envConfigRaw ? JSON.parse(envConfigRaw) : MOCK_FIREBASE_CONFIG;
            if (!firebaseConfig.projectId) {
                firebaseConfig = MOCK_FIREBASE_CONFIG;
            }
        } catch (e) {
            firebaseConfig = MOCK_FIREBASE_CONFIG;
        }

        const appId = typeof __app_id !== 'undefined' ? __app_id : 'demo-app';
        const GALLERY_COLLECTION_PATH = `artifacts/${appId}/public/data/gallery-images`;

        // --- Utility Functions ---
        const uuidv4 = () => {
            return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
            );
        }

        // --- Firebase & Auth Initialization ---
        const initFirebase = async () => {
            try {
                authStatus.textContent = 'Initializing connection...';

                firebaseApp = initializeApp(firebaseConfig);
                db = getFirestore(firebaseApp);
                auth = getAuth(firebaseApp);

                // Check for custom auth token (Admin Check)
                if (typeof __initial_auth_token !== 'undefined') {
                    await signInWithCustomToken(auth, __initial_auth_token);
                    isAdmin = true;
                    authStatus.textContent = `Authenticated as Admin (User ID: ${auth.currentUser.uid}).`;
                    authStatus.classList.remove('text-yellow-400');
                    authStatus.classList.add('text-green-400');
                    submitBtn.disabled = false;
                } else {
                    await signInAnonymously(auth);
                    authStatus.textContent = 'Unauthorized. Log in to manage content.';
                    authStatus.classList.remove('text-yellow-400');
                    authStatus.classList.add('text-red-400');
                    // Lock the form
                    submitBtn.disabled = true;
                    uploadForm.classList.add('opacity-50', 'pointer-events-none');
                }

                // Set default year
                imageYear.value = new Date().getFullYear();

            } catch (error) {
                authStatus.textContent = `Initialization Error: ${error.message}`;
                authStatus.classList.add('text-red-400');
                submitBtn.disabled = true;
                console.error("Firebase Initialization Error:", error);
            }
        };

        /**
         * Simulates the image upload process and saves metadata to Firestore.
         */
        window.handleImageUpload = async function() {
            if (!isAdmin || submitBtn.disabled) return;

            // 1. Get Metadata
            const caption = imageCaption.value.trim();
            const year = imageYear.value.trim();
            const category = imageCategory.value.trim();
            const file = imageFile.files[0];

            if (!file || !caption || !year || !category) {
                uploadMessage.textContent = "Please fill all fields and select a file.";
                uploadMessage.classList.remove('hidden');
                uploadMessage.classList.add('text-red-400');
                return;
            }

            // 2. Disable form and show loading
            submitBtn.disabled = true;
            submitText.innerHTML = '<i data-lucide="loader" class="w-5 h-5 mr-2 animate-spin"></i> Uploading...';
            uploadMessage.classList.add('hidden');
            uploadMessage.classList.remove('text-green-400', 'text-red-400');

            try {
                // --- 3. SIMULATED FILE UPLOAD & STORAGE RESPONSE ---
                // In a real application, this is where you would call your Storage API (e.g., Firebase Storage)
                // and wait for the public URL or a unique ID.
                const fileName = file.name.replace(/\s/g, '_');
                const uniqueId = uuidv4();

                // We generate the required "uploaded:filename-id" format
                const simulatedStorageUrl = `uploaded:${fileName}-${uniqueId}`;
                // --- END SIMULATED UPLOAD ---

                // 4. Save Metadata to Firestore
                const newImageRef = await addDoc(collection(db, GALLERY_COLLECTION_PATH), {
                    src: simulatedStorageUrl,
                    caption: caption,
                    year: year,
                    category: category,
                    timestamp: serverTimestamp() // Add creation timestamp
                });

                // 5. Success
                uploadMessage.textContent = `Image "${caption}" added successfully! ID: ${newImageRef.id}`;
                uploadMessage.classList.remove('hidden');
                uploadMessage.classList.add('text-green-400');

                uploadForm.reset();
                imageYear.value = new Date().getFullYear(); // Reset year field

            } catch (e) {
                // 6. Error Handling
                console.error("Error saving image metadata:", e);
                uploadMessage.textContent = `Error saving data: ${e.message}. Check console for details.`;
                uploadMessage.classList.remove('hidden');
                uploadMessage.classList.add('text-red-400');
            } finally {
                // 7. Re-enable form
                submitBtn.disabled = false;
                submitText.innerHTML = '<i data-lucide="save" class="w-5 h-5 mr-2"></i> Add to Gallery';
                // Re-render lucide icons
                lucide.createIcons();
            }
        };

        // --- Initial Load ---
        document.addEventListener('DOMContentLoaded', () => {
            initFirebase();
            lucide.createIcons(); // Initialize icons on load
        });
    </script>

</body>

</html>