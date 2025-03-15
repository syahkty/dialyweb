<?php
require "../../config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Gunakan prepared statement untuk mencegah SQL Injection
    $sql = "UPDATE debts SET status = 'Lunas' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}

header("Location: debts.php");
exit();
?>
