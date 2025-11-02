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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background-image: url('https://images.unsplash.com/photo-1557683316-973673baf926?auto=format&fit=crop&w=1740&q=80');
      background-size: cover;
      background-position: center;
      background-blend-mode: overlay;
      background-color: rgba(0, 64, 0, 0.6);
    }
    .fade-in {
      animation: fadeInUp 0.8s ease-in-out;
    }
    @keyframes fadeInUp {
      0% { opacity: 0; transform: translateY(40px); }
      100% { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>

<body class="flex justify-center items-center min-h-screen">

  <form method="POST"
        class="fade-in bg-white/90 backdrop-blur-md shadow-2xl rounded-3xl p-10 w-full max-w-sm border border-green-200 transition-transform transform hover:scale-[1.02]">

    <div class="flex justify-center mb-5">
      <img src="https://sustainsudgecorg.org/assets/logo.BpdcY0v0.png" alt="SUDGEC Logo" class="w-16 h-16">
    </div>

    <h2 class="text-2xl font-bold text-center text-green-700 mb-4">Admin Login</h2>
    <p class="text-center text-gray-500 text-sm mb-6">Secure Access Panel</p>

    <?php if (!empty($error)): ?>
      <p class="text-red-600 text-center mb-3 font-medium"><?= $error ?></p>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Username" required
           class="w-full mb-4 border border-gray-300 p-3 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
    
    <input type="password" name="password" placeholder="Password" required
           class="w-full mb-4 border border-gray-300 p-3 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">

    <button type="submit"
            class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold transition duration-300 shadow-lg hover:shadow-green-400/40">
      Login
    </button>

    <p class="text-xs text-center text-gray-500 mt-4">© SUDGEC 2025 | Admin Panel</p>
  </form>
</body>
</html>
