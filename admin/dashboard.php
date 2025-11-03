<?php
// === DEBUG MODE (temporary, disable after testing) ===
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit();
}

// === Database connection ===
include "../db.php";

// === Fetch registrations ===
$rows = [];
$result = $conn->query("SELECT * FROM registrations ORDER BY created_at DESC");

if (!$result) {
  die("Database query failed: " . $conn->error);
}

// === Totals ===
$total_ngn = 0;
$total_usd = 0;

// Disable ONLY_FULL_GROUP_BY mode
$conn->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

while ($row = $result->fetch_assoc()) {
  $amount = preg_replace('/[^\d.]/', '', $row['amount']);
  if (strpos($row['amount'], '₦') !== false) $total_ngn += floatval($amount);
  if (strpos($row['amount'], '$') !== false) $total_usd += floatval($amount);
  $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SUDGEC 2025 | Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
</head>
<body class="bg-green-50 min-h-screen">
  <!-- Header -->
  <header class="bg-green-700 text-white p-4 flex justify-between items-center shadow-md">
    <h1 class="text-xl font-bold">SUDGEC 2025 Registration & Payment Dashboard</h1>
    <a href="logout.php" class="bg-white text-green-700 px-3 py-1 rounded-md font-semibold hover:bg-green-100 transition">
      Logout
    </a>
  </header>

  <!-- Main Section -->
  <main class="p-6 space-y-6">
    <!-- Search + Export -->
    <div class="flex justify-between items-center flex-wrap gap-4">
      <input id="searchBox" 
             type="text" 
             placeholder="🔍 Search by name, email, or institution..." 
             class="border p-2 rounded-md w-full sm:w-1/3 focus:ring-green-500 focus:outline-none">
      <button id="exportBtn" 
              class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
        Export to Excel
      </button>
    </div>

    <!-- Totals -->
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col sm:flex-row justify-around items-center gap-4">
      <div class="text-green-700 font-bold text-lg">💰 Total NGN: ₦<?= number_format($total_ngn, 2) ?></div>
      <div class="text-green-700 font-bold text-lg">💵 Total USD: $<?= number_format($total_usd, 2) ?></div>
      <div class="text-gray-600 text-lg">🧾 Total Records: <?= count($rows) ?></div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow-lg border border-green-100">
      <table id="regTable" class="min-w-full text-sm text-gray-700">
        <thead class="bg-green-600 text-white">
          <tr>
            <th class="px-4 py-2 text-left">Full Name</th>
            <th class="px-4 py-2 text-left">Email</th>
            <th class="px-4 py-2 text-left">Phone</th>
            <th class="px-4 py-2 text-left">Institution</th>
            <th class="px-4 py-2 text-left">Reg Type</th>
            <th class="px-4 py-2 text-left">Amount</th>
            <th class="px-4 py-2 text-left">Payment Method</th>
            <th class="px-4 py-2 text-left">Transaction ID</th>
            <th class="px-4 py-2 text-left">Date</th>
            <th class="px-4 py-2 text-left">Presenter</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
          <tr class="border-b hover:bg-green-50 transition">
            <td class="px-4 py-2"><?= htmlspecialchars($row['fullName']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['phone']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['institution']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['regType']) ?></td>
            <td class="px-4 py-2 font-semibold text-green-700"><?= htmlspecialchars($row['amount']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['paymentMethod']) ?></td>
            <td class="px-4 py-2 text-xs"><?= htmlspecialchars($row['transactionId']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['date']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($row['isPresenter']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Scripts -->
  <script>
    // 🔎 Live Search
    const searchBox = document.getElementById('searchBox');
    searchBox.addEventListener('input', function() {
      const filter = searchBox.value.toLowerCase();
      document.querySelectorAll('#regTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });

    // 📊 Export to Excel
    document.getElementById('exportBtn').addEventListener('click', function() {
      const table = document.getElementById('regTable');
      const wb = XLSX.utils.table_to_book(table, { sheet: "Registrations" });
      XLSX.writeFile(wb, "SUDGEC2025_Registrations.xlsx");
    });
  </script>
</body>
</html>
