<?php
header("Content-Type: application/json");

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// DB Connection
$mysqli = new mysqli("localhost", "DB_USER", "DB_PASS", "u968639263_SUDGEC");

if ($mysqli->connect_errno) {
    echo json_encode(["status" => "error", "message" => $mysqli->connect_error]);
    exit;
}

// Extract payment returned by Flutterwave
$payment = $data["payment"];
$transaction_id = $payment["transaction_id"] ?? $payment["tx_ref"] ?? null;
$payment_status = $payment["status"] ?? "successful";

// Determine payment amount & category
$fee_category = "";
$amount_value = floatval($data["fee"]);

if ($amount_value <= 20) {
    $fee_category = "International Exhibitor (USD)";
} else {
    $fee_category = "Nigerian Exhibitor (NGN)";
}

// Prepare SQL for exhibition_applicants
$stmt = $mysqli->prepare("
    INSERT INTO exhibition_applicants (
        organization_name, contact_person, position_title, phone, email,
        website, postal_address, organization_type, focus_area, products_services,
        booth_size, fee_category, amount, payment_status, transaction_id,
        payment_option, agreed_terms, agreed_disclaimer, signature
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Bind values
$stmt->bind_param(
    "sssssssssssssssssss",
    $data["company_name"],         // organization_name
    $data["contact_person"],       // contact_person
    $data["position"],             // position_title
    $data["phone"],                // phone
    $data["email"],                // email
    $data["website"],              // website
    $data["address"],              // postal_address
    $data["org_type"],             // organization_type
    $data["focus_area"],           // focus_area
    $data["products"],             // products_services
    $data["booth"],                // booth_size
    $fee_category,                 // fee_category
    $amount_value,                 // amount
    $payment_status,               // payment_status
    $transaction_id,               // transaction_id
    "Flutterwave",                 // payment_option
    1,                             // agreed_terms
    1,                             // agreed_disclaimer
    $data["contact_person"]        // signature (placeholder)
);

// Execute and return result
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Application saved successfully!"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$mysqli->close();
?>
