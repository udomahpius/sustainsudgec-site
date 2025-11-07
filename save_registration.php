<?php
// --------------------------
// ⚠️ Enable errors for debugging (remove in production)
// --------------------------
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --------------------------
// Set JSON header
// --------------------------
header('Content-Type: application/json');

// --------------------------
// Database connection
// --------------------------
$servername = "localhost";
$username = "u968639263_SUDGEC";
$password = "MaLaChy@2000#";
$database = "u968639263_SUDGEC";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["success"=>false, "message"=>"Database connection failed: ".$conn->connect_error]);
    exit();
}

// --------------------------
// Only handle POST
// --------------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success"=>false, "message"=>"Invalid request method"]);
    exit();
}

// --------------------------
// Collect POST data
// --------------------------
$company   = $_POST['company_name'] ?? '';
$address   = $_POST['company_address'] ?? '';
$person    = $_POST['contact_person'] ?? '';
$phone     = $_POST['phone'] ?? '';
$email     = $_POST['email'] ?? '';
$category  = $_POST['category'] ?? '';
$value     = $_POST['contract_value'] ?? '';
$payment   = $_POST['payment_details'] ?? '';
$signature = $_POST['signature'] ?? '';
$date      = $_POST['date'] ?? '';

// --------------------------
// Handle file uploads
// --------------------------
$uploaded_files = [];
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if (!empty($_FILES['documents']['name'][0])) {
    foreach ($_FILES['documents']['name'] as $key => $filename) {
        $tmp_name = $_FILES['documents']['tmp_name'][$key];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        $new_name = time() . "_" . uniqid() . "." . $file_ext;
        $destination = $upload_dir . $new_name;

        if (move_uploaded_file($tmp_name, $destination)) {
            $uploaded_files[] = "/uploads/" . $new_name;
        }
    }
}

$documents_str = implode(", ", $uploaded_files);

// --------------------------
// Insert into database
// --------------------------
$stmt = $conn->prepare("INSERT INTO contractor_registrations 
    (company_name, company_address, contact_person, phone, email, category, contract_value, payment_details, signature, date_created, documents)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sssssssssss",
    $company, $address, $person, $phone, $email,
    $category, $value, $payment, $signature, $date, $documents_str
);

if (!$stmt->execute()) {
    echo json_encode(["success"=>false, "message"=>"Database insert failed: ".$conn->error]);
    exit();
}

// --------------------------
// Generate PDF
// --------------------------
$pdf_dir = $_SERVER['DOCUMENT_ROOT'] . "/pdfs/";
if (!is_dir($pdf_dir)) mkdir($pdf_dir, 0777, true);

$pdf_filename = time() . "_contractor_registration.pdf";
$pdf_path = $pdf_dir . $pdf_filename;
$pdf_url  = "/pdfs/" . $pdf_filename;

// Dompdf include path (manual installation)
$dompdf_path = $_SERVER['DOCUMENT_ROOT'] . "/dompdf/autoload.inc.php";
if (!file_exists($dompdf_path)) {
    echo json_encode(["success"=>false, "message"=>"Dompdf autoload not found at $dompdf_path"]);
    exit();
}

require_once($dompdf_path);

use Dompdf\Dompdf;

try {
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

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    file_put_contents($pdf_path, $dompdf->output());
} catch (Exception $e) {
    echo json_encode(["success"=>false, "message"=>"PDF generation failed: ".$e->getMessage()]);
    exit();
}

// --------------------------
// Success response
// --------------------------
echo json_encode(["success"=>true, "pdf_url"=>$pdf_url]);

$stmt->close();
$conn->close();
exit();
