<?php
require 'phpqrcode/qrlib.php';
require 'fpdf/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    // Get form data
    $transaction_id = $_POST['transaction_id'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $customer_name = $_POST['customer_name'];

    // Define QR code content
    $qr_content = "Transaction ID: $transaction_id\nAmount: $amount\nDate: $date\nCustomer: $customer_name";

    // Generate QR code and save as image
    $qr_file = 'qrcode.png';
    QRcode::png($qr_content, $qr_file);

    // Create a PDF receipt
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, 'Receipt', 0, 1, 'C');
            $this->Ln(10);
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }

        function ReceiptBody($transaction_id, $amount, $date, $customer_name, $qr_file) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, "Transaction ID: $transaction_id", 0, 1);
            $this->Cell(0, 10, "Amount: $amount", 0, 1);
            $this->Cell(0, 10, "Date: $date", 0, 1);
            $this->Cell(0, 10, "Customer: $customer_name", 0, 1);
            $this->Ln(10);
            $this->Image($qr_file, $this->GetX(), $this->GetY(), 40, 40);
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->ReceiptBody($transaction_id, $amount, $date, $customer_name, $qr_file);

    // Output the PDF to a string
    $pdf_content = $pdf->Output('', 'S');

    // Base64 encode the PDF and QR code for embedding
    $base64_pdf = base64_encode($pdf_content);
    $base64_qr = base64_encode(file_get_contents($qr_file));

    // Clean up QR code image file
    unlink($qr_file);

    // Display the receipt and download button
    echo <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Receipt</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .receipt-container {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .qr-code {
                margin-top: 20px;
            }
            .download-button {
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            .download-button:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="receipt-container">
            <h2>Receipt</h2>
            <p><strong>Transaction ID:</strong> $transaction_id</p>
            <p><strong>Amount:</strong> $amount</p>
            <p><strong>Date:</strong> $date</p>
            <p><strong>Customer:</strong> $customer_name</p>
            <div class="qr-code">
                <img src="data:image/png;base64,{$base64_qr}" alt="QR Code">
            </div>
            <form method="post" action="download_receipt.php">
                <input type="hidden" name="pdf_content" value="{$base64_pdf}">
                <button type="submit" class="download-button" name="download">Download Receipt</button>
            </form>
        </div>
    </body>
    </html>
    HTML;
}
?>
