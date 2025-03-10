<?php
include "../../config.php";

// Ambil Data Hutang
$id = $_GET['id'];
$debt = $conn->query("SELECT * FROM debts WHERE id = $id")->fetch_assoc();

// Proses Update
if (isset($_POST['update_debt'])) {
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    $sql = "UPDATE debts SET amount = '$amount', description = '$description' WHERE id = $id";
    $conn->query($sql);
    header("Location: debts.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Hutang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <h1 class="text-2xl font-bold mb-4">Edit Hutang</h1>
    <form method="POST">
        <input type="number" name="amount" value="<?= $debt['amount'] ?>" required class="border p-2 mr-2">
        <input type="text" name="description" value="<?= $debt['description'] ?>" required class="border p-2 mr-2">
        <button type="submit" name="update_debt" class="bg-blue-500 text-white p-2">Update</button>
    </form>
</body>
</html>
