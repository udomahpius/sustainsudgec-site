<?php
// ✅ Show PHP errors (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// 🛠 Database Connection
$servername = "localhost";
$username = "u968639263_SUDGEC";
$password = "MaLaChy@2000#";
$database = "u968639263_SUDGEC";

$conn = new mysqli($servername, $username, $password, $database);

// ✅ Check connection
if ($conn->connect_error) {
  die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

// ✅ Process Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
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

  // ✅ Handle File Uploads
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

  // ✅ Insert into Database
  $stmt = $conn->prepare("INSERT INTO contractor_registrations 
    (company_name, company_address, contact_person, phone, email, category, contract_value, payment_details, signature, date_created, documents)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  $stmt->bind_param(
    "sssssssssss", 
    $company, $address, $person, $phone, $email, 
    $category, $value, $payment, $signature, $date, $documents_str
  );

  if ($stmt->execute()) {
    // ✅ Create PDF directory
    $pdf_dir = $_SERVER['DOCUMENT_ROOT'] . "/pdfs/";
    if (!is_dir($pdf_dir)) mkdir($pdf_dir, 0777, true);

    $pdf_filename = time() . "_contractor_registration.pdf";
    $pdf_path = $pdf_dir . $pdf_filename;
    $pdf_url  = "/pdfs/" . $pdf_filename; // Web URL

    // ✅ Include PDF generator
    $generator_path = $_SERVER['DOCUMENT_ROOT'] . "/pdfs/generate_pdf.php";
    if (file_exists($generator_path)) {
      include($generator_path);
      generateRegistrationPDF($company, $person, $category, $pdf_path);
    } else {
      echo json_encode(["success" => false, "message" => "PDF generator not found at: " . $generator_path]);
      exit();
    }

    echo json_encode(["success" => true, "pdf_url" => $pdf_url]);
  } else {
    echo json_encode(["success" => false, "message" => "Database insert failed: " . $conn->error]);
  }

  $stmt->close();
  $conn->close();
}
?>
