<?php
// =================== DATABASE CONNECTION ===================
// NOTE: This connection is not used directly in this file but
// is here for context. The actual processing happens in process_application.php.
$host = "localhost";
$user = "root";
$pass = "";
$db = "cadetportal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cadet Application Form - 3rd Brigade Headquarters</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
  <link rel="icon" href="favicon-16x16.png" sizes="16x16" type="image/png">
  <link rel="icon" href="favicon-32x32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="favicon-64x64.png" sizes="64x64" type="image/png">

  <style>
    :root {
      --brand-dark: #002b20;
      --brand-mid: #00796b;
      --text: #1f2937;
      --muted: #6b7280;
      --card: #ffffff;
      --bg: #f4f6f8;
      --radius: 12px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #1b2d1b, #3a5a40, #2d4739);
      background-size: 200px;
      background-attachment: fixed;
      color: #fff;
      font-family: "Inter", system-ui, sans-serif;
    }

    /* Logo styling */
    .logo {
      position: fixed;
      top: 25px;
      left: 2px;
    }

    .logo img {
      width: 60px;
      height: auto;
    }

    #page-loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      display: none;
      /* Hide by default */
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    /* Your custom image styling */
    .loader-icon {
      width: 200px;
      height: auto;
      animation: spin 3s linear infinite;
    }

    @keyframes spin {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .page {
      max-width: 900px;
      margin: 24px auto;
      padding: 0 16px;
    }

    /* White box for content */
    .content-box {
      position: relative;
      background: #fff;
      color: #000;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      max-width: 890px;
      margin: 50px auto;
      z-index: 1;
      overflow: hidden;
    }

    /* Watermark inside the box with Logo */
    .content-box::after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 340px;
      /* adjust size */
      height: 250px;
      /* adjust size */
      background: url('img/ncclogo.png') no-repeat center center;
      background-size: 150%;
      /* makes sure it scales properly */
      opacity: 0.09;
      /* faded effect like watermark */
      pointer-events: none;
      z-index: 0;
    }

    /* Text responsiveness */
    @media (max-width: 400px) {
      .content-box {
        padding: 5px;
      }

      .form-card h1 {
        font-size: 20px;
      }

      .form-card p,
      .field label,
      .field input,
      .field select,
      .field textarea,
      .submit-btn {
        font-size: 14px;
      }
    }

    @media (max-width: 400px) {
      .content-box {
        padding: 5px;
      }

      .form-card h1 {
        font-size: 18px;
      }

      .form-card p,
      .field label,
      .field input,
      .field select,
      .field textarea,
      .submit-btn {
        font-size: 13px;
      }
    }


    .form-card {
      background: var(--card);
      border: 1px solid #e5e7eb;
      border-radius: var(--radius);
      box-shadow: 0 12px 24px rgba(0, 0, 0, .08);
      padding: 28px;
      position: relative;
    }

    .form-card h1 {
      margin: 0 0 12px;
      text-align: center;
      color: var(--brand-dark);
      font-size: 24px;
      font-weight: 700;
    }

    .form-card p {
      text-align: center;
      color: var(--muted);
      margin-bottom: 24px;
    }

    .fee-note .p {
      text-align: center;
      color: #d9534f !important;
      font-weight: 600;
      margin-top: 10px;
      margin-bottom: 10px;
    }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .field {
      display: flex;
      flex-direction: column;
    }

    .field label {
      font-weight: 600;
      color: var(--brand-dark);
      margin-bottom: 6px;
      font-size: 14px;
    }

    .field input,
    .field select,
    .field textarea {
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid #d1d5db;
      font-size: 14px;
      outline: none;
      transition: border .2s;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      border-color: var(--brand-mid);
    }

    textarea {
      resize: vertical;
    }

    .full {
      grid-column: span 2;
    }

    .submit-btn {
      margin-top: 20px;
      width: 100%;
      padding: 14px;
      background: var(--brand-dark);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      transition: all .2s;
      font-size: 16px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
    }

    .submit-btn:hover {
      background: #004438;
    }

    footer {
      text-align: center;
      margin-top: 18px;
      font-size: 13px;
      color: var(--muted);
    }
  </style>
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

    <div class="content-box">
      <form class="form-card" id="applicationForm" action="process_application.php" method="POST" enctype="multipart/form-data">
        <h1>3rd Brigade Application Form</h1>
        <p>Please fill out the form carefully. All fields marked * are required.</p>

        <div class="grid">
          <div class="field">
            <label for="surname">Surname *</label>
            <input type="text" id="surname" name="surname" required>
          </div>

          <div class="field">
            <label for="firstname">First Name *</label>
            <input type="text" id="firstname" name="firstname" required>
          </div>

          <div class="field">
            <label for="lastname">Last Name *</label>
            <input type="text" id="lastname" name="lastname" required>
          </div>

          <div class="field">
            <label for="marital_status">Marital Status *</label>
            <select id="marital_status" name="marital_status" required>
              <option value="">-- Select --</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Divorced">Divorced</option>
            </select>
          </div>

          <div class="field">
            <label for="dob">Date of Birth *</label>
            <input type="date" id="dob" name="dob" required>
          </div>

          <div class="field">
            <label for="sex">Sex *</label>
            <select id="sex" name="sex" required>
              <option value="">-- Select --</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>

          <div class="field">
            <label for="age">Age *</label>
            <input type="number" id="age" name="age" min="15" max="60" required>
          </div>

          <div class="field">
            <label for="nationality">Nationality *</label>
            <input type="text" id="nationality" name="nationality" required>
          </div>

          <div class="field">
            <label for="religion">Religion</label>
            <input type="text" id="religion" name="religion">
          </div>

          <div class="field">
            <label for="hometown">Hometown</label>
            <input type="text" id="hometown" name="hometown">
          </div>

          <div class="field">
            <label for="state">State of Origin *</label>
            <input type="text" id="state" name="state" required>
          </div>

          <div class="field">
            <label for="lga">L.G.A *</label>
            <input type="text" id="lga" name="lga" required>
          </div>

          <div class="field full">
            <label for="address">Residential Address *</label>
            <textarea id="address" name="address" rows="2" required></textarea>
          </div>

          <div class="field">
            <label for="phone">Phone Number *</label>
            <input type="text" id="phone" name="phone" required>
          </div>

          <div class="field">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
          </div>

          <div class="field full">
            <label for="qualifications">Qualification(s)</label>
            <textarea id="qualifications" name="qualifications" rows="2"></textarea>
          </div>

          <div class="field">
            <label for="referee1">Referee 1 *</label>
            <input type="text" id="referee1" name="referee1" required>
          </div>

          <div class="field">
            <label for="referee1_address">Referee 1 Address *</label>
            <input type="text" id="referee1_address" name="referee1_address" required>
          </div>

          <div class="field">
            <label for="referee2">Referee 2 *</label>
            <input type="text" id="referee2" name="referee2" required>
          </div>

          <div class="field">
            <label for="referee2_address">Referee 2 Address *</label>
            <input type="text" id="referee2_address" name="referee2_address" required>
          </div>

          <div class="field full">
            <label for="challenges">Medical Challenges (if any)</label>
            <textarea id="challenges" name="challenges" rows="2"></textarea>
          </div>

          <div class="field full">
            <label for="declaration">Declaration *</label>
            <textarea id="declaration" name="declaration" rows="3" readonly required></textarea>
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
  </div>

  <script>
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