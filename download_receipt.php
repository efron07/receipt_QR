<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['download'])) {
    // Get the PDF content from the form
    $pdf_content = base64_decode($_POST['pdf_content']);

    // Set headers for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="receipt.pdf"');
    header('Content-Length: ' . strlen($pdf_content));

    // Output the PDF content
    echo $pdf_content;
    exit;
}
?>
