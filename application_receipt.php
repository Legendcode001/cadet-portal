<?php
// application_receipt.php
// Drop-in replacement — preserves frontend design and improves backend robustness.

// =================== DEBUGGING ===================
// Enable error reporting for better debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =================== DATABASE CONNECTION ===================
$host = "localhost";
$user = "root";
$pass = "";
$db = "cadetportal";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

// =================== READ IDENTIFIER FROM QUERY ===================
$rawId = isset($_GET['id']) ? trim($_GET['id']) : null;

// Echo the received ID for debugging
echo "<!-- Received ID: " . htmlspecialchars($rawId) . " -->";

if (!$rawId) {
  die("<h1 style='text-align:center;color:red'>🚨 No Application Id Provided Yet 🚨</h1>");
}

$appData = null;

// First, try to find the application by its numeric 'id'
if (preg_match('/^\d+$/', $rawId)) {
  $stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
  $stmt->bind_param("i", $rawId);
  $stmt->execute();
  $res = $stmt->get_result();
  $appData = $res->fetch_assoc();
  $stmt->close();
}

// If not found by 'id' (or if the input was alphanumeric), try to find it by 'application_id'
if (!$appData) {
  $stmt = $conn->prepare("SELECT * FROM applications WHERE application_id = ? LIMIT 1");
  $stmt->bind_param("s", $rawId);
  $stmt->execute();
  $res = $stmt->get_result();
  $appData = $res->fetch_assoc();
  $stmt->close();
}

if (!$appData) {
  die("<h1 style='text-align:center;color:red'>Application not found for ID: " . htmlspecialchars($rawId) . "</h1>");
}

// =================== HELPER FUNCTIONS ===================
function getField($row, $names, $default = '')
{
  foreach ((array)$names as $n) {
    if (isset($row[$n]) && $row[$n] !== null && $row[$n] !== '') {
      return $row[$n];
    }
  }
  return $default;
}

// Function to generate a random 6-digit number
function generateRandomSixDigits()
{
  return str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
}

// =================== NORMALIZE FIELDS ===================
$fullName = getField($appData, ['full_name', 'fullname', 'name']);
if (!$fullName) {
  $surname = getField($appData, ['surname']);
  $firstname = getField($appData, ['firstname', 'first_name']);
  $lastname = getField($appData, ['lastname', 'last_name']);
  $fullName = trim(implode(' ', array_filter([$surname, $firstname, $lastname])));
  if (!$fullName) $fullName = '(Name not provided)';
}

$passportRaw = getField($appData, ['passport', 'passport_filename', 'passport_path'], '');
$passportUrl = '';
if ($passportRaw) {
  if (preg_match('#^https?://#i', $passportRaw) || strpos($passportRaw, '/') === 0) {
    $passportUrl = $passportRaw;
  } else {
    if (stripos($passportRaw, 'uploads/') === 0) {
      $passportUrl = $passportRaw;
    } else {
      $passportUrl = 'uploads/' . $passportRaw;
    }
  }
}

$amountRaw = getField($appData, ['amount_paid', 'amount', 'payment_amount'], '');
$paymentStatus = getField($appData, ['payment_status', 'status'], 'Unpaid');

if (strtolower($paymentStatus) === 'paid' && (float)$amountRaw === 0.00) {
  $amountRaw = 10000;
}
$amountFormatted = $amountRaw !== '' ? '₦' . number_format((float)$amountRaw, 2) : '₦0.00';

// =================== APPLICATION ID HANDLING ===================
$displayAppId = getField($appData, ['application_id'], '');

// If the `application_id` field is empty, generate a new unique one and save it to the database
if (empty($displayAppId)) {
  $newAppId = '';
  $count = 1;

  // Generate a unique ID by checking against the database in a loop
  do {
    $newAppId = '3BH-' . generateRandomSixDigits();
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE application_id = ?");
    $checkStmt->bind_param("s", $newAppId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();
  } while ($count > 0);

  // Save the newly generated ID back to the database for this application
  $updateStmt = $conn->prepare("UPDATE applications SET application_id = ? WHERE id = ?");
  $updateStmt->bind_param("si", $newAppId, $appData['id']);
  $updateStmt->execute();
  $updateStmt->close();

  $displayAppId = $newAppId;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Application Receipt - 3rd Brigade Headquarters</title>

  <!-- Fonts & PDF -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel=" icon" href="./img/cadetlogo_prev_ui.png" sizes="16x16" type="image/png">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <style>
    /* === (IDENTICAL DESIGN YOU HAD) === */
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
      font-family: "Inter", system-ui, sans-serif;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* Loader */
    #loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #000;
      color: #0f0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      font-family: monospace;
    }

    .loader-circle {
      width: 60px;
      height: 60px;
      border: 6px solid #0f0;
      border-top: 6px solid transparent;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 12px;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .page {
      max-width: 900px;
      margin: 24px auto;
      padding: 0 16px;
    }

    .page.visible {
      opacity: 1;
      pointer-events: auto;
    }

    /* Receipt Card */
    #receipt {
      background: var(--card);
      border: 1px solid #e5e7eb;
      border-radius: var(--radius);
      box-shadow: 0 12px 24px rgba(0, 0, 0, .06);
      padding: 28px;
      position: relative;
      overflow: hidden;
    }

    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: min(60%, 520px);
      opacity: .08;
      pointer-events: none;
      z-index: 0;
    }

    .content {
      position: relative;
      z-index: 1;
    }

    .header {
      text-align: center;
      margin-bottom: 16px;
    }

    .title h1 {
      margin: 0;
      font-size: 22px;
      color: var(--brand-dark);
    }

    .subtitle {
      color: var(--brand-mid);
      font-weight: 600;
      margin: 4px 0;
    }

    .meta {
      font-size: 12px;
      color: var(--muted);
    }

    .stamp {
      font-size: 32px;
      font-weight: 900;
      color: #b20b0b;
      border: 3px dashed #b20b0b;
      padding: 8px 14px;
      background: rgba(255, 0, 0, 0.08);
      position: absolute;
      right: 5px;
      top: 300px;
      transform: rotate(-12deg);
      opacity: 0.9;
      animation: stamp 1.5s ease forwards;
    }

    @keyframes stamp {
      0% {
        opacity: 0;
        transform: scale(3) rotate(-20deg);
      }

      60% {
        opacity: 1;
        transform: scale(1) rotate(-20deg);
      }

      100% {
        opacity: 0.95;
      }
    }

    .status-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 14px;
    }

    .status-badge {
      background: #e6fff6;
      color: #006644;
      padding: 6px 10px;
      border-radius: 8px;
      font-weight: 700;
      border: 1px solid rgba(0, 102, 68, 0.08);
    }

    .divider {
      height: 1px;
      background: #e5e7eb;
      margin: 18px 0;
    }

    .grid {
      display: grid;
      grid-template-columns: 220px 1fr;
      gap: 8px 16px;
    }

    .label {
      text-transform: uppercase;
      font-weight: 600;
      color: var(--brand-dark);
      border-bottom: 1px solid #444;
      padding-bottom: 6px;
    }

    .value {
      color: #002b20;
      white-space: pre-wrap;
      word-break: break-word;
      border-bottom: 1px solid #444;
      padding-bottom: 9px;
    }

    .passport {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 14px;
      margin-left: 90px;
    }

    .passport img {
      width: 170px;
      height: 170px;
      object-fit: cover;
      border-radius: 10px;
      border: 3px solid var(--brand-dark);
    }

    .actions {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 18px;
    }

    .btn {
      background: var(--brand-dark);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 10px 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
    }

    .btn:hover {
      background: #003b32;
    }

    .btn:active {
      transform: translateY(1px);
    }

    /* Logo styling */
    .logo {
      position: fixed;
      /* Position it to the very start of the page */
      top: 25px;
      /* Adjust the top margin if needed */
      left: 2px;
      /* Adjust the left margin to keep it at the start */
    }

    .logo img {
      width: 60px;
      /* Adjust the width of the logo */
      height: auto;
      /* Maintain the aspect ratio */
    }

    footer {
      text-align: center;
      margin-top: 18px;
      font-size: 13px;
      color: var(--muted);
    }

    @media print {
      body {
        background: white !important;
      }

      #receipt {
        box-shadow: none !important;
        border: none !important;
      }

      .actions,
      .stamp {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <!-- Loader -->
  <div id="loader">
    <div class="loader-circle"></div>
    <div>Generating receipt...</div>
  </div>

  <div class="page" id="page">
    <section id="receipt">
      <!-- Watermark image location: ./img/cadetlogo_prev_ui.png (make sure file exists) -->
      <img class="watermark" src="./img/cadetlogo_prev_ui.png" alt="Watermark">
      <div class="content">

        <div class="logo">
          <a href="#"><img src="./img/cadetlogo_prev_ui.png" alt="Cadet Logo"></a>
        </div>

        <div class="header">
          <div class="title">
            <h1>3rd Brigade Headquarters</h1>
            <div class="subtitle">Application & Payment Receipt</div>
            <div class="meta">Issued on: <?php echo htmlspecialchars(date("F j, Y, g:i a")); ?></div>
          </div>
          <div class="stamp">VERIFIED</div>

          <!-- Passport -->
          <div class="passport">
            <div style="min-width:220px;font-weight:700;color:var(--brand-dark)"></div>
            <?php if ($passportUrl): ?>
              <!-- Ensure the image path is correct -->
              <img src="<?php echo htmlspecialchars($passportUrl); ?>" alt="Applicant passport">
            <?php else: ?>
              <p>No passport uploaded</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="status-row">
          <div class="status-badge">Application ID: <?php echo htmlspecialchars($displayAppId); ?></div>
          <div class="status-badge">Payment: <?php echo htmlspecialchars($amountFormatted . " • " . $paymentStatus); ?></div>
        </div>

        <div class="divider"></div>

        <!-- Applicant Info -->
        <div class="grid">
          <div class="label">Full Name</div>
          <div class="value"><?php echo htmlspecialchars($fullName); ?></div>

          <div class="label">Marital Status</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['marital_status', 'maritalStatus'], '')); ?></div>

          <div class="label">Date of Birth</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['dob'], '')); ?></div>

          <div class="label">Sex</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['sex'], '')); ?></div>

          <div class="label">Age</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['age'], '')); ?></div>

          <div class="label">Nationality</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['nationality'], '')); ?></div>

          <div class="label">Religion</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['religion'], '')); ?></div>

          <div class="label">Hometown</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['hometown'], '')); ?></div>

          <div class="label">State of Origin</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['state', 'state_of_origin'], '')); ?></div>

          <div class="label">L.G.A</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['lga'], '')); ?></div>

          <div class="label">Residential Address</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['address'], '')); ?></div>

          <div class="label">Phone Number</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['phone', 'phone_number'], '')); ?></div>

          <div class="label">Email</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['email'], '')); ?></div>

          <div class="label">Qualification(s)</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['qualifications'], 'N/A')); ?></div>

          <div class="label">Referee 1</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['referee1', 'referee_1'], 'N/A')); ?></div>

          <div class="label">Referee 1 Address</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['referee1_address', 'referee_1_address'], 'N/A')); ?></div>

          <div class="label">Referee 2</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['referee2', 'referee_2'], 'N/A')); ?></div>

          <div class="label">Referee 2 Address</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['referee2_address', 'referee_2_address'], 'N/A')); ?></div>

          <div class="label">Medical Challenges</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['challenges'], 'N/A')); ?></div>

          <div class="label">Declaration</div>
          <div class="value"><?php echo htmlspecialchars(getField($appData, ['declaration'], '')); ?></div>
        </div>

        <!-- Buttons -->
        <div class="actions">
          <?php if (strtolower($paymentStatus) === 'paid'): ?>
            <button class="btn" onclick="window.print()">Print</button>
            <button class="btn" id="saveBtn">Save as PDF</button>
          <?php else: ?>
            <!-- If unpaid, hide/disable the actions but keep UI consistent -->
            <button class="btn" disabled title="Complete payment to enable printing">Print (locked)</button>
            <button class="btn" disabled title="Complete payment to enable saving">📄Save as PDF (locked)</button>
          <?php endif; ?>
        </div>

        <footer>© <?php echo date("Y"); ?> 3rd Brigade Headquarters. All Rights Reserved.</footer>
      </div>
    </section>
  </div>

  <script>
    // show loader briefly (same as your original)
    setTimeout(() => {
      document.getElementById('loader').style.display = 'none';
      document.getElementById('page').classList.add('visible');
    }, 900);

    // Save as PDF using html2pdf when allowed/visible
    document.getElementById('saveBtn')?.addEventListener('click', () => {
      const opt = {
        margin: 12,
        filename: 'application_receipt.pdf',
        image: {
          type: 'jpeg',
          quality: 0.98
        },
        html2canvas: {
          scale: 2,
          useCORS: true
        },
        jsPDF: {
          unit: 'pt',
          format: 'a4',
          orientation: 'portrait'
        }
      };
      html2pdf().set(opt).from(document.getElementById('receipt')).save();
    });
  </script>
</body>

</html>

<?php
// close DB
$conn->close();
?>