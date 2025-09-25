<?php
// db.php
// Manages the MySQL database connection.

$host = "localhost";
$user = "root"; // Your MySQL username
$pass = "";     // Your MySQL password
$db = "cadetportal"; // The database name

// Establish the database connection
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection errors and terminate the script if a problem occurs
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
