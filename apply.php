<?php
// =================== DATABASE CONNECTION ===================
// NOTE: This connection is not used directly in this file but
// is here for context. The actual processing happens in process_application.php.
$host = "localhost";
$user = "root";
$pass = "";
$db = "cadetportal";

// Initialize $conn to null and $conn_error to false
$conn = null;
$conn_error = false;
$error_message = "";

// Use try...catch for safer connection handling
try {
  $conn = new mysqli($host, $user, $pass, $db);

  if ($conn->connect_error) {
    // Throw an exception if the connection object fails post-instantiation
    throw new Exception("MySQLi Connect Error: " . $conn->connect_error);
  }
} catch (Exception $e) {
  // Catch the error, set the flag, and store the message
  $conn_error = true;
  $error_message = "Database connection failed: " . $e->getMessage();
  // Note: In a production app, you should log $e->getMessage() and show a user-friendly message.
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadet Application Form - 3rd Brigade Headquarters</title>
  <script src="https://cdn.tailwindcss.com"></script>

</head>

<body>

  <div id="page-loader">
    <img src="./img/cadetlogo_prev_ui.png" alt="Loading..." class="loader-icon">
  </div>

  <header>
    <div class="logo">
      <a href="index.html"><img src="./img/cadetlogo_prev_ui.png" alt="Cadet Logo"></a>
    </div>
  </header>

  <div class="page">
    <?php if ($conn_error): ?>
      <div class="content-box" style="background: #ffcccc; color: #cc0000; border: 1px solid #cc0000; padding: 20px; text-align: center; margin-top: 50px;">
        <h2>🚨 System Error 🚨</h2>
        <p>We are currently experiencing technical difficulties. The application form is unavailable.</p>
        <p style="font-size: 0.9em; margin-top: 15px;">**Details:** <?= htmlspecialchars($error_message) ?></p>
        <p style="font-size: 0.9em;">Please check your database connection settings.</p>
      </div>
    <?php else: ?>
      <div class="content-box">
        <form class="form-card" id="applicationForm" action="process_application.php" method="POST" enctype="multipart/form-data">
          <h1>3rd Brigade Application Form</h1>
          <p>Please fill out the form carefully. All fields marked * are required.</p>

          <div class="grid">
            <div class="field">
              <label for="surname">Surname *</label>
              <input type="text" id="surname" name="surname" required>
            </div>

            <div class="field full">
              <label for="passport">Upload Passport *</label>
              <input type="file" id="passport" name="passport" accept="image/*" required>
            </div>
          </div>
          <div class="fee-note">
            <p>All applicants are required to pay a non-refundable fee of **N10,000** for this form.</p>
          </div>

          <button type="submit" class="submit-btn">Proceed to Payment</button>

          <footer>© <?php echo date("Y"); ?> 3rd Brigade Headquarters. All Rights Reserved.</footer>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <script>
    // ... (JavaScript remains the same) ...
    document.addEventListener('DOMContentLoaded', function() {
      const surname = document.getElementById('surname');
      const firstname = document.getElementById('firstname');
      const lastname = document.getElementById('lastname');
      const declaration = document.getElementById('declaration');

      function updateDeclaration() {
        const s = surname.value.trim() || "______________";
        const f = firstname.value.trim() || "______________";
        const l = lastname.value.trim() || "______________";
        declaration.value = `${s} ${f} ${l}, hereby declare that the above information provided rightfully belongs to me, and I agree that any wrong information provided here will lead to disqualification of my application.`;
      }

      surname.addEventListener('input', updateDeclaration);
      firstname.addEventListener('input', updateDeclaration);
      lastname.addEventListener('input', updateDeclaration);
      updateDeclaration();
    });
  </script>
</body>

</html>