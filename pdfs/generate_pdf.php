<?php
// ✅ Manual include for Dompdf v3.x
require_once($_SERVER['DOCUMENT_ROOT'] . "/dompdf/src/Dompdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/dompdf/src/Options.php");

use Dompdf\Dompdf;
use Dompdf\Options;

function generateRegistrationPDF($company, $person, $category, $file_path) {
    ini_set("memory_limit", "256M");

    $html = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color:#047857;'>SUDGEC 2025 Contractor Registration Confirmation</h2>
        <p>Dear <strong>$person</strong>,</p>
        <p>Thank you for registering your company, <strong>$company</strong>, under category <strong>$category</strong>.</p>
        <p>Your details have been successfully received and stored in our database.</p>
        <p style='margin-top:20px;'>Best regards,<br><strong>SUDGEC Project Management Team</strong></p>
    </body>
    </html>";

    $options = new Options();
    $options->set('isRemoteEnabled', true); // Allows loading remote images if needed

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    file_put_contents($file_path, $dompdf->output());
}
?>
