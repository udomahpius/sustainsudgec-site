<?php
require_once("vendor/autoload.php"); // if using Composer + Dompdf

use Dompdf\Dompdf;

function generateRegistrationPDF($company, $person, $category, $file_path) {
  $html = "
  <html>
  <body style='font-family: Arial;'>
    <h2 style='color:#047857;'>SUDGEC 2025 Contractor Registration Confirmation</h2>
    <p>Dear <strong>$person</strong>,</p>
    <p>Thank you for registering your company, <strong>$company</strong>, under category <strong>$category</strong>.</p>
    <p>Your details have been successfully received and stored in our database.</p>
    <p style='margin-top:20px;'>Best regards,<br><strong>SUDGEC Project Management Team</strong></p>
  </body>
  </html>";

  $dompdf = new Dompdf();
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  file_put_contents($file_path, $dompdf->output());
}
?>
