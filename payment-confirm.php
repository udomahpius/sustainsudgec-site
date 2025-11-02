<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

// DB Connection
$host = "localhost";
$user = "u968639263_SUDGEC";
$pass = "MaLaChy@2000#";
$dbname = "u968639263_SUDGEC";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  echo json_encode(["success" => false, "error" => $conn->connect_error]);
  exit();
}

// Verify that user exists by email
$email = $data['email'];
$sql = "SELECT id FROM registrations WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  // Update existing registration with payment info
  $update = $conn->prepare("UPDATE registrations 
    SET paymentMethod=?, transactionId=?, amount=?, date=NOW(), discountNote=?, regType=? 
    WHERE email=?");
  $update->bind_param(
    "ssssss",
    $data['payment_method'],
    $data['tx_ref'],
    $data['amount'],
    $data['status'],
    $data['regType'],
    $email
  );
  $success = $update->execute();

  echo json_encode(["success" => $success]);
  $update->close();
} else {
  // If user not found, insert as new record
  $insert = $conn->prepare("INSERT INTO registrations (fullName, email, phone, regType, paymentMethod, transactionId, amount, date, discountNote) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
  $insert->bind_param(
    "ssssssss",
    $data['name'],
    $data['email'],
    $data['phone'],
    $data['regType'],
    $data['payment_method'],
    $data['tx_ref'],
    $data['amount'],
    $data['status']
  );
  $insert->execute();
  echo json_encode(["success" => true]);
  $insert->close();
}

$stmt->close();
$conn->close();
?>
