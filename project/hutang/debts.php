<?php
include "../../config.php";

// Tambah Hutang
if (isset($_POST['add_debt'])) {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $due_date = date('Y-m-d');

    try {
        $stmt = $pdo->prepare("INSERT INTO debts (amount, description, due_date, status) 
                               VALUES (:amount, :description, :due_date, 'Belum Lunas')");
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':due_date', $due_date);

        $stmt->execute();
        header("Location: debts.php");
        exit();
    } catch (PDOException $e) {
        echo "Gagal menambahkan hutang: " . $e->getMessage();
    }
}

// Ambil Data Hutang Belum Lunas
try {
    $stmt = $pdo->prepare("SELECT * FROM debts WHERE status = 'Belum Lunas'");
    $stmt->execute();
    $pending_debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_hutang = 0;
    foreach ($pending_debts as $row) {
        $total_hutang += $row['amount'];
    }
} catch (PDOException $e) {
    echo "Gagal mengambil data hutang: " . $e->getMessage();
}

// Ambil Data Hutang Lunas
try {
    $stmt = $pdo->prepare("SELECT * FROM debts WHERE status = 'Lunas'");
    $stmt->execute();
    $paid_debts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Gagal mengambil data hutang lunas: " . $e->getMessage();
}

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
        <div class="max-w-full mx-auto mt-10 p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
            💰 Hutang Belum Lunas
        </h2>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-900 shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        <th class="py-3 px-4 text-left">💲 Jumlah</th>
                        <th class="py-3 px-4 text-left">📌 Keterangan</th>
                        <th class="py-3 px-4 text-left">📅 Tanggal</th>
                        <th class="py-3 px-4 text-center">⚙ Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pending_debts)): ?>
                        <?php foreach ($pending_debts as $row): ?>
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <td class="py-3 px-4 font-semibold">Rp <?= number_format($row['amount'], 2) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['description']) ?></td>
                            <td class="py-3 px-4">
                                 <?= !empty($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '-' ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="edit_debt.php?id=<?= $row['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded mr-2">Edit</a>
                                <a href="mark_paid.php?id=<?= $row['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">Lunas</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">
                                ❌ Tidak ada hutang yang belum lunas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($debts)): ?>
                <tfoot>
                    <tr class="bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-200 font-bold">
                        <td colspan="2" class="py-3 px-4 text-left">💰 Total Hutang:</td>
                        <td class="py-3 px-4">Rp <?= number_format($total_hutang, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

        <!-- Hutang Lunas -->
<h2 class="text-xl font-bold mt-10 mb-4">✅ Hutang Lunas</h2>

<div class="overflow-x-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-green-500 text-white">
                <th class="p-3 text-left">Jumlah</th>
                <th class="p-3 text-left">Keterangan</th>
                <th class="p-3 text-left">Tanggal</th>
            </tr>
        </thead>
        <tbody>
        <?php $totalPaid = 0; ?>
<?php foreach ($paid_debts as $row): 
    $totalPaid += $row['amount']; // Tambahkan ke total
?>
<tr class="border-b dark:border-gray-700">
    <td class="p-3 text-green-500 font-semibold">Rp <?= number_format($row['amount'], 2) ?></td>
    <td class="p-3"><?= htmlspecialchars($row['description']) ?></td>
    <td class="p-3">
        <?= !empty($row['due_date']) ? date("d M Y", strtotime($row['due_date'])) : '-' ?>
    </td>
</tr>
<?php endforeach; ?>

        </tbody>
        <tfoot>
            <tr class="bg-green-100 dark:bg-green-900 font-bold">
                <td class="p-3">Total</td>
                <td colspan="2" class="p-3 text-green-500">Rp <?= number_format($totalPaid, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>
    </div>

</body>
</html>
