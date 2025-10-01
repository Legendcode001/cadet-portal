<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registrations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            @apply bg-gray-950 text-gray-200;
        }

        .container-padding {
            @apply px-4 py-8 sm:px-6 lg:px-8;
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

    <!-- Firebase SDKs -->
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
            collection,
            onSnapshot,
            setDoc,
            doc,
            serverTimestamp,
            query
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        // Firebase Setup
        const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
        const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        const auth = getAuth(app);

        const signIn = async () => {
            try {
                if (typeof __initial_auth_token !== 'undefined') {
                    await signInWithCustomToken(auth, __initial_auth_token);
                } else {
                    await signInAnonymously(auth);
                }
            } catch (error) {
                console.error("Firebase Auth Error:", error);
            }
        };
        signIn();

        // Seed data for demonstration
        const seedData = async () => {
            const usersCollection = collection(db, `artifacts/${appId}/public/data/users`);
            const snapshot = await getDocs(usersCollection);
            if (snapshot.empty) {
                console.log("Seeding data for User Registrations.");
                const sampleUsers = [{
                        name: 'David Lee',
                        email: 'david.lee@example.com',
                        registeredAt: serverTimestamp()
                    },
                    {
                        name: 'Sarah Jones',
                        email: 'sarah.jones@example.com',
                        registeredAt: serverTimestamp()
                    }
                ];
                sampleUsers.forEach(async (user) => {
                    const newDocRef = doc(usersCollection);
                    await setDoc(newDocRef, user);
                });
            }
        };
        seedData();

        // Real-time data listener
        const tableBody = document.getElementById('users-table-body');
        const usersCollectionRef = collection(db, `artifacts/${appId}/public/data/users`);
        onSnapshot(query(usersCollectionRef), (snapshot) => {
            tableBody.innerHTML = ''; // Clear table
            snapshot.forEach((doc) => {
                const data = doc.data();
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="py-4 whitespace-nowrap">${data.name}</td>
                    <td class="py-4 whitespace-nowrap">${data.email}</td>
                    <td class="py-4 whitespace-nowrap">${data.registeredAt ? new Date(data.registeredAt.seconds * 1000).toLocaleDateString() : 'N/A'}</td>
                `;
                tableBody.appendChild(row);
            });
        }, (error) => {
            console.error("Error fetching users:", error);
        });
    </script>

    <div class="container mx-auto p-4 sm:p-8 md:p-12">
        <header class="text-center mb-10">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-yellow-400 tracking-tight">User Registrations</h1>
            <p class="text-md sm:text-lg text-gray-400 mt-2">A complete list of everyone who has registered on the
                portal.</p>
        </header>

        <a href="#" onclick="history.back()"
            class="flex items-center text-yellow-400 hover:text-yellow-500 transition-colors duration-200 mb-6">
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
                            Name</th>
                        <th scope="col" class="px-6 py-3 text-sm font-semibold text-gray-400 uppercase tracking-wider">
                            Email</th>
                        <th scope="col" class="px-6 py-3 text-sm font-semibold text-gray-400 uppercase tracking-wider">
                            Date Registered</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="bg-gray-800 divide-y divide-gray-700">
                    <!-- Data will be populated here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>