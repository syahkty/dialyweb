<?php
include "config.php";

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
<html lang="id">
<head>
    <title>Catatan Hutang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <h1 class="text-2xl font-bold mb-4">Catatan Hutang</h1>
    <a href="index.php" class="bg-green-500 text-white p-2 rounded-md mb-4 inline-block">⬅ Kembali ke Tugas</a>

    <!-- Form Tambah Hutang -->
    <form method="POST" class="mb-4">
        <input type="number" name="amount" placeholder="Jumlah (Rp)" required class="border p-2 mr-2">
        <input type="text" name="description" placeholder="Keterangan" required class="border p-2 mr-2">
        <button type="submit" name="add_debt" class="bg-blue-500 text-white p-2">Tambah</button>
    </form>

    <!-- Tabel Hutang Belum Lunas -->
    <h2 class="text-xl font-bold mt-6 mb-2">Hutang Belum Lunas</h2>
    <table class="border-collapse border w-full">
        <tr class="bg-gray-200">
            <th class="border p-2">Jumlah (Rp)</th>
            <th class="border p-2">Keterangan</th>
            <th class="border p-2">Aksi</th>
        </tr>
        <?php while ($row = $pending_debts->fetch_assoc()): ?>
        <tr>
            <td class="border p-2">Rp <?= number_format($row['amount'], 2) ?></td>
            <td class="border p-2"><?= $row['description'] ?></td>
            <td class="border p-2">
                <a href="edit_debt.php?id=<?= $row['id'] ?>" class="bg-yellow-500 text-white p-1 rounded">✏ Edit</a>
                <a href="mark_paid.php?id=<?= $row['id'] ?>" class="bg-green-500 text-white p-1 rounded">✔ Lunas</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Tabel Hutang Lunas -->
    <h2 class="text-xl font-bold mt-6 mb-2">Hutang Lunas</h2>
    <table class="border-collapse border w-full">
        <tr class="bg-gray-200">
            <th class="border p-2">Jumlah (Rp)</th>
            <th class="border p-2">Keterangan</th>
        </tr>
        <?php while ($row = $paid_debts->fetch_assoc()): ?>
        <tr>
            <td class="border p-2">Rp <?= number_format($row['amount'], 2) ?></td>
            <td class="border p-2"><?= $row['description'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
