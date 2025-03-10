<?php
include "config.php"; // Sesuaikan dengan path ke config.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Gagal mendaftar! Username mungkin sudah digunakan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white text-center">Register</h2>
        <p class="text-gray-600 dark:text-gray-400 text-center">Buat akun baru untuk mengakses sistem</p>

        <?php if (isset($error)): ?>
            <div class="mt-4 p-3 bg-red-500 text-white text-sm rounded-md">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-6">
            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300">Username</label>
                <input type="text" name="username" required placeholder="Masukkan username"
                    class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 dark:text-gray-300">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password"
                    class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition">
                Daftar
            </button>
        </form>

        <p class="mt-4 text-center text-gray-600 dark:text-gray-400">
            Sudah punya akun? <a href="login.php" class="text-blue-500 hover:underline">Login</a>
        </p>
    </div>
</body>
</html>
