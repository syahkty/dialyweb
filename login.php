<?php
include "config.php"; // Sesuaikan dengan path ke config.php
session_start();

$login_url = $client->createAuthUrl();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Pastikan mengambil "username" juga dalam SELECT
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username']; // ✅ Sekarang ini akan tersimpan
            header("Location: index.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white text-center">Login</h2>
        <p class="text-gray-600 dark:text-gray-400 text-center">Masukkan akun Anda untuk melanjutkan</p>

        <?php if (isset($error)): ?>
            <div class="mt-4 p-3 bg-red-500 text-white text-sm rounded-md">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="mt-6">
            <div class="mb-4">
                <input type="text" name="username" required placeholder="Masukkan username"
                    class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div class="mb-4">
                <input type="password" name="password" required placeholder="Masukkan password"
                    class="w-full px-4 py-2 mt-1 border rounded-lg focus:ring focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-bold py-2 px-4 mt-2 rounded-lg transition">
                Login
            </button>
        </form>
        <h2 class="text-sm text-gray-800 dark:text-gray-200 mt-4 text-center"> Login dengan Google</h2>
        <a href="<?= $login_url ?>" 
        class="flex items-center justify-center gap-3 mt-2 px-4 py-2 mt-2 rounded-lg border border-gray-300 dark:border-gray-600 
                bg-white text-gray-700 dark:text-gray-700 
                hover:bg-gray-100 dark:hover:bg-gray-200 transition-all shadow-sm">
                <img src="https://logopng.com.br/logos/google-37.png" 
         alt="Google Logo" class="w-5 h-5">
    <span class="font-medium">Login dengan Google</span>
        </a>

        <p class="mt-4 text-center text-gray-600 dark:text-gray-400">
            Belum punya akun? <a href="register.php" class="text-blue-500 hover:underline">Daftar</a>
        </p>
    </div>
</body>
</html>
