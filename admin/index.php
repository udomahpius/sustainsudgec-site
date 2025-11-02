<?php
session_start();
if (isset($_SESSION['admin'])) {
  header("Location: dashboard.php");
  exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];
  if ($username === 'admin' && $password === 'SUDGEC@2025') {
    $_SESSION['admin'] = true;
    header("Location: dashboard.php");
    exit();
  } else {
    $error = "Invalid credentials.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | SUDGEC 2025</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-green-50 flex justify-center items-center min-h-screen">
  <form method="POST" class="bg-white shadow-xl rounded-3xl p-10 w-full max-w-sm">
    <h2 class="text-2xl font-bold text-center text-green-700 mb-6">Admin Login</h2>
    <?php if (!empty($error)): ?>
      <p class="text-red-600 text-center mb-3"><?= $error ?></p>
    <?php endif; ?>
    <input type="text" name="username" placeholder="Username" required class="w-full mb-4 border p-2 rounded-md focus:ring-green-500">
    <input type="password" name="password" placeholder="Password" required class="w-full mb-4 border p-2 rounded-md focus:ring-green-500">
    <button type="submit" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold">Login</button>
  </form>
</body>
</html>
