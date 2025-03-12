<?php
require '../../config.php'; // Pastikan koneksi database

// Ambil Data Hutang
$id = $_GET['id'];
$debt = $conn->query("SELECT * FROM debts WHERE id = $id")->fetch_assoc();

// Proses Update
if (isset($_POST['update_debt'])) {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    
    // Jika `due_date` kosong, gunakan tanggal saat ini
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : date('Y-m-d');

    // Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("UPDATE debts SET amount = ?, description = ?, due_date = ? WHERE id = ?");
    $stmt->bind_param("dssi", $amount, $description, $due_date, $id);

    if ($stmt->execute()) {
        header("Location: debts.php"); // Redirect ke halaman daftar hutang
        exit();
    } else {
        echo "Gagal memperbarui hutang!";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Hutang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex justify-center items-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-4 text-gray-900 dark:text-gray-100">Edit Hutang</h1>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-medium">Jumlah Hutang</label>
                <input type="number" name="amount" value="<?= $debt['amount'] ?>" required 
                    class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 dark:text-gray-300 font-medium">Deskripsi</label>
                <input type="text" name="description" value="<?= $debt['description'] ?>" required 
                    class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" name="update_debt" 
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-lg transition">
                Update
            </button>
        </form>
    </div>
</body>
</html>

