<?php
header('Content-Type: application/json');
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $company = $_POST['company_name'] ?? '';
  $address = $_POST['company_address'] ?? '';
  $person = $_POST['contact_person'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $email = $_POST['email'] ?? '';
  $category = $_POST['category'] ?? '';
  $value = $_POST['contract_value'] ?? '';
  $payment = $_POST['payment_details'] ?? '';
  $signature = $_POST['signature'] ?? '';
  $date = $_POST['date'] ?? '';

  // Handle File Uploads
  $uploadDir = __DIR__ . "/uploads/";
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
  $uploadedFiles = [];

  if (!empty($_FILES['documents']['name'][0])) {
    foreach ($_FILES['documents']['name'] as $key => $filename) {
      $tmp = $_FILES['documents']['tmp_name'][$key];
      $safeName = time() . "_" . basename($filename);
      $targetPath = $uploadDir . $safeName;
      if (move_uploaded_file($tmp, $targetPath)) {
        $uploadedFiles[] = "uploads/" . $safeName;
      }
    }
  }

  $filesJSON = json_encode($uploadedFiles);

  // Insert into DB
  $stmt = $conn->prepare("INSERT INTO contractor_registrations 
    (company_name, company_address, contact_person, phone, email, category, contract_value, payment_details, signature, date_created, documents)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssssssss", $company, $address, $person, $phone, $email, $category, $value, $payment, $signature, $date, $filesJSON);

  if ($stmt->execute()) {
    echo json_encode(["success" => true, "pdf_url" => ""]);
  } else {
    echo json_encode(["success" => false, "message" => "Database insert failed: " . $conn->error]);
  }

  $stmt->close();
  $conn->close();
}
?>
