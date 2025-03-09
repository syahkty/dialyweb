<?php
include "../../config.php";

// Tambah Hutang
if (isset($_POST['add_debt'])) {
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    $sql = "INSERT INTO debts (amount, description) VALUES ('$amount', '$description')";
    $conn->query($sql);
    header("Location: debts.php");
}

// Ambil Data Hutang Belum Lunas
$pending_debts = $conn->query("SELECT * FROM debts WHERE status = 'Belum Lunas'");

// Ambil Data Hutang Lunas
$paid_debts = $conn->query("SELECT * FROM debts WHERE status = 'Lunas'");
?>

<!DOCTYPE html>
<html lang=id">
<head>
    <title>Catatan Hutang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi Tailwind agar dark mode bisa pakai class
        tailwind.config = { darkMode: 'class' };

        document.addEventListener("DOMContentLoaded", () => {
            const theme = localStorage.getItem("theme");
            if (theme === "dark") {
                document.documentElement.classList.add("dark");
                document.getElementById("darkModeIcon").innerHTML = "☀️";
            } else {
                document.documentElement.classList.remove("dark");
                document.getElementById("darkModeIcon").innerHTML = "🌙";
            }
        });

        function toggleDarkMode() {
            if (document.documentElement.classList.contains("dark")) {
                document.documentElement.classList.remove("dark");
                localStorage.setItem("theme", "light");
                document.getElementById("darkModeIcon").innerHTML = "🌙";
            } else {
                document.documentElement.classList.add("dark");
                localStorage.setItem("theme", "dark");
                document.getElementById("darkModeIcon").innerHTML = "☀️";
            }
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <!-- Navbar -->
    <nav class="fixed top-0 left-0 w-full bg-white dark:bg-gray-800 bg-opacity-70 dark:bg-opacity-70 backdrop-blur-lg shadow-md py-4 px-6 flex justify-between items-center z-50">
        <h1 class="text-xl font-bold">📌 Catatan Hutang</h1>
        <button onclick="toggleDarkMode()" class="text-2xl focus:outline-none transition">
            <span id="darkModeIcon">🌙</span>
        </button>
    </nav>

    <div class="container mx-auto p-6 mt-16">

        <a href="../../project.html" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-md inline-block mb-6">⬅ Kembali</a>

        <!-- Form Tambah Hutang -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4">Tambah Hutang</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm">Jumlah (Rp)</label>
                    <input type="number" name="amount" placeholder="Masukkan jumlah" required class="w-full p-2 border rounded-md dark:bg-gray-700">
                </div>
                <div>
                    <label class="block text-sm">Keterangan</label>
                    <input type="text" name="description" placeholder="Masukkan keterangan" required class="w-full p-2 border rounded-md dark:bg-gray-700">
                </div>
                <button type="submit" name="add_debt" class="w-full bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-lg">Tambah Hutang</button>
            </form>
        </div>

        <!-- Hutang Belum Lunas -->
        <h2 class="text-xl font-bold mt-10 mb-4">💰 Hutang Belum Lunas</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($row = $pending_debts->fetch_assoc()): ?>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold">Rp <?= number_format($row['amount'], 2) ?></h3>
                <p class="text-gray-500 dark:text-gray-400"><?= $row['description'] ?></p>
                <div class="mt-4 flex justify-start">
                    <a href="edit_debt.php?id=<?= $row['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mr-2">✏ Edit</a>
                    <a href="mark_paid.php?id=<?= $row['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">✔ Lunas</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Hutang Lunas -->
        <h2 class="text-xl font-bold mt-10 mb-4">✅ Hutang Lunas</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($row = $paid_debts->fetch_assoc()): ?>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold text-green-500">Rp <?= number_format($row['amount'], 2) ?></h3>
                <p class="text-gray-500 dark:text-gray-400"><?= $row['description'] ?></p>
            </div>
            <?php endwhile; ?>
        </div>

    </div>

</body>
</html>
