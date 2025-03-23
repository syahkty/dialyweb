<?php
session_start();
require '../config.php'; // Pastikan konfigurasi database sudah benar

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $_POST['bio'] ?? ''; // Pastikan nilai tidak null
    $user_id = $_SESSION['user_id'];

    // Gunakan prepared statement dengan PDO
    $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
    
    if ($stmt->execute([$bio, $user_id])) {
        $_SESSION['success_message'] = "Bio berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui bio!";
    }

    header("Location: profile.php");
    exit;
}
?>
