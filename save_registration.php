<?php
// 🛠 Database Connection - Hostinger MySQL
$servername = "localhost"; // stays localhost for Hostinger
$username = "your_db_username"; // e.g. u123456789_sudgec_user
$password = "your_db_password";
$database = "your_db_name"; // e.g. u123456789_sudgec_db

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}
?>


<?php
header('Content-Type: application/json');
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $company = $_POST['company_name'];
  $address = $_POST['company_address'];
  $person = $_POST['contact_person'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $category = $_POST['category'];
  $value = $_POST['contract_value'];
  $payment = $_POST['payment_details'];
  $signature = $_POST['signature'];
  $date = $_POST['date'];

  // Insert into DB
  $stmt = $conn->prepare("INSERT INTO contractor_registrations 
    (company_name, company_address, contact_person, phone, email, category, contract_value, payment_details, signature, date_created)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssssssss", $company, $address, $person, $phone, $email, $category, $value, $payment, $signature, $date);

  if ($stmt->execute()) {
    // Generate PDF confirmation
    $pdf_dir = "pdfs/";
    if (!is_dir($pdf_dir)) mkdir($pdf_dir);
    $pdf_path = $pdf_dir . time() . "_registration.pdf";

    include("generate_pdf.php");
    generateRegistrationPDF($company, $person, $category, $pdf_path);

    echo json_encode(["success" => true, "pdf_url" => $pdf_path]);
  } else {
    echo json_encode(["success" => false, "message" => "Database insert failed: " . $conn->error]);
  }

  $stmt->close();
  $conn->close();
}
?>
