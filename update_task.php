<?php
include "config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'];

// Pastikan tugas milik user yang login
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    $_SESSION['error_message'] = "Tugas tidak ditemukan atau bukan milik Anda!";
    header("Location: task.php");
    exit();
}

// Update status tugas
$stmt = $pdo->prepare("UPDATE tasks SET status = 'Selesai', completed_at = NOW() WHERE id = ? AND user_id = ?");
$success = $stmt->execute([$task_id, $user_id]);

if ($success) {
    $_SESSION['success_message'] = "Tugas berhasil diselesaikan!";
} else {
    $_SESSION['error_message'] = "Gagal menyelesaikan tugas!";
}

header("Location: task.php");
exit();
?>
