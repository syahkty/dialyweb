<?php
include "config.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'];

// Update status tugas jika milik user yang login
$stmt = $conn->prepare("UPDATE tasks SET status = 'Selesai' WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();

header("Location: task.php");
exit();
?>
