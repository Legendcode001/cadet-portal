<?php
include 'db.php';

if (!isset($_GET['id'])) {
    print($_GET['id']);
    die("Invalid receipt request.");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No receipt found for this ID.");
}

$data = $result->fetch_assoc();
$stmt->close();
$conn->close();

$ref = $data['transaction_reference'] ?: $data['application_id'];
$verifyUrl = "https://yourdomain.com/verify.php?reference=" . urlencode($ref);
$qrCodeUrl = "https://chart.googleapis.com/chart?chs=180x180&cht=qr&chl=" . urlencode($verifyUrl);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./img/cadetlogo_prev_ui.png" type="image/png">
    <title>Receipt - 3rd Brigade Headquarters</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #002b20;
            color: white;
            padding: 15px;
            text-align: center;
        }

        header img {
            width: 70px;
            height: auto;
            vertical-align: middle;
            margin-right: 10px;
        }

        header h1 {
            display: inline-block;
            font-size: 24px;
            margin: 0;
            vertical-align: middle;
        }

        main {
            padding: 25px;
            max-width: 750px;
            margin: 30px auto;
            background-color: white;
            border-radius: 12px;
            border: 1px solid #ccc;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            background: url('img/logo.png') no-repeat center;
            background-size: 220px;
            opacity: 0.97;
        }

        h2 {
            color: #00796b;
            text-align: center;
            margin-bottom: 20px;
        }

        .receipt-details {
            margin-top: 15px;
            padding: 20px;
            border: 1px solid #00796b;
            border-radius: 10px;
            background: rgba(249, 253, 251, 0.97);
            font-size: 15px;
            color: #004d40;
        }

        .receipt-details div {
            margin-bottom: 10px;
        }

        .receipt-details strong {
            width: 180px;
            display: inline-block;
        }

        .passport {
            text-align: center;
            margin-top: 20px;
        }

        .passport img {
            max-width: 120px;
            border: 2px solid #444;
            border-radius: 6px;
        }

        .qr-section {
            text-align: center;
            margin-top: 25px;
        }

        .qr-section img {
            border: 3px solid #004d40;
            padding: 6px;
            border-radius: 8px;
            background: #fff;
        }

        .btn-group {
            text-align: center;
            margin-top: 20px;
        }

        .print-receipt {
            background-color: #004d40;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: inline-block;
            font-size: 15px;
            margin: 5px;
        }

        .print-receipt:hover {
            background-color: #00695c;
        }

        .verification-message {
            margin-top: 30px;
            font-size: 13px;
            color: #555;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 12px;
        }

        .signature-area {
            top: 15px;
            margin-top: 40px;
            text-align: right;
        }

        .signature-area .p {
            margin-top: none;
            bottom: none;
        }

        .signature-image {
            width: 150px;
            top: 15px;
            height: auto;
            margin: 0 auto 5px;
            display: right;
        }

        @media print {
            .btn-group {
                display: none;
            }
        }
    </style>
    <!-- jsPDF & html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<body>
    <header>
        <a href="receipt.php"><img src="./img/cadetlogo_prev_ui.png" alt="Cadet Logo"></a>
        <h1>3rd Brigade Headquarters</h1>
    </header>

    <main id="receiptContent">
        <h2>Official Payment Receipt</h2>
        <div class="receipt-details" id="receiptDetails">
            <div><strong>Receipt ID:</strong> <?= $id ?></div>
            <div><strong>Application ID:</strong> <?= htmlspecialchars($id) ?></div>
            <div><strong>Transaction Ref:</strong> <?= htmlspecialchars($data['transaction_reference'] ?? 'N/A') ?></div>
            <div><strong>From:</strong> <?= htmlspecialchars($data['firstname'] . ' ' . $data['lastname'] . ' ' . $data['surname']) ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></div>
            <div><strong>Phone:</strong> <?= htmlspecialchars($data['phone']) ?></div>
            <div><strong>To:</strong> 3rd Brigade Headquarters</div>
            <div><strong>Amount Paid:</strong> ₦<?= number_format($data['amount'], 2) ?></div>
            <div><strong>Payment Status:</strong> <?= htmlspecialchars($data['payment_status']) ?></div>
            <div><strong>Date:</strong> <?= date("F j, Y, g:i A", strtotime($data['created_at'])) ?></div>
        </div>

        <?php if (!empty($data['passport'])): ?>
            <div class="passport">
                <p><b>Uploaded Passport:</b></p>
                <img src="uploads/<?= htmlspecialchars($data['passport']) ?>" alt="Passport">
            </div>
        <?php endif; ?>

        <!--
        <div class="qr-section">
            <p><b>Scan to Verify Receipt</b></p>
            <a href="<?= $verifyUrl ?>" target="_blank">
                <img src="<?= $qrCodeUrl ?>" alt="QR Code">
            </a>
            <p style="font-size: 12px; color:#555;">Or visit: <?= $verifyUrl ?></p>
        </div>
         -->
        <!-- The signature area -->
        <div class="signature-area">
            <img class="signature-image" src="./img/sign (2).png" alt="Authorized Signature">
            <p class="text-sm font-bold mt-2">Authorized Signature</p>
        </div>

        <div class="btn-group">
            <button class="print-receipt" onclick="window.print()">🖨 Print Receipt</button>
            <button class="print-receipt" onclick="goToApplicationReceipt(<?= $id ?>)">📄 Print Application Form</button>
            <button class="print-receipt" onclick="downloadPDF()">💾 Download PDF</button>
        </div>

        <div class="verification-message">
            This receipt is an official document of the 3rd Brigade Headquarters.
            Keep it safe, as it is required for verification and training clearance.
        </div>
    </main>

    <script>
        function goToApplicationReceipt(id) {
            window.location.href = `application_receipt.php?id=${id}`;
        }

        function downloadPDF() {
            const {
                jsPDF
            } = window.jspdf;
            const receipt = document.getElementById("receiptContent");
            html2canvas(receipt, {
                scale: 2
            }).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jsPDF('p', 'pt', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save("Receipt_<?= $id ?>.pdf");
            });
        }
    </script>
</body>

</html>