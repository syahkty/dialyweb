<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Hapus akun user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();

        // Hapus semua sesi aktif di server
        $session_path = session_save_path();
        foreach (glob("$session_path/sess_*") as $file) {
            unlink($file);
        }

        // Hancurkan sesi saat ini
        session_destroy();

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>
