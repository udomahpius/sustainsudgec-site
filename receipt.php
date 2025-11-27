<?php
require("fpdf/fpdf.php");

// Get data (usually from DB or POST)
$name       = $_GET['name'] ?? "John Doe";
$email      = $_GET['email'] ?? "example@gmail.com";
$phone      = $_GET['phone'] ?? "0000000000";
$amount     = $_GET['amount'] ?? "250";
$category   = $_GET['category'] ?? "Nigerian Participant";
$txn        = $_GET['txn'] ?? "TXN-" . rand(100000,999999);
$date       = date("F j, Y h:i A");

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// --- HEADER ---
$pdf->SetFont("Arial","B",20);
$pdf->Cell(0,10,"SUDGEC 2025",0,1,"C");

$pdf->SetFont("Arial","",12);
$pdf->Cell(0,8,"Sustainable Development Goals Evaluation Conference",0,1,"C");
$pdf->Ln(5);

// Line
$pdf->SetDrawColor(0,128,0);
$pdf->SetLineWidth(1);
$pdf->Line(10,30,200,30);
$pdf->Ln(10);

// --- RECEIPT TITLE ---
$pdf->SetFont("Arial","B",16);
$pdf->Cell(0,10,"Payment Receipt",0,1,"L");
$pdf->Ln(3);

// --- DETAILS BOX ---
$pdf->SetFont("Arial","",12);

$pdf->Cell(50,8,"Full Name:",0,0);
$pdf->Cell(100,8,$name,0,1);

$pdf->Cell(50,8,"Email:",0,0);
$pdf->Cell(100,8,$email,0,1);

$pdf->Cell(50,8,"Phone:",0,0);
$pdf->Cell(100,8,$phone,0,1);

$pdf->Cell(50,8,"Category:",0,0);
$pdf->Cell(100,8,$category,0,1);

$pdf->Cell(50,8,"Amount Paid:",0,0);
$pdf->Cell(100,8,"₦" . number_format($amount),0,1);

$pdf->Cell(50,8,"Transaction ID:",0,0);
$pdf->Cell(100,8,$txn,0,1);

$pdf->Cell(50,8,"Date:",0,0);
$pdf->Cell(100,8,$date,0,1);

$pdf->Ln(10);

// --- FOOTER ---
$pdf->SetFont("Arial","I",10);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(0,10,"Thank you for registering. See you at SUDGEC 2025!",0,1,"C");
$pdf->Ln(5);

// Output PDF
$pdf->Output("I", "Receipt-".$txn.".pdf");
?>
