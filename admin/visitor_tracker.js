// Add this script to your PUBLIC website (the one visitors see)

import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
import { getFirestore, doc, setDoc, increment } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

// --- Use the same Firebase config as your other files ---
const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');
const app = initializeApp(firebaseConfig);
const db = getFirestore(app);

const trackUniqueVisitor = async () => {
    const today = new Date().toISOString().split('T')[0]; // Get date as 'YYYY-MM-DD'
    const lastVisit = localStorage.getItem('lastVisit');

    // If the user hasn't visited today
    if (lastVisit !== today) {
        console.log("New unique visit detected. Updating database.");

        // Get a reference to the document
        const visitorDocRef = doc(db, `artifacts/${appId}/public/data/analytics`, 'visitors');

        // Use the 'increment' function to safely increase the count
        // This also creates the document with a 'todayCount' of 1 if it doesn't exist
        try {
            await setDoc(visitorDocRef, {
                todayCount: increment(1)
            }, { merge: true }); // 'merge: true' prevents overwriting other fields

            // Mark that the user has visited today
            localStorage.setItem('lastVisit', today);
        } catch (error) {
            console.error("Error updating visitor count:", error);
        }
    } else {
        console.log("Visitor already counted today.");
    }
};

trackUniqueVisitor();
