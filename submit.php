<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
$host = "localhost";
$user = "u968639263_SUDGEC";
$pass = "MaLaChy@2000#";
$dbname = "u968639263_SUDGEC";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  echo json_encode(["success" => false, "error" => $conn->connect_error]);
  exit();
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Prepare SQL
$stmt = $conn->prepare("INSERT INTO registrations (
  fullName, title, institution, department, email, phone, regType,
  isPresenter, presentationTitle, sessionPreference, paymentMethod,
  transactionId, hotelReservation, accommodationType, vegetarian, otherDiet,
  signature, date, amount, discountNote
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
  "ssssssssssssssssssss",
  $data['fullName'], $data['title'], $data['institution'], $data['department'],
  $data['email'], $data['phone'], $data['regType'],
  $data['isPresenter'], $data['presentationTitle'], $data['sessionPreference'],
  $data['paymentMethod'], $data['transactionId'],
  $data['hotelReservation'], $data['accommodationType'],
  $data['vegetarian'], $data['otherDiet'],
  $data['signature'], $data['date'],
  $data['amount'], $data['discountNote']
);

if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
