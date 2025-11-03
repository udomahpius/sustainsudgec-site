<?php
$host = "localhost";
$user = "u968639263_SUDGEC";
$pass = "MaLaChy@2000#";
$dbname = "u968639263_SUDGEC";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}
?>
