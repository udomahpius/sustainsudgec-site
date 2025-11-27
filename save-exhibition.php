<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$mysqli = new mysqli("localhost", "u968639263_SUDGEC", "MaLaChy@2000#", "u968639263_SUDGEC");
if ($mysqli->connect_errno) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $mysqli->connect_error]);
    exit;
}

// Read input
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);
if (!$data) {
    echo json_encode(["status"=>"error","message"=>"Invalid or empty JSON input", "raw"=>$rawInput]);
    exit;
}

// Extract payment info
$payment = $data["payment"] ?? [];
$transaction_id = $payment["transaction_id"] ?? $payment["tx_ref"] ?? null;
$payment_status = $payment["status"] ?? "pending";

// Fee and category
$amount_value = isset($data["fee"]) ? floatval($data["fee"]) : 0;
$fee_category = $amount_value <= 20 ? "International Exhibitor (USD)" : "Nigerian Exhibitor (NGN)";

// Optional fields
$position = $data["position"] ?? null;
$website = $data["website"] ?? null;
$org_type = $data["org_type"] ?? null;
$focus_area = $data["focus_area"] ?? null;
$products = $data["products"] ?? null;
$booth = $data["booth"] ?? null;
$signature = $data["contact_person"] ?? null;
$agreed_terms = 1;
$agreed_disclaimer = 1;

// Updating existing record (payment update)
if (!empty($data["record_id"])) {
    $stmt = $mysqli->prepare("
        UPDATE exhibition_applicants SET 
            payment_status=?, 
            transaction_id=?, 
            amount=?, 
            fee_category=?
        WHERE id=?
    ");
    if (!$stmt) {
        echo json_encode(["status"=>"error","message"=>"Prepare failed: " . $mysqli->error]);
        exit;
    }

    $stmt->bind_param("ssdsi", $payment_status, $transaction_id, $amount_value, $fee_category, $data["record_id"]);
    if($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Payment info updated"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Update failed: " . $stmt->error]);
    }
    $stmt->close();
    $mysqli->close();
    exit;
}

// New form submission
$stmt = $mysqli->prepare("
    INSERT INTO exhibition_applicants (
        organization_name, contact_person, position_title, phone, email,
        website, postal_address, organization_type, focus_area, products_services,
        booth_size, fee_category, amount, payment_status, transaction_id,
        payment_option, agreed_terms, agreed_disclaimer, signature
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
if (!$stmt) {
    echo json_encode(["status"=>"error","message"=>"Prepare failed: " . $mysqli->error]);
    exit;
}

$stmt->bind_param(
    "ssssssssssssdsssis",
    $data["company_name"],
    $data["contact_person"],
    $position,
    $data["phone"],
    $data["email"],
    $website,
    $data["address"],
    $org_type,
    $focus_area,
    $products,
    $booth,
    $fee_category,
    $amount_value,
    $payment_status,
    $transaction_id,
    "Flutterwave",
    $agreed_terms,
    $agreed_disclaimer,
    $signature
);

if($stmt->execute()){
    echo json_encode([
        "status"=>"success",
        "message"=>"Application saved successfully!",
        "record_id"=>$stmt->insert_id
    ]);
} else {
    echo json_encode(["status"=>"error","message"=>"Insert failed: " . $stmt->error]);
}

$stmt->close();
$mysqli->close();
