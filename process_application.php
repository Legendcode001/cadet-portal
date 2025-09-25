<?php
// process_application.php
// Handles form submission, file upload, and database insertion.

// =================== DEBUGGING ===================
// Enable error reporting for better debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =================== DATABASE CONNECTION ===================
// Assuming your database connection code is in a separate file (e.g., db.php)
// It's a good practice to include it to avoid code repetition.
$host = "localhost";
$user = "root";
$pass = "";
$db = "cadetportal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// =================== FORM & FILE PROCESSING ===================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $surname = $_POST['surname'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $marital_status = $_POST['marital_status'];
    $dob = $_POST['dob'];
    $sex = $_POST['sex'];
    $age = $_POST['age'];
    $nationality = $_POST['nationality'];
    $religion = $_POST['religion'];
    $hometown = $_POST['hometown'];
    $state = $_POST['state'];
    $lga = $_POST['lga'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $qualifications = $_POST['qualifications'];
    $referee1 = $_POST['referee1'];
    $referee1_address = $_POST['referee1_address'];
    $referee2 = $_POST['referee2'];
    $referee2_address = $_POST['referee2_address'];
    $challenges = $_POST['challenges'];
    $declaration = $_POST['declaration'];

    // Handle file upload
    $target_dir = "uploads/";
    $file_name = uniqid() . '-' . basename($_FILES["passport"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["passport"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (in this case, it's 10MB)
    if ($_FILES["passport"]["size"] > 10000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        echo "Sorry, only JPG, JPEG & PNG files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["passport"]["tmp_name"], $target_file)) {
            // File uploaded successfully, now insert data into database
            // SQL query to insert data. Using prepared statements for security.
            $sql = "INSERT INTO applications (surname, firstname, lastname, marital_status, dob, sex, age, nationality, religion, hometown, state, lga, address, phone, email, qualifications, referee1, referee1_address, referee2, referee2_address, challenges, declaration, passport_filename) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssissssssssssssssss", $surname, $firstname, $lastname, $marital_status, $dob, $sex, $age, $nationality, $religion, $hometown, $state, $lga, $address, $phone, $email, $qualifications, $referee1, $referee1_address, $referee2, $referee2_address, $challenges, $declaration, $target_file);

            if ($stmt->execute()) {
                // Get the ID of the last inserted record
                $applicationId = $conn->insert_id;

                // Display a loading page and then redirect using JavaScript
                echo '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link rel=" icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
                    <link rel=" icon" href="favicon-16x16.png" sizes="16x16" type="image/png">
                    <link rel=" icon" href="favicon-32x32.png" sizes="32x32" type="image/png">
                    <link rel=" icon" href="favicon-64x64.png" sizes="64x64" type="image/png">
                    <title>Processing Application...</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f4f4f4;
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            margin: 0;
                            text-align: center;
                            flex-direction: column;
                        }
                        .container {
                            background-color: #fff;
                            padding: 30px 50px;
                            border-radius: 10px;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                        }
                        h1 {
                            color: #28a745;
                        }
                        .loader {
                            border: 8px solid #f3f3f3;
                            border-radius: 50%;
                            border-top: 8px solid #3498db;
                            width: 60px;
                            height: 60px;
                            animation: spin 2s linear infinite;
                            margin: 20px auto;
                        }
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>Your Application Has Been Saved!</h1>
                        <p>Proceeding to payment page...</p>
                        <div class="loader"></div>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.href = "payment.php?id=' . $applicationId . '";
                        }, 2000); // 2000 milliseconds = 2 seconds
                    </script>
                </body>
                </html>
                ';
                exit(); // Important to stop further script execution
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
} else {
    // If the form wasn't submitted via POST, redirect them back to the form.
    header("Location: apply.php");
    exit();
}

$conn->close();
